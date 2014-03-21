<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 * Enter description here ...
 * @author xucongbin
 *
 */
class Controller_Mobile_User extends Mobile {
	
	private $objLogicUser;
	
	public function before() {
		parent::before();
		$arrNeedLogin = array(
		    'editnick' => 1,
			'editintro' => 1,
			'editavatar' => 1,
			'dofollow' => 1,
			'unfollow' => 1
		);
		$action = $this->request->action();
		if(isset($arrNeedLogin[$action])) {
			$this->_needLogin();
		}
		$this->objLogicUser = new Model_Logic_Mobile_User();
	}
	
	public function action_index() {
		$arrUserCommon = $this->_user_common();
		$isAdmin = $arrUserCommon['is_admin'];
		$uid = $arrUserCommon['uid'];
		if(!$uid) {
			$this->err(null, null, null, null, "user.not_exists");
		}
		
		$arrUser = $this->objLogicUser->get($uid);
		if( !$arrUser ) {
			$this->err(null, null, null, null, "user.not_exists");
		}
		$this->objLogicUser->completeUserAvatar($arrUser);
		#feed2
		$objFeed2 = new Model_Logic_Feed2();
	    try {
	        $intSelfNum = $objFeed2->getNewUserFeedNum(array('user_id' => $uid, 'lasttime' => -1, 
	        'type' => Model_Logic_Feed2::SUBTYPE_FOLLOWING, 'no_reduce'=>false));
	        $intCircleNum = $objFeed2->getNewUserFeedNum(array('user_id' => $uid, 'lasttime' => -1, 
	        'type' => Model_Logic_Feed2::SUBTYPE_CIRCLE_SLEF, 'no_reduce'=>false));
	    } catch (Exception $e) {
	        $intCircleNum = 0;
	        $intSelfNum = 0;
	    }
//	    $arrUser['feeds_num'] = $intNum;
	    $arrUser['self_feeds_num'] = intval($intSelfNum+$intCircleNum);
	    $arrUser['is_admin'] = $isAdmin;
	    if(!$isAdmin) {
	    	$arrUser['is_fans'] = $this->objLogicUser->isFollowing($this->_uid, $uid);
	    }
	    unset($arrUser['password'], $arrUser['update_time']);
		$this->ok($arrUser);
	}
	
	public function action_editnick() {
		$nick = $this->request->param("nick");
		$uid = $this->_uid;
		if(!$nick) {
			$this->err(null, null, null, null, "sys.param.empty");
		}
		$arrRules = $this->_formRule( array('@nick') );
		$arrParams = array("nick"=>trim($nick));
		$objValidation = new Validation($arrParams, $arrRules);
		if (! $this->valid($objValidation)) {
		    return;
		}
		$objBlackword = Model_Logic_Blackword::instance();
		if ($objBlackword->filter($arrParams['nick'])) {
			$this->err(null, array('nick'=>"内容包含敏感词"), null, null, "usr.submit.valid");
		}
		$objModelUser = new Model_Data_User();
		if( $objModelUser->getByNick($arrParams['nick'], $uid) ){
			$this->err(null, null, null, null, "sys.user.nick_exists");
		}
		
		try {
			$res = $this->objLogicUser->modifyUser($uid, $arrParams);
		} catch (Model_Logic_Exception $e) {
			$code = $e->getCode();
			if($code==-2002) {
				$this->err(null, "用户名已存在！");
			}
				
		}
		if($res) {
			$this->objLogicUser->setToken($this->_token, $uid);
			$arrUserInfo = $this->objLogicUser->getUserByToken($this->_token);
			$this->ok($arrUserInfo);
		}
		$this->err(null, null, null, null, "user.modify.fail");
	}
	
