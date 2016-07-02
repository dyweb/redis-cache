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
     * @var RedisRepository
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
    protected $items;

    /**
     * Test keys for items
     * @var string
     */
    protected $keys;

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


    public function testSet()
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

    public function testIsHit()
    {
        $this->assertFalse($this->item->isHit());
        $this->assertTrue($this->item2->isHit());
        $this->assertTrue($this->item3->isHit());
        $this->assertFalse($this->item4->isHit());
    }

    public function testGetItem()
    {
        $this->item2 = new Item($this->redisRepository,"test2");
        $this->item2->set('123');
        $this->item4=$this->pool->getItem("test2");
        $this->assertEquals($this->item2, $this->item4);
    }

    public function testGetItems()
    {
        $this->item2 = new Item($this->redisRepository,"test2");
        $this->item2->set('123');
        $this->keys =array();
        $this->keys[]='test2';
        $this->items=$this->pool->getItems($this->keys);
        foreach ($this->items as $item) {
            $this->assertEquals($this->item2, $item);
        }
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
        $this->pool->deleteItems(array("test2", "test3"));
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
        $this->assertTrue($this->pool->hasItem("test"));
        $this->assertTrue($this->pool->hasItem("test2"));
        $this->assertTrue($this->pool->hasItem("test3"));
        $this->pool->clear();
        $this->assertFalse($this->pool->hasItem("test"));
        $this->assertFalse($this->pool->hasItem("test2"));
        $this->assertFalse($this->pool->hasItem("test3"));
    }

    public function testSaveForItem()
    {
        $this->item2 = new Item($this->redisRepository, "test2");
        $this->item2->set('bbb');
        $this->item2->expiresAfter(10);
        $this->item2->save();
        $this->assertEquals('bbb',$this->item2->get());
    }

    public function testSaveForPool()
    {
        $this->item2 = new Item($this->redisRepository, "test2");
        $this->item2->set('ccc');
        $this->item2->expiresAfter(10);
        $this->pool->save($this->item2);
        $this->assertEquals('ccc',$this->item2->get());
    }

    public function testSaveDeferred()
    {
        $this->item2 = new Item($this->redisRepository, "test2");
        $this->item2->set('ddd');
        $this->item2->expiresAfter(10);
        $this->pool->saveDeferred($this->item2);
        $this->assertEquals('ddd',$this->item2->get());
    }

    public function testCommit()
    {
        $this->assertTrue($this->pool->commit());
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