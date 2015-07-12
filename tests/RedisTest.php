<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/10
 * Time: 21:32
 */

use Dy\Cache\RedisCache;

final class RedisTest extends PHPUnit_Framework_TestCase
{

    public function __construct()
    {
        RedisCache::config(array(
            'namespace' => array(
                'name' => 'dy:cache:test',
                'key_set_name' => 'keys',
                'lazy_record' => true
            ),
            'memory_cache' => true
        ));
    }

    public function testPut()
    {
        RedisCache::put('test', 'aaa', 1);
        RedisCache::put('test2', '123', 1);
    }

    public function testSet()
    {
        $this->assertEquals(RedisCache::get('test'), 'aaa');
        $this->assertEquals(RedisCache::get('test2'), '123');
    }

}
