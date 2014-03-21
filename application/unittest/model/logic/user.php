<?php defined('SYSPATH') or die('No direct script access.');

class ModelLogicUserTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Logic_User();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }
    
    function test_subscribedCircleCount()
    {
    	return;
        $count = $this->_model->subscribedCircleCount(10001);
        JKit::$log->debug(__FUNCTION__, $count);
        $this->assertIsA($count, 'int');
    }
    
    function test_register() {
    	return ;
    	$email = "123456@b.com";
    	$passwd = "b@b.com";
    	$nick = "huluwann";
    	$avatar = "/home/worker/snda-php/videosearch/resource/img/c3.jpg";
    	$result = $this->_model->register($email, $passwd, $nick, $avatar);
    	var_dump($result);
    	$this->assertIsA( $result, "float" );
    }
    
	function test_login() {
		return;
    	$this->_model->login(1013, true);
    	var_dump(Session::instance()->get('user'));
    }
    
    function test_logout() {
    	return;
    	$this->_model->logout(1013);
    	var_dump(Session::instance()->get('user'));
    }
    
	function test_modify() {
    	
    }
    
	function test_checkPasswd() {
		return;
		$passwd = 'b@b.com';
		$passwd = '111111';
    	$result = $this->_model->checkPasswd('123456@b.com', $passwd);
    	
    	var_dump($result);
    	$this->assertTrue( $result );
    }
    
    function test_getUserByid() {
    	return;
    	$result = $this->_model->getUserByid(1013);
    	
    	var_dump($result);
    	$this->assertIsA( $result, "array" );
    }
    
	function test_getMultiUserByIds() {
		return;
    	$result = $this->_model->getMultiUserByIds( array(1011, 1013, 1012, 1010) );
    	
    	var_dump($result);
    	$this->assertIsA( $result, "array" );
    }
    
	function test_getUserCirclesByUid() {
		return;
    	$result = $this->_model->getUserCirclesByUid( 1013, true );
    	
    	var_dump($result);
    	$this->assertIsA( $result, "array" );
    }
    
	function test_getRecommendCircles() {
		return;
    	$result = $this->_model->getRecommendCircles( 1013 );
    	
    	var_dump($result);
    	$this->assertIsA( $result, "array" );
    }
    
    function test_getCircleVideos() {
    	return;
    	$cids = array(
    		10051,
    		10006,
    		10066,
    	);
    	$uid = 10008;
    	$result = $this->_model->getCircleVideos($cids, $uid);
    	
    	var_dump($result);
    	$this->assertIsA( $result, "array" );
    }
    
    function test_follow() 
    {
        return;
        $user = 794123477;
        $followings = array(811877992, 1249313851, 131846);
        $biFollowings = array(1668717529);
        $hiddenFollowings = array(1834117719);
        
	    $modelDataUserFollowing = new Model_Data_UserFollowing();
        $modelDataUserFollowing->delete(array(
        	'user' => array(
            	'$in' => array_merge(array($user), $biFollowings)
            )
        ));
	    $modelDataUserStatAll = new Model_Data_UserStatAll();
	    $modelDataUserStatAll->update(array(
        	'_id' => array(
            	'$in' => array_merge(array($user), $followings, $biFollowings, 
	                $hiddenFollowings)
            )
        ), array(
            'followings_count' => 0,
            'followers_count' => 0
        ));
        
        sleep(1);
        
        foreach ($followings as $following) {
            $result = $this->_model->follow($user, $following);
            $this->assertTrue($result);
        }
        foreach ($biFollowings as $following) {
            $result = $this->_model->follow($user, $following);
            $this->assertTrue($result);
            sleep(1);
            $result = $this->_model->follow($following, $user);
            $this->assertTrue($result);
        }
        foreach ($hiddenFollowings as $following) {
            $result = $this->_model->follow($user, $following, true);
            $this->assertTrue($result);
        }
        
        sleep(1);
        
        $result = $this->_model->isFollowing($user, reset($followings));
        $this->assertTrue($result);
        $result = $this->_model->isFollowing($user, reset($biFollowings), true);
        $this->assertTrue($result);
        $result = $this->_model->isFollowing(reset($biFollowings), $user, true);
        $this->assertTrue($result);
        $result = $this->_model->isFollowing($user, reset($hiddenFollowings), 
            null, true);
        $this->assertTrue($result);
        
        $total = 0;
        $result = $this->_model->followings($user, 0, 1, null, null, $total);
        $this->assertTrue(count($result) == 1);
        $this->assertTrue($total == 5);
        
        $total = 0;
        $result = $this->_model->followers(current($biFollowings), 0, 1, null, 
            null, $total);
        $this->assertTrue(count($result) == 1);
        $this->assertTrue($total == 1);
        
        $result = $this->_model->unfollow($user, current($followings));
        $this->assertTrue($result);
        
        sleep(1);
        
        $result = $this->_model->isFollowing($user, current($followings));
        $this->assertTrue(!$result);
        $total = 0;
        $result = $this->_model->followings($user, 0, 0, null, null, $total);
        $this->assertTrue($total == 4);
    }
}
