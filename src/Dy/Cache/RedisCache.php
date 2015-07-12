<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/12
 * Time: 0:24
 */

namespace Dy\Cache;

use Closure;
use Dy\Redis\ClientInterface as Client;
use Dy\Redis\PredisClient;

final class RedisCache
{
    /**
     * Redis client.
     * @var Client
     */
    protected $client;

    /**
     * Cache key namespace.
     * @var string
     */
    protected $namespace = '';

    /**
     * Nmae of the set key storing all the cached key names.
     * If this variable is empty, the function will be disabled.
     * @var string
     */
    protected $keySetName = '';

    /**
     * Switch of memory cache. If true, the cache got from Redis
     * will be stored in memory until the session exits.
     *
     * @var bool
     */
    protected $memoryCache = false;

    /**
     * Hash table for memory cache.
     *
     * @var array
     */
    protected $memoryHash = array();

    public function __construct(array $config)
    {
        $this->client = new PredisClient($config);
        if (isset($config['namespace'])) {
            $this->namespace = $config['namespace'] . ':';
        }
        if (isset($config['keyset_name'])) {
            $this->keySetName = trim($config['keyset_name']);
        }
        if (isset($config['memory_cache'])) {
            $this->memoryCache = (bool)$config['memory_cache'];
        }
    }

    public function __destruct()
    {
        $this->client->quit();
    }

    /**
     * @param string $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        if (!empty($this->namespace)) {
            $this->namespace .= ':';
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return empty($this->namespace) ? '' :
            substr($this->namespace, 0, strlen($this->namespace) - 1);
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setMemoryCacheSwitch($value)
    {
        $this->memoryCache = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     */
    public function getMemoryCacheSwitch()
    {
        return $this->memoryCache;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $minutes
     * @return $this
     */
    public function put($key, $value, $minutes)
    {
        $key = $this->getKeyName($key);
        $value = is_numeric($value) ? $value : serialize($value);
        $this->client->setex($key, $value, $minutes * 60);
        $this->recordKey($key, $value);
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $minutes
     * @return $this
     */
    public function set($key, $value, $minutes)
    {
        $this->put($key, $value, $minutes);
        return $this;
    }

    /**
     * @param string $key
     * @param mixed|Closure $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $cachedValue = $this->retrieveCachedKey($key);
        if ($cachedValue !== null) {
            return $cachedValue;
        }

        $value = $this->client->get($this->getKeyName($key));
        $value = $value !== null ?
            (is_numeric($value) ? $value : unserialize($value)) :
            ($default instanceof Closure ? $default() : $default);
        $this->recordKey($key, $value);
        return $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        $cachedValue = $this->retrieveCachedKey($key);
        if ($cachedValue !== null) {
            return true;
        }
        return $this->client->exists($this->getKeyName($key)) === 1;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function pull($key)
    {
        $value = $this->get($key);
        $this->del($key);
        return $value;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function forever($key, $value)
    {
        $key = $this->getKeyName($key);
        $value = is_numeric($value) ? $value : serialize($value);
        $this->client->set($key, $value);
        $this->recordKey($key, $value);
        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function del($key)
    {
        $key = $this->getKeyName($key);
        $this->recordDeletedKey($key);
        return $this->client->del(array($key)) === 1;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function forget($key)
    {
        return $this->del($key);
    }

    /**
     * @param string $key
     * @param int $value
     * @return int
     */
    public function increment($key, $value = 1)
    {
        $value = $this->client->incrby($this->getKeyName($key), $value);
        $this->recordKey($key, $value);
        return $value;
    }

    /**
     * @param string $key
     * @param int $value
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        $value = $this->client->decrby($this->getKeyName($key), $value);
        $this->recordKey($key, $value);
        return $value;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getKeyName($key)
    {
        return $this->namespace . $key;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function recordKey($key, $value)
    {
        $setKey = $this->getKeyName($this->keySetName);
        $this->client->sadd($setKey, $key);
        $this->cacheKey($key, $value);
    }

    /**
     * @param string $key
     */
    protected function recordDeletedKey($key)
    {
        $setKey = $this->getKeyName($this->keySetName);
        $this->client->srem($setKey, array($key));
        $this->cacheKey($key, null);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function cacheKey($key, $value)
    {
        if (!$this->memoryCache) {
            return;
        }
        $this->memoryHash[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function retrieveCachedKey($key)
    {
        if (!$this->memoryCache || !isset($this->memoryHash[$key])) {
            return null;
        }
        return $this->memoryHash[$key];
    }
}
