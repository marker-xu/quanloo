<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 盛大第三方接口，用户注册帐号
 * @author zhangjianbin
 */
class Model_Data_Sndareg extends Model
{
	CONST APP_ID = 317;
	
	CONST APP_AREA = -1;
	
	CONST APP_GROUPID = -1;
	
	CONST APP_SPNAME = '3_317_289';
	
	CONST APP_SPID = 3;
	
	CONST APP_SECRET = 'DY.:suP_vj5S/H_N';

	
	//人人网 rr renren.com
	const C_ID_RR = 101;
	//开心网 kx kaixin001.com 110
	const C_ID_KX = 110;
	//豆瓣网 db douban.com 109
	const C_ID_DB = 109;
	//新浪网 xl sina.com 105
	const C_ID_XL = 105;
	//360 36 360.cn 113
	const C_ID_36 = 113;
	//谷歌 102
	const C_ID_GOOGLE = 102;
	//雅虎 103
	const C_ID_YAHOO = 103;
	//百度 104
	const C_ID_BAIDU = 104;
	//QQ 106
	const C_ID_QQ = 106;
	//腾讯微博 107
	const C_ID_TQQ = 107;
	
    /**
     * 上一次请求返回的Httpcode
     * @var number
     */
    protected $_http_code = null;

    /**
     * 是否debug
     * @var bool
     */
    protected $_debug = false;
    	
	protected $_http_header = array();

    protected $_useragent = 'OpenSDK-OAuth1.0';

    protected $_http_info = array();

    public $connecttimeout = 3;
    public $timeout = 3;
    public $ssl_verifypeer = false;
    
    public function debug($debug){
    
    	$this->_debug = $debug;
    }
    
    /**
     * 盛大第三方自动注册接口
     * http://hps.sdo.com/apl_thirdaccount4sdg/?method=thirdaccount.AutoBindThirdAccountLogin
     * @param array $params
     * @return array
     * */
	//http://hps.sdo.com/apl_thirdaccount4sdg/?method=thirdaccount.AutoBindThirdAccountLogin
	// $params = array('CompanyId','AccountId')
	public function AutoBindThirdAccountLogin($params){
		$apiServer = 'http://hps.sdo.com/apl_thirdaccount4sdg';
		$params['method'] = 'thirdaccount.AutoBindThirdAccountLogin';
		$params['signature_method'] = 'MD5';
		$params['AppId'] = self::APP_ID;
		$params['EndpointIp'] = Request::$client_ip;
		$params['EndpointType'] = 1;
		$params['CallTime'] = date("Y-m-d H:i:s");;
		$params['AreaId'] = self::APP_AREA;
		$params['osap_user'] = self::APP_SPNAME;
		$params['GUID'] = $this->guid();
		$params['signature'] = $this->sign($params);
		
		//$response = $this->curl_http($apiServer, $params, $method='GET');
		
		$url = $apiServer.'/?'.http_build_query($params);
		
		$objResponse = Request::factory($url)->execute();
        if($objResponse->status()!=200) {
        	throw new Model_Data_Exception("cas AutoBindThirdAccountLogin service failure, code-".$objResponse->status(), -2001);
        }
        $response = $objResponse->body();
        JKit::$log->debug("checkstat, content-".$response);
        $response = json_decode($response,true);
        
		return $response;

	}
	
	/**
     * 盛大第三方自动注册 查询
     * http://ip:port/apl_thirdaccount4sdg/?method=thirdaccount.QueryThirdAccount
     * @param array $params
     * @return array
     * */
	//http://hps.sdo.com/apl_thirdaccount4sdg/?method=thirdaccount.AutoBindThirdAccountLogin
	// $params = array('CompanyId','AccountId','osap_user')
	public function QueryThirdAccount($params){
		$apiServer = 'http://hps.sdo.com/apl_thirdaccount4sdg';
		$params['method'] = 'thirdaccount.QueryThirdAccount';
		$params['signature_method'] = 'MD5';
		$params['AppId'] = self::APP_ID;
		$params['EndpointIp'] = Request::$client_ip;
		$params['EndpointType'] = 1;
		$params['CallTime'] = date("Y-m-d H:i:s");;
		$params['osap_user'] = self::APP_SPNAME;
		$params['GUID'] = $this->guid();
		$params['signature'] = $this->sign($params);
		
		//$response = $this->curl_http($apiServer, $params, $method='GET');
		
		$url = $apiServer.'/?'.http_build_query($params);
		$objResponse = Request::factory($url)->execute();
        if($objResponse->status()!=200) {
        	throw new Model_Data_Exception("cas AutoBindThirdAccountLogin service failure, code-".$objResponse->status(), -2001);
        }
        $response = $objResponse->body();
        JKit::$log->debug("checkstat, content-".$response);
		return $response;

	}
	
