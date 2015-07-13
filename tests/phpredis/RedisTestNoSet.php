<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/10
 * Time: 21:32
 */

use Dy\Cache\RedisCache;

final class RedisTestNoSet extends PHPUnit_Framework_TestCase
{
    protected $prefix = 'dy:cache:test';

    protected $config = null;

    public function __construct()
    {
        parent::__construct();
        $this->config = array(
            'connection' => array(
                'client' => 'redis',
                'schema' => 'tcp',
                'host' => '127.0.0.1',
                'port' => 6379,
            ),
            'namespace' => array(
                'name' => $this->prefix,
                'key_set_name' => '',
                'lazy_record' => false
            ),
            'memory_cache' => false
        );
        RedisCache::config($this->config);
    }

    public function testPut()
    {
        RedisCache::put('test', 'aaa', 1/60);
        RedisCache::put('test2', '123', 1);
        RedisCache::set('test3', 'abc', 10);
    }

    public function testGet()
    {
        sleep(2);
        $this->assertEquals(null, RedisCache::get('test'));
        $this->assertEquals(123, RedisCache::get('test2'));
        $this->assertEquals('abc', RedisCache::get('test3'));
        $this->assertEquals(null, RedisCache::get('nullkey'));
    }

    public function testGetDefault()
    {
        $this->assertEquals('abc', RedisCache::get('test3', 1));
        $this->assertEquals(888, RedisCache::get('nullkey', 888));
        $this->assertEquals('888', RedisCache::get('nullkey2', '888'));
        $this->assertEquals('a', RedisCache::get('nullkey3', function () {
            static $i = '';
            return $i .= 'a';
        }));
    }

    public function testHas()
    {
        $this->assertTrue(RedisCache::has('test2'));
        $this->assertFalse(RedisCache::has('nullkey'));
        $this->assertFalse(RedisCache::has('hastest'));
    }

    public function testPull()
    {
        $this->assertEquals('abc', RedisCache::pull('test3'));
        $this->assertNull(RedisCache::pull('test3'));
        $this->assertNull(RedisCache::pull('pulltest'));
    }

    public function testForever()
    {
        RedisCache::set('foreverkey', 'leave you', 1/60);
        $this->assertEquals('leave you', RedisCache::get('foreverkey'));
        RedisCache::forever('foreverkey', 'love you');
        sleep(2);
        $this->assertEquals('love you', RedisCache::get('foreverkey'));
    }

    public function testDel()
    {
        RedisCache::set('deltest', 'abc', 1);
        $this->assertTrue(RedisCache::del('test2'));
        $this->assertTrue(RedisCache::del('deltest'));
        $this->assertFalse(RedisCache::del('delnullkey'));
    }

    public function testForget()
    {
        RedisCache::set('forgettest', 'abc', 1);
        $this->assertFalse(RedisCache::forget('delnullkey'));
        $this->assertTrue(RedisCache::forget('forgettest'));
    }

    public function testIncrement()
    {
        $this->assertEquals(1, RedisCache::increment('incr_test'));
        $this->assertEquals(3, RedisCache::increment('incr_test', 2));
        $this->assertEquals(2, RedisCache::increment('incr2_test', 2));
    }

    /**
     * @expectedException \Exception
     */
    public function testIncrementFailed()
    {
        RedisCache::set('incrtest', 'abc', 2);
        RedisCache::increment('incrtest', 2);
    }

    public function testDecrement()
    {
        $this->assertEquals(-1, RedisCache::decrement('decr_test'));
        $this->assertEquals(-3, RedisCache::decrement('decr_test', 2));
        $this->assertEquals(-2, RedisCache::decrement('decr2_test', 2));
    }

    /**
     * @expectedException \Exception
     */
    public function testDecrementFailed()
    {
        RedisCache::set('decrtest', 'abc', 2);
        RedisCache::decrement('decrtest', 2);
    }

    public function testFluentInterface()
    {
        $this->assertEquals('yes', RedisCache::put('fluent', 'yes', 1)->get('fluent'));
        $this->assertEquals('yes', RedisCache::forever('fluent2', 'yes')->get('fluent2'));
    }

