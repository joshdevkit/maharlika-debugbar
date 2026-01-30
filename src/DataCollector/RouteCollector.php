<?php

namespace Maharlika\Debugbar\DataCollector;

class RouteCollector implements CollectorInterface
{
    protected ?array $route = null;
    protected array $routes = [];

    public function getName(): string
    {
        return 'route';
    }

    public function setCurrentRoute(array $route): void
    {
        $this->route = $route;
    }

    public function setAllRoutes(array $routes): void
    {
        $this->routes = $routes;
    }

    public function getData(): array
    {
        $data = [
            'current' => $this->route,
            'total_routes' => count($this->routes),
        ];

        if ($this->route) {
            $data['current'] = [
                'uri' => $this->route['uri'] ?? 'unknown',
                'name' => $this->route['name'] ?? null,
                'action' => $this->route['action'] ?? null,
                'method' => $this->route['method'] ?? [],
                'middleware' => $this->route['middleware'] ?? [],
                'parameters' => $this->route['parameters'] ?? [],
                'controller' => $this->getControllerInfo($this->route),
            ];
        }

        return $data;
    }

    protected function getControllerInfo(array $route): ?array
    {
        if (!isset($route['action'])) {
            return null;
        }

        $action = $route['action'];

        if (is_string($action)) {
            if (str_contains($action, '@')) {
                [$controller, $method] = explode('@', $action);
                return [
                    'class' => $controller,
                    'method' => $method,
                ];
            }
            return ['class' => $action];
        }

        if (is_array($action)) {
            return [
                'class' => is_object($action[0]) ? get_class($action[0]) : $action[0],
                'method' => $action[1] ?? null,
            ];
        }

        if (is_callable($action)) {
            return ['type' => 'Closure'];
        }

        return null;
    }

    public function reset(): void
    {
        $this->route = null;
        $this->routes = [];
    }
}