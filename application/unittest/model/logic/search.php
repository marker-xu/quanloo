<?php defined('SYSPATH') or die('No direct script access.');

class ModelLogicSearchTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Logic_Search();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_search()
    {
        $result = $this->_model->search('十三钗', 0, 5);
        Kohana::$log->debug(__FUNCTION__, $result);
        $this->assertIsA($result, 'array');
    }

    function test_relationQuery()
    {
        $result = $this->_model->relationQuery('十三钗');
        Kohana::$log->debug(__FUNCTION__, $result);
        $this->assertIsA($result, 'array');
    }
}
