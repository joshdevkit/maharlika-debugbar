<?php

namespace Maharlika\Debugbar\DataCollector;

class LogCollector implements CollectorInterface
{
    protected array $logs = [];

    public function getName(): string
    {
        return 'logs';
    }

    public function addLog(string $level, string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'time' => microtime(true),
        ];
    }

    public function getData(): array
    {
        return [
            'logs' => $this->logs,
            'count' => count($this->logs),
            'by_level' => $this->groupByLevel(),
        ];
    }

    protected function groupByLevel(): array
    {
        $grouped = [
            'DEBUG' => 0,
            'INFO' => 0,
            'NOTICE' => 0,
            'WARNING' => 0,
            'ERROR' => 0,
            'CRITICAL' => 0,
            'ALERT' => 0,
            'EMERGENCY' => 0,
        ];

        foreach ($this->logs as $log) {
            $level = $log['level'];
            if (isset($grouped[$level])) {
                $grouped[$level]++;
            }
        }

        return array_filter($grouped);
    }

    public function reset(): void
    {
        $this->logs = [];
    }
}