<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/12
 * Time: 0:24
 */

namespace Dy\Cache;

use Dy\Redis\ClientInterface as Client;
use Dy\Redis\PredisClient;

final class RedisCache
{
    /**
     * Redis client.
     * @var Client
     */
    protected $client;

    /**
     * Cache key namespace.
     * @var string
     */
    protected $namespace = '';

    public function __construct(array $config)
    {
        $this->client = new PredisClient($config);
        if (isset($config['namespace'])) {
            $this->namespace = $config['namespace'] . ':';
        }
    }

    public function __destruct()
    {
        $this->client->quit();
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace . ':';
    }

    public function getNamespace()
    {
        return empty($this->namespace) ? '' :
            substr($this->namespace, 0, strlen($this->namespace) - 1);
    }

    public function put($key, $value, $minutes)
    {
        $key = $this->getKeyName($key);
        $value = is_numeric($value) ? $value : serialize($value);
        $this->client->setex($key, $value, $minutes * 60);
    }

    public function get($key, $default = null)
    {
        $value = $this->client->get($this->getKeyName($key));
        return $value != null ? (is_numeric($value) ? $value : unserialize($value)) : $default;
    }

    public function has($key)
    {
        return $this->client->exists($this->getKeyName($key)) === 1;
    }

    public function pull($key)
    {
        $value = $this->get($key);
        $this->del($key);
        return $value;
    }

    public function forever($key, $value)
    {
        $key = $this->getKeyName($key);
        $value = is_numeric($value) ? $value : serialize($value);
        $this->client->set($key, $value);
    }

    public function del($key)
    {
        $key = $this->getKeyName($key);
        return $this->client->del(array($key)) === 1;
    }

    public function forget($key)
    {
        return $this->del($key);
    }

    protected function getKeyName($key)
    {
        return $this->namespace . $key;
    }

}