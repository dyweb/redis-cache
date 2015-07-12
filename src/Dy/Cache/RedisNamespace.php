<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/12
 * Time: 19:16
 */

namespace Dy\Cache;

use Dy\Redis\ClientInterface as Client;

/**
 * Class RedisNamespace
 * The class manages namespaces and corresponding key sets in Redis cache.
 *
 * @see RedisRepository
 * @package Dy\Cache
 */
final class RedisNamespace
{

    /**
     * Redis client.
     *
     * @var Client
     */
    protected $client = null;

    /**
     * Namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * Name of the set storing all the cached key names.
     * The function will be disabled if an empty value is given.
     *
     * @var string
     */
    protected $keySetName = '';

    /**
     * Keys set buffer.
     *
     * @var array
     */
    protected $keyRecords = array();

    /**
     * Whether to enable lazy record of the key name set.
     * If used, when a session ends, the set will be updated.
     *
     * @var bool
     */
    protected $lazyRecord = false;

    /**
     * Constructor.
     *
     * @param string $namespace
     * @param Client $client
     * @param string $keySetName
     * @param bool $lazyRecord
     */
    public function __construct($namespace, $client, $keySetName = '', $lazyRecord = false)
    {
        $this->namespace = $namespace;
        $this->client = $client;
        $this->keySetName = $keySetName;
        $this->lazyRecord = $lazyRecord;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->lazyRecord) {
            $this->flushRecord();
        }
    }

    /**
     * Record the key into the namespace key set.
     *
     * @param string $key
     */
    public function recordKey($key)
    {
        $this->keyRecords[$key] = true;
        if (!$this->lazyRecord) {
            $this->flushRecord();
        }
    }

    /**
     * Remove the key from the namespace key set.
     *
     * @param string $key
     */
    public function deleteKey($key)
    {
        $this->keyRecords[$key] = false;
        if (!$this->lazyRecord) {
            $this->flushRecord();
        }
    }

    /**
     * Get all the keys in the cache.
     * Note: This function is time-consuming if $keySetName is not set,
     * so you should execute it when few users access the server.
     *
     * @return array
     */
    public function getAllKeys()
    {
        $this->flushRecord();
        if (!empty($this->keySetName)) {
            return $this->client->smembers($this->getKeyName($this->keySetName));
        } else {
            return $this->client->keys(preg_quote($this->namespace) . '*');
        }
    }

    /**
     * Clear all the keys in the cache.
     * Note: This function is time-consuming, so you should
     * execute it when few users access the server.
     */
    public function clearAllKeys()
    {
        if (!empty($this->keySetName)) {
            $keys = $this->getAllKeys();
            if (count($keys) > 0) {
                $this->client->del($keys);
                $this->client->srem($this->getKeyName($this->keySetName), $keys);
            }
        } else {
            $regexp = preg_quote($this->namespace) . '*';
            $cursor = 0;
            do {
                $result = $this->client->scan($cursor, array('match' => $regexp));
                $cursor = $result[0];
                if (!empty($result[1])) {
                    $this->client->del($result[1]);
                }
            } while ($cursor != 0);
        }
        $this->keyRecords = array();
    }

    /**
     * Flush the key sets buffer and update the set in Redis.
     */
    public function flushRecord()
    {
        if (empty($this->keySetName)) {
            return;
        }

        $addedKeys = array();
        $deletedKeys = array();
        foreach ($this->keyRecords as $key => $status) {
            if ($status) {
                $addedKeys[] = $key;
            } else {
                $deletedKeys[] = $key;
            }
        }

        // only Redis 2.4+ supported
        if (count($addedKeys) > 0) {
            $this->client->sadd($this->getKeyName($this->keySetName), $addedKeys);
        }
        if (count($deletedKeys) > 0) {
            $this->client->srem($this->getKeyName($this->keySetName), $deletedKeys);
        }
        $this->keyRecords = array();
    }

    /**
     * Get the name of the namespace.
     *
     * @return string
     */
    public function toString()
    {
        return $this->namespace;
    }

    /**
     * Get the actual key name stored in Redis.
     *
     * @param string $key
     * @return string
     */
    public function getKeyName($key)
    {
        return $this->getPrefix() . $key;
    }

    /**
     * Get the key prefix.
     *
     * @return string
     */
    protected function getPrefix()
    {
        return ($this->namespace === '') ? '' : $this->namespace . ':';
    }
}
