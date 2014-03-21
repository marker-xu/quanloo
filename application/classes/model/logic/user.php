<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 用户、第三方帐号、用户视频关系等页面逻辑
 * @author xucongbin
 */
class Model_Logic_User extends Model
{
    public static $USER_UNREAD_MSG_COUNT_KEY = array('intFollowUnread', 'intPeerUnread', 'intMentionUnread');
    public static $basicFields = array('email', 'is_email_verified', 'nick', 'avatar', 'intro');
    
	protected $objModelUser;
	
	public function __construct() {
		$this->objModelUser = new Model_Data_User();
	}
	
	/**
	 * 查询单个用户的基本信息，以及统计信息
	 * @param string $id
	 * @param bool $isNeedStat 是否带上统计信息
	 * @return array|null
	 */
	public function get($id, $isNeedStat=true)
	{
	    $modelDataUser = new Model_Data_User();
	    Profiler::startMethodExec();
	    $user = $modelDataUser->get($id);
		Profiler::endMethodExec(__FUNCTION__.' get');
	    
	    if ($user && $isNeedStat) {
    	    $users = array($user);
    	    $modelLogicStat = new Model_Logic_Stat();  
	        Profiler::startMethodExec();
    	    $modelLogicStat->complementUserStatInfo($users);
		    Profiler::endMethodExec(__FUNCTION__.' complementUserStatInfo');
    	    $user = array_shift($users);
	    }
	    
	    return $user;
	}
	
	/**
	 * 查询多个用户的基本信息，以及统计信息
	 * @param array $ids
	 * @param bool $keepOrder 是否保持传入参数中ID的顺序
	 * @param bool $isNeedStat 是否带上统计信息
	 * 
	 * @return array
	 */
	public function getMulti($ids, $keepOrder = false, $isNeedStat=true) 
	{
	    $modelDataUser = new Model_Data_User();
	    $users = $modelDataUser->getMulti($ids, self::$basicFields);
	    $userIds = Arr::pluck($users, '_id');
	    if (!$userIds) {
	        return array();
	    }
	    if($isNeedStat) {
	    	$modelLogicStat = new Model_Logic_Stat();  
	    	$modelLogicStat->complementUserStatInfo($users);
	    }
	    
        if ($keepOrder) {
	        $tmp = array();
	        foreach ($ids as $id) {
	            if (isset($users[$id])) {
	                $tmp[$id] = $users[$id];
	            }
	        }
	        $users = $tmp;
	    }
	    
	    return $users;
	}
	
