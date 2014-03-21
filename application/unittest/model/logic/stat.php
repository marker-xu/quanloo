<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataStatTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Logic_Stat();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_mostLikedVideos()
    {
    	return;
        $videos = $this->_model->mostLikedVideos();
        JKit::$log->debug(__FUNCTION__, $videos);
        $this->assertIsA($videos, 'array');
    }

    function test_mostLikedCircleVideos()
    {
    	return;
        $videos = $this->_model->mostLikedCircleVideos(10001);
        JKit::$log->debug(__FUNCTION__, $videos);
        $this->assertIsA($videos, 'array');
    }

    function test_mostWatchedCircleVideos()
    {
    	return;
        $videos = $this->_model->mostWatchedCircleVideos(10001);
        JKit::$log->debug(__FUNCTION__, $videos);
        $this->assertIsA($videos, 'array');
    }
    
    public function test_getQueryTopContent() {
    	return;
    	$result = $this->_model->getQueryTopContent();
    	print_r($result);
        $this->assertIsA($result, 'array');
    }

    function test_mostPlayedVideosInCircle()
    {
        $videos = $this->_model->mostPlayedVideosInCircle(10001, 'month', 10);
        print_r($videos);
        $this->assertIsA($videos, 'array');
    }
}
