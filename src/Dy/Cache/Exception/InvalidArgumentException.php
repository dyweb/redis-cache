<?php
/**
 * Created by PhpStorm.
 * User: bluemit
 * Date: 16-5-2
 * Time: 下午7:54.
 */
namespace Dy\Cache\Exception;

use Exception;
use InvalidArgumentException as PhpInvalidArgumentException;
use Psr\Cache\InvalidArgumentException as Psr6InvalidArgumentException;

/**
 * Class InvalidArgumentException
 * Exception class for argument errors in redis-cache.
 *
 * @cover
 */
class InvalidArgumentException extends PhpInvalidArgumentException implements Psr6InvalidArgumentException
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }
}
