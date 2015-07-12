<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/10
 * Time: 23:30
 */

namespace Dy\Cache;

use Dy\Common\SingletonFacade;

/**
 * Class RedisCache
 *
 * The class is a facade of a RedisRepository instance.
 *
 * @static
 * @package Dy\Cache
 * @see RedisRepository
 *
 * @method static RedisRepository   setNamespace(string $namespace, mixed $lazyRecord = null)
 * @method static string            getNamespace(string $namespace)
 * @method static RedisRepository   enableMemoryCache()
 * @method static RedisRepository   disableMemoryCache()
 * @method static bool              usingMemoryCache()
 * @method static RedisRepository   put(string $key, mixed $value, int $minutes)
 * @method static RedisRepository   set(string $key, mixed $value, int $minutes)
 * @method static mixed             get(string $key, mixed $default = null)
 * @method static bool              has(string $key)
 * @method static mixed             pull(string $key)
 * @method static RedisRepository   forever(string $key, mixed $value)
 * @method static bool              del(string $key)
 * @method static bool              forget(string $key)
 * @method static int               increment(string $key, int $value = 1)
 * @method static int               decrement(string $key, int $value = 1)
 * @method static array             getAllKeys()
 * @method static array             keysByNamespace(string $namespace)
 * @method static RedisRepository   clearAll()
 * @method static RedisRepository   delByNamespace(string $namespace)
 */
final class RedisCache extends SingletonFacade
{
    /**
     * Config list.
     *
     * @var array
     */
    protected static $config = array(
        'connection' => array(
            //'schema' => 'unix',
            //'path' =>'/var/run/redis.sock',
            'client' => 'predis',
            'schema' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'timeout' => 5.0
        ),
        'namespace' => array(
            'name' => '',
            'key_set_name' => '',
            'lazy_record' => false
        ),
        'memory_cache' => false
    );

    /**
     * Set the connection config. Only valid before executing other
     * functions of this class.
     *
     * @param array $config
     */
    public static function config(array $config)
    {
        if (isset($config['connection'])) {
            static::$config['connection'] = $config;
        }
        static::$config = array_merge(static::$config, $config);
    }

    /**
     * Create a RedisRepository instance.
     *
     * @return RedisRepository
     */
    protected static function createInstance()
    {
        return new RedisRepository(static::$config);
    }
}
