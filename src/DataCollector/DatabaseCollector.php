<?php

namespace Maharlika\Debugbar\DataCollector;

class DatabaseCollector implements CollectorInterface
{
    protected array $queries = [];
    protected int $totalQueries = 0;
    protected float $totalTime = 0;

    public function getName(): string
    {
        return 'database';
    }

    public function addQuery(string $sql, array $bindings = [], float $time = 0, string $connection = 'default'): void
    {
        $this->queries[] = [
            'sql' => $sql,
            'bindings' => $bindings,
            'time' => round($time, 2),
            'connection' => $connection,
            'formatted_sql' => $this->formatSql($sql, $bindings),
        ];

        $this->totalQueries++;
        $this->totalTime += $time;
    }

    public function getData(): array
    {
        return [
            'queries' => $this->queries,
            'total_queries' => $this->totalQueries,
            'total_time' => round($this->totalTime, 2),
            'duplicates' => $this->findDuplicates(),
        ];
    }

    protected function formatSql(string $sql, array $bindings): string
    {
        if (empty($bindings)) {
            return $sql;
        }

        // Replace ? placeholders with actual values
        $formattedSql = $sql;
        foreach ($bindings as $binding) {
            $value = $this->formatBinding($binding);
            $formattedSql = preg_replace('/\?/', $value, $formattedSql, 1);
        }

        return $formattedSql;
    }

    protected function formatBinding(mixed $binding): string
    {
        if (is_null($binding)) {
            return 'NULL';
        }

        if (is_bool($binding)) {
            return $binding ? 'TRUE' : 'FALSE';
        }

        if (is_numeric($binding)) {
            return (string) $binding;
        }

        if (is_string($binding)) {
            return "'" . addslashes($binding) . "'";
        }

        if (is_array($binding)) {
            return json_encode($binding);
        }

        return (string) $binding;
    }

    protected function findDuplicates(): array
    {
        $duplicates = [];
        $sqlCounts = [];

        foreach ($this->queries as $query) {
            $sql = $query['sql'];
            if (!isset($sqlCounts[$sql])) {
                $sqlCounts[$sql] = 0;
            }
            $sqlCounts[$sql]++;
        }

        foreach ($sqlCounts as $sql => $count) {
            if ($count > 1) {
                $duplicates[] = [
                    'sql' => $sql,
                    'count' => $count,
                ];
            }
        }

        return $duplicates;
    }

    public function reset(): void
    {
        $this->queries = [];
        $this->totalQueries = 0;
        $this->totalTime = 0;
    }
}