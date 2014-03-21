<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 第三方登录和绑定
 * @author xucongbin zhangjianbin
 */
class Model_Logic_Connect2 extends Model {
	
	private $objModelConnect;
	
	private $objLogicUser;
	
	public function __construct() {
		
		$this->objModelConnect = new Model_Data_UserConnect();
		$this->objLogicUser = new Model_Logic_User();
	}
	
	public function doubanCallback(){
		$this->initConnectApiModel(Model_Data_UserConnect::TYPE_DOUBAN);
		$ret = Model_Douban::getAccessToken();
		JKit::$log->debug(__FUNCTION__." Model_Douban::getAccessToken, ret-". json_encode($ret));
		if( !$ret ) {
			return array('err'=>true);
		}
        $oauth_id = Model_Douban::getParam(Model_Douban::OAUTH_UID);
        $uinfo = Model_Douban::call('people/'.$oauth_id);
        JKit::$log->debug( __FUNCTION__." Model_Douban::call people/{$oauth_id}, ret-". json_encode($uinfo) );
		if(!isset($uinfo['title']['$t'])){
			JKit::$log->warn( __FUNCTION__." Model_Douban::call people/{$oauth_id} ,ret-". json_encode($uinfo) );
        	return array('err'=>true,'data'=>$uinfo);
        }
        return array( 
        	'bindUser' => array(
            	'id' => $oauth_id,
                'name' => $uinfo['title']['$t'],
            ),
            'token' => array(
            	'access_token'=> Model_Douban::getParam(Model_Douban::ACCESS_TOKEN),
            	'oauth_token_secret' => Model_Douban::getParam(Model_Douban::OAUTH_TOKEN_SECRET),
            )
        );
	}	
	/**
	 * 
	 * 获取豆瓣跳转地址
	 * @param string $strCallBackUrl
	 */
	public function getDoubanRedirectUrl($strCallBackUrl){
		$this->initConnectApiModel(Model_Data_UserConnect::TYPE_DOUBAN);
		//Model_Douban::debug(true);

		$callback = $strCallBackUrl.URL::query( array(
        	'type' => Model_Data_UserConnect::TYPE_DOUBAN,
		) );
		$request_token = Model_Douban::getRequestToken();
		if (!$request_token) {
			JKit::$log->warn(__FUNCTION__." Model_Douban::getRequestToken fail, ret-", $request_token);
			throw new Model_Logic_Exception("request douban token fail",  -8001 );
		}
		$url = Model_Douban::getAuthorizeURL($request_token,$callback);
		return $url;
	}
	/**
	 * 
	 * 豆瓣分享
	 * @param $content 140个字
	 * @param $arrAccessToekn
	 * @param $uid
	 * 
	 * @return boolean
	 */
	public function doubanShare($content, $arrAccessToken=NULL, $uid=NULL)
	{
		$this->initConnectApiModel(Model_Data_UserConnect::TYPE_DOUBAN);
//		Model_Douban::debug(true);
		if( $arrAccessToken===NULL ) {
			$arrConnectInfo = $this->getBindConnectInfo($uid, Model_Data_UserConnect::TYPE_DOUBAN);
			if(!$arrConnectInfo) {
				return false;
			}
			$arrAccessToken = $arrConnectInfo['access_token'];
		}
		Model_Douban::setParam( Model_Douban::ACCESS_TOKEN , $arrAccessToken['access_token'] );
		Model_Douban::setParam( Model_Douban::OAUTH_TOKEN_SECRET , $arrAccessToken['oauth_token_secret'] );
		$post_body = '<?xml version="1.0" encoding="UTF-8"?>'.
		'<entry xmlns:ns0="http://www.w3.org/2005/Atom" xmlns:db="http://www.douban.com/xmlns/"><content>'.
		$content.'</content></entry>';
		$arrParams = array(
			'header_only'=> true,
			'post_body' => $post_body,
			'realm' => 'http://sp.example.com/'
		);
		$retShare = Model_Douban::call('miniblog/saying', $arrParams, 'POST');
		//TODO
		$httpCode = Model_Douban::getHttpCode();
		if( $httpCode=="201" ) {
			return true;
		}
		
		JKit::$log->warn(__FUNCTION__." Model_Douban::call miniblog/saying, http-code-{$httpCode}, ret-", $retShare);
        return false;
	}

