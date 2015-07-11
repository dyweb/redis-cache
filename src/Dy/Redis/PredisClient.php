<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/12
 * Time: 0:51
 */

namespace Dy\Redis;

use Predis\Client;

/**
 * Class PredisClient
 *
 * Predis implementation of redis client.
 *
 * @package Dy\Redis
 */
final class PredisClient implements ClientInterface
{
    /**
     * The redis instance.
     * @var Client
     */
    protected $redis;

    public function __construct(array $config)
    {
        $this->redis = new Client($config);
    }

    /**
     * Close the Redis connection.
     */
    public function quit()
    {
        $this->redis->quit();
    }

    /**
     * @param $key
     * @return int
     */
    public function exists($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function set($key, $value)
    {
        return $this->redis->set($key, $value);
    }

    /**
     * @param string $key
     * @param int $seconds
     * @param string $value
     * @return int
     */
    public function setex($key, $seconds, $value)
    {
        return $this->redis->setex($key, $seconds, $value);
    }

    /**
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

    /**
     * @param array $keys
     * @return int
     */
    public function del(array $keys)
    {
        return $this->redis->del($keys);
    }

    /**
     * @param string $key
     * @return int
     */
    public function incr($key)
    {
        return $this->redis->incr($key);
    }

    /**
     * @param string $key
     * @param int $increment
     * @return int
     */
    public function incrby($key, $increment)
    {
        return $this->redis->incrby($key, $increment);
    }

    /**
     * @param string $key
     * @return int
     */
    public function decr($key)
    {
        return $this->redis->decr($key);
    }

    /**
     * @param string $key
     * @param int $decrement
     * @return int
     */
    public function decrby($key, $decrement)
    {
        return $this->redis->decrby($key, $decrement);
    }
}