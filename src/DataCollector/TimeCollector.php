<?php

namespace Maharlika\Debugbar\DataCollector;

class TimeCollector implements CollectorInterface
{
    protected float $startTime;
    protected array $measures = [];

    public function __construct()
    {
        $this->startTime = defined('MAHARLIKA_START') ? MAHARLIKA_START : microtime(true);
    }

    public function getName(): string
    {
        return 'time';
    }

    public function startMeasure(string $name, string $label = null): void
    {
        $this->measures[$name] = [
            'label' => $label ?? $name,
            'start' => microtime(true),
            'end' => null,
            'duration' => null,
        ];
    }

    public function stopMeasure(string $name): void
    {
        if (isset($this->measures[$name])) {
            $this->measures[$name]['end'] = microtime(true);
            $this->measures[$name]['duration'] = 
                ($this->measures[$name]['end'] - $this->measures[$name]['start']) * 1000;
        }
    }

    public function measure(string $name, callable $callback, string $label = null): mixed
    {
        $this->startMeasure($name, $label);
        $result = $callback();
        $this->stopMeasure($name);
        return $result;
    }

    public function getData(): array
    {
        $totalTime = (microtime(true) - $this->startTime) * 1000;

        return [
            'total' => round($totalTime, 2),
            'measures' => array_map(function ($measure) {
                return [
                    'label' => $measure['label'],
                    'duration' => $measure['duration'] ? round($measure['duration'], 2) : null,
                ];
            }, $this->measures),
        ];
    }

    public function reset(): void
    {
        $this->measures = [];
        $this->startTime = microtime(true);
    }

    public function getTotalTime(): float
    {
        return (microtime(true) - $this->startTime) * 1000;
    }
}