	public function tqqCallback($oauth_verifier){
        $this->initConnectApiModel(Model_Data_UserConnect::TYPE_TQQ);
		//Model_Tqq::debug(true);
		
		if( !($ret = Model_Tqq::getAccessToken($oauth_verifier)) ){
			JKit::$log->warn(__FUNCTION__." getAccessToken failure oauth_verifier-{$oauth_verifier},ret-",$ret);
			return array('err'=>true);
			//throw new Model_Logic_Exception(__FUNCTION__.'获取Access Token失败。', -4001);
		}
        $uinfo = Model_Tqq::call('user/info');
        JKit::$log->debug(__FUNCTION__." {$oauth_verifier},ret-",$uinfo);
        if(!isset($uinfo['data']['name'])){
        	JKit::$log->warn(__FUNCTION__." Model_Tqq::call user/info oauth_verifier-{$oauth_verifier},ret-", 
        		$uinfo);
        	return array('err'=>true,'data'=>$uinfo);
        }
        return array( 
        	'bindUser' => array(
            	'id' => $uinfo['data']['name'],
                'name' => $uinfo['data']['nick'],
        		'email' => $uinfo['data']['email'],
        		'avatar'=>$uinfo['data']['head'] ? $uinfo['data']['head']."/180" : "" ,
            ),
            'token' => array(
            	'access_token'=> Model_Tqq::getParam(Model_Tqq::ACCESS_TOKEN),
            	'oauth_token_secret' => Model_Tqq::getParam(Model_Tqq::OAUTH_TOKEN_SECRET),
            )
        );
	}	
	
	public function getTqqRedirectUrl($strCallBackUrl){
	    $this->initConnectApiModel(Model_Data_UserConnect::TYPE_TQQ);
		//Model_Tqq::debug(true);

		$callback = $strCallBackUrl.URL::query(array(
        	'type' => Model_Data_UserConnect::TYPE_TQQ,
		));
		$request_token = Model_Tqq::getRequestToken($callback);
        	!$request_token && exit('获取request_token失败，请检查网络或者appkey和appsecret是否正确');
		$url = Model_Tqq::getAuthorizeURL($request_token);
		return $url;
	}
	
	/**
	 * 腾讯微博分享 http://wiki.open.qq.com/wiki/t/add_picurl_t
	 * @param array param = array('content'=>'test','pic_url'=>'url')
	 * */
	public function tqqShare($param, $accessToken, $oauthTokenSecret){
	    $this->initConnectApiModel(Model_Data_UserConnect::TYPE_TQQ);
//		Model_Tqq::debug(true);
		Model_Tqq::setParam( Model_Tqq::ACCESS_TOKEN , $accessToken);
		Model_Tqq::setParam( Model_Tqq::OAUTH_TOKEN_SECRET , $oauthTokenSecret);
		$ret = Model_Tqq::call('t/add_pic_url',array(
			'content'=>$param['content'],
			//'clientip' => '123.119.32.253',
			'pic_url' => $param['pic_url'],
		), 'POST');
        if($ret['ret'] != 0)
        {
        	JKit::$log->warn(__FUNCTION__." Model_Tqq::call t/add_pic_url content-{$param['content']}, ".
        		"pic_url-{$param['pic_url']} access_token-{$accessToken} , ret-",$ret);
        	return false;
        }
        return true;
	}
	public function sinaCallback($code,$redirect_uri){
        $this->initConnectApiModel(Model_Data_UserConnect::TYPE_SINA);
		//Model_Sina::debug(true);
		
		if(!( $tokenTmp = Model_Sina::getAccessToken('code',array('code'=>$code,'redirect_uri'=>$redirect_uri)) ))
	    {
	    	return array('err'=>true);
	        //throw new Model_Logic_Exception(__FUNCTION__.'获取Access Token失败。', -4001);
	    }
        $oauth_id = Model_Sina::getParam(Model_Sina::OAUTH_USER_ID);
        $uinfo = Model_Sina::call('users/show',array('uid'=>$oauth_id));
		if(!isset($uinfo['name'])){
			JKit::$log->warn(__FUNCTION__." Model_Sina::call users/show uid-{$oauth_id}, code-{$code},".
				"redirect_uri-{$redirect_uri},ret-",$uinfo);
        	return array('err'=>true,'data'=>$uinfo);
        }
        return array( 
        	'bindUser' => array(
            	'id' => $oauth_id,
                'name' => $uinfo['name'],
        		'avatar' => $uinfo['avatar_large'],
            ),
            'token' => array(
            	'access_token'=> Model_Sina::getParam (Model_Sina::ACCESS_TOKEN) ,
            	'refresh_token' => Model_Sina::getParam (Model_Sina::REFRESH_TOKEN),
            	'expires_in' => Model_Sina::getParam(Model_Sina::EXPIRES_IN),
            )
        );
	}	
	
