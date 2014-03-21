<?php defined('SYSPATH') or die('No direct script access.');

class ModelLogicVideoTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Logic_Video();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_get()
    {
        $video = $this->_model->get('10000ae9549a0f83a30292d6c00ca7b8');
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
        return;
        $videos = $this->_model->random(1);
        JKit::$log->debug(__FUNCTION__, $videos);
        $this->assertIsA($videos, 'array');
    }
}
