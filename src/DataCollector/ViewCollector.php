<?php

namespace Maharlika\Debugbar\DataCollector;

class ViewCollector implements CollectorInterface
{
    protected array $views = [];
    protected int $totalViews = 0;

    public function getName(): string
    {
        return 'views';
    }

    public function addView(string $name, string $path, array $data = [], float $renderTime = 0): void
    {
        $this->views[] = [
            'name' => $name,
            'path' => $path,
            'data' => $this->formatData($data),
            'data_count' => count($data),
            'render_time' => round($renderTime, 2),
        ];

        $this->totalViews++;
    }

    public function getData(): array
    {
        return [
            'views' => $this->views,
            'total_views' => $this->totalViews,
        ];
    }

    protected function formatData(array $data): array
    {
        $formatted = [];

        foreach ($data as $key => $value) {
            $formatted[$key] = [
                'type' => $this->getType($value),
                'value' => $this->formatValue($value),
            ];
        }

        return $formatted;
    }

    protected function getType(mixed $value): string
    {
        if (is_object($value)) {
            return get_class($value);
        }

        return gettype($value);
    }

    protected function formatValue(mixed $value): string
    {
        if (is_null($value)) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }
            return get_class($value);
        }

        return gettype($value);
    }

    public function reset(): void
    {
        $this->views = [];
        $this->totalViews = 0;
    }
}