	public function getSinaRedirectUrl($strCallBackUrl){
	    $this->initConnectApiModel(Model_Data_UserConnect::TYPE_SINA);
		//Model_Sina::debug(true);

		$callback = $strCallBackUrl.URL::query(array(
        	'type' => Model_Data_UserConnect::TYPE_SINA,
		));
	    $url = Model_Sina::getAuthorizeURL($callback, 'code', 'state');
	    
		return $url;
	}
	
	/**
	 * 新浪微博分享
	 * */
	public function sinaShare($param, $access_token){
	    $this->initConnectApiModel(Model_Data_UserConnect::TYPE_SINA);
		Model_Sina::setParam( Model_Sina::ACCESS_TOKEN , $access_token);
		/*$ret = Model_Sina::call('statuses/upload_url_text',array(
			'status'=>urlencode($param['content']),
			'url' => $param['pic_url'],
		), 'POST');*/
		/*$ret = Model_Sina::call('statuses/update',array(
		        'status' => $param['content'],
		    ), 'POST'
		);
		*/
		$pic_url = trim($param['pic_url']);
		$pic_content = file_get_contents($pic_url);
		$pic_file = "/tmp/".uniqid("pic_url").".jpg";
		if(!file_put_contents($pic_file, $pic_content)){
			JKit::$log->warn("Unable to generate the file in the temporary location , file-".$pic_file.
				"source-".$pic_url);
		}else{
			$ret = Model_Sina::call('statuses/upload',array(
			        'status' => trim($param['content']),
			    ), 'POST', array(
			    	'pic'=> $pic_file
			    )
			);
		}

    
        if(isset($ret['error'])){
        	JKit::$log->warn(__FUNCTION__." Model_Sina::call statuses/upload_url_text status-{$param['content']}, ".
        		"pic-{$param['pic_url']} ,access_token-{$access_token},ret-",$ret);
        	return false;
        }
        return true;
	}
	
	public function renrenCallback($code, $redirect_uri){
        $this->initConnectApiModel(Model_Data_UserConnect::TYPE_RENREN);
		//Model_Renren::debug(true);
		$ret = Model_Renren::getAccessToken('code',array('code'=>$code,'redirect_uri'=>$redirect_uri));
		JKit::$log->debug(__FUNCTION__." Model_Renren::getAccessToken code-{$code},ret-", $ret);
		if( !$ret ) {
			JKit::$log->warn(__FUNCTION__." Model_Renren::getAccessToken code-{$code},ret-", $ret);
	    	return array('err'=>true);
	    }
        $intOauthUserId = Model_Renren::getParam(Model_Renren::OAUTH_USER_ID);
        $uinfo = Model_Renren::call('users.getInfo',array(
        	'uids' => $intOauthUserId,
        	'fields' => 'uid,name,tinyurl,headhurl,zidou,star,headurl,mainurl',
        ));
        JKit::$log->debug(__FUNCTION__." Model_Renren::call users.getInfo uid-{$intOauthUserId}".
			", code-{$code}, ret-", $uinfo);
		if( !isset($uinfo[0]['name']) ){
			JKit::$log->warn(__FUNCTION__." Model_Renren::call users.getInfo uid-{$intOauthUserId}".
			", code-{$code}, url-{$redirect_uri}, ret-", $uinfo);
        	return array('err'=>true,'data'=>$uinfo);
        }
        return array( 
        	'bindUser' => array(
            	'id' => Model_Renren::getParam(Model_Renren::OAUTH_USER_ID),
                'name' => $uinfo[0]['name'],
        		'avatar'=> isset( $uinfo[0]['mainurl'] ) ? $uinfo[0]['mainurl']: "" ,
            ),
            'token' => array(
            	'access_token'=> Model_Renren::getParam (Model_Renren::ACCESS_TOKEN) ,
            	'refresh_token' => Model_Renren::getParam (Model_Renren::REFRESH_TOKEN),
            	'expires_in' => Model_Renren::getParam(Model_Renren::EXPIRES_IN),
            )
        );
	}	
	
