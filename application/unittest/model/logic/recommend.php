<?php 
class ModelLogicRecommendTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Logic_Recommend();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }
    
    function test_getHomepageRecommendVideos() {
    	return;
    	$result = $this->_model->getHomepageRecommendVideos(0, 11);
        print_r($result);
        $this->assertIsA($result, "array");
    }
    
    function test_getCircleInfoByVids() {
    	return;
    	$ids = array(
    		"1000b16a6d38747ec44bb7890a445a1d",
    		"1000780a04d092c8baa76ef7e0114cf0",
    		"1000d24de98d86ee995ab315b7326f48"
    	);
    	$result = $this->_model->getCircleInfoByVids($ids);
        print_r($result);
        $this->assertIsA($result, "array");
    }
    
	function test_buildVideoAndStatAndCircle() {
		return;
    	$ids = array(
    		"1000b16a6d38747ec44bb7890a445a1d",
    		"1000780a04d092c8baa76ef7e0114cf0",
    		"1000d24de98d86ee995ab315b7326f48"
    	);
    	$result = $this->_model->buildVideoAndStatAndCircle($ids);
        print_r($result);
        $this->assertIsA($result, "array");
    }
    
    function test_getGuessCirclesByUid() {
    	return;
    	$result = $this->_model->getGuessCirclesByUid(10008);
        print_r($result);
        $this->assertIsA($result, "array");
    }
    
	function test_getGuessVideosByUid() {
    	return;
    	$result = $this->_model->getGuessVideosByUid(10008, 0,4);
        print_r($result);
        $this->assertIsA($result, "array");
    }
    
	function test_getRecommendVideos() {
//		return;
    	$result = $this->_model->getRecommendVideos("6f3f53f83509064d4827bea4706cb32b");
        print_r($result);
        $this->assertIsA($result, "array");
    }
}