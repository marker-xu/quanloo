<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 * 示例
 * @author xucongbin
 *
 */
class Controller_Mobile_Welcome extends Mobile {
	
	public function action_index() {
		$this->ok("hello world");
	}
	
	public function action_gettoken() {
		$uid = (int) $this->request->param("uid");
		$objLogicUser = new Model_Logic_Mobile_User();
		$sessionId = $uid;
		$token = $objLogicUser->processSessionToToken($sessionId);
		if($objLogicUser->getUserByToken($token)) {
			$this->ok($token);
		}
		$arrUserInfo = $objLogicUser->getUserByid($uid);
		if(!$arrUserInfo) {
			$this->err(null, null, null, null, "user.not_exists");
		}
		$objLogicUser->setToken($token, $uid);
		$this->ok($token);
	}
}