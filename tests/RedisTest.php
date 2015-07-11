<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/10
 * Time: 21:32
 */

use Dy\Cache\Redis as Redis;

class RedisTest extends PHPUnit_Framework_TestCase {
    public function __construct()
    {
        $this->redis = new Redis();
    }
}