	public function action_editintro() {
		$intro = $this->request->param("intro");
		$uid = $this->_uid;
		if( $intro===NULL ) {
			$this->err(null, null, null, null, "sys.param.empty");
		}
		$arrRules = $this->_formRule(array('@intro'));
	    $arrParams = array("intro"=>trim($intro));
		$objValidation = new Validation($arrParams, $arrRules);
		if (! $this->valid($objValidation)) {
		    return;
		}
		$objBlackword = Model_Logic_Blackword::instance();
		if ($objBlackword->filter($arrParams['intro'])) {
			$this->err(null, array('intro'=>"内容包含敏感词"), null, null, "usr.submit.valid");
		}
		$res = $this->objLogicUser->modifyUser($uid, $arrParams);
		if($res) {
			$this->objLogicUser->setToken($this->_token, $uid);
			$arrUserInfo = $this->objLogicUser->getUserByToken($this->_token);
			$this->ok($arrUserInfo);
		}
		$this->err(null, null, null, null, "user.modify.fail");
	}
	/**
	 * 
	 * 编辑用户信息
	 */
	public function action_edit() {
		$nick = $this->request->param("nick");
		$intro = $this->request->param("intro");
		$uid = $this->_uid;
		$arrField = array();
		$arrParams = array();
		if($nick) {
			$arrField[] = '@nick';
			$arrParams['nick'] = trim($nick);
		}
		if($intro!==NULL) {
			$arrField[] = '@intro';
			$arrParams['intro'] = trim($intro);
		}
		if(!$arrField) {
			$this->err(null, null, null, null, "sys.param.empty");
		}
		$arrRules = $this->_formRule($arrField);
		$objValidation = new Validation($arrParams, $arrRules);
		if (! $this->valid($objValidation)) {
		    return;
		}
		$objBlackword = Model_Logic_Blackword::instance();
		if ( isset($arrRules['@nick']) && $objBlackword->filter($arrParams['nick']) ) {
			$this->err(null, array('nick'=>"内容包含敏感词"), null, null, "usr.submit.valid");
		}
		if ( isset($arrRules['@intro']) && $objBlackword->filter($arrParams['intro']) ) {
			$this->err(null, array('intro'=>"内容包含敏感词"), null, null, "usr.submit.valid");
		}
		try {
			$res = $this->objLogicUser->modifyUser($uid, $arrParams);
		} catch (Model_Logic_Exception $e) {
			$code = $e->getCode();
			if($code==-2002) {
				$this->err(null, "用户名已存在！");
			}
				
		}
		if($res) {
			$this->objLogicUser->setToken($this->_token, $uid);
			$arrUserInfo = $this->objLogicUser->getUserByToken($this->_token);
			$this->ok($arrUserInfo);
		}
		$this->err(null, null, null, null, "user.modify.fail");
	}
	
	public function action_editavatar() {
		
		JKit::$log->debug("mobile-editavatar, image-", $_FILES );
		$uid = $this->_uid;
		$intMaxWidth = 600;
	    $intMaxHeight = 600;
		$avatar = isset($_FILES['file']) ? $_FILES['file']:'';
		$validAvatar = $this->validAvatar($avatar);
		if( !$validAvatar['ok'] ) {
			JKit::$log->debug("mobile-editavatar, msg-".$validAvatar['msg'].", user.submit.fail" );
			$this->err(null, $validAvatar['msg'], null, null, "user.submit.fail");
		}
		$fileName = "/tmp/".uniqid("org").".jpg";
		$imageInfo = $validAvatar['attr'];			
		if ($imageInfo[0] > $intMaxWidth && $imageInfo[1] > $intMaxHeight) {
		    $objImage = Image::factory($avatar['tmp_name']);
		    $imageMaster = Image::AUTO;
		    $diff = $objImage->width - $objImage->height;
		    if($diff>0) {
		        $imageMaster = Image::HEIGHT;
		    } elseif($diff<0) {
		        $imageMaster = Image::WIDTH;
		    }
		    $objImage->resize($intMaxWidth, $intMaxHeight, $imageMaster);
		    $objImage->save($fileName);
		} else {
			file_put_contents($fileName, file_get_contents($avatar['tmp_name']));
		}
		@unlink($avatar['tmp_name']);
		$arrThumnil = $this->objLogicUser->resizeAvatar($fileName);
		if( !$this->objLogicUser->changeAvatar($uid, $fileName, $arrThumnil) ) {
			JKit::$log->warn("mobile-editavatar, user.editavatar.fail, org-".$avatar['tmp_name'] );
			$this->err(null, null, null, null, "user.editavatar.fail");
		}
		$this->objLogicUser->setToken($this->_token, $uid);
				
		$arrUserInfo = $this->objLogicUser->getUserByToken($this->_token);
		$this->ok($arrUserInfo['avatar']['30']);
	}
	
