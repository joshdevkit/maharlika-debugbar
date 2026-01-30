<?php

namespace Maharlika\Debugbar\DataCollector;

interface CollectorInterface
{
    /**
     * Get the collector name.
     */
    public function getName(): string;

    /**
     * Get the collected data.
     */
    public function getData(): array;

    /**
     * Reset the collector.
     */
    public function reset(): void;
}