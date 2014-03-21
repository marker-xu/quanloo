<?php defined('SYSPATH') or die('No direct script access.');

class CacheTest extends UnitTestCase
{
    function __construct()
    {
        parent::__construct();
    }
    
    function setUp() {}
    
    function tearDown() {}

    function test_videoThumbnailUrl()
    {
        $fdfsPath = 'group1/M00/57/07/CpwYW076qsShLkshAAADL1scZ4o818.php';
        $url = Util::videoThumbnailUrl($fdfsPath);
        Kohana::$log->debug(__FUNCTION__, $url);
        $this->assertTrue($url !== FALSE);
    }
    
    function test_userAvatarUrl()
    {
        $fdfsPath = 'group1/M00/57/07/CpwYW076qsShLkshAAADL1scZ4o818.php';
        $url = Util::userAvatarUrl($fdfsPath);
        Kohana::$log->debug(__FUNCTION__, $url);
        $this->assertTrue($url !== FALSE);
    }
}
