<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataGuessRecommendTest extends UnitTestCase {
	private $_model;
    
    function __construct()
    {
        parent::__construct();
        $watchedVids = array("6ccb11c95a383c0c6fe0c10c7e6865d8", "0000a4e175ec7a0dea41584658e6e8a5");
        $watchlaterVids = array("1f84525666d3c12fced25587696292db", "9567d553dd696b47ad3eaccf3b5b870f","87ac69f2fa32449177184837de7fc79a" );
        $this->_model = new Model_Data_Guessrecommend($watchedVids, $watchlaterVids);
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }
    
    public function test_getGuessVids() {
    	return;
    	$result = $this->_model->getGuessVids();
        var_dump($result);
        $this->assertIsA($result, 'array');
    }
    
	public function test_getGuessCircleIds() {
    	return;
    	$result = $this->_model->getGuessCircleIds();
        var_dump($result);
        $this->assertIsA($result, 'array');
    }
    
	public function test_getCircleRecommendVidList() {
//    	return;
//		$arrWatchCids = array(10015, 10052);
//		$arrWatchlaterCids = array(10005, 10534);
    	$result = $this->_model->getCircleRecommendVidList();
        var_dump($result);
        $this->assertIsA($result, 'array');
    }
}