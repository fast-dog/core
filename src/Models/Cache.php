<?php

namespace FastDog\Core\Models;

/**
 * Class Cache
 *
 * @package FastDog\Core\Models
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Cache
{
    protected $cache = null;

    /**
     * Cache constructor.
     * @param \Illuminate\Support\Facades\Cache $cache
     */
    public function __construct(\Illuminate\Support\Facades\Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param $key
     * @param $callback
     * @param array $tag
     * @return mixed
     */
    public function get($key, $callback, $tag = ['all'])
    {
        $isRedis = config('cache.default') == 'redis';
        $result = null;

        if ($key && config('cache.enabled')) {// если передан ключ и включено кэширование, пытаемся получить кеш
            $result = ($isRedis) ? $this->cache->tags($tag)->get($key, null) : $this->cache->get($key, null);
        }

        if (null === $result) {
            $result = $callback();// кэша нет, выполняем замыкание
            if ($key && config('cache.enabled')) {
                if ($isRedis) {
                    $this->cache->tags($tag)->put($key, $result, config('cache.ttl_core', 5));
                } else {
                    $this->cache->put($key, $result, config('cache.ttl_core', 5));
                }
            }
        }

        return $result;
    }
}