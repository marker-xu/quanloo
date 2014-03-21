<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 用户、用户视频关系等页面逻辑
 * @author xucongbin
 */
class Model_Logic_Connect extends Model {
	private $objModelUser;
	
	private $objModelConnect;
	
	public function __construct() {
		$this->objModelUser = new Model_Data_User();
		$this->objModelConnect = new Model_Data_UserConnect();
	}
	
	public function qqCallback($arrParams) {
		// 用地址栏参数里的授权码到QQ获取Access Token
        $config = Kohana::$config->load('connect.qq');
        $params = array(
            'oauth_consumer_key' => $config['app']['id'],
            'oauth_token' => $arrParams['oauth_token'],
            'oauth_nonce' => time(),
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_vericode' => $arrParams['oauth_vericode']
        );
        $params['oauth_signature'] = Model_Qq::sign(Request::GET, 
            $config['accessTokenUri'], $params, Session::instance()->get('qqOauthTokenSecret'));
        try {
            $request = Request::factory($config['accessTokenUri']);
            $request->client()->options(array(
                CURLOPT_TIMEOUT => 5
            ));
            $response = $request->method(Request::GET)
                ->query($params)
                ->execute();
        } catch (Exception $e) {
        	JKit::$log->debug(__FUNCTION__." code-{$e->getCode()}, msg-{$e->getMessage()}");
        	throw new Model_Logic_Exception('获取Access Token失败。(' . $e->getMessage() .')', -4001);
        }
        $body = $response->body();
        parse_str($body, $accessToken);
        // 使用Access Token获取QQ用户信息
        $modelQq = new Model_Qq($accessToken, Kohana::$config->load('connect.qq'));
        $qqUser = $modelQq->getUserInfo();
        return array( 
        	'bindUser' => array(
            	'id' => $qqUser['uid'],
                'name' => $qqUser['nickname']
            ),
            'token' => $accessToken
        );
	}
	
	public function getQQRedirectUrl($strCallBackUrl) {
		$config = Kohana::$config->load('connect.qq');
		$body = $this->verifyQQSign();
        $url = $config['authorizeUri'] . URL::query(array(
            'oauth_consumer_key' => $config['app']['id'],
            'oauth_token' => $body['oauth_token'],
            'oauth_callback' => $strCallBackUrl.URL::query(array(
        	'type' => Model_Data_UserConnect::TYPE_QQ
        ), false)
        ), false);
        return $url;
	}
	
	public function verifyQQSign() {
		$config = Kohana::$config->load('connect.qq');
        $params = array(
            'oauth_consumer_key' => $config['app']['id'],
            'oauth_nonce' => time(),
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1'
        );
        $params['oauth_signature'] = Model_Qq::sign(Request::GET, 
            $config['requestTokenUri'], $params);
        try {
            $request = Request::factory($config['requestTokenUri']);
            $request->client()->options(array(
                CURLOPT_TIMEOUT => 5
            ));
            $response = $request->method(Request::GET)
                ->query($params)
                ->execute();
        } catch (Exception $e) {
        	JKit::$log->debug(__FUNCTION__." code-{$e->getCode()}, msg-{$e->getMessage()}");
        	throw new Model_Logic_Exception('获取Request Token失败。 (' . $e->getMessage() . ')', -4001);
        }
        $body = $response->body();
        parse_str($body, $body);
        Session::instance()->set('qqOauthTokenSecret', $body['oauth_token_secret']);
        
        return $body;
	}
	
	public function getSndaRedirectUrl($strCallBackUrl) {
		$config = Kohana::$config->load('connect.snda');
        $modelSnda = new Model_Snda($config);
        $url = $modelSnda->getAuthorizeUrl($strCallBackUrl.URL::query(array(
        	'type' => Model_Data_UserConnect::TYPE_SNDA
        ), false));
        
        return $url;
	}
	
	public function sndaCallback($arrParams) {
		// 用地址栏参数里的授权码到盛大获取Access Token
        $config = Kohana::$config->load('connect.snda');
        $modelSnda = new Model_Snda($config);
        $accessToken = $modelSnda->getAccessToken($arrParams['code'], 
            URL::site('/connect/callback', $arrParams));
        // 使用Access Token获取盛大用户信息
        $oauthClient = Model_Snda::getOauthClient($config['app']['key'], $config['app']['secret'], 
            $accessToken);
        // TODO 查询用户信息
        $sndaUser = array(
        	'id' => $accessToken['sdid'],
        	'name' => $accessToken['sdid']
        );
        return array( 
        	'bindUser' => $sndaUser,
            'token' => $accessToken
        );
	}
	
	
	public function getSinaRedirectUrl($strCallBackUrl) {
		$modelSina = new Model_Sina(Kohana::$config->load('connect.sina'));
        $requestToken = $modelSina->getRequestToken();
        if (!$requestToken) {
            JKit::$log->debug(__FUNCTION__." token-", $requestToken);
            throw new Model_Logic_Exception('获取Request Token出错', -4001);
        }
        $callback = $strCallBackUrl . URL::query(array(
        	'oauth_token' => $requestToken['oauth_token'],
        	'oauth_token_secret' => $requestToken['oauth_token_secret'],
        	'type' => Model_Data_UserConnect::TYPE_WEIBO
        ), false);
        $url = $modelSina->getAuthorizeUrl($requestToken['oauth_token'], $callback);
        
        return $url;
	}
	
