<?php

require_once JKit::find_file('vendor', 'Util');

/**
 * OAuth协议接口
 *
 * 依赖：
 * PHP 5 >= 5.1.2, PECL hash >= 1.1 (no need now)
 * 
 * @ignore
 * @author icehu@vip.qq.com
 *
 */

class OAuth_Client
{
    /**
     * 签名的url标签
     * @var string
     */
    public $oauth_signature_key = 'oauth_signature';

    /**
     * app secret
     * @var string
     */
    protected $_app_secret = '';

    /**
     * token secret
     * @var string
     */
    protected $_token_secret = '';

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

    public function  __construct( $appsecret='' , $debug=false)
    {
        $this->_app_secret = $appsecret;
        $this->_debug = $debug;
    }
    /**
     * 设置App secret
     * @param string $appsecret
     */
    public function setAppSecret($appsecret)
    {
        $this->_app_secret = $appsecret;
    }

    /**
     * 设置token secret
     * @param string $tokensecret
     */
    public function setTokenSecret($tokensecret)
    {
        $this->_token_secret = $tokensecret;
    }

    /**
     * 组装参数签名并请求接口
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @param false|array $multi false:普通post array: array ( '{fieldname}' =>array('type'=>'mine','name'=>'filename','data'=>'filedata') ) 文件上传
     * @param array $extheaders 附加的HTTP头信息
     * @return string
     */
    public function request( $url, $method, $params, $multi = false , $extheaders=array())
    {

        $header_only = isset($params['header_only']) && $params['header_only'];
        if(!$header_only){
            $oauth_signature = $this->sign($url, $method, $params);
            $params[$this->oauth_signature_key] = $oauth_signature;
        }else{
            $header = $this->build_signature_header($params, $url, $method);
            $extheaders = array_merge(array($header,'Content-Type: application/atom+xml; charset=utf-8'),$extheaders);
        }

        return $this->http($url, $params, $method, $multi, $extheaders);
    }

    /**
     * OAuth 协议的签名
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @return string
     */
    protected function sign( $url , $method, $params )
    {
        uksort($params, 'strcmp');
        $pairs = array();
        foreach($params as $key => $value)
        {
            $key = OpenSDKUtil::urlencode_rfc3986($key);
            if(is_array($value))
            {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                natsort($value);
                foreach($value as $duplicate_value)
                {
                    $pairs[] = $key . '=' . OpenSDKUtil::urlencode_rfc3986($duplicate_value);
                }
            }
            else
            {
                $pairs[] = $key . '=' . OpenSDKUtil::urlencode_rfc3986($value);
            }
        }

        $sign_parts = OpenSDKUtil::urlencode_rfc3986(implode('&', $pairs));

        $base_string = implode('&', array( strtoupper($method) , OpenSDKUtil::urlencode_rfc3986($url) , $sign_parts ));

        $key_parts = array(OpenSDKUtil::urlencode_rfc3986($this->_app_secret), OpenSDKUtil::urlencode_rfc3986($this->_token_secret));

        $key = implode('&', $key_parts);
        $sign = base64_encode(OpenSDKUtil::hash_hmac('sha1', $base_string, $key, true));
        if($this->_debug)
        {
            echo 'base_string: ' , $base_string , "<br>\n";
            echo 'sign key: ', $key , "<br>\n";
            echo 'sign: ' , $sign , "<br>\n";
        }
        return $sign;
    }

    /**
     * Http请求接口
     * 优先使用curl，在不支持curl的情况下，使用socket操作
     *
     * 发送文件需要使用 $method = 'POST' ， 同时指定 $multi = array('{fieldname}'=>'/path/to/file)
     *
     * @param string $url
     * @param array $params
     * @param string $method 支持 GET / POST / DELETE
     * @param false|array $multi false:普通post array: array ( '{fieldname}'=>'/path/to/file' ) 文件上传
     * @return string
     */
    protected function http( $url , $params , $method='GET' , $multi=false , $extheaders=array())
    {
        if(function_exists('curl_init'))
        {
            return $this->curl_http($url, $params, $method, $multi, $extheaders);
        }
        else
        {
            return $this->socket_http($url, $params, $method, $multi, $extheaders);
        }
    }

    protected $_http_header = array();

    protected $_useragent = 'OpenSDK-OAuth1.0';

    protected $_http_info = array();

