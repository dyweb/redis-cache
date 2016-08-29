<?php
/**
 * Created by PhpStorm.
 * User: ComMouse
 * Date: 2016/8/30
 * Time: 0:36
 */

use Dy\Cache\MemoryRepository;

class MemoryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    protected $memory;

    public function __construct()
    {
        parent::__construct();
        $this->memory = new MemoryRepository();
    }

    public function testPut()
    {
        $this->assertFalse($this->memory->has('test'));
        $this->memory->put('test', 'aaa');
        $this->assertEquals('aaa', $this->memory->get('test'));
    }

    public function testDel()
    {
        $this->memory->put('test_del', 'aaa');
        $this->assertTrue($this->memory->has('test_del'));
        $this->memory->del('test_del');
        $this->assertFalse($this->memory->has('test_del'));
        $this->memory->del('test_not_exists');
        $this->assertFalse($this->memory->has('test_not_exists'));
    }

    public function testPutDel()
    {
        $this->memory->put('test_putdel', 'aaa');
        $this->assertTrue($this->memory->has('test_putdel'));
        $this->memory->put('test_putdel', null);
        $this->assertFalse($this->memory->has('test_putdel'));
    }

    public function testSet()
    {
        $this->assertFalse($this->memory->has('test'));
        $this->memory->set('test', 'aaa');
        $this->assertEquals('aaa', $this->memory->get('test'));
        $this->memory->forget('test');
        $this->assertFalse($this->memory->has('test'));
    }

    public function testGetDefault()
    {
        $this->assertEquals('abc', $this->memory->get('test_default', 'abc'));
        $this->assertEquals('aaa', $this->memory->get('test_default2', function () {
            return 'aaa';
        }));
    }

    public function testPull()
    {
        $this->memory->set('test_pull', '123');
        $this->assertEquals('123', $this->memory->pull('test_pull'));
        $this->assertFalse($this->memory->has('test_pull'));

        $this->assertNull($this->memory->pull('test_pull_not_exists'));
        $this->assertFalse($this->memory->has('test_pull_not_exists'));
    }
}