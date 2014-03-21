<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataWebRedisTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_WebRedis();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_circlePreviewUpdateMsg()
    {
        return;
//        $circleIds = array(15521, 15486, 15485, 15372);
//        foreach ($circleIds as $circleId) {
//            $result = $this->_model->sendCirclePreviewUpdateMsg($circleId);
//            $this->assertTrue($result);
//        }
        $result = $this->_model->receiveCirclePreviewUpdateMsgs(0, 100, false);
        print_r($result);
//        $this->assertTrue(count($result) == count($result));
    }

    function test_cmsAcl()
    {
        $acl = new ACL();
        $role = 'admin';
        $resource = 'circle';
        $privilege = 'view';
        $acl->add_role($role);
        $acl->add_resource($resource);
        $acl->allow($role, $resource, $privilege);
        $this->assertTrue($acl->is_allowed($role, $resource, $privilege));
        
        $result = $this->_model->cmsAcl($acl);
        $this->assertTrue($result);
        
        $result = $this->_model->cmsAcl();
        $this->assertIsA($result, 'ACL');
        $this->assertTrue($result->is_allowed($role, $resource, $privilege));
    }
}