	/**
	 * 用户注册
	 * @param string $email
	 * @param string $passwd
	 * @param string $nick
	 * @param array $avatar 头像地址，通过上传接口，拼好的数据,groupXX/filename;
	 *  array(
	 * 	'org' => 原始图,
     *  160 => 120*120的缩略图
     *  48 => 48*48的缩略图
     *  30 => 30*30的缩略图
	 * )
	 * @param array $extra array(
	 * 	'intro' => 简介
	 * )
	 * @throws Model_Logic_Exception -2002昵称重复
	 * 
	 * @return uid|boolean
	 */
	public function register($email, $passwd, $nick, $avatar, $extra=array()) {
/*
 * 现在邮箱不要求必填，不要求唯一了
 *  		if( !$email ) {
			throw new Model_Logic_Exception("email is emtpy", -2005, NULL);
		}
		
		if ( $email && $this->objModelUser->getByEmail($email) ) {
			throw new Model_Logic_Exception("email exists", -2001, NULL);
		} */
		
		if ( $nick && $this->objModelUser->getByNick($nick) ) {
			throw new Model_Logic_Exception("nick exists", -2002, NULL);
		}
		$salt = $_SERVER['REQUEST_TIME'];
		$arrParams = array(
			'password' => md5($passwd.$salt),
			'nick' => $nick,
			'avatar' => $avatar ,
			'create_time' => new MongoDate($_SERVER['REQUEST_TIME'])
		);
		$arrParams = array_merge($arrParams, $extra);
		$ret = $this->objModelUser->addUser($email, $arrParams);
		if($ret) {
		    $this->login($ret, true);
		}
		return $ret;
	}
	/**
	 * 
	 * 用户登录，生成session，记录feed
	 * @param int $uid
	 * @param bool $bolFromMaster 是否从数据库主库查询 
	 */
    public function login($uid, $bolFromMaster = FALSE) {
    	$user = Session::instance()->get('user');
    	if( isset($user['_id']) && $user['_id']==$uid ) {
    		return true;
    	}
		$userInfo = $this->getUserByid($uid, $bolFromMaster);
		if ($userInfo) {
		    Session::instance()->set('user', $userInfo);
		    $this->objModelUser->modifyById($uid, array(
		        'last_login_time' => new MongoDate(),
		        'last_login_ip' => Request::$client_ip
		    ));
		    #TODO connect
		    
		    return true;
		} 
		return false;
    }
   /**
    * 
    * Enter description here ...
    * @param $uid
    * @param $arrParams
    * @throws Model_Logic_Exception -2002昵称重复, 
    * 
    * @return boolean
    */
    public function modifyUser($uid, $arrParams) {
    	if ( isset($arrParams['nick']) && 
    	$this->objModelUser->getByNick($arrParams['nick'], $uid) ) {
    		throw new Model_Logic_Exception("nick exists", -2002, NULL);
    	}
    	if( isset($arrParams['password']) ) {
    		$userInfo = $this->objModelUser->get($uid, array( "create_time" ));
    		$arrParams['password'] = md5($arrParams['password'].$userInfo['create_time']->sec);
    	}

    	return $this->objModelUser->modifyById($uid, $arrParams);
    }
    /**
     * 
     * 换头像
     * @param int $uid
     * @param string $org
     * @param array $arrThumnil
     * 
     * @return boolean
     */
    public function changeAvatar($uid, $org, $arrThumnil) {
    	$arrUserInfo = $this->objModelUser->get($uid);
    	if (!$arrUserInfo) {
    		return false;
    	}
    	$avatarOrgReturn = Model_Data_User::uploadAvatar($org, "png");
    	if ( !$avatarOrgReturn ) {
    		return false;
    	}
    	
    	$avatar = array(
    		"org" => $avatarOrgReturn['group_name']."/".$avatarOrgReturn['filename']
    	);
    	@unlink($org);
    	$arrThumnilTmp = array();
    	foreach($arrThumnil as $k=>$thumbTmp) {
    		$tmpThumnilReturn = Model_Data_User::uploadAvatar($thumbTmp, "png");
    		if($tmpThumnilReturn) {
    			$avatar[$k] = $tmpThumnilReturn['group_name']."/".$tmpThumnilReturn['filename'];
    			$arrThumnilTmp[] = $tmpThumnilReturn;
    		}
    		@unlink($thumbTmp);
    	}
    	
    	$res = $this->modifyUser($uid, array('avatar' => $avatar));
    	if(!$res) {
    		Model_Data_User::removeAvatar($avatarOrgReturn['group_name'], $avatarOrgReturn['filename']);
    		foreach($arrThumnilTmp as $arrTmp) {
    			Model_Data_User::removeAvatar($arrTmp['group_name'], $arrTmp['filename']);
    		}
    	} else {
    		if( $arrUserInfo['avatar'] && is_array($arrUserInfo['avatar'])) {
    			$arrTmp = array();
    			foreach($arrUserInfo['avatar'] as $val) {
    				$arrTmp = explode("/", $val, 2);
    				Model_Data_User::removeAvatar($arrTmp[0], $arrTmp[1]);
    			}
    		}
    	}
    	
    	return $res;
    }
    
    /**
     * 
     * 退出，毁灭session，添加feed
     * @param int $uid
     */
	public function logout($uid)
	{
		Session::instance()->destroy();
	}
	/**
	 * 
	 * 更新session
	 * @param unknown_type $uid
	 */
	public function changeSession($uid) {
		$userInfo = $this->getUserByid($uid, true);
		if ($userInfo) {
		    Session::instance()->set('user', $userInfo);
		    
		    return true;
		} 
		return false;
	}
    /**
     * 
     * 获取用户信息
     * @param int $uid
     * @param bool $bolFromMaster 从主库查询
     * 
     * @reutrn array|boolean
     */
    public function getUserByid($uid, $bolFromMaster = FALSE) {
        if ($bolFromMaster) {
            $this->objModelUser->setSlaveOkay(false);
        }
    	$userInfo = $this->objModelUser->get($uid);
    	if($userInfo) {
    		$userInfo['create_time'] = $userInfo['create_time']->sec;
    		$userInfo['update_time'] = $userInfo['update_time']->sec;
    		$userInfo['last_login_time'] = $userInfo['last_login_time']->sec;
    	}
    	return $userInfo;
    }
    