	public function action_login() {
		$arrReturn = array(
			"user" => array(),
			"org" => array(),
			"s_token" => ""
		);
		$sessionId = $this->request->param("session_id");
		if( !$sessionId ) {
			$this->err();
		}
		if($this->_uid) {
			$arrReturn['s_token'] = $this->_token;
			$arrReturn['user'] = $this->_user;
			$this->ok($arrReturn);
		}
		$token = $this->objLogicUser->processSessionToToken($sessionId);
		$arrReturn["s_token"] = $token;
		$arrUserInfo = $this->objLogicUser->getUserByToken($token);
		if($arrUserInfo) {
			$arrReturn['user'] = $arrUserInfo;
			$this->ok($arrReturn);
		}
		$arrData = Model_Data_Sndauser::getMobileTicketInfo($sessionId);
//		$arrData = array("sdid"=>1145891720, "account"=>15821714334);
		if( !$arrData ) {
			$this->err(null, null, null, null, "user.verify.failed");
		}
		$arrReturn['org'] = array("token"=>$arrData['token'], "resultCode"=>$arrData['resultCode']);
		$uid = $arrData['sdid'];
		$arrUserInfo = $this->objLogicUser->getUserByid($uid, true);
		if(!$arrUserInfo) {
			$res = $this->objLogicUser->autoCreateUser($uid, $arrData['account']);
			if( !$res ) {
				#TODO token=>uid
				$this->err(null, null, null, null, "sys.user.create_fail");
			}
			$this->objLogicUser->setToken($token, $uid);
			$arrUserInfo = $this->objLogicUser->getUserByToken($token);
		} else {
			$this->objLogicUser->setToken($token, $uid);
		}
		$this->objLogicUser->loginToken($uid);
		$arrReturn['user'] = $arrUserInfo;
		$this->ok($arrReturn);
	}
	
	/**
	 * 
	 * 验证昵称是否被使用
	 */
	public function action_is_nick_reged() {
		$nick = trim( $this->request->param('nick') );
		if(!$nick){
			$this->err(null, null, null, null, "sys.param.empty");
		} elseif (Model_Logic_Blackword::instance()->filter($nick)) {
		    $this->err(null, null, null, null, "sys.forbidden.blackword");
		}
		$objModelUser = new Model_Data_User();
		if( $objModelUser->getByNick($nick, $this->_uid) ){
			$this->err(null, null, null, null, "sys.user.nick_exists");
		}
		$this->ok();
	}
	/**
	 * 
	 * 腾讯微博帐号登录
	 */
	public function action_tqqlogin() {
		$arrReturn = array(
			"user" => array(),
			"s_token" => "",
			"account" => ""
		);
		$accountType = Model_Data_UserConnectMobile::TYPE_TQQ;
		$account = trim( $this->request->param('account') );
		if(!$account) {
			$this->err(null, null, null, null, "sys.param.empty");
		}
		$arrReturn["account"] = $account;
		if($this->_uid) {
			$arrReturn['s_token'] = $this->_token;
			$arrReturn['user'] = $this->_user;
			$this->ok( $arrReturn );
		}
		$objModelConnectMobile = new Model_Data_UserConnectMobile();
		$arrConnect = $objModelConnectMobile->getConnectByCid($accountType, $account);
		if($arrConnect) {
			$sessionId = $arrConnect['user_id']."##".$account."##".$accountType;
			$token = $this->objLogicUser->processSessionToToken($sessionId);
			$arrReturn['s_token'] = $token; 
			$arrUserInfo = $this->objLogicUser->getUserByToken($token);
			if( $arrUserInfo ) {
				$arrReturn['user'] = $arrUserInfo;
				$this->ok($arrReturn);
			}
			$uid = $arrConnect['user_id'];
			$arrUserInfo = $this->objLogicUser->getUserByid( $uid );
			if(!$arrUserInfo) {
				#TODO avoid user not exists
				JKit::$log->warn("user not exists, account-".$account.", type-".$accountType.", uid-".$uid );
				$this->err(null, null, null, null, "sys.user.create_except");
			}
			
			$arrReturn['user'] = $arrUserInfo;
			$this->objLogicUser->setToken($token, $uid);
			$this->objLogicUser->loginToken($uid);
			$this->ok($arrReturn);
		} 
		
		$uid = $this->objLogicUser->connectAutoCreteUser($account, $accountType);
		if(!$uid) {
			$this->err(null, null, null, null, "sys.user.create_fail");
		}
		$sessionId = $uid."##".$account."##".$accountType;
		$token = $this->objLogicUser->processSessionToToken($sessionId); 
		
		$this->objLogicUser->setToken($token, $uid);
		$this->objLogicUser->loginToken($uid);
		$arrReturn['s_token'] = $token;
		$arrReturn['user'] = $this->objLogicUser->getUserByToken($token);
		$this->ok($arrReturn);
	}
	
