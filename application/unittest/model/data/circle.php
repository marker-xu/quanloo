<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataCircleTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_Circle();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_random()
    {
        $count = 10;
        $circles = $this->_model->random($count);
        JKit::$log->debug(__FUNCTION__, $circles);
        $this->assertEqual(count($circles), $count);
    }
}
