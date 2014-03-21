<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataUserfeedTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_UserConnect();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_addConnect()
    {
		return ;
    	$arrParams = array(
			'connect_id' => 12345,
			'access_token' => array(
				  'oauth_signature' => 'KP0dNDh39RJa81WR9B0+6mZhwrM=',
				  'oauth_token' => '16644211156205421118',
				  'oauth_token_secret' => 'V9dSi4hbcbD8aIqx',
				  'openid' => '50ABB0E84903CCC8E5A0EDBC4DA62B87',
				  'timestamp' => '1318990668',
			)
		);
		var_dump( $result = $this->_model->addConnect(Model_Data_UserConnect::TYPE_QQ, 1005, $arrParams) );
		$this->assertTrue($result);
    }
	
    function test_modifyConnectTokenByUid()
    {
    	return;
    	$arrToken = array(
				  'oauth_signature' => 'Kno problem',
				  'oauth_token' => '16644211156205421118',
				  'oauth_token_secret' => 'V9dSi4hbcbD8aIqx',
				  'openid' => '50ABB0E84903CCC8E5A0EDBC4DA62B87',
				  'timestamp' => time(),
			);
        $result = $this->_model->modifyConnectTokenByUid(Model_Data_UserConnect::TYPE_SNDA, 1005, $arrToken);
        var_dump($result);
        $this->assertTrue($result);
    }
	
	function test_getConnectByUid()
    {
    	return;
        $result = $this->_model->getConnectByUid(Model_Data_UserConnect::TYPE_QQ, 1005);
        print_r($result);
        $this->assertIsA($result, 'array');
    }
    
    function test_removeConnectByUid() {
    	$result = $this->_model->removeConnectByUid(Model_Data_UserConnect::TYPE_QQ, 1005);
        var_dump($result);
        $this->assertTrue($result);
    }
    
	function test_getConnectByCid() {
    	$result = $this->_model->getConnectByCid(Model_Data_UserConnect::TYPE_RENREN, 12345);
        var_dump($result);
        $this->assertTrue($result);
    }
}
