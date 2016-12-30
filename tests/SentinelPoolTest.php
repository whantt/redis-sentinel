<?php
/**
 * Created by PhpStorm.
 * User: huyanping
 * Date: 2016/12/30
 * Time: 09:30
 */

namespace Jenner\RedisSentinel\Test;

use Jenner\RedisSentinel\SentinelPool;

class SentinelPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SentinelPool
     */
    private $sentinel_pool;
    private $master_name = 'mymaster';

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->sentinel_pool = new SentinelPool();
        $this->sentinel_pool->addSentinel('127.0.0.1', 26379);
        $this->sentinel_pool->addSentinel('127.0.0.1', 26380);
        $this->sentinel_pool->addSentinel('127.0.0.1', 26381);
    }

    public function testAll()
    {
        $this->assertEquals('+PONG', $this->sentinel_pool->ping());
        $masters = $this->sentinel_pool->masters();
        $this->assertEquals(1, count($masters));
        $this->assertEquals($this->master_name, $masters[0]['name']);

        $master = $this->sentinel_pool->master($this->master_name);
        $this->assertEquals($this->master_name, $master['name']);

        $slaves = $this->sentinel_pool->slaves($this->master_name);
        $this->assertEquals(2, count($slaves));
        $this->assertEquals('127.0.0.1', $slaves[0]['ip']);
        $this->assertTrue(in_array($slaves[0]['port'], array('6380', '6381')));

        $sentinels = $this->sentinel_pool->sentinels($this->master_name);
        $this->assertEquals(2, count($sentinels));
        $this->assertEquals('127.0.0.1', $sentinels[0]['ip']);
        $this->assertTrue(in_array($sentinels[0]['port'], array('26380', '26381')));

        $address = $this->sentinel_pool->getMasterAddrByName($this->master_name);
        $this->assertEquals('127.0.0.1', $address['ip']);
        $this->assertEquals(6379, $address['port']);

        $this->assertTrue($this->sentinel_pool->flushConfig());
        $this->assertTrue($this->sentinel_pool->checkQuorum($this->master_name));
        $this->assertTrue($this->sentinel_pool->ckquorum($this->master_name));
        $this->assertFalse($this->sentinel_pool->failOver($this->master_name));

        $redis = $this->sentinel_pool->getRedis($this->master_name);
        $this->assertEquals('+PONG', $redis->ping());
    }
}