    public function testGetAll()
    {
        $keys = RedisCache::getAllKeys();
        $this->assertContains($this->prefix . ':incr_test', $keys);
        $this->assertContains($this->prefix . ':incr2_test', $keys);
        $this->assertContains($this->prefix . ':decr_test', $keys);
        $this->assertContains($this->prefix . ':decr2_test', $keys);
        $this->assertContains($this->prefix . ':foreverkey', $keys);
    }

    public function testClearAll()
    {
        $keys = RedisCache::clearAll()->getAllKeys();
        $this->assertEmpty($keys);
    }

    public function testKeysByNamespace()
    {
        $client = RedisCache::client();
        $client->set('dy:other:test:haha', 'haha');
        $client->set('dy:other:test:pipi', 'hhhh');
        if (!empty($this->config['namespace']['key_set_name'])) {
            $client->sadd('dy:other:test:' . $this->config['namespace']['key_set_name'], array(
                'dy:other:test:haha', 'dy:other:test:pipi'
            ));
        }

        RedisCache::set('myspace', true, 1);
        $keys = RedisCache::keysByNamespace('dy:other:test');
        $this->assertContains('dy:other:test:haha', $keys);
        $this->assertContains('dy:other:test:pipi', $keys);

        $this->assertTrue(RedisCache::has('myspace'));
    }

    public function testDelByNamespace()
    {
        $client = RedisCache::client();
        $client->set('dy:other2:test:haha', 'haha');
        $client->set('dy:other2:test:pipi', 'hhhh');
        if (!empty($this->config['namespace']['key_set_name'])) {
            $client->sadd('dy:other2:test:' . $this->config['namespace']['key_set_name'], array(
                'dy:other2:test:haha', 'dy:other2:test:pipi'
            ));
        }

        RedisCache::set('myspace2', true, 1);
        $this->assertEquals(0, count(RedisCache::delByNamespace('dy:other2:test')->keysByNamespace('dy:other2:test')));

        $this->assertTrue(RedisCache::has('myspace2'));
    }

    public function testSetNamespace()
    {
        $this->assertEquals(123, RedisCache::setNamespace('dy:space')->set('test', 123, 1)->get('test'));
        $this->assertFalse(RedisCache::del('myspace'));
        $this->assertEquals(true, unserialize(RedisCache::client()->get($this->prefix . ':myspace')));
        RedisCache::setNamespace($this->prefix);
        $this->assertTrue(RedisCache::has('myspace2'));
    }

    public function testGetNamespace()
    {
        $this->assertEquals($this->prefix, RedisCache::getNamespace());
    }

    public function testMemoryCache()
    {
        RedisCache::enableMemoryCache();
        RedisCache::put('mtest', 123, 1);
        RedisCache::put('mtest2', '123', 1);
        RedisCache::put('mtest3', 'test', 1);

        $this->assertEquals(123, RedisCache::get('mtest'));
        $this->assertEquals(123, RedisCache::get('mtest2'));
        $this->assertEquals('test', RedisCache::get('mtest3'));
        $this->assertEquals(null, RedisCache::get('mtest4'));
        $this->assertEquals(null, RedisCache::get('mtest4'));

        $i = '';
        $this->assertTrue(RedisCache::del('mtest3'));
        $this->assertEquals('a', RedisCache::get('mtest3', function () use (&$i) {
            $i .= 'a';
            return $i;
        }));
        $this->assertEquals('aa', RedisCache::get('mtest3', function () use (&$i) {
            $i .= 'a';
            return $i;
        }));

        $this->assertEquals(3, RedisCache::get('mtest4', 3));
        $this->assertEquals(4, RedisCache::get('mtest4', 4));
        RedisCache::put('mtest4', 5, 1);
        $this->assertEquals(5, RedisCache::get('mtest4', 6));

        $this->assertEquals(5, RedisCache::disableMemoryCache()->get('mtest4', 7));
    }

    public function testFinish()
    {
        $client = RedisCache::client();
        $regexp = 'dy\:*';
        $cursor = 0;
        do {
            $result = $client->scan($cursor, $regexp);
            $cursor = $result[0];
            if (!empty($result[1])) {
                $client->del($result[1]);
            }
        } while ($cursor != 0);
    }
}
