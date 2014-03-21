<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataUserfeedTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_UserFeed();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_addFeed()
    {
    	return;
		$uid = 10007;
    	$arrParams = array(
			'circle_id' => 111111,
			'video_id' => '0149790e8412b41832509759970205c0',
			'data' => array(
				'a' => '234',
				'b' => 'cdef'
			)
		);
		$result = $this->_model->addFeed($uid, Model_Data_UserFeed::TYPE_LIKE_VIDEO, $arrParams);
		$this->assertTrue($result);
    }
	
    function test_removeFeedById()
    {
    	return;
        $result = $this->_model->removeFeedById("4efa89997f8b9a7145000000");
        var_dump($result);
        $this->assertTrue($result);
    }
	
	function test_getFeedList()
    {
    	return;
        $result = $this->_model->getFeedList(10008, NULL);
        print_r($result);
        $this->assertIsA($result, 'array');
    }
    
	function test_find()
    {
//    	return;
	$count = 10;
$offset = 0;
        $result = $this->_model->find(array(), array(), array('create_time' => -1), 
            $count, $offset);;
        print_r($result);
        $this->assertIsA($result, 'array');
    }
    
}
