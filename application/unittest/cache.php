<?php defined('SYSPATH') or die('No direct script access.');

class CacheTest extends UnitTestCase
{
    function __construct()
    {
        parent::__construct();
    }
    
    function setUp() {}
    
    function tearDown() {}

    function test_setex()
    {
        $cache = Cache::instance('web');
        /* @var $redis Redis */
        $redis = $cache->getRedisDB();
        $key = 'a';
        $value = 1;
        $ttl = 1;
        $result = $redis->setex($key, $ttl, $value);
        Kohana::$log->debug(__FUNCTION__, $result);
        $result = $redis->ttl($key);
        Kohana::$log->debug(__FUNCTION__, $result);
        $result = $redis->get($key);
        Kohana::$log->debug(__FUNCTION__, $result);
        sleep(2);
        $result = $redis->get($key);
        Kohana::$log->debug(__FUNCTION__, $result);
        $this->assertFalse($result);
    }

    function test_set()
    {
        $cache = Cache::instance('web');
        $key = 'a';
        $value = 1;
        $ttl = 0;
        $result = $cache->set($key, $value, $ttl);
        Kohana::$log->debug(__FUNCTION__, $result);
        $result = $cache->get($key);
        $this->assertEqual($result, $value);
        Kohana::$log->debug(__FUNCTION__, $result);
        
        $ttl = 1;
        $result = $cache->set($key, $value, $ttl);
        Kohana::$log->debug(__FUNCTION__, $result);
        $result = $cache->get($key);
        Kohana::$log->debug(__FUNCTION__, $result);
        $this->assertEqual($result, $value);
        sleep(2);
        $result = $cache->get($key);
        Kohana::$log->debug(__FUNCTION__, $result);
        $this->assertEqual($result, NULL);
        
        $result = $cache->get('not.exist');
        $this->assertEqual($result, NULL);
    }
}
