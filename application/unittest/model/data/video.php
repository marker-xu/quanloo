<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataVideoTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_Video();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_get()
    {
        $video = $this->_model->get('230e8dd048d07d2bab54c77ad6ed553e');
        JKit::$log->debug(__FUNCTION__, $video);
        $this->assertIsA($video, 'array');
    }

    function test_getMulti()
    {
        $videos = $this->_model->getMulti(array(
        	'10000ae9549a0f83a30292d6c00ca7b8'
        ));
        JKit::$log->debug(__FUNCTION__, $videos);
        $this->assertIsA($videos, 'array');
    }

    function test_random()
    {
        $videos = $this->_model->random(1);
        JKit::$log->debug(__FUNCTION__, $videos);
        $this->assertIsA($videos, 'array');
    }

    function test_getThumbnails()
    {
        $thumbnails = $this->_model->getThumbnails(array(
        	'7c2205e2a4150ee7bca11640186d6c6c'
        ));
        JKit::$log->debug(__FUNCTION__, $thumbnails);
        $this->assertIsA($thumbnails, 'array');
    }
}
