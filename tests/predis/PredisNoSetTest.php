<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/10
 * Time: 21:32.
 */
use Dy\Cache\RedisRepository;

class PredisNoSetTest extends PHPUnit_Framework_TestCase
{
    protected $prefix = 'dy:cache:ptest:noset';

    protected $config = null;

    protected $cache = null;

    public function __construct()
    {
        parent::__construct();
        $this->config = array(
            'connection' => array(
                'client' => 'predis',
                'schema' => 'tcp',
                'host'   => '127.0.0.1',
                'port'   => 6379,
            ),
            'namespace' => array(
                'name'         => $this->prefix,
                'key_set_name' => '',
                'lazy_record'  => false,
            ),
            'memory_cache' => false,
        );

        $this->cache = new RedisRepository($this->config);
    }

    public function testPut()
    {
        $this->cache->put('test', 'aaa', 1 / 60);
        $this->cache->put('test2', '123', 1);
        $this->cache->set('test3', 'abc', 10);
    }

    public function testGet()
    {
        sleep(2);
        $this->assertEquals(null, $this->cache->get('test'));
        $this->assertEquals(123, $this->cache->get('test2'));
        $this->assertEquals('abc', $this->cache->get('test3'));
        $this->assertEquals(null, $this->cache->get('nullkey'));
    }

    public function testGetDefault()
    {
        $this->assertEquals('abc', $this->cache->get('test3', 1));
        $this->assertEquals(888, $this->cache->get('nullkey', 888));
        $this->assertEquals('888', $this->cache->get('nullkey2', '888'));
        $this->assertEquals('a', $this->cache->get('nullkey3', function () {
            static $i = '';

            return $i .= 'a';
        }));
    }

    public function testHas()
    {
        $this->assertTrue($this->cache->has('test2'));
        $this->assertFalse($this->cache->has('nullkey'));
        $this->assertFalse($this->cache->has('hastest'));
    }

    public function testPull()
    {
        $this->assertEquals('abc', $this->cache->pull('test3'));
        $this->assertNull($this->cache->pull('test3'));
        $this->assertNull($this->cache->pull('pulltest'));
    }

    public function testForever()
    {
        $this->cache->set('foreverkey', 'leave you', 1 / 60);
        $this->assertEquals('leave you', $this->cache->get('foreverkey'));
        $this->cache->forever('foreverkey', 'love you');
        sleep(2);
        $this->assertEquals('love you', $this->cache->get('foreverkey'));
    }

    public function testDel()
    {
        $this->cache->set('deltest', 'abc', 1);
        $this->assertTrue($this->cache->del('test2'));
        $this->assertTrue($this->cache->del('deltest'));
        $this->assertFalse($this->cache->del('delnullkey'));
    }

    public function testForget()
    {
        $this->cache->set('forgettest', 'abc', 1);
        $this->assertFalse($this->cache->forget('delnullkey'));
        $this->assertTrue($this->cache->forget('forgettest'));
    }

    public function testIncrement()
    {
        $this->assertEquals(1, $this->cache->increment('incr_test'));
        $this->assertEquals(3, $this->cache->increment('incr_test', 2));
        $this->assertEquals(2, $this->cache->increment('incr2_test', 2));
    }

    /**
     * @expectedException \Exception
     */
    public function testIncrementFailed()
    {
        $this->cache->set('incrtest', 'abc', 2);
        $this->cache->increment('incrtest', 2);
    }

    public function testDecrement()
    {
        $this->assertEquals(-1, $this->cache->decrement('decr_test'));
        $this->assertEquals(-3, $this->cache->decrement('decr_test', 2));
        $this->assertEquals(-2, $this->cache->decrement('decr2_test', 2));
    }

    /**
     * @expectedException \Exception
     */
    public function testDecrementFailed()
    {
        $this->cache->set('decrtest', 'abc', 2);
        $this->cache->decrement('decrtest', 2);
    }

    public function testFluentInterface()
    {
        $this->assertEquals('yes', $this->cache->put('fluent', 'yes', 1)->get('fluent'));
        $this->assertEquals('yes', $this->cache->forever('fluent2', 'yes')->get('fluent2'));
    }