    public function getMultiUserByIds($arrUids) {
    	$arrUserList = $this->objModelUser->getMulti($arrUids, array('_id', 'email', 'nick', 'avatar'), true);
    	
    	return $arrUserList;
    }
    /**
     * 关注圈子数
     * @param int $id
     * @return int
     */
    public function subscribedCircleCount($id)
    {
        $cache = Cache::instance('web');
        $key = 'sub.circle.count:'.$id;
        $value = $cache->get($key);
        if (is_null($value)) {
            $modelDataCircleUser = new Model_Data_CircleUser();
            $value = $modelDataCircleUser->subscribedCircleCount($id);
            $cache->set($key, $value, 3600);
            $modelDataUserStatAll = new Model_Data_UserStatAll();
            $modelDataUserStatAll->update(array('_id' => $id), array(
            	'subscribed_circle_count' => (int) $value
            ), array('upsert' => TRUE));
        }
        return (int) $value;
    }
    /**
     * 
     * 获取用户统计数据
     * @param string $uid
     * 
     * @return array(
     * 	sub_circle_count =>关注圈子数
     * like_count	 => 推次数
     * watch_count	 => 观看次数
     * comment_count	 => 评论次数
     * activity	 => 活跃度
     * )
     */
    public function getUserStatsByUid($uid) {
    	$objUserStatAllModel = new Model_Data_UserStatAll();
    	return $objUserStatAllModel->getByUid($uid);
    }
    /**
     * 
     * 获取用户圈子信息
     * @param int $uid
     * @param boolean $onlyCid 是否只返回cid列表
     * @param int $sortType 排序方式，0: 按照order倒序，1:按照加入时间倒序
     * 
     * @return array
     */
    public function getUserCirclesByUid($uid, $onlyCid=false, $sortType=NULL) {
    	$arrReturn = array();
    	$objModelCircleUser = new Model_Data_CircleUser();
    	$query = array("user_id"=>intval( $uid ));
    	$sort = array('order' => 1);
    	if($sortType) {
    		$sort = array("create_time"=>-1);
    	}
	    Profiler::startMethodExec();
    	$arrTmpCids = $objModelCircleUser->find($query, array("circle_id"), $sort);
		Profiler::endMethodExec(__FUNCTION__.' find');
    	if(!$arrTmpCids) {
    		return $arrReturn;
    	}
    	$circleIds = Arr::pluck($arrTmpCids, "circle_id");
    	if($onlyCid) {
    		return $circleIds;
    	}
    	$objModelCircle = new Model_Logic_Circle();
    	
	    Profiler::startMethodExec();
    	$arrReturn = $objModelCircle->getMulti($circleIds, true);
		Profiler::endMethodExec(__FUNCTION__.' getMulti');
    	
    	return $arrReturn;
    }
    
    public function getSharedCirclesByUid($uid, $onlyCid=false) {
    	$arrReturn = array();
    	$objModelUserShareCircle = new Model_Data_Usersharecircle($uid);
    	$arrTmpCids = $objModelUserShareCircle->getList(0, -1, true);
    	if(!$arrTmpCids) {
    		return $arrReturn;
    	}
    	foreach($arrTmpCids as $k=>$tmpCid) {
    		$arrTmpCids[$k] = intval($tmpCid);
    	}
    	if($onlyCid) {
    		return $arrTmpCids;
    	}
    	$objModelCircle = new Model_Logic_Circle();
    	
    	$arrReturn = $objModelCircle->getMulti($arrTmpCids, true);
    	
    	return $arrReturn;
    }
    
    /**
     * 
     * 获取用户注册推荐的圈子（聚合）
     * @param array $arrTags
     * @param int $offset
     * @param int $count
     * @param int $uid
     * @param boolean $isShuffle
     * 
     * @return array;
     */
    public function getUserRegisterCirclesByTags( $arrTags, $offset=0, $count=6, $uid=null, $isShuffle=false ) {
    	$arrCircleReason = array();
    	$arrCids = array();
    	$objLogicCircle = new Model_Logic_Circle();
    	//cache住，做分页
    	$objCache = Cache::instance('web');
        
    	sort($arrTags);
    	$strKey = "RegisterCircles:".md5(implode(",", $arrTags));
    	$strRet = $objCache->get($strKey, null);
    	$arrTmpCircleReason = json_decode($strRet, true);
    	if(!$arrTmpCircleReason) {
	    	$objModelCircleStatAll = new Model_Data_CircleStatAll();
	    	$arrTagCircles = Model_Data_Recommend::getUserRegisterCirclesByTags($arrTags, 0, 24);
	    	$arrTagCids = array_keys($arrTagCircles);
	    	$arrPopCids = $objModelCircleStatAll->getPopularCircleIds(0, 24);
	    	while ($arrTagCids && $arrPopCids) {
	    		$arrCids[] = array_shift($arrTagCids);
	    		$arrCids[] = array_shift($arrPopCids);
	    	}
	    	if($arrTagCids) {
	    		$arrCids = array_merge($arrCids, $arrTagCids);
	    	}
	    	if($arrPopCids) {
	    		$arrCids = array_merge($arrCids, $arrPopCids);
	    	}
	    	$arrCids = array_unique($arrCids);
	    	$arrCircleReason = array();
	    	$arrCids = array_slice($arrCids, 0, 24);
	    	foreach($arrCids as $tmpCid) {
	    		if( isset($arrTagCircles[$tmpCid]) ) {
	    			$arrCircleReason[$tmpCid] = $arrTagCircles[$tmpCid];
	    		} else {
	    			$arrCircleReason[$tmpCid] = array("cid"=>$tmpCid, "reason"=>"热门圈子");
	    		}
	    	}
	    	
	    	$objCache->set($strKey, json_encode($arrCircleReason), 600);
    	} else {
    		$arrCircleReason = $arrTmpCircleReason;
    		$arrCids = array_keys($arrTmpCircleReason);
    	}
    	if($uid) {
    		$arrSubscribedCids = $this->getUserCirclesByUid($uid, true);
    		$arrCids = array_diff($arrCids, $arrSubscribedCids);
    	}
    	if($isShuffle) {
    		shuffle($arrCids);
    	}
    	$arrCircles = $objLogicCircle->getMulti(array_slice($arrCids, $offset, $count), true, null, false);
    	foreach($arrCircles as $tmpCid=>$row) {
    		$arrCircles[$tmpCid] = array_merge($row, $arrCircleReason[$tmpCid]);
    	}
    	return $arrCircles;
    }
    /**
     * 
     * 获取圈子视频九宫格
     * @param array $cids
     * @param int $uid
     * 
     * @return array(
     * 	array(
     * 		circle => 圈子信息,
     * 		video_list => 视频
     * 	),
     *  ...
     * )
     */
    public function getCircleVideos( $cids, $uid ) {
    	$arrReturn = array();
    	$objLogicCircle = new Model_Logic_Circle();
	    Profiler::startMethodExec();
    	$arrTmpCircles = $objLogicCircle->getMulti($cids, true);
		Profiler::endMethodExec(__FUNCTION__.' circle getMulti');
    	if(!$arrTmpCircles) {
    		return $arrReturn;
    	}
	    Profiler::startMethodExec();
    	$this->complementUserCircleRel($arrTmpCircles, $uid);
		Profiler::endMethodExec(__FUNCTION__.' complementUserCircleRel');
    	return $arrTmpCircles;
    }
    