	public function action_weibologin() {
		$arrReturn = array(
			"user" => array(),
			"s_token" => "",
			"account" => ""
		);
		$accountType = Model_Data_UserConnectMobile::TYPE_SINA;
		$account = trim( $this->request->param('account') );
		if(!$account) {
			$this->err(null, null, null, null, "sys.param.empty");
		}
		$arrReturn["account"] = $account;
		if($this->_uid) {
			$arrReturn['s_token'] = $this->_token;
			$arrReturn['user'] = $this->_user;
			$this->ok( $arrReturn );
		}
		$objModelConnectMobile = new Model_Data_UserConnectMobile();
		$arrConnect = $objModelConnectMobile->getConnectByCid($accountType, $account);
		if($arrConnect) {
			$sessionId = $arrConnect['user_id']."##".$account."##".$accountType;
			$token = $this->objLogicUser->processSessionToToken($sessionId);
			$arrReturn['s_token'] = $token; 
			$arrUserInfo = $this->objLogicUser->getUserByToken($token);
			if( $arrUserInfo ) {
				$arrReturn['user'] = $arrUserInfo;
				$this->ok($arrReturn);
			}
			$uid = $arrConnect['user_id'];
			$arrUserInfo = $this->objLogicUser->getUserByid( $uid );
			if(!$arrUserInfo) {
				#TODO avoid user not exists
				JKit::$log->warn("user not exists, account-".$account.", type-".$accountType.", uid-".$uid );
				$this->err(null, null, null, null, "sys.user.create_except");
			}
			
			$arrReturn['user'] = $arrUserInfo;
			$this->objLogicUser->setToken($token, $uid);
			$this->objLogicUser->loginToken($uid);
			$this->ok($arrReturn);
		} 
		
		$uid = $this->objLogicUser->connectAutoCreteUser($account, $accountType);
		if(!$uid) {
			$this->err(null, null, null, null, "sys.user.create_fail");
		}
//		$arrUserInfo = $this->objLogicUser->getUserByid( $uid );
		$sessionId = $uid."##".$account."##".$accountType;
		$token = $this->objLogicUser->processSessionToToken($sessionId); 
		
		$this->objLogicUser->setToken($token, $uid);
		$this->objLogicUser->loginToken($uid);
		$arrReturn['s_token'] = $token;
		$arrReturn['user'] = $this->objLogicUser->getUserByToken($token);
		$this->ok($arrReturn);
		
	}
	/**
	 * 
	 * 宋总的通行证登录
	 */
	public function action_sndalogin() {
		$arrReturn = array(
			"user" => array(),
			"s_token" => ""
		);
		$sessionId = trim( $this->request->param("session_id") );
		$intSdid = intval( $this->request->param("sdid") );
		if( !$sessionId || !$intSdid ) {
			$this->err(null, null, null, null, "sys.param.empty");
		}
		if($this->_uid) {
			$arrReturn['s_token'] = $this->_token;
			$arrReturn['user'] = $this->_user;
			$this->ok($arrReturn);
		}
		$token = $this->objLogicUser->processSessionToToken( $intSdid."##".$sessionId );
		$arrReturn["s_token"] = $token;
		$arrUserInfo = $this->objLogicUser->getUserByToken($token);
		if($arrUserInfo) {
			$arrReturn['user'] = $arrUserInfo;
			$this->ok($arrReturn);
		}
		$uid = $intSdid;
		$arrUserInfo = $this->objLogicUser->getUserByid($uid, true);
		if(!$arrUserInfo) {
			$res = $this->objLogicUser->autoCreateUser($uid, $intSdid, false);
			if( !$res ) {
				#TODO token=>uid
				$this->err(null, null, null, null, "sys.user.create_fail");
			}
			$this->objLogicUser->setToken($token, $uid);
			$arrUserInfo = $this->objLogicUser->getUserByToken($token);
		} else {
			$this->objLogicUser->setToken($token, $uid);
		}
		$this->objLogicUser->loginToken($uid);
		$arrReturn['user'] = $arrUserInfo;
		$this->ok($arrReturn);
	}
	
