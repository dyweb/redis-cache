<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/12
 * Time: 21:51
 */

namespace Dy\Redis;

use Redis as Client;

/**
 * Class RedisClient
 *
 * php-redis implementation of redis client.
 *
 * @package Dy\Redis
 */
final class RedisClient implements ClientInterface
{
    /**
     * The redis instance.
     * @var Client
     */
    protected $redis;

    /**
     * Constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->redis = new Client();
        if ($config['schema'] == 'unix') {
            $this->redis->connect($config['path']);
        } else {
            if (!isset($config['timeout'])) {
                $config['timeout'] = 0;
            }
            $this->redis->connect($config['host'], $config['port'], $config['timeout']);
        }
        if (isset($config['password'])) {
            $this->redis->auth($config['password']);
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->quit();
    }

    /**
     * @inheritdoc
     */
    public function quit()
    {
        $this->redis->close();
    }

    /**
     * @inheritdoc
     * @param string $key
     * @return int
     */
    public function exists($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * @inheritdoc
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function set($key, $value)
    {
        return $this->redis->set($key, $value);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

    /**
     * @inheritdoc
     * @param array|mixed $keys
     * @return int
     */
    public function del($keys)
    {
        return $this->redis->del($keys);
    }

    /**
     * @inheritdoc
     * @param string $key
     * @return int
     */
    public function incr($key)
    {
        return $this->redis->incr($key);
    }

    /**
     * @inheritdoc
     * @param string $key
     * @param int $increment
     * @return int
     */
    public function incrby($key, $increment)
    {
        return $this->redis->incrBy($key, $increment);
    }

    /**
     * @inheritdoc
     * @param string $key
     * @return int
     */
    public function decr($key)
    {
        return $this->redis->decr($key);
    }

    /**
     * @inheritdoc
     * @param string $key
     * @param int $decrement
     * @return int
     */
    public function decrby($key, $decrement)
    {
        return $this->redis->decrBy($key, $decrement);
    }

    /**
     * @inheritdoc
     * @param string $pattern
     * @return array
     */
    public function keys($pattern)
    {
        return $this->redis->keys($pattern);
    }

    /**
     * @inheritdoc
     * @param int $cursor
     * @param string $pattern
     * @param int $count
     * @return array|bool
     */
    public function scan($cursor, $pattern = '', $count = 0)
    {
        return $this->redis->scan($cursor, $pattern, $count);
    }

    /**
     * @inheritdoc
     * @param string $key
     * @param array|mixed $members
     * @return int
     */
    public function sadd($key, $members)
    {
        if (!is_array($members)) {
            return $this->redis->sAdd($key, $members);
        }
        switch (count($members)) {
            case 1:
                return $this->redis->sAdd($key, $members[0]);
            case 2:
                return $this->redis->sAdd($key, $members[0], $members[1]);
            case 3:
                return $this->redis->sAdd($key, $members[0], $members[1], $members[2]);
            default:
                array_unshift($members, $key);
                return call_user_func_array(array($this->redis, 'sAdd'), $members);
        }
    }

    /**
     * @inheritdoc
     * @param string $key
     * @return int
     */
    public function scard($key)
    {
        return $this->redis->sCard($key);
    }

    /**
     * @inheritdoc
     * @param string $key
     * @param string $member
     * @return int
     */
    public function sismember($key, $member)
    {
        return $this->redis->sIsMember($key, $member);
    }

    /**
     * @inheritdoc
     * @param string $key
     * @return array
     */
    public function smembers($key)
    {
        return $this->redis->sMembers($key);
    }

    /**
     * @inheritdoc
     * @param string $key
     * @param array|mixed $member
     * @return int
     */
    public function srem($key, $member)
    {
        if (!is_array($member)) {
             return $this->redis->sAdd($key, $member);
        }
        switch (count($member)) {
            case 1:
                return $this->redis->sRem($key, $member[0]);
            case 2:
                return $this->redis->sRem($key, $member[0], $member[1]);
            case 3:
                return $this->redis->sRem($key, $member[0], $member[1], $member[2]);
            default:
                array_unshift($member, $key);
                return call_user_func_array(array($this->redis, 'sRem'), $member);
        }
    }

    /**
     * @inheritdoc
     * @param string $key
     * @param int $cursor
     * @param string $pattern
     * @param int $count
     * @return array|bool
     */
    public function sscan($key, $cursor, $pattern = '', $count = 0)
    {
        return $this->redis->sScan($key, $cursor, $pattern, $count);
    }
}
