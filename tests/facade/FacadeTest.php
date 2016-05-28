<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2015/7/10
 * Time: 21:32
 */

use Dy\Cache\RedisCache;

final class FacadeSet extends PHPUnit_Framework_TestCase
{
    protected $prefix = 'dy:cache:facade';

    protected $config = null;

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
    }

    /**
     * @expectedException     \RuntimeException
     * @expectedExceptionMessage instance
     */
    public function testNew()
    {
        $test = new RedisCache();
    }

    public function testConfig()
    {
        RedisCache::config($this->config);
    }

    public function testPut()
    {
        RedisCache::put('test', 'aaa', 1/60);
        RedisCache::put('test2', '123', 1);
    }

    public function testSet()
    {
        RedisCache::set('test3', 'abc', 10);
        $this->assertEquals('abc', RedisCache::get('test3'));
        $this->assertEquals('aaa', RedisCache::get('test4', 'aaa'));
    }

    public function testClearAll()
    {
        RedisCache::clearAll();
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

        RedisCache::close();
    }
}