    public static function getUserMsgCount($intUid, $mixedKey = null, &$arrOrig = null) {
        $objTmp = new Model_Logic_Msg();
        $mixedKey2 = null;
        if (is_array($mixedKey)) {
            $mixedKey2 = $mixedKey;
            $mixedKey = null;
        }
        $mixedRet = $objTmp->getMsgCountInfo($intUid, $mixedKey);
        if ($mixedKey2) {
            $intTmp = 0;
            foreach ($mixedKey2 as $v) {
                $intTmp += (int) $mixedRet[$v];
            }
            $arrOrig = $mixedRet;
            $mixedRet = $intTmp;
        }
        return $mixedRet;
    }
    
    /**
     * 
     * 补齐圈子信息里的用户关系
     * @param array $data 二维数组，里面包含_id（圈子ID）
     * @param int $uid
     */
    public function complementUserCircleRel(&$data, $uid) {
    	if(!$data) {
    		return ;
    	}
    	$cids = Arr::pluck($data, '_id');
    	$objModelCircleUser = new Model_Data_CircleUser();
    	$arrTmpRel = array();
    	$arrTmpShared = array();
    	if($uid) {
	    	$query = array(
	    		"circle_id" => array(
	    			'$in' => $cids
	    		),
	    		"user_id" => intval( $uid )
	    	);
		    Profiler::startMethodExec();
	    	$arrResult = $objModelCircleUser->find($query);
			Profiler::endMethodExec(__FUNCTION__.' circle user find');
	    	
	    	if( $arrResult ) {
	    		$tmpCircleUser = array();
	    		foreach($arrResult as $tmpCircleUser) {
	    			$arrTmpRel[$tmpCircleUser['circle_id']] = 1;
	    		}
	    	}
	    	#TODO 分享
		    Profiler::startMethodExec();
	    	$arrTmpCids = $this->getSharedCirclesByUid($uid, true);
			Profiler::endMethodExec(__FUNCTION__.' getSharedCirclesByUid');
	    	
	    	if($arrTmpCids) {
	    		$arrTmpShared = array_combine($arrTmpCids , range(0, count($arrTmpCids)-1));
	    	}
    	}
    	
    	foreach($data as $k=>$row) {
    		$row['is_focus'] = isset($arrTmpRel[$row["_id"]]) ?  true : false;
    		$row['is_shared'] = isset($arrTmpShared[$row["_id"]]) ?  true : false;
    		$data[$k] = $row;
    	}
    }
    /**
     * 
     * 生成邮箱验证码，并记入缓存,15分钟有效期
     * @param string $email
     * @throws Model_Logic_Exception -2010 邮箱不存在
     * 
     * @return string
     */
    public function createEmailVerifyCode($email) {
    	$arrUserInfo = $this->objModelUser->getByEmail($email);
    	if ( !$arrUserInfo ) {
    		throw new Model_Logic_Exception("email not exits", -2010, NULL);
    	}
    	$strCode =  base64_encode(md5($email."_".microtime(true)));
    	$redis = Database::instance("web_redis_master");
        $objDb = $redis->getRedisDB(1);
    	$res = $objDb->set("user:".$email, $strCode, 15*60);
    	if($res) {
    		return $strCode;
    	}
    	return false;
    }
    /**
     * 
     * 获取帐号对应的邮箱激活码
     * @param string $email
     * 
     * @return string
     */
    public function getEmailVerifyCode($email) {
    	$redis = Database::instance("web_redis_master");
        $objDb = $redis->getRedisDB(1);
    	return $objDb->get("user:".$email);
    }
    /**
     * 
     * 返回缩略图
     * @param string $sourceImage 原始图
     * 
     * @return array(
     * 	200 => 200*200的缩略图
     *  160 => 160*160的缩略图
     *  48 => 48*48的缩略图
     *  30 => 30*30的缩略图
     * )
     */
    public function resizeAvatar($sourceImage) {
    	$thumb200TmpName = "/tmp/".uniqid("200").".jpg";
    	$thumb160TmpName = "/tmp/".uniqid("160").".jpg";
    	$thumb48TmpName = "/tmp/".uniqid("48").".jpg";
    	$thumb30TmpName = "/tmp/".uniqid("30").".jpg";
		$objImage = Image::factory($sourceImage);
		$objImage->resize(200, 200);
		$objImage->save($thumb200TmpName, 85);
		$objImage->resize(160, 160);
		$objImage->save($thumb160TmpName, 85);
		$objImage->resize(48, 48);
		$objImage->save($thumb48TmpName, 92);
		$objImage->resize(30, 30);
		$objImage->save($thumb30TmpName, 92);
		return array(
			200 => $thumb200TmpName,
			160 => $thumb160TmpName,
			48  => $thumb48TmpName,
			30  => $thumb30TmpName,
		);
    }
    /**
     * 
     * 获取用户相关视频
     * @param int $uid
     * @param string $type
     * @param int $offset
     * @param int $length
     * @throws Model_Logic_Exception
     * @return array
     */
    public function getUserVideos($uid, $type, $offset=0, $length=10) {
    	$arrReturn = array(
    		'count' => 0,
    		'data' => array()
    	);
    	$objModelUserVideo = new Model_Data_UserVideo($uid);
    	
		if($type===NULL) {
			$type = Model_Data_UserVideo::TYPE_COMMENTED;
		}
		switch ($type) {
			case Model_Data_UserVideo::TYPE_SHARED:
			case Model_Data_UserVideo::TYPE_MOODED:
			case Model_Data_UserVideo::TYPE_WATCHED:
			case Model_Data_UserVideo::TYPE_WATCHLATER:
				$arrData = $objModelUserVideo->getListByType($type, $offset, ($offset+$length-1), true, true);
				break;
			case Model_Data_UserVideo::TYPE_COMMENTED:
				$arrData = $objModelUserVideo->getCommented($offset, $offset+$length, true);
				break;
			default:
				throw new Model_Logic_Exception("type({$type}) not exists", -9001);
		}
		$arrReturn['count'] = $arrData['count'];
		if($arrData['data']) {
			if($type==Model_Data_UserVideo::TYPE_COMMENTED) {
				$arrVids = Arr::pluck($arrData['data'], "video_id");
	    		$arrVideoList = $this->buildVideoAndStatAndCircle($arrVids);
	    		foreach($arrData['data'] as $row) {
	    			if( isset($arrVideoList[$row['video_id']]) ) {
	    				$row = array_merge($row, $arrVideoList[$row['video_id']]);
	    			}
	    			$arrReturn['data'][] = $row;
	    		}
			} else {
				$arrReturn['data'] = array_values( $this->buildVideoAndStatAndCircle( array_keys($arrData['data']) ) );
    			$this->mergeUserVideoRec( $arrReturn['data'], $arrData['data']);
			}
			foreach($arrReturn['data'] as $k=>$row) {
				$row['rec_type'] = 0;
				shuffle($row['circle_list']);
				$row['circle'] = $row['circle_list'] ? $row['circle_list'][0] : NULL;
				unset($row['circle_list']);
				$row['comments'] = NULL;
				$arrReturn['data'][$k] = $row;
			}
		}
		
		return $arrReturn;
    }
    /**
     * 
     * 获取用户发生的动态
     * @param int $uid
     * @param string $type
     * @param int $offset
     * @param int $count
     */
    public function getUserFeedListByType($uid, $type=NULL, $offset=0, $count=20) {
    	$objModelUserFeed = new Model_Data_UserFeed();
    	$arrTypeFilters = NULL;
    	if($type) {
    		$arrTypeFilters[] = $type;
    	}
    	$arrFeedList = $objModelUserFeed->getFeedList($uid, $arrTypeFilters, $offset, $count);
    	if($arrFeedList['data']) {
    		$this->_complementInfo( $arrFeedList['data'] );
    	}
    	return $arrFeedList;
    }
    /**
     * 
     * 邀请好友关注圈子
     * @param unknown_type $uid
     * @param unknown_type $intCircleId
     * @param unknown_type $content
     * @param unknown_type $arrMailList
     */
    public function inviteFriendSubscribeCircle($uid, $intCircleId, $content, $arrMailList) {
    	#TODO 发邮件
    	$userInfo = $this->objModelUser->get($uid);
    	$objLogicCircle = new Model_Logic_Circle();
    	$circleInfo = $objLogicCircle->get(intval($intCircleId));
		$failed = array();
		$objEmail = Email::factory("你的好友{$userInfo['nick']}邀请你加入圈子{$circleInfo['title']}", $content);
		$objEmail->to($arrMailList);
		$arrEmailConfig = Jkit::config("email.options");
		$objEmail->from($arrEmailConfig['from']);
		$objEmail->send($failed);
		if($failed) {
			JKit::$log->info(__FUNCTION__." uid-{$uid}, failure-", $failed);
		}
		#加入圈子feed
		$objLogicFeed = new Model_Logic_Feed();
		$objLogicFeed->addCircleFeed(intval($intCircleId), Model_Data_CircleFeed::TYPE_INVITED, $uid);
		
		#TODO 授予用户邀请勋章
		if(!$userInfo['medal'] || !in_array(Model_Data_User::MEDAL_INVITE_FRIEND, $userInfo['medal'])) {
			$arrMedal = $userInfo['medal']? $userInfo['medal']:array();
			$arrMedal[] = Model_Data_User::MEDAL_INVITE_FRIEND;
			$this->modifyUser($uid, array('medal'=>$arrMedal));
		}
		
		return true;
    }
    /**
     * 
     * 生成邀请链接
     * @param int $uid
     * @param string $type 圈子邀请，circle
     * @param array $arrExtra 附加参数
     */
    public static function buildInviteUrl($uid, $type, $arrExtra=array()) {
    	$code = "";
    	switch($type) {
    		case 'circle':
    			$code = $uid."\3"."circle\3".$arrExtra["circle_id"];
    			break;
    		default:
    			$code = $uid."\3"."{$type}";
    			if($arrExtra) {
    				$code.= implode("\3", $arrExtra);
    			}
    	}
    	return 'http://'.DOMAIN_SITE."/index/invitecb?code=".base64_encode( $code );
    }
    
