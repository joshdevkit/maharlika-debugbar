<?php

namespace Maharlika\Debugbar\Facades;

use Maharlika\Facades\Facade;

/**
 * @method static void addCollector(\Maharlika\Debugbar\DataCollector\CollectorInterface $collector)
 * @method static \Maharlika\Debugbar\DataCollector\CollectorInterface|null getCollector(string $name)
 * @method static bool hasCollector(string $name)
 * @method static array collect()
 * @method static void save()
 * @method static array|null getData(string $id = null)
 * @method static array getAllData()
 * @method static void clearStorage()
 * @method static bool isEnabled()
 * @method static void enable()
 * @method static void disable()
 * @method static string getRequestId()
 * @method static void reset()
 *
 * @see \Maharlika\Debugbar\DebugBar
 */
class Debugbar extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'debugbar';
    }
}