<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataUserTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_User();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_addUser()
    {
    	return;
    	$account = "119@1.com";
		$arrParams = array(
			'password' => md5(111111),
			'avatar' => '',
			'nick' => 'baowei'
		);
		echo $result = $this->_model->addUser($account, $arrParams);
		$this->assertIsA($result, 'float');
    }
	
    function test_getByEmail()
    {
    	return;
        $result = $this->_model->getByEmail("123@1.com");
        print_r($result);
        $this->assertIsA($result, 'array');
    }
	
	function test_getByNick()
    {
    	return;
        $result = $this->_model->getByEmail("123@1.com");
        print_r($result);
        $this->assertIsA($result, 'array');
    }
    
    function test_modifyById()
    {
    	return;
        $result = $this->_model->modifyById(10003, array("email"=>"k@k.com"));
        $this->assertTrue($result);
    }
    
	function test_uploadAvatar()
    {
  		return;
    	$filename = "/home/worker/snda-php/videosearch/resource/img/c3.jpg";
        $result = $this->_model->uploadAvatar($filename);
        var_dump($result);
        $this->assertIsA($result, "array");
    }
    
	function test_removeAvatar()
    {
    	return;
    	$groupName = "group1";
    	$fileName = "M00/00/00/CpwYY078F0CsBsihAAAUufayyUA362.jpg";
        $result = $this->_model->removeAvatar($groupName, $fileName);
        var_dump($result);
        $this->assertTrue($result);
    }
    
    function test_get()
    {
    	return;
        $result = $this->_model->get(1011);
        var_dump($result);
        $this->assertIsA($result, 'array');
    }
    
    function test_medal()
    {
        $result = $this->_model->awardMedal(10007, Model_Data_User::MEDAL_SUBSCRIBE_CIRCLE);
        $this->assertTrue($result);
        $result = $this->_model->isAwardedMedal(10007, Model_Data_User::MEDAL_SUBSCRIBE_CIRCLE);
        $this->assertTrue($result);
        $result = $this->_model->medals(10007);
        $this->assertTrue(in_array(Model_Data_User::MEDAL_SUBSCRIBE_CIRCLE, $result));
        $result = $this->_model->unawardMedal(10007, Model_Data_User::MEDAL_SUBSCRIBE_CIRCLE);
        $this->assertTrue($result);
        $result = $this->_model->isAwardedMedal(10007, Model_Data_User::MEDAL_SUBSCRIBE_CIRCLE);
        $this->assertFalse($result);
        $result = $this->_model->awardMedal(10007, Model_Data_User::MEDAL_SUBSCRIBE_CIRCLE);
        $result = $this->_model->awardMedal(10007, Model_Data_User::MEDAL_INVITE_FRIEND);
        $result = $this->_model->awardMedal(10007, Model_Data_User::MEDAL_CREATE_CIRCLE);
        $result = $this->_model->medals(10007);
        $this->assertTrue(count($result) == 3);
        $result = $this->_model->unawardMedal(10007, Model_Data_User::MEDAL_SUBSCRIBE_CIRCLE);
        $result = $this->_model->unawardMedal(10007, Model_Data_User::MEDAL_INVITE_FRIEND);
        $result = $this->_model->unawardMedal(10007, Model_Data_User::MEDAL_CREATE_CIRCLE);
    }
}
