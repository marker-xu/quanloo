<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataUserGuessRecommendTest extends UnitTestCase {
	private $_model;
    
    function __construct()
    {
        parent::__construct();
        $uid = 10008;
        $this->_model = new Model_Data_Userguessrecommend($uid);
        $this->_model->setDebugMode(true);
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }
    
    public function test_getGuessVids() {
//    	return;
    	$result = $this->_model->getGuessVids();
        var_dump($result);
        print_r($this->_model->getDebugData("video", 0, 20));
        $this->assertIsA($result, 'array');
    }
    
	public function test_getGuessCircleIds() {
    	return;
    	$result = $this->_model->getGuessCircleIds();
        var_dump($result);
        print_r($this->_model->getDebugData("circle", 0, 20));
        $this->assertIsA($result, 'array');
    }
}