<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataQuerystatTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_QueryStat();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }
	
    function test_getRecentPeriod()
    {
        $result = $this->_model->getRecentPeriod();
        var_dump($result);
        $this->assertIsA($result, 'float');
    }
	
	function test_batchGetPeriodBySameRecentAndType()
    {
    	
        $result = $this->_model->batchGetPeriodBySameRecentAndType( NULL);
        print_r($result);
        $this->assertIsA($result, 'array');
    }
    
}
