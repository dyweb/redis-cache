<?php
/**
 * Created by PhpStorm.
 * User: bluemit
 * Date: 16-4-23
 * Time: 下午8:38.
 */
namespace Dy\Cache\Psr;

use Dy\Cache\Exception\InvalidArgumentException;
use Dy\Cache\RedisRepository;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class Pool
 * CacheItemPoolInterface generates CacheItemInterface objects.
 */
class Pool implements CacheItemPoolInterface
{
    /**
     * A RedisRepository instance this Item used.
     *
     * @var RedisRepository
     */
    private $redisRepository;

    /**
     * Constructor.
     *
     * @param RedisRepository
     */
    public function __construct($redisRepository)
    {
        $this->redisRepository = $redisRepository;
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
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *                    The key for which to return the corresponding Cache Item.
     *
     * @throws InvalidArgumentException
     *                                  If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *                                  MUST be thrown.
     *
     * @return Item
     *              The corresponding Cache Item.
     */
    public function getItem($key)
    {
        $this->checkKeyName($key);

        $item = new Item($this->redisRepository, $key);

        return $item;
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param array $keys
     *                    An indexed array of keys of items to retrieve.
     *
     * @throws InvalidArgumentException
     *                                  If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *                                  MUST be thrown.
     *
     * @return array|\Traversable
     *                            A traversable collection of Cache Items keyed by the cache keys of
     *                            each item. A Cache item will be returned for each key, even if that
     *                            key is not found. However, if no keys are specified then an empty
     *                            traversable MUST be returned instead.
     */
    public function getItems(array $keys = array())
    {
        foreach ($keys as $key) {
            $this->checkKeyName($key);
        }

        // $this in closure can only be used in PHP 5.4+
        $repository = $this->getRepository();

        return array_map(function ($key) use ($repository) {
            return new Item($repository, $key);
        }, $keys);
    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key
     *                    The key for which to check existence.
     *
     * @throws InvalidArgumentException
     *                                  If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *                                  MUST be thrown.
     *
     * @return bool
     *              True if item exists in the cache, false otherwise.
     */
    public function hasItem($key)
    {
        $this->checkKeyName($key);

        $item = new Item($this->redisRepository, $key);

        return $item->isHit();
    }

    /**
     * Deletes all items in the pool.
     *
     * @return bool
     *              True if the pool was successfully cleared. False if there was an error.
     */
    public function clear()
    {
        try {
            $this->redisRepository->clearAll();

            return true;
        } catch (\RuntimeException $exception) {
            return false;
        }
    }

    /**
     * Removes the item from the pool.
     *
     * @param string $key
     *                    The key for which to delete
     *
     * @throws InvalidArgumentException
     *                                  If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *                                  MUST be thrown.
     *
     * @return bool
     *              True if the item was successfully removed. False if there was an error.
     */
    public function deleteItem($key)
    {
        $this->checkKeyName($key);

        return $this->redisRepository->del($key);
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param array $keys
     *                    An array of keys that should be removed from the pool.
     *
     * @throws InvalidArgumentException
     *                                  If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *                                  MUST be thrown.
     *
     * @return bool
     *              True if the items were successfully removed. False if there was an error.
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->checkKeyName($key);
        }

        foreach ($keys as $key) {
            $this->redisRepository->del($key);
        }
    }

    /**
     * Persists a cache item immediately.
     *
     * @param CacheItemInterface $item
     *                                 The cache item to save.
     *
     * @return bool
     *              True if the item was successfully persisted. False if there was an error.
     */
    public function save(CacheItemInterface $item)
    {
        // Save the item permanently if it is not from this lib
        if (!$item instanceof Item) {
            try {
                $this->redisRepository->forever($item->getKey(), $item->get());

                return true;
            } catch (\RuntimeException $exception) {
                return false;
            }
        }

        try {
            $item->save();

            return true;
        } catch (\RuntimeException $exception) {
            return false;
        }
    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param CacheItemInterface $item
     *                                 The cache item to save.
     *
     * @return bool
     *              False if the item could not be queued or if a commit was attempted and failed. True otherwise.
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        return $this->save($item);
    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     *              True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit()
    {
        return true;
    }

    /**
     * Check whether the key name is legal.
     *
     * @param $key
     */
    private function checkKeyName($key)
    {
        // Try creating a new item
        new Item($this->redisRepository, $key);
    }
}
