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
 * Class Redis
 *
 * The class is a facade of a RedisCache instance.
 *
 * @static
 * @package Dy\Cache
 *
 * @method static mixed  setNamespace(string $namespace)
 * @method static string getNamespace(string $namespace)
 * @method static mixed  put(string $key, mixed $value, int $minutes)
 * @method static mixed  get(string $key, mixed $default = null)
 * @method static bool   has(string $key)
 * @method static mixed  pull(string $key)
 * @method static mixed  forever(string $key, mixed $value)
 * @method static bool   del(string $key)
 * @method static bool   forget(string $key)
 */
final class Redis extends SingletonFacade
{
    /**
     * Config list.
     *
     * @var array
     */
    protected static $config = array(
        'host' => '127.0.0.1',
        'port' => 6379
    );

    /**
     * Set the connection config. Only valid before executing other
     * functions of this class.
     *
     * @param array $config
     */
    public static function config(array $config)
    {
        static::$config = $config;
    }

    /**
     * Create a RedisCache instance.
     *
     * @return RedisCache
     */
    protected static function createInstance()
    {
        return new RedisCache(static::$config);
    }
}
