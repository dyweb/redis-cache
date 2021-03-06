<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/12
 * Time: 0:51.
 */
namespace Dy\Redis;

use Predis\Client;

/**
 * Class PredisClient.
 *
 * Predis implementation of redis client.
 */
final class PredisClient implements ClientInterface
{
    /**
     * The redis instance.
     *
     * @var Client
     */
    protected $redis;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->redis = new Client($config);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->quit();
    }

    /**
     * {@inheritdoc}
     */
    public function quit()
    {
        $this->redis->quit();
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return (bool) $this->redis->exists($key);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        $this->redis->set($key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @param int    $seconds
     * @param string $value
     */
    public function setex($key, $seconds, $value)
    {
        $this->redis->setex($key, $seconds, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     *
     * @return string|false
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

    /**
     * {@inheritdoc}
     *
     * @param array|mixed $keys
     *
     * @return int
     */
    public function del($keys)
    {
        return $this->redis->del($keys);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     *
     * @return int
     */
    public function incr($key)
    {
        return $this->redis->incr($key);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @param int    $increment
     *
     * @return int
     */
    public function incrby($key, $increment)
    {
        return $this->redis->incrby($key, $increment);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     *
     * @return int
     */
    public function decr($key)
    {
        return $this->redis->decr($key);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @param int    $decrement
     *
     * @return int
     */
    public function decrby($key, $decrement)
    {
        return $this->redis->decrby($key, $decrement);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $pattern
     *
     * @return array
     */
    public function keys($pattern)
    {
        return $this->redis->keys($pattern);
    }

    /**
     * {@inheritdoc}
     *
     * @param int    $cursor
     * @param string $pattern
     * @param int    $count
     *
     * @return array
     */
    public function scan($cursor, $pattern = '', $count = 0)
    {
        $option = array();
        if ($pattern != '') {
            $option['match'] = $pattern;
        }
        if ($count != 0) {
            $option['count'] = $count;
        }

        return $this->redis->scan($cursor, $option);
    }

    /**
     * {@inheritdoc}
     *
     * @param string      $key
     * @param array|mixed $members
     *
     * @return int
     */
    public function sadd($key, $members)
    {
        return $this->redis->sadd($key, $members);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     *
     * @return int
     */
    public function scard($key)
    {
        return $this->redis->scard($key);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @param string $member
     *
     * @return bool
     */
    public function sismember($key, $member)
    {
        return (bool) $this->redis->sismember($key, $member);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     *
     * @return array
     */
    public function smembers($key)
    {
        return $this->redis->smembers($key);
    }

    /**
     * {@inheritdoc}
     *
     * @param string      $key
     * @param array|mixed $member
     *
     * @return int
     */
    public function srem($key, $member)
    {
        return $this->redis->srem($key, $member);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @param int    $cursor
     * @param string $pattern
     * @param int    $count
     *
     * @return array
     */
    public function sscan($key, $cursor, $pattern = '', $count = 0)
    {
        $options = array();
        if ($pattern != '') {
            $option['match'] = $pattern;
        }
        if ($count != 0) {
            $option['count'] = $count;
        }

        return $this->redis->sscan($key, $cursor, $options);
    }
}