    /**
     * 查询某个用户的圈友，也就是跟用户关注了同一个圈子的其它用户
     * @param int $userId
     * @param int $circleId 默认为用户关注的所有圈子
     * @return array
     */
    public function circleFriends($userId, $circleId = NULL)
    {
        if (is_null($circleId)) {
            $key = "circle.friends:$userId";
        } else {
            $key = "circle.friends:$userId:$circleId";
        }
        $cache = Cache::instance('web');
        $userIds = $cache->get($key);
        Kohana::$log->debug(__FUNCTION__, $userIds);
        if (is_null($userIds)) {
            $modelDataCircleUserStatRecent = new Model_Data_CircleUserStatRecent();
            $modelDataCircleUser = new Model_Data_CircleUser();
            if (is_null($circleId)) {
                $stats = $modelDataCircleUserStatRecent->find(array('user_id' => (int) $userId), 
                    array('circle_id'), array('activity' => -1), 10);
                $circleIds = Arr::pluck($stats, 'circle_id');
                if (count($circleIds) < 10) {
                    $docs = $modelDataCircleUser->find(array('user_id' => (int) $userId), 
                        array('circle_id'), NULL, 10);
                    $circleIds = array_merge($circleIds, Arr::pluck($docs, 'circle_id'));
                    $circleIds = array_slice(array_unique($circleIds), 0, 10);
                }
                $userIds = array();
                foreach ($circleIds as $circleId) {
                    $stats = $modelDataCircleUserStatRecent->find(array('circle_id' => (int) $circleId), 
                        array('user_id'), array('activity' => -1), 10);
                    $tmp = Arr::pluck($stats, 'user_id');
                    if (count($tmp) < 10) {
                        $docs = $modelDataCircleUser->find(array('circle_id' => (int) $circleId), 
                            array('user_id'), NULL, 10);
        				$tmp = array_merge($tmp, Arr::pluck($docs, 'user_id'));
                        $tmp = array_slice(array_unique($tmp), 0, 10);
                    }
                    $userIds = array_merge($userIds, $tmp);
                }
                $userIds = array_unique($userIds);
            } else {
                $stats = $modelDataCircleUserStatRecent->find(array('circle_id' => (int) $circleId), 
                    array('user_id'), array('activity' => -1), 10);
                $userIds = Arr::pluck($stats, 'user_id');
                if (count($userIds) < 10) {
                    $docs = $modelDataCircleUser->find(array('circle_id' => (int) $circleId), 
                        array('user_id'), NULL, 10);
        			$userIds = array_merge($userIds, Arr::pluck($docs, 'user_id'));
                    $userIds = array_slice(array_unique($userIds), 0, 10);
                }
            }
            $userIds = array_values(array_diff($userIds, array($userId)));
            $cache->set($key, json_encode($userIds), 3600);
        } else {
            $userIds = json_decode($userIds, true);
        }
        return $userIds;
    }
    