    public function testGetAll()
    {
        $keys = $this->cache->getAllKeys();
        $this->assertContains($this->prefix.':incr_test', $keys);
        $this->assertContains($this->prefix.':incr2_test', $keys);
        $this->assertContains($this->prefix.':decr_test', $keys);
        $this->assertContains($this->prefix.':decr2_test', $keys);
        $this->assertContains($this->prefix.':foreverkey', $keys);
        $keys2 = $this->cache->keysByNamespace($this->prefix);
        $this->assertEquals(count($keys), count($keys2));
    }

    public function testClearAll()
    {
        $keys = $this->cache->clearAll()->getAllKeys();
        $this->assertEmpty($keys);
    }

    public function testKeysByNamespace()
    {
        $client = $this->cache->client();
        $client->set('dy:other:test:haha', 'haha');
        $client->set('dy:other:test:pipi', 'hhhh');
        if (!empty($this->config['namespace']['key_set_name'])) {
            $client->sadd('dy:other:test:'.$this->config['namespace']['key_set_name'], array(
                'dy:other:test:haha', 'dy:other:test:pipi',
            ));
        }

        $this->cache->set('myspace', true, 1);
        $keys = $this->cache->keysByNamespace('dy:other:test');
        $this->assertContains('dy:other:test:haha', $keys);
        $this->assertContains('dy:other:test:pipi', $keys);

        $this->assertTrue($this->cache->has('myspace'));
    }

    public function testDelByNamespace()
    {
        $client = $this->cache->client();
        $client->set('dy:other2:test:haha', 'haha');
        $client->set('dy:other2:test:pipi', 'hhhh');
        if (!empty($this->config['namespace']['key_set_name'])) {
            $client->sadd('dy:other2:test:'.$this->config['namespace']['key_set_name'], array(
                'dy:other2:test:haha', 'dy:other2:test:pipi',
            ));
        }

        $this->cache->set('myspace2', true, 1);
        $this->assertEquals(0, count($this->cache->delByNamespace('dy:other2:test')->keysByNamespace('dy:other2:test')));

        $this->assertTrue($this->cache->has('myspace2'));
    }

    public function testSetNamespace()
    {
        $this->assertEquals(123, $this->cache->setNamespace('dy:space')->set('test', 123, 1)->get('test'));
        $this->assertFalse($this->cache->del('myspace'));
        $this->assertEquals(true, unserialize($this->cache->client()->get($this->prefix.':myspace')));
        $this->cache->setNamespace($this->prefix);
        $this->assertTrue($this->cache->has('myspace2'));
    }

    public function testGetNamespace()
    {
        $this->assertEquals($this->prefix, $this->cache->getNamespace());
    }

    public function testMemoryCache()
    {
        $this->cache->enableMemoryCache();
        $this->cache->put('mtest', 123, 1);
        $this->cache->put('mtest2', '123', 1);
        $this->cache->put('mtest3', 'test', 1);

        $this->assertEquals(123, $this->cache->get('mtest'));
        $this->assertEquals(123, $this->cache->get('mtest2'));
        $this->assertEquals('test', $this->cache->get('mtest3'));
        $this->assertEquals(null, $this->cache->get('mtest4'));
        $this->assertEquals(null, $this->cache->get('mtest4'));

        $i = '';
        $this->assertTrue($this->cache->del('mtest3'));
        $this->assertEquals('a', $this->cache->get('mtest3', function () use (&$i) {
            $i .= 'a';

            return $i;
        }));
        $this->assertEquals('aa', $this->cache->get('mtest3', function () use (&$i) {
            $i .= 'a';

            return $i;
        }));

        $this->assertEquals(3, $this->cache->get('mtest4', 3));
        $this->assertEquals(4, $this->cache->get('mtest4', 4));
        $this->cache->put('mtest4', 5, 1);
        $this->assertEquals(5, $this->cache->get('mtest4', 6));

        $this->assertEquals(5, $this->cache->disableMemoryCache()->get('mtest4', 7));
    }

    public function testFinish()
    {
        $this->cache->delByNamespace($this->prefix);

        $client = $this->cache->client();
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
