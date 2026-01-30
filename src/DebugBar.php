<?php

namespace Maharlika\Debugbar;

use Maharlika\Debugbar\DataCollector\CollectorInterface;
use Maharlika\Debugbar\Storage\FileStorage;

class DebugBar
{
    protected array $collectors = [];
    protected ?FileStorage $storage = null;
    protected bool $enabled = true;
    protected array $config = [];
    protected string $requestId;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->enabled = $config['enabled'] ?? true;
        $this->requestId = $this->generateRequestId();

        if ($config['storage']['enabled'] ?? false) {
            $this->storage = new FileStorage(
                $config['storage']['path'],
                $config['storage']['max_files'] ?? 20
            );
        }
    }

    public function addCollector(CollectorInterface $collector): void
    {
        $this->collectors[$collector->getName()] = $collector;
    }

    public function getCollector(string $name): ?CollectorInterface
    {
        return $this->collectors[$name] ?? null;
    }

    public function hasCollector(string $name): bool
    {
        return isset($this->collectors[$name]);
    }

    public function collect(): array
    {
        $data = [
            'id' => $this->requestId,
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s'),
            'collectors' => [],
        ];

        foreach ($this->collectors as $name => $collector) {
            try {
                $data['collectors'][$name] = $collector->getData();
            } catch (\Throwable $e) {
                $data['collectors'][$name] = [
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $data;
    }

    public function save(): void
    {
        if (!$this->storage || !$this->enabled) {
            return;
        }

        $data = $this->collect();
        $this->storage->save($this->requestId, $data);
    }

    public function getData(?string $id = null): ?array
    {
        if (!$this->storage) {
            return null;
        }

        if ($id === null) {
            $id = $this->requestId;
        }

        return $this->storage->get($id);
    }

    public function getAllData(): array
    {
        if (!$this->storage) {
            return [];
        }

        return $this->storage->all();
    }

    public function clearStorage(): void
    {
        if ($this->storage) {
            $this->storage->clear();
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    protected function generateRequestId(): string
    {
        return uniqid('dbg_', true) . '_' . time();
    }

    public function reset(): void
    {
        foreach ($this->collectors as $collector) {
            $collector->reset();
        }
        $this->requestId = $this->generateRequestId();
    }
}