	public function action_videos() {
		$arrUserCommon = $this->_user_common();
		$isAdmin = $arrUserCommon['is_admin'];
		$uid = $arrUserCommon['uid'];
		$defaultType = "watched";
		$typeMethodRelMap = array(
			"watched" => "getWatched",
			"commented" => "getCommented",
			"watch_later" => "getWatchLater",
			"shared"	=> "getShared",
			"mooded" => "getMooded"
		);
		if(!$isAdmin) {
			$typeMethodRelMap = array(
				"commented" => "getCommented",
				"mooded" => "getMooded"
			);
			
			$defaultType = "commented";
		}
		
		$type = $this->request->param("type", $defaultType);
		
		$offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 4);
		$modelLogicVideo = new Model_Logic_Video();
		$arrFieldTmp = Model_Logic_Video::$basicFieldsForMobile;
		
		if( !isset($typeMethodRelMap[$type]) ) {
			$this->err(null, null, null, null, "sys.type.not_exist");
		}
		if ($type == 'commented') {
	        $videos = $modelLogicVideo->getCommented($uid, $offset, $count, true, $arrFieldTmp);
	        
	    } else {
	    	$method = $typeMethodRelMap[$type];
	    	$videos = $modelLogicVideo->$method($uid, $offset, $count, $arrFieldTmp);
	    }
	    $this->objLogicUser->completeVideoPic( $videos['data'] );
	    $arrVideo = array_values($videos['data']);
		$objLogicUtil = new Model_Logic_Mobile_Util();
		$objLogicUtil->appendVideoMp4Playurl($arrVideo);
	    $videos = array('total' => $videos['total'], 'data' => $arrVideo);
	    $this->ok($videos);
	}
	
	public function action_circles() {
		$arrUserCommon = $this->_user_common();
		$objLogicCircle = new Model_Logic_Circle();
		$isAdmin = $arrUserCommon['is_admin'];
		$uid = $arrUserCommon['uid'];
		// subscribed, shared
		$type = $this->request->param("type", "subscribed");
		
		$offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 2);
		
		if ($type == 'shared') {
			$arrCircleIds = $this->objLogicUser->getSharedCirclesByUid($uid, true);
	    } else {
	    	$arrCircleIds = $this->objLogicUser->getUserCirclesByUid($uid, true);
	    }
	    $cids = array_slice($arrCircleIds, $offset, $count);
	    $arrCircles = $this->objLogicUser->getCircleVideos($cids, $this->_uid);
	    $this->objLogicUser->completeCirclePic($arrCircles);
	    $objLogicCircle->completeMobilePicPath($arrCircles);
	    $arrReturn = array('total' => count($arrCircleIds), 'data' =>$arrCircles );
	    $this->ok($arrReturn);
	}
	
	public function action_follow() {
		$arrUserCommon = $this->_user_common();
		$isAdmin = $arrUserCommon['is_admin'];
		$uid = $arrUserCommon['uid'];
		$hidden = $arrUserCommon['is_admin'] ? null : false;
		$offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 6);
	    
	    $total = 0;
	    
	    try {
	        $followings = (array) $this->objLogicUser->followings($uid, $offset, $count,
	            null, $hidden, $total);
	        if (! empty($followings)) {
	            $users = $this->objLogicUser->getMulti(Arr::pluck($followings, 'following'), true);
	        } else {
	            $users = array();
	        }	        
	    } catch (Exception $e) {
	        $users = array();
	    }
	    $this->objLogicUser->completeUserAvatar($users);
	    $this->ok(array(
	    	"total" => $total,
	    	"data" => array_values($users)
	    ));
	}
	
	public function action_fans() {
		
		$arrUserCommon = $this->_user_common();
		$isAdmin = $arrUserCommon['is_admin'];
		$uid = $arrUserCommon['uid'];
		$hidden = $arrUserCommon['is_admin'] ? null : false;
		$offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 6);
	    
	    $total = 0;
		try {
	        $followers = $this->objLogicUser->followers($uid, $offset, $count,
	                null, $hidden, $total);
	        $arrTmp = array();
	        foreach ($followers as $v) {
	            $arrTmp[$v['user']] = @$v['bidirectional'];
	        }
	        if (! empty($arrTmp)) {
	            $users = $this->objLogicUser->getMulti(array_keys($arrTmp), true);
	            if ($isAdmin) {
	                $objMsg = new Model_Logic_Msg();
	                $objMsg->resetNewFansCounter($this->_uid);         
    	            foreach ($users as $k => $v) {
    	                $users[$k]['bidirectional'] = $arrTmp[$k];
    	            }
	            }
	        } else {
	            $users = array();
	        }
	    } catch (Exception $e) {
	        $users = array();
	    }
	    
	    $this->objLogicUser->completeUserAvatar($users);
	    $this->ok(array(
	    	"total" => $total,
	    	"data" => array_values($users)
	    ));
	}
	
	/**
	 * 关注用户
	 */
	public function action_dofollow()
	{
	    $following = (int) $this->request->param('id');
	    if ($following <= 0) {
	        $this->err(null, 'invalid uid');
	    }
	    $hidden = (bool) $this->request->param('hidden', false);
		if( $following==$this->_uid ) {
	    	$this->err(NULL, "自己不能关注自己呀");
	    }
	    $modelLogicUser = new Model_Logic_User();	    
	    try {
	        $modelLogicUser->follow($this->_uid, $following, $hidden, $this->_user['nick']);
	        if (! $hidden) {
	            $objMsg = new Model_Logic_Msg();
	            $objMsg->sendFollowMsg($this->_uid, $this->_user['nick'], $following);
	        }
	    } catch (Model_Logic_Exception $e) {
	        $this->err(array('err' => $e->getCode(), 'msg' => $e->getMessage()));
	    }
	    $this->ok();
	}
	
	/**
	 * 取消关注
	 */
	public function action_unfollow()
	{
	    $following = (int) $this->request->param('id');
	    if ($following <= 0) {
	        $this->err(null, 'invalid uid');
	    }
	    
	    $modelLogicUser = new Model_Logic_User();
	    try {
	        $modelLogicUser->unfollow($this->_uid, $following);
	    } catch (Model_Logic_Exception $e) {
	        $this->err(array('err' => $e->getCode(), 'msg' => $e->getMessage()));
	    }
	    $this->ok();
	}
	
	private function _user_common() {
		$isAdmin = true;
		$uid = $this->request->param("id");
		if( $uid===NULL ) {
			$this->_needLogin();
			$uid = $this->_uid;
		} else {
			$uid = intval($uid);
			if( $uid!=$this->_uid ) {
				$isAdmin = false;
			}
		}
		
		return array(
			"uid" => $uid,
			"is_admin" => $isAdmin
		);
	}
	
	/**
	 * @param array $avatar Array (
            [name] => Water lilies.jpg
            [type] => image/jpeg
            [tmp_name] => /tmp/phpeFK8jV
            [error] => 0
            [size] => 83794
        )
	 */
	private function validAvatar($avatar) {
		$arrReturn = array(
			'ok' => false,
			'msg' => ''
		);
		if( !isset($avatar['tmp_name']) || ! $avatar['tmp_name']) {
			$arrReturn['msg'] = "头像不能为空";
			return $arrReturn;
		}
		
		if($avatar['error'] !== UPLOAD_ERR_OK) {
			$arrReturn['msg'] = "头像上传失败";
			return $arrReturn;
		}
		
		$arrImgAttr = getimagesize($avatar['tmp_name']);
		$arrValidType = array(IMAGETYPE_GIF => true, IMAGETYPE_PNG => true,
		    IMAGETYPE_JPEG => true);
		if(! is_array($arrImgAttr) || ! isset($arrValidType[$arrImgAttr[2]])) {
			$arrReturn['msg'] = "图片格式错误";
			return $arrReturn;
		}
		$arrReturn['attr'] = $arrImgAttr;

		$maxSizeLimit = 5242880;
		if($avatar['size'] > $maxSizeLimit) {
			$arrReturn['msg'] = "图片大小不能超过5M";
			return $arrReturn;
		} 
		$arrReturn['ok'] = true;
		return $arrReturn;
	}
	
	protected function _formRule($arrField = null) {
	    $arrRules = array(
            '@nick' => array(
                    'datatype' => 'reg',
                    'reqmsg' => '用户名',
            		'reg-pattern' => "/^[\u4e00-\u9fa5\_a-zA-Z\d\-0-9]{2,10}$/",
            ),
            '@intro' => array(
                    'datatype' => 'text',
//                    'reqmsg' => '简介',
                    'maxlength' => 100
            ),
	    );

	    if ($arrField == null) {
	        $arrRes = $arrRules;
	    } else {	    
    	    $arrRes = array();
    	    foreach ($arrField as $v) {
    	        $arrRes[$v] = $arrRules[$v];
    	    }
	    }
	    
	    return $arrRes;
	}
}