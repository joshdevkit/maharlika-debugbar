<?php

namespace Maharlika\Debugbar;

use Maharlika\Providers\ServiceProvider;
use Maharlika\Debugbar\DataCollector\TimeCollector;
use Maharlika\Debugbar\DataCollector\MemoryCollector;
use Maharlika\Debugbar\DataCollector\RequestCollector;
use Maharlika\Debugbar\DataCollector\DatabaseCollector;
use Maharlika\Debugbar\DataCollector\ViewCollector;
use Maharlika\Debugbar\DataCollector\RouteCollector;
use Maharlika\Debugbar\DataCollector\LogCollector;
use Maharlika\Debugbar\DataCollector\SessionCollector;
use Maharlika\Debugbar\Middleware\InjectDebugbar;

class DebugbarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../config/debugbar.php', 'debugbar');

        // Register the main DebugBar instance
        $this->app->singleton('debugbar', function ($app) {
            $config = config('debugbar', []);
            $debugbar = new DebugBar($config);

            // Register collectors
            if ($config['collectors']['time'] ?? true) {
                $debugbar->addCollector(new TimeCollector());
            }

            if ($config['collectors']['memory'] ?? true) {
                $debugbar->addCollector(new MemoryCollector());
            }

            if ($config['collectors']['request'] ?? true) {
                $debugbar->addCollector(new RequestCollector());
            }

            if ($config['collectors']['database'] ?? true) {
                $debugbar->addCollector(new DatabaseCollector());
            }

            if ($config['collectors']['views'] ?? true) {
                $debugbar->addCollector(new ViewCollector());
            }

            if ($config['collectors']['route'] ?? true) {
                $debugbar->addCollector(new RouteCollector());
            }

            if ($config['collectors']['logs'] ?? true) {
                $debugbar->addCollector(new LogCollector());
            }

            if ($config['collectors']['session'] ?? true) {
                $debugbar->addCollector(new SessionCollector());
            }

            return $debugbar;
        });

        $this->app->singleton(DebugBar::class, function ($app) {
            return $app->get('debugbar');
        });
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/debugbar.php' => config_path('debugbar.php'),
        ], 'debugbar-config');

        // Register views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'debugbar');

        // Hook into database query events if available
        $this->hookDatabaseQueries();

        // Hook into view rendering if available
        $this->hookViewRendering();

        // Hook into logging if available
        $this->hookLogging();

        // Hook into session if available
        $this->hookSession();
    }

    protected function hookDatabaseQueries(): void
    {
        if (!$this->app->has('db') || !config('debugbar.collectors.database', true)) {
            return;
        }

        try {
            $debugbar = $this->app->get('debugbar');
            $collector = $debugbar->getCollector('database');

            if (!$collector) {
                return;
            }

            // Try to hook into the database connection
            $db = $this->app->get('db');
            
            if (method_exists($db, 'listen')) {
                $db->listen(function ($query) use ($collector) {
                    $collector->addQuery(
                        $query->sql ?? '',
                        $query->bindings ?? [],
                        $query->time ?? 0,
                        $query->connectionName ?? 'default'
                    );
                });
            }
        } catch (\Throwable $e) {
            // Silently fail if database hooks aren't available
        }
    }

    protected function hookViewRendering(): void
    {
        if (!$this->app->has('view') || !config('debugbar.collectors.views', true)) {
            return;
        }

        try {
            $debugbar = $this->app->get('debugbar');
            $collector = $debugbar->getCollector('views');

            if (!$collector) {
                return;
            }

            $view = $this->app->get('view');
            
            // Hook into view composer
            if (method_exists($view, 'composer')) {
                $view->composer('*', function ($view) use ($collector) {
                    $startTime = microtime(true);
                    
                    register_shutdown_function(function () use ($view, $collector, $startTime) {
                        $renderTime = (microtime(true) - $startTime) * 1000;
                        $collector->addView(
                            $view->getName() ?? 'unknown',
                            $view->getPath() ?? '',
                            $view->getData() ?? [],
                            $renderTime
                        );
                    });
                });
            }
        } catch (\Throwable $e) {
            // Silently fail if view hooks aren't available
        }
    }

    protected function hookLogging(): void
    {
        if (!config('debugbar.collectors.logs', true)) {
            return;
        }

        try {
            $debugbar = $this->app->get('debugbar');
            $collector = $debugbar->getCollector('logs');

            if (!$collector && $this->app->has('log')) {
                // Hook into logger if available
                // This would require the logger to support listeners
            }
        } catch (\Throwable $e) {
            // Silently fail
        }
    }

    protected function hookSession(): void
    {
        if (!config('debugbar.collectors.session', true)) {
            return;
        }

        try {
            $debugbar = $this->app->get('debugbar');
            $collector = $debugbar->getCollector('session');

            if ($collector && $this->app->has('session')) {
                $session = $this->app->get('session');
                if (method_exists($session, 'all')) {
                    $collector->setSessionData($session->all());
                }
            }
        } catch (\Throwable $e) {
            // Silently fail
        }
    }

    protected function mergeConfigFrom(string $path, string $key): void
    {
        if (!file_exists($path)) {
            return;
        }

        $config = require $path;
        $existing = config($key, []);
        
        config([$key => array_merge($config, $existing)]);
    }


    protected function loadViewsFrom(string $path, string $namespace): void
    {
        if ($this->app->has('view.finder')) {
            $finder = $this->app->get('view.finder');
            if (method_exists($finder, 'addNamespace')) {
                $finder->addNamespace($namespace, $path);
            }
        }
    }
}