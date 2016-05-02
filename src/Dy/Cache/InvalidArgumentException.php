<?php
/**
 * Created by PhpStorm.
 * User: bluemit
 * Date: 16-5-2
 * Time: 下午7:54
 */
namespace Dy\Cache;

use Exception;

class InvalidArgumentException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction()
    {
        echo "Invalid argument exception\n";
    }
}
