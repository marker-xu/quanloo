<?php defined('SYSPATH') or die('No direct script access.');

class ModelLogicCircleTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Logic_Circle();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_related()
    {
        return;
        $circles = $this->_model->related(10001, 5);
        JKit::$log->debug(__FUNCTION__, $circles);
        $this->assertIsA($circles, 'array');
    }
    
    function test_groupByCategory()
    {
        return;
        $circles = $this->_model->groupByCategory();
        JKit::$log->debug(__FUNCTION__, $circles);
        $this->assertIsA($circles, 'array');
    }
    
    function test_mostPopulated()
    {
        return;
        $circles = $this->_model->mostPopulated();
        JKit::$log->debug(__FUNCTION__, $circles);
        $this->assertIsA($circles, 'array');
    }
    
    function test_findByPinyin()
    {
        return;
        $circles = $this->_model->findByPinyin('C');
        JKit::$log->debug(__FUNCTION__, $circles);
        $this->assertIsA($circles, 'array');
    }
    
    function test_findByTitle()
    {
        return;
        $circles = $this->_model->findByTitle('风尚');
        JKit::$log->debug(__FUNCTION__, $circles);
        $this->assertIsA($circles, 'array');
    }
    
    function test_create()
    {
        return;
        $result = $this->_model->create('法网2012', 14, 794123477, array('网球', '法国', '2012'));
        $this->assertTrue($result);
        sleep(1);
        $result = $this->_model->create('费德勒', 14, 794123477, array('网球', '奶牛'), 
            Model_Data_Circle::STATUS_PRIVATE);
        $this->assertTrue($result);
        
        sleep(1);
        $total = 0;
        $result = $this->_model->created(794123477, 0, 10, 'guest', $total);
        $this->assertTrue(count($result) == 1);
        $this->assertTrue($total == 1);
        $result = $this->_model->created(794123477, 0, 10, 'host', $total);
        $this->assertTrue(count($result) == 2);
        $this->assertTrue($total == 2);
        $circle = array_shift($result);
        
        $result = $this->_model->modify($circle['_id'], array(
            'status' => Model_Data_Circle::STATUS_PUBLIC
        ));
        $this->assertTrue($result);
        sleep(1);
        
        $total = 0;
        $result = $this->_model->created(794123477, 0, 10, 'guest', $total);
        $this->assertTrue(count($result) == 2);
        $this->assertTrue($total == 2);
        
        foreach ($result as $circle) {
            $result = $this->_model->delete($circle['_id']);
            $this->assertTrue($result);
        }
    }
}
