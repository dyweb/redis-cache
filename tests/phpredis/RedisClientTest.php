<?php
/**
 * Created by PhpStorm.
 * User: 凯の凯
 * Date: 2016/8/30
 * Time: 10:37
 */

use Dy\Cache\RedisRepository;

class RedisClientTest extends \PHPUnit_Framework_TestCase
{
    private $config;

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
                'name' => 'dy:cache:client',
                'key_set_name' => '',
                'lazy_record' => false
            ),
            'memory_cache' => false
        );
    }

    public function testBasic()
    {
        $repository = new RedisRepository($this->config);
        $this->assertInstanceOf('Dy\Redis\RedisClient', $repository->client());
    }

    public function testCache()
    {
        $this->config['memory_cache'] = true;
        $repository = new RedisRepository($this->config);
        $this->assertInstanceOf('Dy\Redis\RedisClient', $repository->client());
    }

    public function testSet()
    {
        $this->config['namespace']['key_set_name'] = 'keys';
        $repository = new RedisRepository($this->config);
        $this->assertInstanceOf('Dy\Redis\RedisClient', $repository->client());
    }

    public function testLazy()
    {
        $this->config['namespace']['lazy_record'] = true;
        $repository = new RedisRepository($this->config);
        $this->assertInstanceOf('Dy\Redis\RedisClient', $repository->client());
    }
}
