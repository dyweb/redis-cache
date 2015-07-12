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

/**
 * Class RedisRepository
 * The class is the core implementation of Redis cache.
 *
 * @package Dy\Cache
 */
final class RedisRepository
{
    /**
     * Redis client.
     *
     * @var Client
     */
    protected $client = null;

    /**
     * Cache key namespace.
     *
     * @var RedisNamespace
     */
    protected $namespace = null;

    /**
     * Name of the set storing all the cached key names.
     *
     * @var string
     */
    protected $keySetName = '';

    /**
     * Whether to enable lazy record of the key name set.
     * If used, when a session ends, the set will be updated.
     *
     * @var bool
     */
    protected $namespaceLazyRecord = false;

    /**
     * Switch of memory cache. If true, the cache got from Redis
     * will be stored in memory until the session exits.
     *
     * @var bool
     */
    protected $enableMemoryCache = false;

    /**
     * Hash table for memory cache.
     *
     * @var MemoryRepository
     */
    protected $memoryCache = null;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->client = new PredisClient($config['connection']);
        $this->namespaceLazyRecord = (bool)$config['namespace']['lazy_record'];
        $this->keySetName = $config['namespace']['key_set_name'];
        $this->setNamespace($config['namespace']['name']);
        $this->enableMemoryCache = $config['memory_cache'];
        if ($this->enableMemoryCache) {
            $this->memoryCache = new MemoryRepository();
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->clearNamespace();
    }

    /**
     * Switch to a new namespace.
     *
     * @param string      $namespace    Namespace.
     * @param bool|null   $lazyRecord   Whether to enable lazy record of the key name set.
     *                                  If not given, the default config will be used.
     * @return $this
     */
    public function setNamespace($namespace, $lazyRecord = null)
    {
        if ($lazyRecord === null) {
            $lazyRecord = $this->namespaceLazyRecord;
        }
        $this->clearNamespace();
        $this->namespace = new RedisNamespace(
            $namespace,
            $this->client,
            $this->keySetName,
            $lazyRecord
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace->toString();
    }

    /**
     * @return $this
     */
    public function enableMemoryCache()
    {
        $this->enableMemoryCache = true;
        if ($this->memoryCache === null) {
            $this->memoryCache->clearAll();
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function disableMemoryCache()
    {
        $this->enableMemoryCache = false;
        $this->memoryCache = null;
        return $this;
    }

    /**
     * @return bool
     */
    public function usingMemoryCache()
    {
        return $this->enableMemoryCache;
    }

    /**
     * Put the data into the cache.
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $minutes Expired time.
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
     * Put the data into the cache, the same as put()
     *
     * @see put()
     * @param string $key
     * @param mixed  $value
     * @param int    $minutes Expired time.
     * @return $this
     */
    public function set($key, $value, $minutes)
    {
        $this->put($key, $value, $minutes);
        return $this;
    }

    /**
     * Get data from cache, if the key does not exist,
     * $default(value or Closure) will be replaced.
     *
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
     * Return whether the key exists in the cache.
     *
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
     * Retrieve a key from the cache and delete it.
     *
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
     * Save the data persistently into the cache
     *
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
     * Delete a key from the cache.
     *
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
     * Delete a key from the cache, the same as del()
     *
     * @param string $key
     * @return bool
     */
    public function forget($key)
    {
        return $this->del($key);
    }

    /**
     * Increase the value of the key by $value.
     *
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
     * Decrease the value of the key by $value.
     *
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
     * Get all the keys in the cache.
     * Note: This function is time-consuming if $keySetName is not set,
     * so you should execute it when few users access the server.
     *
     * @see RedisNamespace::getAllKeys()
     * @return array
     */
    public function getAllKeys()
    {
        return $this->namespace->getAllKeys();
    }

    /**
     * Get all the keys of a namespace in the cache.
     * Note: This function is time-consuming if $keySetName is not set,
     * so you should execute it when few users access the server.
     *
     * @see RedisNamespace::getAllKeys()
     * @param string $namespace
     * @return array
     */
    public function keysByNamespace($namespace)
    {
        if ($this->namespace->toString() == $namespace) {
            return $this->getAllKeys();
        }
        $namespaceObj = new RedisNamespace($namespace, $this->client);
        $keys = $namespaceObj->getAllKeys();
        unset($namespaceObj);
        return $keys;
    }

    /**
     * Clear all the keys in the cache.
     * Note: This function is time-consuming, so you should
     * execute it when few users access the server.
     *
     * @see RedisNamespace::clearAllKeys()
     */
    public function clearAll()
    {
        $this->namespace->clearAllKeys();
        if ($this->memoryCache !== null) {
            $this->memoryCache->clearAll();
        }
        return $this;
    }

    /**
     * Clear all the keys of a namespace in the cache.
     * Note: This function is time-consuming, so you should
     * execute it when few users access the server.
     *
     * @see RedisNamespace::clearAllKeys()
     * @param string $namespace
     * @return $this
     */
    public function delByNamespace($namespace)
    {
        if ($this->namespace->toString() == $namespace) {
            $this->clearAll();
            return $this;
        }
        $namespaceObj = new RedisNamespace($namespace, $this->client);
        $namespaceObj->clearAllKeys();
        unset($namespaceObj);
        return $this;
    }

    /**
     * Save and clear the current namespace.
     */
    protected function clearNamespace()
    {
        if ($this->namespace !== null) {
            $this->namespace->flushRecord();
            unset($this->namespace);
        }

        if ($this->memoryCache !== null) {
            $this->memoryCache->clearAll();
        }
    }

    /**
     * Get the actual name of the key stored in Redis.
     *
     * @see RedisNamespace::getKeyName()
     * @param string $key
     * @return string
     */
    protected function getKeyName($key)
    {
        return $this->namespace->getKeyName($key);
    }

    /**
     * Record the key into the namespace key set.
     *
     * @see RedisNamespace::recordKey()
     * @param string $key
     * @param mixed $value
     */
    protected function recordKey($key, $value)
    {
        $this->namespace->recordKey($key);
        $this->cacheKey($key, $value);
    }

    /**
     * Remove the key from the namespace key set.
     *
     * @see RedisNamespace::deleteKey()
     * @param string $key
     */
    protected function recordDeletedKey($key)
    {
        $this->namespace->deleteKey($key);
        $this->cacheKey($key, null);
    }

    /**
     * Put the key into memory cache.
     *
     * @see MemoryRepository::put()
     * @param string $key
     * @param mixed $value
     */
    protected function cacheKey($key, $value)
    {
        if (!$this->enableMemoryCache) {
            return;
        }
        $this->memoryCache->put($key, $value);
    }

    /**
     * Get the key from memory cache.
     *
     * @see MemoryRepository::get()
     * @param string $key
     * @return mixed
     */
    protected function retrieveCachedKey($key)
    {
        if (!$this->enableMemoryCache) {
            return null;
        }
        return $this->memoryCache->get($key);
    }
}