	public function renrenShare($strComment, $strUrl, $accessToken=NULL, $uid=NULL) {
		$this->initConnectApiModel(Model_Data_UserConnect::TYPE_RENREN);
		if( $accessToken===NULL ) {
			$arrConnectInfo = $this->getBindConnectInfo($uid, Model_Data_UserConnect::TYPE_RENREN);
			if(!$arrConnectInfo) {
				return false;
			}
			$accessToken = $arrConnectInfo['access_token']['access_token'];
		}
		Model_Renren::setParam(Model_Renren::ACCESS_TOKEN, $accessToken);
		$arrParams = array(
			"comment" => $strComment,
			"type" => 6,
			"url" => $strUrl
		);
		$ret = Model_Renren::call( "share.share", $arrParams );
		Jkit::$log->debug(__FUNCTION__. " share.share, url-".$strUrl." ret-".json_encode( $ret) );
		if( !$ret || isset($ret["error_code"]) ) {
			Jkit::$log->warn(__FUNCTION__. " share.share, url-".$strUrl." ret-".json_encode($ret) );
			return false;
		}
		return true;
	}
	
	public function refreshRenrenToken( $uid, $connectType=null ) {
		$this->initConnectApiModel(Model_Data_UserConnect::TYPE_RENREN);
		$arrConnects = $this->objModelConnect->getConnectByUid(Model_Data_UserConnect::TYPE_RENREN, $uid);
		if(!$arrConnects) {
			return true;
		}
		$arrInclude = array(
			Model_Data_UserConnect::CONNECT_TYPE_BIND => true,
			Model_Data_UserConnect::CONNECT_TYPE_LOGIN => true
		);
		if( $connectType!==NULL ) {
			if( $connectType == Model_Data_UserConnect::CONNECT_TYPE_BIND ) {
				$arrInclude[Model_Data_UserConnect::CONNECT_TYPE_LOGIN] = false;
			} else {
				$arrInclude[Model_Data_UserConnect::CONNECT_TYPE_BIND] = false;
			}
			
		} 
		$bolRet = false;
		foreach ($arrConnects as $row) {
			if( $arrInclude[$row['connect_type']] ) {
				$arrToken = Model_Renren::getAccessToken('token', 
					array('refresh_token'=>$row['access_token']['refresh_token'])
				);
				if( $arrToken ) {
					unset($arrToken["scope"]);
					$ret = $this->objModelConnect->modifyConnectTokenById($id, $arrToken);
					if( $ret ) {
						$bolRet = true;
					}	
				}
			}
		}
		return $bolRet;
	}
	
