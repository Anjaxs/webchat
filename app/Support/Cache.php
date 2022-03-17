<?php

declare(strict_types=1);

namespace App\Support;

class Cache
{
    public static function __callStatic($method, $parameters)
    {
        $container = \Hyperf\Utils\ApplicationContext::getContainer();
        $cache = $container->get(\Psr\SimpleCache\CacheInterface::class);

        return $cache->$method(...$parameters);
    }
}