    /**
     * 清楚缓存的某个用户的圈友
     * @param int $userId
     * @param int $circleId 默认为用户关注的所有圈子
     * @return bool
     */
    public function clearCircleFriendsCache($userId, $circleId = NULL)
    {
        if (is_null($circleId)) {
            $key = "circle.friends:$userId";
        } else {
            $key = "circle.friends:$userId:$circleId";
        }
        /* $cache Cache_Redis */
        $cache = Cache::instance('web');
        return $cache->delete($key);
    }
    
    public function logFeedbackCount() {
    	$redis = Database::instance('web_redis_master');
    	$objModelReids = $redis->getRedisDB(4);
    	$strKey = "FEEDBACK_CLICK_COUNT";
    	$objModelReids->incr($strKey);
    }
    
	private function _complementInfo(&$feeds)
    {
        $modelDataUser = new Model_Data_User();
        $userIds = Arr::pluck($feeds, 'user_id');
        $users = $modelDataUser->getMulti($userIds, Model_Logic_User::$basicFields);
        $modelDataCircle = new Model_Data_Circle();
        $circleIds = Arr::pluck($feeds, 'circle_id');
        $circles = $modelDataCircle->getMulti($circleIds, Model_Logic_Circle::$basicFields);
        $modelDataVideo = new Model_Data_Video();
        $videoIds = Arr::pluck($feeds, 'video_id');
        $videos = $modelDataVideo->getMulti($videoIds, Model_Logic_Video::$basicFields);
        
        foreach ($feeds as &$feed) {
            if (isset($feed['user_id']) && isset($users[$feed['user_id']])) {
                $feed['user'] = $users[$feed['user_id']];
            }
            if (isset($feed['circle_id']) && isset($circles[$feed['circle_id']])) {
                $feed['circle'] = $circles[$feed['circle_id']];
            }
            if (isset($feed['video_id']) && isset($videos[$feed['video_id']])) {
                $feed['video'] = $videos[$feed['video_id']];
            }
        }
        unset($feed);
    }
    
