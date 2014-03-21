<?php defined('SYSPATH') or die('No direct script access.');

class UserVideoTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_UserVideo(10008);
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_addToWatched()
    {
//    	return;
        $result = $this->_model->addToWatched('279683c8a535e22f89941a3e8da51549');
        var_dump($result);
        $this->assertTrue($result);
    }

    function test_getWatched()
    {
        return;
    	$videos = $this->_model->getWatched(0, 1, FALSE);
		print_r($videos);
        $this->assertIsA($videos, 'array');
    }
    
	function test_addToCommented()
    {
        return;
    	$result = $this->_model->addToCommented('230e8dd048d07d2b', "baowei没问题");
        var_dump($result);
        $this->assertTrue($result);
    }

    function test_getCommented()
    {
        $videos = $this->_model->getCommented();
		print_r($videos);
        $this->assertIsA($videos, 'array');
    }
    
	function test_addToByType()
    {
//        return ;
    	$result = $this->_model->addToByType(Model_Data_UserVideo::TYPE_WATCHLATER, '0149790e8412b41832509759970205c0');
        var_dump($result);
        $this->assertTrue($result);
    }

    function test_getListByType()
    {
    	return;
        $videos = $this->_model->getListByType(Model_Data_UserVideo::TYPE_COMMENTED, 0, 2, false, true, array('start'=>1325060547));
		print_r($videos);
        $this->assertIsA($videos, 'array');
    }
    
 	function test_gc()
    {
    	return ;
    	for($i=0; $i<100; $i++) {
	        $result = $this->_model->gc(Model_Data_UserVideo::TYPE_COMMENTED, 1001);
	        if($result) {
	        	break;
	        }
    	}
		var_dump($result);
        $this->assertTrue($result);
    }
}