	public function sinaCallback($arrParams) {
	 	// 用地址栏参数里的授权码到新浪获取Access Token
        $oAuthVerifier = $arrParams['oauth_verifier'];
        $oAuthToken = $arrParams['oauth_token'];
        $oAuthTokenSecret = $arrParams['oauth_token_secret'];
        $modelSina = new Model_Sina(Kohana::$config->load('connect.sina'));
        $accessToken = $modelSina->getAccessToken($oAuthVerifier, $oAuthToken, $oAuthTokenSecret);
        if (!$accessToken) {
            JKit::$log->debug(__FUNCTION__." token-", $accessToken);
            throw new Model_Logic_Exception('获取Access Token出错', -4001);
        }
        // 使用Access Token获取新浪用户信息
        $config = Kohana::$config->load('connect.sina');
        $weiboClient = Model_Sina::getWeiboClient($config['app']['key'], $config['app']['secret'], 
            $accessToken['oauth_token'], $accessToken['oauth_token_secret']);
        $sinaUser = $weiboClient->show_user($accessToken['user_id']);
        
        return array( 
        	'bindUser' => $sinaUser,
            'token' => $accessToken
        );
	}
	
	public function getRenrenRedirectUrl($strCallBackUrl) {
		$config = Kohana::$config->load('connect.renren');
        $url = $config['authorizeUri'] . URL::query(array(
            'client_id' => $config['app']['key'],
            'redirect_uri' => $strCallBackUrl.URL::query(
        		array(
        			'type' => Model_Data_UserConnect::TYPE_RENREN
        		), false),
            'response_type' => 'code',
            'scope' => 'publish_feed,read_user_album,read_user_photo,create_album,photo_upload'
        ), false);
        
        return $url;
	}
	
	public function renrenCallback($arrParams, $strCallBackUrl) {
		// 用地址栏参数里的授权码到人人获取Access Token
        $config = Kohana::$config->load('connect.renren');
        $params = array(
            'client_id' => $config['app']['key'],
            'client_secret' => $config['app']['secret'],
            'redirect_uri' => $strCallBackUrl,
            'grant_type' => 'authorization_code',
            'code' => $arrParams['code']
        );
        try {
            $request = Request::factory($config['accessTokenUri']);
            $request->client()->options(array(
                CURLOPT_TIMEOUT => 5
            ));
            $response = $request->method(Request::POST)
                ->post($params)
                ->execute();
        } catch (Exception $e) {
        	JKit::$log->debug("getRenRenToken code-{$e->getCode()}, msg-{$e->getMessage()}");
        	throw new Model_Logic_Exception('获取Access Token失败。 (' . $e->getMessage() . ')', -4001);
        }
        $body = $response->body();
        $accessToken = json_decode($body, true);
        $accessToken['expiration_time'] = time() + $accessToken['expires_in'];
        $params = array(
            'oauth_token' => $accessToken['access_token']
        );
        try {
            $request = Request::factory($config['sessionKeyUri']);
            $request->client()->options(array(
                CURLOPT_TIMEOUT => 5
            ));
            $response = $request->method(Request::GET)
                ->post($params)
                ->execute();
        } catch (Exception $e) {
        	JKit::$log->debug("getRenrenSessionKey code-{$e->getCode()}, msg-{$e->getMessage()}");
        	throw new Model_Logic_Exception('获取Session Key失败。 (' . $e->getMessage() . ')', -4002);
        }
        $body = $response->body();
        $sessionKey = json_decode($body, true);
        $sessionKey['renren_token']['expiration_time'] = time() + $sessionKey['renren_token']['expires_in'];
        // 使用Access Token获取人人用户信息
        $modelRenren = new Model_Renren($sessionKey['renren_token']['session_key'], 
            Kohana::$config->load('connect.renren'));
        $renrenUsers = $modelRenren->request('users.getInfo', array(
            'uids' => $sessionKey['user']['id'],
            'fields' => 'uid,name,sex,birthday,tinyurl,headurl,mainurl'
        ));
        $renrenUser = $renrenUsers[0];
        return array( 
        	'bindUser' => array(
                	'id' => $renrenUser['uid'], 
         			'name' => $renrenUser['name']
            ),
            'token' => $accessToken
        );
	}
	
	public function processToken($connectType, $bindUser, $accessToken) {
		// 建立第三方用户跟本站用户的绑定关系
        $row = $this->objModelConnect->getConnectByCid($connectType, $bindUser['id']);
        if ($row) {
            // 已经绑定过，更新Access Token
            $this->objModelConnect->modifyConnectTokenByUid($connectType, 
            $row['user_id'], $accessToken);
            User::login($row['user']);
            return true;
        } else {
            // 没有绑定过
            $user = Session::instance()->get('user');
            if ($user) {
                // 已经登录，直接绑定到当前登录用户
                $this->objModelConnect->addConnect($connectType, $user["_id"], array(
                	'connect_id' => $bindUser['id'],
                	'access_token' => $accessToken
                ));
                User::login($user['_id']);
                return true;
            } else {
                // 没有登录，跳转到绑定页面，绑定到指定帐号
                Session::instance()->set('bindUser', $bindUser);
                Session::instance()->set('bindToken', $accessToken);
                return false;
            }
        }
	}
	
	public function sndaShow($isToBind) {
		return $isToBind ? '<script type="text/javascript">
                	window.opener.location.href = "/user/bind?to=snda";
                	window.close();
                </script>':'<script type="text/javascript">
            	window.opener.location.href = "/";
            	window.close();
            </script>';
	}
}