    private function mergeUserVideoRec(&$arrVideos, $arrVidTime) {
    	if($arrVideos) {
    		foreach($arrVideos as $k=>$row) {
    			if( isset($arrVidTime[$k]) ) {
    				$arrVideos[$k]['record_time'] = $arrVidTime[$k];
    			}
    		}
    	}
    }
    
	private function buildVideoAndStatAndCircle($arrInput) {
		$objLogicVideo = new Model_Logic_Video();
		$arrResult = $objLogicVideo->getMulti($arrInput, true);
		if($arrResult) {
			$objLogicRecommend = new Model_Logic_Recommend();
			$arrTmpCircles = $objLogicRecommend->getCircleInfoByVids($arrInput);
			foreach($arrResult as $k=>$row) {
				$arrResult[$k]['circle_list'] = array();
				if( isset($arrTmpCircles[$k]) ) {
					$arrResult[$k]['circle_list'] = array_values( $arrTmpCircles[$k] );
				}
			}
		}
		return $arrResult;
	}
	
	/**
	 * 关注用户
	 * @param int $user
	 * @param int $following
	 * @param boolean $hidden 是否悄悄关注
	 * @param string $userNick $user这个用户ID的nick
	 * @return boolean
	 */
	public function follow($user, $following, $hidden = false, $userNick = null)
	{
		$user = (int) $user;
		$following = (int) $following;
		if ($user == $following) {
			throw new Model_Logic_Exception("不能自己关注自己", 'self_followed');
		} elseif ($this->isFollowing($user, $following)) {
	        throw new Model_Logic_Exception("已经关注过。", 'already_followed');
	    }
	    
	    $modelDataUserFollowing = new Model_Data_UserFollowing();
	    $modelDataUserStatAll = new Model_Data_UserStatAll();
	    $doc = array(
	    	'user' => $user, 
	    	'following' => $following,
	        'create_time' => new MongoDate()
	    );
	    if ($hidden) {
	        $doc['hidden'] = true;
	    } else {
    	    if ($this->isFollowing($following, $user, null, false)) {
    	        $doc['bidirectional'] = true;
    	    }
	    }
	    try {
	        $total = 0;
	        $this->followings($user, 0, 0, null, null, $total);
	        if ($total >= Model_Data_UserFollowing::MAX_FOLLOWINGS_COUNT) {
	            throw new Model_Logic_Exception("关注用户太多。", 'too_many_followings');
	        }
	        $modelDataUserFollowing->insert($doc, array('safe' => true));
	        
	        if (isset($doc['bidirectional']) && $doc['bidirectional']) {
    	        $modelDataUserFollowing->update(array(
        	    	'user' => $following, 
        	    	'following' => $user,
    	        ), array('bidirectional' => true), array('safe' => true));
	        }
    	    
	        $modelDataUserStatAll->inc(array('_id' => $user), 'followings_count', 
	            1, array('upsert' => true));
	        if (!isset($doc['hidden']) || !$doc['hidden']) {
	            $modelDataUserStatAll->inc(array('_id' => $following), 'followers_count', 
	                1, array('upsert' => true));
	        }
	    } catch (Exception $e) {
	        Kohana::$log->error($e);
	        return false;
	    }
	    
	    $objModelLogicAtuser = new Model_Logic_Atuser();
	    $objModelLogicAtuser->addSugUser($user, $following, $userNick);
	    
	    return true;
	}
	
