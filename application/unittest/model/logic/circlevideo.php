<?php defined('SYSPATH') or die('No direct script access.');

class ModelLogicCircleVideoTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Logic_CircleVideo();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_add()
    {
        $circleId = 15373;
        $videoIds = array('e84141846d751f8e8e4e73e62290e26a', '84172d2d2a9f02e85e6ed9b2f547325a', 
            '8051ba2b21178f9d0fa256d1791480d6', '88eca4ffb9f8d0985a505d9f696adfb2');
        $userId = 794123477;
        foreach ($videoIds as $videoId) {
            $result = $this->_model->add($circleId, $videoId);
            $this->assertTrue($result);
        }
        sleep(1);
        
        $total = 0;
        $result = $this->_model->circledVideosByCircle($circleId, 0, count($videoIds), $total);
        $this->assertTrue(count($result) == count($videoIds));
        $this->assertTrue($total == count($videoIds));
        
        $result = $this->_model->circledVideosByUser($userId, 0, count($videoIds), $total);
        $this->assertTrue(count($result) == count($videoIds));
        $this->assertTrue($total == count($videoIds));
        
        $videoId = array_shift($videoIds);
        $result = $this->_model->remove($circleId, $videoId);
        $this->assertTrue($result);
        
        $result = $this->_model->circledVideosByUser($userId, 0, count($videoIds), $total);
        $this->assertTrue(count($result) == count($videoIds));
        $this->assertTrue($total == count($videoIds));
    }
}
