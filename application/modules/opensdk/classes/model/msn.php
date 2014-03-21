<?php

require_once JKit::find_file('vendor', 'OAuth/Interface');
require_once JKit::find_file('vendor', 'OAuth2/Client');

/**
 * 
 * MSN开放平台
 * 
 * @author xucongbin
 *
 */
class Model_Msn extends OAuth_Interface
{

    /**
     * app key
     * @var string
     */
    protected static $client_id = '';
    /**
     * app secret
     * @var string
     */
    protected static $client_secret = '';

    /**
     * 初始化
     * @param string $appkey
     * @param string $appsecret
     */
    public static function init($appkey,$appsecret)
    {
        self::$client_id = $appkey;
        self::$client_secret = $appsecret;
    }
    
    /**
     * OAuth 对象
     * @var OpenSDK_OAuth_Client
     */
    private static $oauth = null;

    private static $accessTokenURL = 'https://oauth.live.com/token';

    private static $authorizeURL = 'https://oauth.live.com/authorize';
	
    const API_BASE_URL = "https://apis.live.net/V5.0/";
    
    /**
     * OAuth 版本
     * @var string
     */
    protected static $version = '2.0';

    /**
     * 存储access_token的session key
     */
    const ACCESS_TOKEN = 'msn_access_token';

    /**
     * 存储refresh_token的session key
     */
    const REFRESH_TOKEN = 'msn_refresh_token';

    /**
     * 存储expires_in的sieesion key
     */
    const EXPIRES_IN = 'msn_expires_in';

    /**
     * authorize接口
     *
     * 对应API：{@link https://oauth.live.com/authorize}
     *
     * @param string $url 授权后的回调地址,站外应用需与回调地址一致,站内应用需要填写canvas page的地址
     * @param string $response_type 支持的值包括 code 和token 默认值为code
     * @param string $scopes 所访问信息的权限范围，各个权限范围之间用空格分隔
     * @return string
     */
    public static function getAuthorizeURL($url, $response_type, $scopes='')
    {
        $params = array();
        $params['client_id'] = self::$client_id;
        $params['redirect_uri'] = $url;
        $params['response_type'] = $response_type;
        if( $scopes ) {
        	$params['scope'] = $scopes;
        }
        
        return self::$authorizeURL . '?' . http_build_query($params);
    }

    /**
     * 存储sina user_id的session key
     */
    const OAUTH_USER_ID = 'msn_user_id';

    /**
	 * access_token接口
	 *
	 * 对应API：{@link https://oauth.live.com/token}
	 *
	 * @param string $type 请求的类型,可以为:code, password, token
	 * @param array $keys 其他参数：
	 *  - 当$type为code时： array('code'=>..., 'redirect_uri'=>...)
	 *  - 当$type为password时： array('username'=>..., 'password'=>...)
	 *  - 当$type为token时： array('refresh_token'=>...)
	 * @return array
	 */
    public static function getAccessToken( $type , $keys )
    {
        $params = array();
        $params['client_id'] = self::$client_id;
        $params['client_secret'] = self::$client_secret;
        if ( $type === 'token' ) {
            $params['grant_type'] = 'refresh_token';
            $params['refresh_token'] = $keys['refresh_token'];
        } elseif ( $type === 'code' ) {
            $params['grant_type'] = 'authorization_code';
            $params['code'] = $keys['code'];
            $params['redirect_uri'] = $keys['redirect_uri'];
        } elseif ( $type === 'password' ) {
            $params['grant_type'] = 'password';
            $params['username'] = $keys['username'];
            $params['password'] = $keys['password'];
        } else {
            exit("wrong auth type");
        }

        $response = self::request(self::$accessTokenURL , 'POST', $params);
        $token = OpenSDKUtil::json_decode($response, true);
        if ( is_array($token) && !isset($token['error']) ) 
        {
            self::setParam(self::ACCESS_TOKEN, $token['access_token']);
           	if(isset($token['refresh_token'])){
            	self::setParam(self::REFRESH_TOKEN, $token['refresh_token']);
        	}
            self::setParam(self::EXPIRES_IN, $token['expires_in']);
        } 
        else
        {
            JKit::$log->warn(__FUNCTION__."get access token failed ,ret-".$response);
            return false;
        }
        return $token;
    }
	
	public static function getUserPic( $accessToken=NULL ) {
		if($accessToken===NULL) {
			$accessToken = self::getParam(self::ACCESS_TOKEN);
		}
		return self::API_BASE_URL."me/picture?access_token=".$accessToken;
	}
    
    /**
     * 统一调用接口的方法
     * 照着官网的参数往里填就行了
     * 需要调用哪个就填哪个，如果方法调用得频繁，可以封装更方便的方法。
     *
     * 如果上传文件 $method = 'POST';
     * $multi 是一个二维数组
     *
     * array(
     *    '{fieldname}' => array(        //第一个文件
     *        'type' => 'mine 类型',
     *        'name' => 'filename',
     *        'data' => 'filedata 字节流',
     *    ),
     *    ...如果接受多个文件，可以再加
     * )
     *
     * @param string $command 官方说明中去掉 https://api.weibo.com/2/ 后面剩余的部分
     * @param array $params 官方说明中接受的参数列表，一个关联数组
     * @param string $method 官方说明中的 method GET/POST
     * @param false|array $multi 是否上传文件 false:普通post array: array ( '{fieldname}'=>'/path/to/file' ) 文件上传
     * @param bool $decode 是否对返回的字符串解码成数组
     * @param OpenSDK_Sina_Weibo::RETURN_JSON|OpenSDK_Sina_Weibo::RETURN_XML $format 调用格式
     */
    public static function call($command , $params=array() , $method = 'GET' , $multi=false , $decode=true , $format='json')
    {
        if($format == self::RETURN_XML)
            ;
        else
            $format == self::RETURN_JSON;
        //去掉空数据
        foreach($params as $key => $val)
        {
            if(strlen($val) == 0)
            {
                unset($params[$key]);
            }
        }
        $params['access_token'] = self::getParam(self::ACCESS_TOKEN);
        $params['source'] = self::$client_id;
        $response = self::request( self::API_BASE_URL .ltrim($command,'/') , $method, $params, $multi);
        if($decode)
        {
            if( $format == self::RETURN_JSON )
            {
                return OpenSDKUtil::json_decode($response, true);
            }
            else
            {
                //todo parse xml2array later
                //没必要。用json即可!
                return $response;
            }
        }
        else
        {
            return $response;
        }
    }

    protected static $_debug = false;

    public static function debug($debug=false)
    {
        self::$_debug = $debug;
    }

    /**
     * 获得OAuth2 对象
     * @return OpenSDK_OAuth2_Client
     */
    protected static function getOAuth()
    {
        if( null === self::$oauth )
        {
            self::$oauth = new OAuth2_Client(self::$_debug);
        }
        return self::$oauth;
    }

    /**
     *
     * OAuth协议请求接口
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @param array $multi
     * @return string
     * @ignore
     */
    protected static function request($url , $method , $params , $multi=false)
    {
        if(!self::$client_id || !self::$client_secret)
        {
            exit('app key or app secret not init');
        }
        $method = strtoupper($method);
        $headers = array(
            'API-RemoteIP: ' . self::getRemoteIp(),
        );
        if($multi){
            array_push($headers, 'Content-Type: multipart/form-data');
        }
        return self::getOAuth()->request($url, $method, $params, $multi ,$headers);
    }

}
