<?php

/**
 * Created by PhpStorm.
 * User: bluemit
 * Date: 16-4-20
 * Time: 下午2:53.
 */
namespace Dy\Cache\Psr;

use Dy\Cache\Exception\InvalidArgumentException;
use Dy\Cache\RedisRepository;
use Psr\Cache\CacheItemInterface;

/**
 * Class Item.
 */
class Item implements CacheItemInterface
{
    /**
     * A RedisRepository instance this Item used.
     *
     * @var RedisRepository
     */
    private $redisRepository;

    /**
     * Cache Item key name.
     *
     * @var string
     */
    private $key;

    /**
     * Cache Item value.
     *
     * @var mixed
     */
    private $value;

    /**
     * Cache Item ttl (time to live) in minutes.
     *
     * @var float
     */
    private $minute;

    /**
     * Constructor.
     *
     * @param RedisRepository $redisRepository a RedisRepository instance
     * @param string          $key             Cache Item key name
     * @param int             $minute          Cache Item expire time
     */
    public function __construct(RedisRepository $redisRepository, $key, $minute = null)
    {
        $this->setRepository($redisRepository);
        $this->setKey($key);
        $this->setExpiredMinute($minute);
    }

    /**
     * Get the redis repository bound to this pool.
     *
     * @return RedisRepository
     */
    public function getRepository()
    {
        return $this->redisRepository;
    }

    /**
     * Set the redis repository for the current item.
     *
     * @param RedisRepository $repository
     */
    protected function setRepository(RedisRepository $repository)
    {
        $this->redisRepository = $repository;
    }

    /**
     * Returns the key for the current cache item.
     *
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string
     *                The key string for this cache item.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the new key for the current cache item.
     * Note that the old item data will be kept.
     *
     * @param string $key The name of the key.
     *
     * @return static
     */
    public function setKey($key)
    {
        $this->checkKeyName($key);

        $this->key = $key;

        return $this;
    }

    /**
     * Check whether the key name is legal.
     *
     * @param $key
     */
    private function checkKeyName($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Illegal key name: '.strval($key));
        }

        if ($key == '') {
            throw new InvalidArgumentException('Illegal key name: '.strval($key));
        }

        if (preg_match('/[\\{\\}\\(\\)\\/\\\\@:]/', $key) !== 0) {
            throw new InvalidArgumentException('Illegal key name: '.strval($key));
        }
    }

    /**
     * Set the expired minute for the item. Changes will apply after the item is saved.
     *
     * @param float|null $minute
     *
     * @return static
     */
    protected function setExpiredMinute($minute)
    {
        $this->minute = $minute;

        return $this;
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
     *               The value corresponding to this cache item's key, or null if not found.
     */
    public function get()
    {
        if (is_null($this->value)) {
            $this->value = $this->getFromRepository();
        }

        return $this->value;
    }

    /**
     * Get the value from the redis repository.
     *
     * @return mixed
     *               The value corresponding to this cache item's key, or null if not found.
     */
    protected function getFromRepository()
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
     *              True if the request resulted in a cache hit. False otherwise.
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
     *                     The serializable value to be stored.
     *
     * @return static
     *                The invoked object.
     */
    public function set($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param \DateTimeInterface $expiration
     *                                       The point in time after which the item MUST be considered expired.
     *                                       If null is passed explicitly, a default value MAY be used. If none is set,
     *                                       the value should be stored permanently or for as long as the
     *                                       implementation allows.
     *
     * @return static
     *                The called object.
     */
    public function expiresAt($expiration)
    {
        if (!($expiration instanceof \DateTimeInterface || $expiration instanceof \DateTime)) {
            throw new InvalidArgumentException('expiration argument must inherit DateTimeInterface');
        }

        $now = new \DateTime();

        return $this->setExpiredMinute(($expiration->getTimestamp() - $now->getTimestamp()) / 60);
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param int|\DateInterval $time
     *                                The period of time from the present after which the item MUST be considered
     *                                expired. An integer parameter is understood to be the time in seconds until
     *                                expiration. If null is passed explicitly, a default value MAY be used.
     *                                If none is set, the value should be stored permanently or for as long as the
     *                                implementation allows.
     *
     * @return static
     *                The called object.
     */
    public function expiresAfter($time)
    {
        if (!is_int($time) && !($time instanceof \DateInterval)) {
            throw new InvalidArgumentException('time argument must be int or DateInterval');
        }

        // Convert DateInterval to minutes
        if ($time instanceof \DateInterval) {
            $now = new \DateTime();
            $end = clone $now;
            $end->add($time);
            $time = $end->getTimestamp() - $now->getTimestamp();
        }

        return $this->setExpiredMinute($time / 60);
    }

    /**
     * Save the Item to the database;.
     *
     * @return static
     *                The called object.
     */
    public function save()
    {
        if (is_null($this->minute)) {
            $this->redisRepository->forever($this->key, $this->value);
        } else {
            $this->redisRepository->put($this->key, $this->value, floatval($this->minute));
        }

        return $this;
    }
}
