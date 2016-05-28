<?php

namespace tests\Psr6;

use Dy\Cache\RedisRepository;
use Dy\Cache\Item;
use Dy\Cache\Pool;
use PHPUnit_Framework_TestCase;


final class PsrTest extends PHPUnit_Framework_TestCase
{
    protected $prefix = 'dy:cache:psr';

    protected $config = null;

    /**
     * Test RedisCache
     * @var RedisCache
     */
    protected $redisRepository;
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

        $this->redisRepository = new RedisRepository($this->config);
        $this->pool = new Pool($this->redisRepository);
        $this->item = new Item($this->redisRepository,"test");
        $this->item2 = new Item($this->redisRepository,"test2");
        $this->item3 = new Item($this->redisRepository,"test3");
        $this->item4 = new Item($this->redisRepository,"nullkey");
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

    public function testHasItem()
    {
        $this->assertFalse($this->pool->hasItem("test1"));
        $this->assertTrue($this->pool->hasItem("test2"));
        $this->assertTrue($this->pool->hasItem("test3"));
        $this->assertFalse($this->pool->hasItem("nullkey"));
    }

    public function testDeleteItem()
    {
        $this->pool->deleteItem("test2");
        $this->pool->deleteItem("test3");
        $this->assertFalse($this->pool->hasItem("test2"));
        $this->assertFalse($this->pool->hasItem("test3"));
    }

    public function testDeleteItems()
    {
        $this->item2 = new Item($this->redisRepository, "test2");
        $this->item3 = new Item($this->redisRepository, "test3");
        $this->item2->set('123');
        $this->item2->expiresAfter(10);
        $this->item3->set('abc');
        $this->item3->expiresAfter(10);
        $this->assertTrue($this->pool->hasItem("test2"));
        $this->assertTrue($this->pool->hasItem("test3"));
        $this->pool->deleteItems(array("test2", "test3");
        $this->assertFalse($this->pool->hasItem("test2"));
        $this->assertFalse($this->pool->hasItem("test3"));
    }

    public function testClear()
    {
        $this->item->set('aaa');
        $this->item->expiresAfter(1);
        $this->item2->set('123');
        $this->item2->expiresAfter(10);
        $this->item3->set('abc');
        $this->item3->expiresAfter(10);
        $this->pool->clear();
        $this->assertFalse($this->pool->hasItem("test"));
        $this->assertFalse($this->pool->hasItem("test2"));
        $this->assertFalse($this->pool->hasItem("test3"));
    }

    public function testFinish()
    {
        $client = $this->redisRepository->client();
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