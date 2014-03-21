<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataMongoCollectionTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_MongoCollection('stat', 'test', 'test');
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_update()
    {
        $ret = $this->_model->update(array('name' => 'jagger'), array('age' => 30), 
            array('upsert' => true));
        $this->assertTrue($ret);
    }
}
