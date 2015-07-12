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
            'memory_cache' => false
        ));
    }

    public function testPut()
    {
        RedisCache::put('test', 'aaa', 1);
        RedisCache::put('test2', '123', 1);
    }

    public function testGet()
    {
        $this->assertEquals('aaa', RedisCache::get('test'));
        $this->assertEquals(123, RedisCache::get('test2'));
    }

}
