<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/12
 * Time: 18:55.
 */
namespace Dy\Cache;

use Closure;

/**
 * Class MemoryRepository
 * Simple in-memory cache implementation. Cached data are disposed
 * after one session.
 */
final class MemoryRepository
{
    protected $hashTable = array();

    /**
     * Put data into cache.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function put($key, $value)
    {
        if ($value === null) {
            $this->del($key);
        } else {
            $this->hashTable[$key] = $value;
        }

        return $this;
    }

    /**
     * Set data into cache, the same as put().
     *
     * @see put()
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->put($key, $value);

        return $this;
    }

    /**
     * Get data from cache, if the key does not exist,
     * $default(value or Closure) will be replaced.
     *
     * @param string        $key
     * @param mixed|Closure $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = null;
        if (isset($this->hashTable[$key])) {
            $value = $this->hashTable[$key];
        }

        return $value !== null ? $value :
            ($default instanceof Closure ? $default() : $default);
    }

    /**
     * Return whether the key exists in the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->hashTable[$key]);
    }

    /**
     * Retrieve a key from the cache and delete it.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function pull($key)
    {
        $value = $this->get($key);
        $this->del($key);

        return $value;
    }

    /**
     * Delete a key from the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function del($key)
    {
        $exists = $this->has($key);
        if ($exists) {
            unset($this->hashTable[$key]);
        }

        return $exists;
    }

    /**
     * Delete a key from the cache, the same as del().
     *
     * @see del()
     *
     * @param string $key
     *
     * @return bool
     */
    public function forget($key)
    {
        return $this->del($key);
    }

    /**
     * Clear all keys in the cache.
     */
    public function clearAll()
    {
        $this->hashTable = array();
    }
}