	/**
	 * 取消关注用户
	 * @param int $user
	 * @param int $following
	 * @return boolean
	 */
	public function unfollow($user, $following)
	{	    
	    $modelDataUserFollowing = new Model_Data_UserFollowing();
	    $modelDataUserStatAll = new Model_Data_UserStatAll();
	    $query = array(
	    	'user' => (int) $user, 
	    	'following' => (int) $following,
        );
	    try {
	        $doc = $modelDataUserFollowing->findOne($query);
    	    if (!$doc) {
    	        return true;
    	    }
	        $modelDataUserFollowing->delete($query, array('safe' => true));
	    
    	    if (isset($doc['bidirectional']) && $doc['bidirectional']) {
    	        $modelDataUserFollowing->update(array(
        	    	'user' => (int) $following, 
        	    	'following' => (int) $user,
    	        ), array('bidirectional' => false), array('safe' => true));
    	    }
	    
	        $modelDataUserStatAll->inc(array('_id' => (int) $user), 'followings_count', 
	            -1);
	        if (!isset($doc['hidden']) || !$doc['hidden']) {
	            $modelDataUserStatAll->inc(array('_id' => (int) $following), 'followers_count', 
	                -1);
	        }
	    } catch (Exception $e) {
	        Kohana::$log->error($e);
	        return false;
	    }
	    return true;
	}
	
	/**
	 * 正在关注
	 * @param int $user
	 * @param int $offset
	 * @param int $count
	 * @param boolean $bidirectional true为互相关注的用户，false为单向关注的用户，null为全部
	 * @param boolean $hidden true为悄悄关注的用户，false为公开关注的用户，null为全部
	 * @param int $total 总数
	 * @return array
	 */
	public function followings($user, $offset = 0, $count = 50, $bidirectional = null, 
	    $hidden = null, &$total = null)
	{
	    $modelDataUserFollowing = new Model_Data_UserFollowing();
	    $query = array(
    	    'user' => (int) $user
	    );
	    if ($bidirectional === true) {
	        $query['bidirectional'] = true;
	    } else if ($bidirectional === false) {
	        $query['$or'] = array(
	            array('bidirectional' => false),
	            array('bidirectional' => array('$exists' => false))
	        );
	    }
	    if ($hidden === true) {
	        $query['hidden'] = true;
	    } else if ($hidden === false) {
	        $query['$or'] = array(
	            array('hidden' => false),
	            array('hidden' => array('$exists' => false))
	        );
	    }
	    try {
	        if ($count > 0) {
    	        $docs = $modelDataUserFollowing->find($query, array(), array('create_time' => -1), 
    	            $count, $offset);
	        } else {
	            $docs = array();
	        }
	        if (!is_null($total)) {
	            $total = $modelDataUserFollowing->count($query);
	        }
	    } catch (Exception $e) {
	        Kohana::$log->error($e);
	        return array();
	    }
	    return $docs;
	}
	
	/**
	 * 是否正在关注某个用户
	 * @param int $user
	 * @param int $following
	 * @param boolean $bidirectional true为互相关注，false为单向关注，null为任意
	 * @param boolean $hidden true为悄悄关注，false为公开关注，null为任意
	 * @return boolean
	 */
	public function isFollowing($user, $following, $bidirectional = null, $hidden = null)
	{
	    $modelDataUserFollowing = new Model_Data_UserFollowing();
	    $query = array(
    	    'user' => (int) $user,
    	    'following' => (int) $following,
	    );
	    if ($bidirectional === true) {
	        $query['bidirectional'] = true;
	    } else if ($bidirectional === false) {
	        $query['$or'] = array(
	            array('bidirectional' => false),
	            array('bidirectional' => array('$exists' => false))
	        );
	    }
	    if ($hidden === true) {
	        $query['hidden'] = true;
	    } else if ($hidden === false) {
	        $query['$or'] = array(
	            array('hidden' => false),
	            array('hidden' => array('$exists' => false))
	        );
	    }
	    try {
	        $doc = $modelDataUserFollowing->findOne($query);
	    } catch (Exception $e) {
	        Kohana::$log->error($e);
	        return false;
	    }
	    return (boolean) $doc;
	}
	
	/**
	 * 粉丝
	 * @param int $user
	 * @param int $offset
	 * @param int $count
	 * @param boolean $bidirectional true为互相关注，false为单向关注，null为任意
	 * @param boolean $hidden true为悄悄关注，false为公开关注，null为任意
	 * @param int $total 总数
	 * @return array
	 */
	public function followers($following, $offset = 0, $count = 50, $bidirectional = null, 
	    $hidden = false, &$total = null, $arrFields = array())
	{
	    $modelDataUserFollowing = new Model_Data_UserFollowing();
	    $query = array(
    	    'following' => (int) $following
	    );
	    if ($bidirectional === true) {
	        $query['bidirectional'] = true;
	    } else if ($bidirectional === false) {
	        $query['$or'] = array(
	            array('bidirectional' => false),
	            array('bidirectional' => array('$exists' => false))
	        );
	    }
	    if ($hidden === true) {
	        $query['hidden'] = true;
	    } else if ($hidden === false) {
	        $query['$or'] = array(
	            array('hidden' => false),
	            array('hidden' => array('$exists' => false))
	        );
	    }
	    try {
	        if ($count > 0) {
    	        $docs = $modelDataUserFollowing->find($query, $arrFields, array('create_time' => -1), 
    	            $count, $offset);
	        } else {
	            $docs = array();
	        }
	        if (!is_null($total)) {
	            $total = $modelDataUserFollowing->count($query);
	        }
	    } catch (Exception $e) {
	        Kohana::$log->error($e);
	        return array();
	    }
	    return $docs;
	}
}