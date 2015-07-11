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