<?php

/**
 * Created by PhpStorm.
 * User: bluemit
 * Date: 16-4-20
 * Time: 下午2:53
 */

namespace Dy\Cache;

class Item
{
    /**
     * A RedisRepository instance this Item used.
     *
     * @var RedisRepository
     */
    protected $redisRepository;

    /**
     * Cache Item key name.
     *
     * @var string
     */
    protected $key;

    /**
     * Cache Item value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Cache Item expire time.
     *
     * @var int
     */
    protected $minute;



    /**
     * Constructor.
     *
     * @param RedisRepository, a RedisRepository instance
     * @param string, Cache Item key name
     * @param int, Cache Item expire time
     */
    public function __construct($redisRepository, $key, $minute = 10000)
    {
        $this->redisRepository = $redisRepository;
        $this->key = $key;
        $this->minute = $minute;
    }


    /**
     * Returns the key for the current cache item.
     *
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string|false
     *   The key string for this cache item.
     */
    public function getKey()
    {
        return isset($this->key) ? $this->key : false;
    }

    /**
     * Retrieves the value of the item from the cache associated with this object's key.
     *
     * The value returned must be identical to the value originally stored by set().
     *
     * If isHit() returns false, this method MUST return null. Note that null
     * is a legitimate cached value, so the isHit() method SHOULD be used to
     * differentiate between "null value was found" and "no value was found."
     *
     * @return mixed
     *   The value corresponding to this cache item's key, or null if not found.
     */
    public function get()
    {
        return $this->redisRepository->get($this->key);
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     *
     * Note: This method MUST NOT have a race condition between calling isHit()
     * and calling get().
     *
     * @return bool
     *   True if the request resulted in a cache hit. False otherwise.
     */
    public function isHit()
    {
        return $this->redisRepository->has($this->key);
    }


    /**
     * Sets the value represented by this cache item.
     *
     * The $value argument may be any item that can be serialized by PHP,
     * although the method of serialization is left up to the Implementing
     * Library.
     *
     * @param mixed $value
     *   The serializable value to be stored.
     *
     * @return static
     *   The invoked object.
     */
    public function set($value)
    {
        if (!isset($this->key)) {
            return false;
        }
        $this->value = $value;
        $this->redisRepository->put($this->key, $this->value, $this->minute);
        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param \DateTimeInterface $expiration
     *   The point in time after which the item MUST be considered expired.
     *   If null is passed explicitly, a default value MAY be used. If none is set,
     *   the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAt($expiration)
    {
        $date = new \DateTime();
        $this->minute = ($expiration-$date)/60;
        if (!isset($this->value) )
        {
            $this->value=0;
        }
        $this->redisRepository->put($this->key, $this->value, $this->minute);
        $this->value=null;
        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param int|\DateInterval $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAfter($time)
    {
        $this->minute = $time;
        if (!isset($this->value) )
        {
            $this->value=0;
        }
        $this->redisRepository->put($this->key, $this->value, $this->minute);
        $this->value=null;
        return $this;
    }

    /**
     * Save the Item to the database;
     *
     * @return static
     *   The called object.
     */
    public function save()
    {
        return $this->redisRepository->put($this->key, $this->value, $this->minute);
    }
}
