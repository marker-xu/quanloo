<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataUserTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_getTicketInfo()
    {
    	return;
    	$ticket = "ST-81941253-8ac2-4f5c-a12f-465e6507e255";
		$result = Model_Data_Sndauser::getTicketInfo($ticket);
		var_dump($result);
		$this->assertIsA($result, 'array');
    }
    
    function test_convertId() {
    	return;
    	$sdid = '1188590571';
		$result = Model_Data_Sndauser::convertId($sdid);
		var_dump($result);
		$this->assertIsA($result, 'array');
    }
    
	function test_buildLoginUrl() {
		return;
		$result = Model_Data_Sndauser::buildLoginUrl(NULL, true);
		if(isset($_GET['ticket'])) {
			print_r($_GET);
			$result = Model_Data_Sndauser::getTicketInfo($_GET['ticket'], NULL, true);
		var_dump($result);
		} else {
			Request::factory(Request::$current)->redirect($result);
			echo $result;
		}
		
		
		$this->assertIsA($result, 'string');
    }
    function test_buildLogoutUrl() {
    	return;
    	$result = Model_Data_Sndauser::buildLogoutUrl(NULL);
		var_dump( $result );
		
		$this->assertIsA($result, 'string');
    }
    
	function test_buildRegisterUrl() {
    	$result = Model_Data_Sndauser::buildRegisterUrl("http://videosearch.sii.sdo.com:8000/");
		if(isset($_GET['ticket'])) {
			print_r($_GET);
			$result = Model_Data_Sndauser::getTicketInfo($_GET['ticket'], NULL, true);
			var_dump($result);
		} else {
			Request::factory(Request::$current)->redirect($result);
			echo $result;
		}
		
		$this->assertIsA($result, 'string');
    }
}