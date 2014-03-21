<?php defined('SYSPATH') or die('No direct script access.');

class UserVideoTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_UserStatAll();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_getByUid()
    {
        $result = $this->_model->getByUid('1005');
        print_r($result);
        $this->assertIsA($result, 'array');
    }

    function test_addStat()
    {
    	$arrParams = array(
			'subscribed_circle_count' => 12,
			'activity' => 24,
		);
    	$result = $this->_model->addStat(1005, $arrParams);
		var_dump($result);
        $this->assertTrue($result);
    }
    
	function test_modifyStatByUid()
    {
//        return;
		$arrParams = array(
			'comment_count' => 2,
			'watch_count' => 1,
		);
    	$result = $this->_model->modifyStatByUid("1005", $arrParams);
        var_dump($result);
        $this->assertTrue($result);
    }

}
