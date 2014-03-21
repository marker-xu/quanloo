<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataCircleUserTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_CircleUser();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_subscribe()
    {
        $result = $this->_model->subscribe(10007, 10003);
        $this->assertTrue($result);
    }

    function test_setSubscribedCircleOrder()
    {
        $docs = $this->_model->find(array('user_id' => 10007), array(), array('circle_id' => 1));
        $circleIds = Arr::pluck($docs, 'circle_id');
        $result = $this->_model->setSubscribedCircleOrder(10007, $circleIds);
        $this->assertTrue($result);
    }
}