	public function getRenrenRedirectUrl($strCallBackUrl){
	    $this->initConnectApiModel(Model_Data_UserConnect::TYPE_RENREN);
		//Model_Renren::debug(true);

	    $url = Model_Renren::getAuthorizeURL($this->getRenrenCallbackUrl($strCallBackUrl), 'code', 'state', 
	    'default', array("scope"=>"publish_share publish_feed"));
	    
		return $url;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param string $strCallBackUrl
	 */
	public function getRenrenCallbackUrl( $strCallBackUrl ) {
		$callback = $strCallBackUrl.URL::query(array(
        	'type' => Model_Data_UserConnect::TYPE_RENREN,
		), false);
		
		return $callback;
	}
	
	/**
	 * 
	 * QQ连接 
	 * @param $code
	 * @param $redirect_uri
	 * 
	 * @return 
	 */
	public function qqCallback($code, $redirect_uri){
        $this->initConnectApiModel(Model_Data_UserConnect::TYPE_QQ);
		//Model_Qq::debug(true);
		$ret = Model_Qq::getAccessToken("code",
			array(
				'code'=>$code, 
				'redirect_uri'=>$redirect_uri,
				'state'=>'state'
			)
		);
		JKit::$log->debug(__FUNCTION__." Model_Qq::getAccessToken code-{$code},ret-", $ret);
		if( !$ret  ) {
	    	return array('err'=>true);
	    }
	    
        $strAccessToken = Model_Qq::getParam(Model_Qq::ACCESS_TOKEN);
        $intOpenId = Model_Qq::getParam(Model_Qq::OPENID);
        $uinfo = Model_Qq::call('user/get_user_info',array('openid'=>$intOpenId));
        JKit::$log->debug(__FUNCTION__." Model_Qq::call user/get_user_info uid-{$intOpenId}".
			", code-{$code}, ret-", $uinfo);
		if(!isset($uinfo['nickname'])){
			JKit::$log->warn(__FUNCTION__." Model_Qq::call user/get_user_info uid-{$intOpenId}".
			", code-{$code},redirect_uri-{$redirect_uri},ret-", $uinfo);
        	return array('err'=>true,'data'=>$uinfo);
        }
        return array( 
        	'bindUser' => array(
            	'id' => $intOpenId,
                'name' => $uinfo['nickname'],
        		'avatar' => isset($uinfo['figureurl_2']) ? $uinfo['figureurl_2'] : ""
            ),
            'token' => array(
            	'access_token'=> Model_Qq::getParam (Model_Qq::ACCESS_TOKEN) ,
            	'refresh_token' => Model_Qq::getParam (Model_Qq::REFRESH_TOKEN),
            	'expires_in' => Model_Qq::getParam(Model_Qq::EXPIRES_IN),
            )
        );
	}	
	/**
	 * 
	 * Enter description here ...
	 * @param string $strComment
	 * @param string $strUrl
	 * @param string $strPic
	 * @param int $uid
	 * @param array $arrExtraParams array(
	 * 	title => 内容标题,
	 * 	summary => 内容简介
	 * )
	 * @param mixed $accessToekn
	 */
	public function qqShare( $strComment, $strUrl, $strPic, $arrExtraParams, $accessToken=null, $uid=NULL ) {
		$this->initConnectApiModel(Model_Data_UserConnect::TYPE_QQ);
		$arrParams = array(
			"site" => DOMAIN_SITE,
//			"nswb" => 1,
			"type" => 4,
			"source" => $this->getAgentType( ),
			"url" => $strUrl,
			"comment" => mb_strimwidth( $strComment, 0, 40),
			"images" => $strPic
		);
		$arrParams = array_merge($arrParams, $arrExtraParams);
		if( isset($arrParams['title']) ) {
			$arrParams['title'] = mb_strimwidth( $arrParams['title'], 0, 36);
		}
		if( isset($arrParams['summary']) ) {
			$arrParams['summary'] = mb_strimwidth( $arrParams['summary'], 0, 80);
		}
		if($accessToken===NULL) {
			$arrConnectInfo = $this->getBindConnectInfo($uid, Model_Data_UserConnect::TYPE_QQ);
			if(!$arrConnectInfo) {
				return false;
			}
			$accessToken = $arrConnectInfo['access_token']['access_token'];
		}
		
		Model_Tqq::setParam( Model_Qq::ACCESS_TOKEN , $accessToekn);
		$ret = Model_Qq::call('share/add_share', $arrParams, 'POST');
		JKit::$log->debug(__FUNCTION__." share/add_share uid-{$uid}, access_token-{$accessToken}, param-".
        		json_encode($arrParams).", ret-", $ret);
        if($ret['ret'] != 0) {
        	JKit::$log->warn(__FUNCTION__." share/add_share uid-{$uid}, access_token-{$accessToken}, param-".
        		json_encode($arrParams).", ret-", $ret);
        	return false;
        }
        return true;
	}
	
	public function getQqRedirectUrl($strCallBackUrl){
	    $this->initConnectApiModel(Model_Data_UserConnect::TYPE_QQ);
		//Model_Sina::debug(true);

		$callback = $strCallBackUrl.URL::query(array(
        	'type' => Model_Data_UserConnect::TYPE_QQ,
		));
	    $url = Model_Qq::getAuthorizeURL($callback, 'code', 'state', 'default', 
	    	'get_user_info,add_share,add_pic_t');
	    
		return $url;
	}
	
	public function getMsnRedirectUrl( $strCallBackUrl ) {
		$this->initConnectApiModel(Model_Data_UserConnect::TYPE_MSN);
		$callback = $strCallBackUrl.URL::query(array(
        	'type' => Model_Data_UserConnect::TYPE_MSN,
		));
		$scopes = "wl.basic wl.signin wl.emails wl.share";
	    $url = Model_Msn::getAuthorizeURL($callback, "code", $scopes);
	    
		return $url;
	}
	
	public function msnCallback( $strCode,  $strRedirectUrl) {
		$this->initConnectApiModel(Model_Data_UserConnect::TYPE_MSN);
//		Model_Msn::debug(true);
		
		if( !( $tokenTmp = Model_Msn::getAccessToken('code', 
				array(
					'code'=>$strCode,'redirect_uri'=>$strRedirectUrl
				)
			) )
		) {
	    	return array('err'=>true);
	    }
	    JKit::$log->debug(__FUNCTION__." Model_Msn::getAccessToken me code-{$strCode},ret-", $tokenTmp);
        $oauth_id = Model_Msn::getParam(Model_Sina::OAUTH_USER_ID);
        $uinfo = Model_Msn::call('me');
        JKit::$log->debug(__FUNCTION__." Model_Msn::call me code-{$strCode},ret-", $uinfo);
		if(!isset($uinfo['name'])){
			JKit::$log->warn(__FUNCTION__." Model_Msn::call me code-{$strCode},".
				"redirect_uri-{$strRedirectUrl},ret-", $uinfo);
        	return array('err'=>true,'data'=>$uinfo);
        }
        return array( 
        	'bindUser' => array(
            	'id' => $uinfo["id"],
                'name' => $uinfo['name'],
        		'avatar' => Model_Msn::getUserPic(),
        		'email' => isset( $uinfo['emails']['account'] ) ? $uinfo['emails']['account'] : ""
            ),
            'token' => array(
            	'access_token'=> Model_Msn::getParam (Model_Msn::ACCESS_TOKEN) ,
            	'refresh_token' => Model_Msn::getParam (Model_Msn::REFRESH_TOKEN),
            	'expires_in' => Model_Msn::getParam(Model_Msn::EXPIRES_IN),
            )
        );
	}
	
	/**
	 * 第三方登录
	 * 
	 * @param int $connectType
	 * @param array $bindUser array('id' => ,'name' => )
	 * @param array $accessToken
	 * 
	 * return array
	 * */
	public function login($connectType, $bindUser, $accessToken){
		JKit::$log->debug(__FUNCTION__." connectType-{$connectType}, bindUser-{$bindUser['id']}, accessToken-", 
			$accessToken);
		$objModelSndareg = new Model_Data_Sndareg();
		$arrReturn = array(
			'uid'=>null,
			'isfirstLogin'=>false,
		);
		//是否为第三方创建帐号
		$row = $this->objModelConnect->getConnectByCid($connectType, $bindUser['id']);

        if (!empty($row)) {
        	$intSdid = (int) $row['user_id'];
            // 已经创建，更新Access Token
            $this->objModelConnect->modifyConnectTokenByUid($connectType, $intSdid, $accessToken);
            //获取用户数据
            $arrUserInfo = $this->objLogicUser->getUserByid($intSdid);
            
            if($arrUserInfo) {
				$this->objLogicUser->login($intSdid);
				//记录日志了
				$modelStat = new Model_Data_Stat();
    			$arrStat = array(
    				'page_id'=>'click_login_success',
    				'user_id' => $intSdid,
    			);
    		    $modelStat->log($arrStat);
			}else{
				$arrReturn['isfirstLogin'] = true;
				Session::instance()->set('sdid', $intSdid);
				Session::instance()->set('avatar', $bindUser['avatar']);
			}
            $arrReturn['uid'] = $intSdid;
            
        } else {
        	//创建帐号
        	switch ($connectType){
        		case Model_Data_UserConnect::TYPE_TQQ :
        			$CompanyId = Model_Data_Sndareg::C_ID_TQQ;
        			break;
        		case Model_Data_UserConnect::TYPE_SINA :
        			$CompanyId = Model_Data_Sndareg::C_ID_XL;
        			break;
        		case Model_Data_UserConnect::TYPE_RENREN :
        			$CompanyId = Model_Data_Sndareg::C_ID_RR;
        			break;
        		case Model_Data_UserConnect::TYPE_DOUBAN :
        			$CompanyId = Model_Data_Sndareg::C_ID_DB;
        			break;
        		case Model_Data_UserConnect::TYPE_QQ :
        			$CompanyId = Model_Data_Sndareg::C_ID_QQ;
        			break;
        	}
        	$sndaParam = array(
        		'AccountId'=>$bindUser["id"],
        		'CompanyId'=>$CompanyId,
        	);
        	//盛大第三方帐号自动创建
        	try {
        		$sndaUserData = $objModelSndareg->AutoBindThirdAccountLogin($sndaParam);
        	} catch (MongoException $e) {
				JKit::$log->warn("AutoBindThirdAccountLogin rpc failure, code-".$e->getCode(). 
				", msg-".$e->getMessage().", param-", $sndaParam);
				return false;
			}
        	if($sndaUserData['return_code']==0){
				$intSdid = (int)$sndaUserData['data']['SndaId'];
				Session::instance()->set('sdid', $intSdid);
				if(isset($bindUser['avatar'])) {
					Session::instance()->set('avatar', $bindUser['avatar']);
				}
				
        	
	        	//创建登录连接
	        	$c_type = Model_Data_UserConnect::CONNECT_TYPE_LOGIN; 
	        	$isCreate = $this->objModelConnect->addConnect($connectType, $intSdid, $c_type, array(
	                	'connect_id' => $bindUser['id'],
	                	'access_token' => $accessToken,
	        			'connect_type' => Model_Data_UserConnect::CONNECT_TYPE_LOGIN,
	                ));
	        	$arrReturn['isfirstLogin'] = true;
	        	$arrReturn['uid'] = $intSdid;
        	}else{
        		Util::sendSmsMonitor("AutoBindThirdAccountLogin failure, ret-". json_encode( $sndaUserData) );
        		JKit::$log->warn("AutoBindThirdAccountLogin response failure, ret-", $sndaUserData);
				return false;
        	}
        }
        JKit::$log->debug(__FUNCTION__." result-", $arrReturn);
		return $arrReturn;
	}
	
	public function bind($uid, $connectType, $bindUser, $accessToken){
		JKit::$log->debug(__FUNCTION__."uid-{$uid} ,connectType-{$connectType}".
			", bindUser-{$bindUser['id']}, accessToken-",$accessToken);
		
		//是否绑定过当前用户
		$row = $this->objModelConnect->getConnectByUid($connectType, $uid);
		if ($row) {
            // 已经绑定过，更新状态
            $ret = $this->objModelConnect->modifyConnectTokenByUid($connectType, $uid, $accessToken);

        } else {
            // 没有绑定过，创建绑定
            $c_type = Model_Data_UserConnect::CONNECT_TYPE_BIND; 
            $ret = $this->objModelConnect->addConnect($connectType, $uid, $c_type, array(
                'connect_id' => $bindUser['id'],
                'access_token' => $accessToken
            ));
        }
        JKit::$log->debug(__FUNCTION__." result-", $ret);
		return $ret;
	}
	/**
	 * 取消绑定
	 * */
	public function unBind($connectType, $uid){
		JKit::$log->debug(__FUNCTION__."uid-{$uid} ,connectType-{$connectType}");
		$row = $this->objModelConnect->getConnectByUid($connectType, $uid);
		$ret = null;
		if($row)
		{
			if($row[0]['connect_type'] == Model_Data_UserConnect::CONNECT_TYPE_LOGIN)
			{
				//绑定登录的帐号
				$ret = $this->objModelConnect->unBindByUid($connectType, $uid);
			}else{
				$ret = $this->objModelConnect->removeConnectByUid($connectType, $uid);
			}
		}
        JKit::$log->debug(__FUNCTION__." result-", $ret);
		return $ret;
	}
	
	public function getBindList($uid){
		$arrConnectList = $this->objModelConnect->getConnectsByUid($uid);
		JKit::$log->debug(__FUNCTION__." ConnectList-", $arrConnectList);
		$bindlist = array(
			'sina'=> array(
				'connect_status' => 0,
			),
			'tqq' => array(
				'connect_status' => 0,
			),
		);
		foreach($arrConnectList as $key=>$val){
			if($val['third_party'] == Model_Data_UserConnect::TYPE_SINA){
				$bindlist['sina'] = $val;
				$bindlist['sina']['time'] = date('Y年m月d日',$val['update_time']->sec);
				$bindlist['sina']['expire_time'] = $val['update_time']->sec+$val['access_token']["expires_in"];
				$bindlist['sina']['access_token'] = $val['access_token'];
			}
			if($val['third_party'] == Model_Data_UserConnect::TYPE_TQQ){
				$bindlist['tqq'] = $val;
				$bindlist['tqq']['time'] = date('Y年m月d日',$val['update_time']->sec);
				$bindlist['tqq']['access_token'] = $val['access_token'];
			}
		}
		return $bindlist;
	}
	/**
	 * 
	 * 获取绑定的那个row
	 * @param int $uid
	 * @param int $thirdParty 第三方类型
	 * 
	 * @return array
	 */
	public function getBindConnectInfo( $uid, $thirdParty ) {
		$arrReturn = array();
		$arrUserConnect = $this->objModelConnect->getConnectByUid($thirdParty, $uid);
		if( !$arrUserConnect ) {
			return $arrReturn;
		}
		$intLength = count($arrUserConnect);
		$arrConnectInfo = array_shift($arrUserConnect);
		if( $arrConnectInfo["connect_type"]==Model_Data_UserConnect::CONNECT_TYPE_BIND 
		|| $intLength==1 ) {
			return $arrConnectInfo;
		}
		return array_shift($arrUserConnect);
	}
	/**
	 * 
	 * 检查是否允许使用
	 * @param int $connectType
	 * @param boolean $isLogin 
	 * 
	 * @return boolean
 	 */
	private function checkModIsAllowUse($connectType, $isLogin=true) {
		$arrWhitelist = array(
			Model_Data_UserConnect::TYPE_DOUBAN => array(
				'is_login' => true,
				'is_bind' => false
			),
			Model_Data_UserConnect::TYPE_QQ => array(
				'is_login' => true,
				'is_bind' => false
			),
			Model_Data_UserConnect::TYPE_RENREN => array(
				'is_login' => true,
				'is_bind' => false
			),
			Model_Data_UserConnect::TYPE_SINA => array(
				'is_login' => true,
				'is_bind' => true
			),
			Model_Data_UserConnect::TYPE_TQQ => array(
				'is_login' => true,
				'is_bind' => true
			),
		);
		
		if( !isset($arrWhitelist[$connectType]) ) {
			return false;
		}
		return $isLogin ? $arrWhitelist[$connectType]['is_login'] : $arrWhitelist[$connectType]['is_bind'];
	}
	/**
	 * 
	 * 根据agent返回
	 * @param string $strAgent
	 * 
	 * @return int
	 */
	private function getAgentType( $strAgent=null ) {
		if($strAgent===NULL) {
    		$strAgent = $_SERVER['HTTP_USER_AGENT'];
    	}
    	$strAgent = strtolower($strAgent);
    	$intType = 1;
		if (strstr($strAgent, 'iphone')) {
			$intType = 4;
		} elseif (strstr($strAgent, 'ipad')) {
			$intType = 5;
		} 
		
		return $intType;
	}
	/**
	 * 
	 * 初始化model
	 * @param unknown_type $thirdParty
	 */ 
    private function initConnectApiModel( $thirdParty ) {
    	$arrThirdPartyToModelMap = array(
    		Model_Data_UserConnect::TYPE_DOUBAN => array(
    			"config" => "connect.douban",
    			"class" => "Model_Douban"
    		),
    		Model_Data_UserConnect::TYPE_QQ => array(
    			"config" => "connect.qq",
    			"class" => "Model_Qq"
    		),
    		Model_Data_UserConnect::TYPE_RENREN => array(
    			"config" => "connect.renren",
    			"class" => "Model_Renren"
    		),
    		Model_Data_UserConnect::TYPE_SINA => array(
    			"config" => "connect.sina",
    			"class" => "Model_Sina"
    		),
    		Model_Data_UserConnect::TYPE_TQQ => array(
    			"config" => "connect.tqq",
    			"class" => "Model_Tqq"
    		),
    		Model_Data_UserConnect::TYPE_MSN => array(
    			"config" => "connect.msn",
    			"class" => "Model_Msn"
    		),
    	);
    	if( !isset($arrThirdPartyToModelMap[$thirdParty]) ) {
    		throw new Model_Logic_Exception();
    	}
    	$arrConfig = $arrThirdPartyToModelMap[$thirdParty];
    	$config = Kohana::$config->load($arrConfig['config']);
    	call_user_func_array(array($arrConfig['class'], "init"), array($config['app']['id'], $config['app']['secret']));
    }
}