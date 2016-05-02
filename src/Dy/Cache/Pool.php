<?php
/**
 * Created by PhpStorm.
 * User: bluemit
 * Date: 16-4-23
 * Time: 下午8:38
 */
namespace Dy\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * CacheItemPoolInterface generates CacheItemInterface objects.
 */
class Pool
{
    /**
     * A RedisRepository instance this Item used.
     *
     * @var RedisRepository
     */
    protected $redisRepository;


    /**
     * Constructor.
     * @param RedisRepository
     */
    public function __construct($redisRepository)
    {
        $this->redisRepository=$redisRepository;
    }


    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return CacheItemInterface
     *   The corresponding Cache Item.
     */
    public function getItem($key)
    {
        if (!isset($key[1]) && strlen($key) < 1) {
            throw new InvalidArgumentException("Invalid Argument!");
        }
        $item= new Item($this->redisRepository, $key);
        $item->set($this->redisRepository->get($key));
        return $item;
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param array $keys
     * An indexed array of keys of items to retrieve.
     *
     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return array|\Traversable
     *   A traversable collection of Cache Items keyed by the cache keys of
     *   each item. A Cache item will be returned for each key, even if that
     *   key is not found. However, if no keys are specified then an empty
     *   traversable MUST be returned instead.
     */
    public function getItems(array $keys = array())
    {
        $items=[];
        foreach ($keys as $key) {
            if (!isset($key[1]) && strlen($key) < 1) {
                throw new InvalidArgumentException("Invalid Argument!");
            }
            $item= new item($this->redisRepository, $key);
            $item->set($this->redisRepository->get($key));
            $items[] = $item;
        }
        return $items;
    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key
     *    The key for which to check existence.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *  True if item exists in the cache, false otherwise.
     */
    public function hasItem($key)
    {
        if (!isset($key[1]) && strlen($key) < 1) {
            throw new InvalidArgumentException("Invalid Argument!");
        }
        $item=new Item($this->redisRepository, $key);
        return $item->isHit();
    }

    /**
     * Deletes all items in the pool.
     *
     * @return bool
     *   True if the pool was successfully cleared. False if there was an error.
     */
    public function clear()
    {
        return $this->redisRepository->clearAll();
    }

    /**
     * Removes the item from the pool.
     *
     * @param string $key
     *   The key for which to delete
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the item was successfully removed. False if there was an error.
     */
    public function deleteItem($key)
    {
        return $this->redisRepository->del($key);
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param array $keys
     *   An array of keys that should be removed from the pool.

     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the items were successfully removed. False if there was an error.
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->redisRepository->del($key);
        }

    }

    /**
     * Persists a cache item immediately.
     *
     * @param Item $item
     *   The cache item to save.
     *
     * @return bool
     *   True if the item was successfully persisted. False if there was an error.
     */
    public function save(Item $item)
    {
        return $item->save();
    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param Item $item
     *   The cache item to save.
     *
     * @return bool
     *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
     */
    public function saveDeferred(Item $item)
    {
        return $this->save($item);
    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit()
    {
        return true;
    }
}
