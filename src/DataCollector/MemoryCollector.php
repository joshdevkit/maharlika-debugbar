<?php

namespace Maharlika\Debugbar\DataCollector;

class MemoryCollector implements CollectorInterface
{
    protected array $measurements = [];

    public function getName(): string
    {
        return 'memory';
    }

    public function addMeasurement(string $label, int $memory): void
    {
        $this->measurements[] = [
            'label' => $label,
            'memory' => $memory,
            'formatted' => $this->formatBytes($memory),
        ];
    }

    public function getData(): array
    {
        $peakMemory = memory_get_peak_usage(true);
        $currentMemory = memory_get_usage(true);

        return [
            'peak' => $peakMemory,
            'peak_formatted' => $this->formatBytes($peakMemory),
            'current' => $currentMemory,
            'current_formatted' => $this->formatBytes($currentMemory),
            'measurements' => $this->measurements,
        ];
    }

    public function reset(): void
    {
        $this->measurements = [];
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}