	/**
	 * 远程http请求
	 * @param string $url
	 * @param array $params
	 * @param string $method
	 * 
	 * @return array
	 * */
	protected function curl_http( $url , $params , $method='GET')
    {
        $method = strtoupper($method);
        $ci = curl_init();
    	
        curl_setopt($ci, CURLOPT_USERAGENT, $this->_useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);

        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));

        curl_setopt($ci, CURLOPT_HEADER, false);

        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params))
                {
                    $post_fields = http_build_query($params);
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $post_fields);
                }
                break;
             case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
            case 'PUT':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                    
                }
                break;
            case 'GET':
                if (!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);

        $response = curl_exec($ci);
        $this->_http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->_http_info = array_merge($this->_http_info, curl_getinfo($ci));

        if($this->_debug)
        {
            echo 'Http Code ' , $this->_http_code , "\r\n";
            foreach ((array)$this->_http_info as $k => $v )
            {
                echo $k , ': ' , $v , "\r\n";
            }
            echo "\r\n";
            echo $response;
            echo "\r\n";
        }
        curl_close ($ci);
        return $response;
    }
    
	/**
	 * 创建GUID
	 * 
	 * @param null
	 * @return string
	 * */
	protected function guid(){
	    if (function_exists('com_create_guid')){
	        return com_create_guid();
	    }else{
	        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
	        $charid = strtoupper(md5(uniqid(rand(), true)));
	        $hyphen = chr(45);// "-"
	        $uuid = chr(123)// "{"
	                .substr($charid, 0, 8).$hyphen
	                .substr($charid, 8, 4).$hyphen
	                .substr($charid,12, 4).$hyphen
	                .substr($charid,16, 4).$hyphen
	                .substr($charid,20,12)
	                .chr(125);// "}"
	        return $uuid;
	    }
	}
	/**
	 * 生成签名
	 * 
	 * @param array $params
	 * @return string
	 * */
	protected function sign($params)
    {
        uksort($params, 'strcmp');
        $pairs = array();
        foreach($params as $key => $value)
        {
            //$key = self::urlencode_rfc3986($key);
            if(is_array($value))
            {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                natsort($value);
                foreach($value as $duplicate_value)
                {
                    $pairs[] = $key . '=' . $duplicate_value;
                }
            }
            else
            {
                $pairs[] = $key . '=' . $value;
            }
        }

        $sign_parts = implode('', $pairs);

        $base_string = $sign_parts.self::APP_SECRET;

        $sign = md5($base_string);
        if($this->_debug)
        {
            echo 'base_string: ' , $base_string , "\n";
            echo 'sign: ' , $sign , "\n";
        }
        return $sign;
    }
    
    /**
     * Get the header info to store.
     *
     * @return int
     */
    function getHeader($ch, $header)
    {
        $i = strpos($header, ':');
        if (!empty($i))
        {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->_http_header[$key] = $value;
        }
        return strlen($header);
    }
    /**
     * urlencode_rfc3986
     *
     * @return string
     */
	public static function urlencode_rfc3986($input) {
		if (is_array($input)) {
			return array_map(array('Model_Logic_Sndareg','urlencode_rfc3986'), $input);
		} else if (is_scalar($input)) {
			return str_replace('+', ' ',
		                       str_replace('%7E', '~', rawurlencode($input)));
		} else {
			return '';
		}
	}
    
    /**
     * urldecode_rfc3986
  	 * This decode function isn't taking into consideration the above 
  	 * modifications to the encoding process. However, this method doesn't 
  	 * seem to be used anywhere so leaving it as is.
  	 * 
     * @return string
     */

  	public static function urldecode_rfc3986($string) {/*{{{*/
    	return rawurldecode($string);
  	}
}