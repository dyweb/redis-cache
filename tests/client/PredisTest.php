<?php
use Dy\Redis\PredisClient;

/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2016/8/29
 * Time: 23:46
 */


class PredisTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The redis instance.
     * @var PredisClient
     */
    protected $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new PredisClient(array(
            'schema' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379
        ));
    }

    public function testConnect()
    {
        $client = new PredisClient(array(
            'schema' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379
        ));
        unset($client);
    }

    public function testDel()
    {
        $this->client->del('test');
    }

    /**
     * @depends testDel
     */
    public function testSetAndGet()
    {
        $this->assertFalse($this->client->exists('test'));
        $this->client->set('test', 'haha');
        $this->assertEquals('haha', $this->client->get('test'));
        $this->assertTrue($this->client->exists('test'));
    }

    public function testSetExAndGet()
    {
        $this->client->setex('testex', 1, 'hahaha');
        $this->assertEquals('hahaha', $this->client->get('testex'));
        sleep(2);
        $this->assertEquals(null, $this->client->get('testex'));
    }

    public function testIncr()
    {
        $this->client->incr('testincr');
        $this->assertEquals(1, $this->client->get('testincr'));
        $this->client->incrby('testincr', 2);
        $this->assertEquals(3, $this->client->get('testincr'));
    }

    public function testDecr()
    {
        $this->client->decr('testdecr');
        $this->assertEquals(-1, $this->client->get('testdecr'));
        $this->client->decrby('testdecr', 2);
        $this->assertEquals(-3, $this->client->get('testdecr'));
    }

    public function testKeys()
    {
        $this->assertNotEmpty($this->client->keys('test*'));
    }

    public function testScan()
    {
        $this->assertNotEmpty($this->client->scan(0, 'test*', 10));
    }

    public function testClear()
    {
        $this->client->del('test');
        $this->client->del('testex');
        $this->client->del('testincr');
        $this->client->del('testdecr');
    }

    public function testSet()
    {
        $this->client->del('testset');
        $this->client->sadd('testset', array('haha', 'huahua'));
        $this->assertEquals(2, $this->client->scard('testset'));
        $this->assertTrue($this->client->sismember('testset', 'haha'));
        $this->assertContains('haha', $this->client->smembers('testset'));
        $this->client->srem('testset', 'haha');
        $this->assertFalse($this->client->sismember('testset', 'haha'));
        $this->assertNotEmpty($this->client->sscan('testset', 0, '*', 10));
        $this->client->del('testset');
    }

    public function testQuit()
    {
        $this->client->quit();
    }
}
