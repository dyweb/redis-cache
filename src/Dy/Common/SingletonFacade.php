<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/12
 * Time: 0:14
 */

namespace Dy\Common;

/**
 * Class SingletonFacade
 * The class is a singleton implementation of Facade. It can be inherited
 * to realize customized facades.
 *
 * @static
 * @package Dy\Common
 */
abstract class SingletonFacade extends Facade
{

    protected static $instance;

    /**
     * Create a new singleton instance and return the instance.
     * The method should be inherited to initialize the new instance.
     *
     * @return mixed
     */
    protected static function createInstance()
    {
        throw new \RuntimeException('Calling abstract method');
    }

    /**
     * Get the instance behind the facade.
     *
     * @return mixed
     */
    protected static function getInstance()
    {
        if (static::$instance != NULL)
            return static::$instance;
        return static::$instance = static::createInstance();
    }

}