<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/10
 * Time: 21:32
 */

use Dy\Cache\Redis;

class RedisTest extends PHPUnit_Framework_TestCase {

    public function testSet()
    {
        Redis::put('test', '123', 1);
    }

}
