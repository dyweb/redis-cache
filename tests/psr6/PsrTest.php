<?php

namespace tests\Psr6;

use Dy\Cache\RedisCache;
use Dy\Cache\Item;
use Dy\Cache\Pool;
use PHPUnit_Framework_TestCase;


final class PsrTest extends PHPUnit_Framework_TestCase
{
    protected $prefix = 'dy:cache:ptest';

    protected $config = null;

    protected $client = null;

    /**
     * Test RedisCache
     * @var RedisCache
     */
    protected $redisCache;
    /**
     * Test pool
     * @var Pool
     */
    protected $pool;

    /**
     * Test items
     * @var Item
     */
    protected $item;
    protected $item2;
    protected $item3;
    protected $item4;


    public function __construct()
    {
        parent::__construct();
        $this->config = array(
            'connection' => array(
                'client' => 'predis',
                'schema' => 'tcp',
                'host' => '127.0.0.1',
                'port' => 6379,
            ),
            'namespace' => array(
                'name' => $this->prefix,
                'key_set_name' => 'keys',
                'lazy_record' => true
            ),
            'memory_cache' => false
        );

        $this->redisCache=new RedisCache();
        $this->pool=new Pool($this->redisCache);

        $this->item=new Item($this->redisCache,"test");
        $this->item2=new Item($this->redisCache,"test2");
        $this->item3=new Item($this->redisCache,"test3");
        $this->item4=new Item($this->redisCache,"nullkey");
    }


    public function testPut()
    {

        $this->item->set('aaa');
        $this->item->expiresAfter(1/60);
        $this->item2->set('123');
        $this->item2->expiresAfter(10);
        $this->item3->set('abc');
        $this->item3->expiresAfter(10);


    }

    public function testGetKey()
    {
        $this->assertEquals("test", $this->item->getKey());
        $this->assertEquals("test2", $this->item2->getKey());
        $this->assertEquals("test3", $this->item3->getKey());
        $this->assertEquals("nullkey", $this->item4->getKey());
    }

    public function testGet()
    {
        sleep(2);
        $this->assertEquals(null, $this->item->get());
        $this->assertEquals(123, $this->item2->get());
        $this->assertEquals('abc', $this->item3->get());
        $this->assertEquals(null, $this->item4->get());
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

}