    public $connecttimeout = 3;
    public $timeout = 3;
    public $ssl_verifypeer = false;

    protected function curl_http( $url , $params , $method='GET' , $multi=false  , $extheaders=array())
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

		$header_only = isset($params['header_only']) && $params['header_only'];
        $headers = (array)$extheaders;
        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params))
                {
                    if($multi)
                    {
                        foreach($multi as $key => $file)
                        {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    }
                    else
                    {
				        if($header_only){
                            $post_fields = $params['post_body'];
                        }else{
                            $post_fields = http_build_query($params);
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $post_fields);
                    }
                }
                break;
             case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    if(!$header_only){
                        $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                    }
                }
                break;
            case 'PUT':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    if($header_only){
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params['post_body']);
                    }else{
                        $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                    }
                    
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
        if(!empty($headers))
        {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }

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
     * Http请求接口
     *
     * @param string $url
     * @param array $params
     * @param string $method 支持 GET / POST / DELETE
     * @param false|array $multi false:普通post array: array ( '{fieldname}'=>'/path/to/file' ) 文件上传
     * @return string
     */
    protected function socket_http( $url , $params , $method='GET' , $multi=false , $extheaders=array())
    {
        $method = strtoupper($method);
        $postdata = '';
        $urls = @parse_url($url);
        $httpurl = $urlpath = $urls['path'] . ($urls['query'] ? '?' . $urls['query'] : '');
        if( !$multi )
        {
            $parts = array();
            foreach ($params as $key => $val)
            {
                $parts[] = urlencode($key) . '=' . urlencode($val);
            }
            if ($parts)
            {
                $postdata = implode('&', $parts);
                $httpurl = $httpurl . (strpos($httpurl, '?') ? '&' : '?') . $postdata;
            }
            else
            {
            }
        }

        $host = $urls['host'];
        $port = $urls['port'] ? $urls['port'] : 80;
        $version = '1.1';
        if($urls['scheme'] === 'https')
        {
            $port = 443;
        }
        $headers = array();
        if($method == 'GET')
        {
            $headers[] = "GET $httpurl HTTP/$version";
        }
        else if($method == 'DELETE')
        {
            $headers[] = "DELETE $httpurl HTTP/$version";
        }
        else
        {
            $headers[] = "POST $urlpath HTTP/$version";
        }
        $headers[] = 'Host: ' . $host;
        $headers[] = 'User-Agent: '.$this->_useragent;
        foreach ((array)$extheaders as $head)
        {
            $headers[] = $head;
        }
        $headers[] = 'Connection: Close';

        if($method == 'POST')
        {
            if($multi)
            {
                $boundary = uniqid('------------------');
                $MPboundary = '--' . $boundary;
                $endMPboundary = $MPboundary . '--';
                $multipartbody = '';
                $headers[]= 'Content-Type: multipart/form-data; boundary=' . $boundary;
                foreach($params as $key => $val)
                {
                    $multipartbody .= $MPboundary . "\r\n";
                    $multipartbody .= 'Content-Disposition: form-data; name="' . $key . "\"\r\n\r\n";
                    $multipartbody .= $val . "\r\n";
                }
                foreach($multi as $key => $path)
                {
                    $multipartbody .= $MPboundary . "\r\n";
                    $multipartbody .= 'Content-Disposition: form-data; name="' . $key . '"; filename="' . pathinfo($path, PATHINFO_BASENAME) . '"' . "\r\n";
                    $multipartbody .= 'Content-Type: ' . self::get_image_mime($path) . "\r\n\r\n";
                    $multipartbody .= file_get_contents($path) . "\r\n";
                }
                $multipartbody .= $endMPboundary . "\r\n";
                $postdata = $multipartbody;
            }
            else
            {
                $headers[]= 'Content-Type: application/x-www-form-urlencoded';
            }
        }
        $ret = '';
        $fp = fsockopen($host, $port, $errno, $errstr, 5);

        if(! $fp)
        {
            $error = 'Open Socket Error';
            return '';
        }
        else
        {
            if( $method != 'GET' && $postdata )
            {
                $headers[] = 'Content-Length: ' . strlen($postdata);
            }
            $this->fwrite($fp, implode("\r\n", $headers));
            $this->fwrite($fp, "\r\n\r\n");
            if( $method != 'GET' && $postdata )
            {
                $this->fwrite($fp, $postdata);
            }
            //skip headers
            while(! feof($fp))
            {
                $ret .= fgets($fp, 1024);
            }
            if($this->_debug)
            {
                echo $ret;
            }
            fclose($fp);
            $pos = strpos($ret, "\r\n\r\n");
            if($pos)
            {
                $rt = trim(substr($ret , $pos+1));
                $responseHead = trim(substr($ret, 0 , $pos));
                $responseHeads = explode("\r\n", $responseHead);
                $httpcode = explode(' ', $responseHeads[0]);
                $this->_http_code = $httpcode[1];
                if(strpos( substr($ret , 0 , $pos), 'Transfer-Encoding: chunked'))
                {
                    $response = explode("\r\n", $rt);
                    $t = array_slice($response, 1, - 1);

                    return implode('', $t);
                }
                return $rt;
            }
            return '';
        }
    }

    public static function get_image_mime( $file )
    {
        $ext = strtolower(pathinfo( $file , PATHINFO_EXTENSION ));
        switch( $ext )
        {
            case 'jpg':
            case 'jpeg':
                $mime = 'image/jpg';
                break;
            case 'png';
                $mime = 'image/png';
                break;
            case 'gif';
            default:
                $mime = 'image/gif';
                break;
        }
        return $mime;
    }

    /**
     * 返回上一次请求的httpCode
     * @return number
     */
    public function getHttpCode()
    {
        return $this->_http_code;
    }

    protected function fwrite($handle,$data)
    {
        fwrite($handle, $data);
        if($this->_debug)
        {
            echo $data;
        }
    }

    /**
     *
     * param to header
     *
     * @param string $realm
     * @param array $params
     * @return string
     * @ignore
     */
    private static function to_header($realm=null,$params) {
        $first = true;
        if($realm) {
          $out = 'Authorization: OAuth realm="' . OpenSDKUtil::urlencode_rfc3986($realm) . '"';
          $first = false;
        } else
          $out = 'Authorization: OAuth';

        $total = array();
        foreach ($params as $k => $v) {
          if (substr($k, 0, 5) != "oauth") continue;
          if (is_array($v)) {
            throw new OAuthException('Arrays not supported in headers');
          }
          $out .= ($first) ? ' ' : ',';
          $out .= OpenSDKUtil::urlencode_rfc3986($k) .
                  '="' .
                  OpenSDKUtil::urlencode_rfc3986($v) .
                  '"';
          $first = false;
        }
        return $out;
    }
    /**
     *
     * build_signature when sand data in header
     *
     * @param array $params
     * @param string $url
     * @param string $method
     * @param array $parameters
     * @return string
     * @ignore
     */
    private function build_signature_header ($params, $url, $method, $parameters=array() ){ 


        $nonce = $params['oauth_nonce'] ;
        $oauth_consumer_key = $params['oauth_consumer_key'] ;
        $oauth_signature_method = $params['oauth_signature_method'] ;
        $oauth_version = $params['oauth_version'] ;
        $time = $params['oauth_timestamp'] ;
        $oauth_token = $params['oauth_token'];

        $post = array( 
            'oauth_consumer_key' => $oauth_consumer_key, 
            'oauth_nonce' => $nonce, 
            'oauth_signature_method' => $oauth_signature_method, 
            'oauth_timestamp' => $time, 
            'oauth_token'=>$oauth_token, 
            'oauth_version' => $oauth_version,   
        ); 

        $signature = $this->sign( $url , $method, $post );

        $post = array( 
            'oauth_consumer_key' => $oauth_consumer_key, 
            'oauth_nonce' => $nonce, 
            'oauth_signature' => $signature,    
            'oauth_signature_method' => $oauth_signature_method, 
            'oauth_timestamp' => $time, 
            'oauth_token'=>$oauth_token, 
            'oauth_version'=>$oauth_version, 
        ); 
       
        $header_string = ''; 
        foreach($post as $key => $value) 
        { 
            $header_string .=$key.'="'.OpenSDKUtil::urlencode_rfc3986($value).'",'; 
        } 

        $header_string = trim($header_string); 
        $header_string = rtrim($header_string, ','); 
        if(isset($params['realm'])){
            $out = 'Authorization: OAuth realm="' . OpenSDKUtil::urlencode_rfc3986($params['realm']) . '",';
        }else{
            $out = 'Authorization: OAuth ';
        }
        $header_string = $out.$header_string; 


        return $header_string; 
    }

}