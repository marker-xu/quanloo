<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataRecommendTest extends UnitTestCase {
	private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_Recommend();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }
    
    public function test_buildIndexTestData() {
    	return;
    	$redis = Database::instance('web_redis_master');
        $objRedisDb = $redis->getRedisDB(2);
        $objVideo = new Model_Data_Video();
        $query = array();
        $objCircle = new Model_Data_Circle();
    	
    	#5 Model_Data_Recommend::HOMEPAGE_REC
        $arrTmp = $objVideo->find($query, array("_id"), NULL, 200, 30000);
        $ids = array_keys($arrTmp);
        $length = count($ids);
        $time = time();
        $rec_type = 0;
        for($i=0; $i<$length; $i++) {
        	$rec_type = rand(0, 2);
        	$objRedisDb->zAdd(Model_Data_Recommend::HOMEPAGE_REC, $i+1, implode("\3", array($ids[$i], $rec_type, $time)));
        }
        $objRedisDb->set(Model_Data_Recommend::HOMEPAGE_CUR_NUM, 200);
    }
    
    public function test_getIndex() {
    	return;
    	$arrKeys = array(
			Model_Data_Recommend::HOME_TOPHOT_REC,
			Model_Data_Recommend::HOME_SPOTLIGHT_REC,
			Model_Data_Recommend::HOME_CIRCLE_REC,
			Model_Data_Recommend::HOME_BINFEN_REC,
			Model_Data_Recommend::HOME_TREND_REC,
		);
		$arrKeys = array(
			Model_Data_Recommend::getCircleCatKey(10069),
			Model_Data_Recommend::BINFEN_TOP_REC,
			Model_Data_Recommend::BINFEN_CATE_REC
		);
		$arrKeys = array(
			Model_Data_Recommend::getUserInterestedCircleKey(1013),
			Model_Data_Recommend::getVideoRelateKey('1d3ca870dcb66d0a9b10d28394cc7040'),
		);
		$arrData = Model_Data_Recommend::get(Model_Data_Recommend::HOME_TOPHOT_REC, true);
		var_dump($arrData);
		
    }
    
    public function test_getList() {
    	return;
		$objRedis = Model_Data_Recommend::getRedisDb(true);
		$arrData = $objRedis->zRange(Model_Data_Recommend::HOMEPAGE_REC, 0, -1);
		
		print_r($arrData);
		echo $objRedis->get(Model_Data_Recommend::HOMEPAGE_CUR_NUM);
    }
    public function test_buildData() {
    	return;
    	$objRedisDb = Model_Data_Recommend::getRedisDb(true);
        $objVideo = new Model_Data_Video();
        $query = array();
        $objCircle = new Model_Data_Circle();
        #1 圈子视频
        $arrTmp = $objVideo->find($query, array("_id"), NULL, 50, 100000);
        $ids = array_keys($arrTmp);
    	$length = count($ids);
        $time = time();
        $rec_type = 0;
        for($i=0; $i<$length; $i++) {
        	$rec_type = rand(0, 2);
        	$objRedisDb->zAdd(Model_Data_Recommend::getCircleCatKey(10051), $i+1, implode("\3", array($ids[$i], $rec_type, $time)));
        }

    }
    
    public function test_buildUserCircleAndVideo() {
    	return;
    	$redis = Database::instance('web_redis_master');
        $objRedisDb = $redis->getRedisDB(2);
        $objVideo = new Model_Data_Video();
        $query = array();
        $objCircle = new Model_Data_Circle();
        
        #1 用户推荐的圈子
        $arrCircles = $objCircle->find($query, array("_id"), NULL, 15, 20);
        $arrCircles = array_keys($arrCircles);
        $arrTmp = array(
        	'video_circle' => array_slice($arrCircles, 0, 5),
        	'friend_circle' => array_slice($arrCircles, 5, 5),
        	'popular_circle' => array_slice($arrCircles, 10, 5),
        );
        $objRedisDb->set(
    		Model_Data_Recommend::getUserInterestedCircleKey(1013),
			json_encode($arrTmp)
    	);
    	
    	#2 播放页的相关视频推荐
    	$arrTmp = $objVideo->find($query, array("_id"), NULL, 15, 250000);
        
        $ids = array_keys($arrTmp);
        $objRedisDb->set(
    		Model_Data_Recommend::getVideoRelateKey('1d3ca870dcb66d0a9b10d28394cc7040'),
			join("\4", $ids)
    	);
    	
    }
    
    public function test_getUserCircles() {
        return;
    	$result = Model_Data_Recommend::getUserCircles(10008, 0, -1, true);
    	var_dump($result);
    	
    }
    
    public function test_relatedCircles() {
        return;
    	$result = Model_Data_Recommend::relatedCircles('搞笑', 794123477);
    	var_dump($result);
    }
    
    public function test_videoRecommendReason()
    {
        $videoIds = array('d3118018a75f59235ea4ba9a9dcd8b1d', 'e84141846d751f8e8e4e73e62290e26a', 
            'aa8e7bd89271ba7e70042b4fdb266d61');
    	$result = Model_Data_Recommend::videoRecommendReason($videoIds);
    	$this->assertTrue($result);
    }
}