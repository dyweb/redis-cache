<?php

namespace Dy\Common;

/**
 * Class Facade
 *
 * The class is a facade of inner instances to use syntax like Class::method().
 *
 * @static
 * @package Dy\Common
 */
abstract class Facade
{
    /**
     * Disable the constructor.
     */
    public function __construct()
    {
        throw new \RuntimeException('Static class cannot be instanced');
    }

    /**
     * Get the instance behind the facade.
     *
     * @return mixed
     */
    protected static function getInstance()
    {
        throw new \RuntimeException('Calling abstract method');
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array   $args
     * @return mixed
     */
    public static function __callStatic($method, array $args)
    {
        $instance = static::getInstance();

        // performance optimization, copied from Laravel
        switch (count($args)) {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);
            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array([$instance, $method], $args);
        }
    }
}
