<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/12
 * Time: 0:33
 */

namespace Dy\Redis;

/**
 * Interface ClientInterface
 *
 * Interface for Redis connection and operation.
 *
 * @abstract
 * @package Dy\Redis
 */
interface ClientInterface
{
    /**
     * Close the Redis connection.
     */
    public function quit();

    /**
     * @param $key
     * @return int
     */
    public function exists($key);

    /**
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @param int $seconds
     * @param string $value
     * @return int
     */
    public function setex($key, $seconds, $value);

    /**
     * @param string $key
     * @return string
     */
    public function get($key);

    /**
     * @param array $keys
     * @return int
     */
    public function del(array $keys);

    /**
     * @param string $key
     * @return int
     */
    public function incr($key);

    /**
     * @param string $key
     * @param int $increment
     * @return int
     */
    public function incrby($key, $increment);

    /**
     * @param string $key
     * @return int
     */
    public function decr($key);

    /**
     * @param string $key
     * @param int $decrement
     * @return int
     */
    public function decrby($key, $decrement);

}