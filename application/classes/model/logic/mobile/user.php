<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 用户、第三方帐号、用户视频关系等页面逻辑
 * @author xucongbin
 */
class Model_Logic_Mobile_User extends Model_Logic_User {
	
	private $objModelRedis;
	
	public function __construct() {
		parent::__construct();
		$redis = Database::instance('web_redis_master');
		$this->objModelRedis = $redis->getRedisDB(11);
	}
	
	public function autoCreateUser($uid, $account, $isMobile=true) {
		$strUsername = $this->buildUserName("手机用户:".mb_substr($account, -5));
		$extra = array(
			"_id" => $uid,
			"intro" => ""
		);
		if($isMobile) {
			$extra['mobile_phone'] = $account;
		}
		$res = $this->register('', '', $strUsername, array(), $extra);
		return $res;
	}
	
	public function buildUserName( $account=null ) {
		if( $account && !$this->objModelUser->getByNick($account) ) {
			return $account;
		}
		while(1) {
			$strUsername = mb_substr(uniqid("手机用户:"), 0, 10);
			if( !$this->objModelUser->getByNick($strUsername) ) {
				break;
			}
		}
		
		return $strUsername;
	}
	
	
	public function connectAutoCreteUser($account, $accountType) {
		$objModelSndareg = new Model_Data_Sndareg();
		$objModelConnect = new Model_Data_UserConnectMobile();
		$arrThirdRelMap = array(
			Model_Data_UserConnect::TYPE_SINA => Model_Data_Sndareg::C_ID_XL,
			Model_Data_UserConnect::TYPE_TQQ => Model_Data_Sndareg::C_ID_TQQ
		);
		if(!isset($arrThirdRelMap[$accountType])) {
			throw new Model_Logic_Exception("type({$accountType}) not exists", -2);
		}
		$sndaParam = array(
        	'AccountId' => $account,
        	'CompanyId' => $arrThirdRelMap[$accountType],
        );
		//盛大第三方帐号自动创建
        try {
        	$sndaUserData = $objModelSndareg->AutoBindThirdAccountLogin($sndaParam);
        } catch (MongoException $e) {
			JKit::$log->warn("AutoBindThirdAccountLogin rpc failure, code-".$e->getCode().", msg-".$e->getMessage().", param-", $sndaParam);
			return false;
		}
        if($sndaUserData['return_code']==0){
			$intSdid = (int)$sndaUserData['data']['SndaId'];
        	$res = $this->autoCreateUser($intSdid, $account, false);
        	if(!$res) {
        		return false;
        	}
        	//创建登录连接
        	$cType = Model_Data_UserConnect::CONNECT_TYPE_LOGIN; 
        	$isCreate = $objModelConnect->addConnect($accountType, $intSdid, $cType, array(
                	'connect_id' => $account,
                ));
            return $intSdid;
//        	$arrReturn['uid'] = $intSdid;
//        	$arrReturn['token'] = $this->processSessionToToken($intSdid."##".$account."##".$accountType);
        }else{
        	JKit::$log->warn("AutoBindThirdAccountLogin response failure, ret-", $sndaUserData);
			return false;
        }
//		return $arrReturn;
	}
	
	public function setToken($token, $uid) {
//		$lifeTime = 3600;
		$arrUserInfo = $this->getUserByid($uid, true);
		$this->objModelRedis->set("mobile:".$token, json_encode($arrUserInfo));
	}
	
	public function getUserByToken($token) {
		$arrReturn = array();
		$strKey = "mobile:".$token;
		$strJson = $this->objModelRedis->get($strKey);
		if( $strJson ) {
			$arrReturn = json_decode($strJson, true);
		}
		return $arrReturn;
	}
	
	public function loginToken($uid) {
	    $res = $this->objModelUser->modifyById($uid, array(
	        'last_login_time' => new MongoDate(),
	        'last_login_ip' => Request::$client_ip
	    ));
	    
	    return $res;
	}
	
	public function processSessionToToken($sessionId) {
		$time = $_SERVER['REQUEST_TIME']-$_SERVER['REQUEST_TIME']%300;
		return md5("QL".$sessionId.$time);
	}
	
	public function getUserByid($uid, $bolFromMaster=false) {
		$arrReturn = parent::getUserByid($uid, $bolFromMaster);
		$this->completeUserAvatar($arrReturn);
		
		return $arrReturn;
	}
	
	public function completeUserAvatar( &$arrInput ) {
		if(!$arrInput) {
			return;
		}
		if( isset($arrInput['avatar']) ||  isset($arrInput['_id']) ) {
			$avatar = $arrInput['avatar'] ? $arrInput['avatar']:array(30=>'');
			unset($avatar['org']);
			foreach($avatar as $size=>$val) {
				$avatar[$size] = Util::userAvatarUrl($val, $size);
			}
			$arrInput['avatar'] = $avatar;
			return;
		}
		
		foreach($arrInput as $k=>$row) {
			$avatar = $row['avatar'] ? $row['avatar']:array(30=>'');
			unset($avatar['org']);
			
			foreach($avatar as $size=>$val) {
				$avatar[$size] = Util::userAvatarUrl($val, $size);
			}
			$arrInput[$k]['avatar'] = $avatar;
		}
	}
	
	public function completeVideoPic( &$arrInput ) {
		if(!$arrInput) {
			return;
		}
		if( isset($arrInput['thumbnail']) ||  isset($arrInput['_id']) ) {
			$arrInput['thumbnail'] = Util::videoThumbnailUrl($arrInput['thumbnail']);
			return;
		}
		foreach($arrInput as $k=>$row) {
			$arrInput[$k]['thumbnail'] = Util::videoThumbnailUrl($row['thumbnail']);
		}
	}
	
	public function completeCirclePic( &$arrInput ) {
		if(!$arrInput) {
			return;
		}
		if( isset($arrInput['tn_path']) ||  isset($arrInput['_id']) ) {
			$arrInput['tn_path'] = Util::circlePreviewPic($arrInput['tn_path']);
			return;
		}
		foreach($arrInput as $k=>$row) {
			$arrInput[$k]['tn_path'] = Util::circlePreviewPic($row['tn_path']);
		}
	}
}