<?php 

/**
 * 用户
 * @author xucongbin
 */
class Model_Data_Sndauser extends Model
{
	CONST APP_ID = 317;
	
	CONST APP_AREA = 0;
	
	CONST MOBILE_SECRET_KEY = "\"57)H[{gl8oX#UK;";
	
	/**
	 * 
	 * 返回sdid
	 * @param string $ticket
	 * @param string $redirectURL
	 * @throws Model_Data_Exception
	 * 
	 * @return array
	 */
	public static function getTicketInfo($ticket, $redirectURL=NULL, $bolComplete=false) {
		if($redirectURL===NULL) {
			$redirectURL = "http://".DOMAIN_SITE."{$_SERVER['REQUEST_URI']}";
		}
		if($bolComplete) {
			$authenURL = 'https://cas.sdo.com/cas/Validate.release?service=' . urlencode($redirectURL) .
		"&ticket={$ticket}" . "&appId=".self::APP_ID."&appArea=".self::APP_AREA."&version=2.1";;
		} else {
			$authenURL = 'https://cas.sdo.com/cas/Validate.Ex?service=' . urlencode($redirectURL) .
		"&ticket={$ticket}" . "&appId=".self::APP_ID."&appArea=".self::APP_AREA;;
		}
		
        $objResponse = Request::factory($authenURL)->execute();
        if($objResponse->status()!=200) {
        	throw new Model_Data_Exception("cas validate service failure, code-".$objResponse->status(), -2001);
        }
        $content = $objResponse->body();
        JKit::$log->debug("getTicketInfo, ticket-".$ticket.", content-".$content);
        $content = iconv('gb2312', 'utf-8', $content);
        if($bolComplete) {
        	$userInfo = simplexml_load_string($content);
        	return (array)$userInfo;
        }
        $userInfo = explode("\n", $content);
        if (isset($userInfo[0]) && $userInfo[0] === 'yes') {
            return array('sdid' => $userInfo[1]);
        }  else {
        	JKit::$log->warn("getTicketInfo, ticket-".$ticket.", content-".$content.", url-".$authenURL);
        }
		return false;
	}
	/**
	 * 
	 * 移动端获取用户信息
	 */
	public static function getMobileTicketInfo( $sessionId ) {
		$appId = 20011;
		$areaId = -1;
		$merchantName = "260_20011_312";
		$arrParams = array(
			"sessionId" => $sessionId,
			"appId" => $appId,
			"appArea" => $areaId,
			"timestamp" => $_SERVER['REQUEST_TIME'],
			"merchant_name" => $merchantName
		);
		$arrParams = self::buildSign($arrParams, self::MOBILE_SECRET_KEY);
        $url = "http://woa.sdo.com/woa/autologin/validate.shtm?" .http_build_query($arrParams, NULL, "&");
        $objResponse = Request::factory($url)->execute();
        if($objResponse->status()!=200) {
        	throw new Model_Data_Exception("validate service failure, code-".$objResponse->status(), -2001);
        }
        $content = $objResponse->body();
        JKit::$log->debug(__FUNCTION__.", content-".$content);
        $arrResult = json_decode($content, true);
        if (isset($arrResult['resultCode']) && $arrResult['resultCode'] == 0) {
            return array('sdid' => intval( $arrResult['sndaId'] ), 'account'=>$arrResult['inputAccount'], 
            "token"=>$arrResult['token'], "resultCode"=>$arrResult['resultCode']);
        } else {
        	JKit::$log->warn(__FUNCTION__.", content-".$content.", params-", $arrParams);
        }
		return false;
	}
	/**
	 * 
	 * 根据数字账号查询其他类型的账号
	 * 
	 * @param int $sdid 盛大帐号数字ID
	 * @param array $arrAccountType array( 
	 * 	PT => 个性化帐号
	 * 	Phone => 手机账号
	 * 	Email => 邮箱账号
	 * 	SndaMail => sdo邮箱 
	 * 	SndaMailAlias => kiki邮箱
	 * 	sndanick => 盛大昵称
	 * 	Mars => 火星账号
	 * 	ExAccount => 外部账号
	 * 	AbroadPhone => 海外手机类型
	 * 	SdMail => 起点邮箱
	 * 	SdMailAlias => 起点邮箱别名
	 * )
	 */
	public static function accountQueryUserInfo($sdid, $arrAccountType=array("PT", "Phone", "Email")) {
		$osapUser = "3_317_289";
		$secretKey = "DY.:uP_vj5S/H_N";
		$arrTypeMaps = array(
			"PT" => 1,
			"Phone" => 3,
			"Email" => 4,
			"SndaMail" => 5,
			"SndaMailAlias" => 6,
			"sndanick" => 7,
			"Mars" => 8,
			"ExAccount" => 9,
			"AbroadPhone" => 10,
			"SdMail" => 11,
			"SdMailAlias" => 12,
		);
		$strAccountTypes = "";
		foreach($arrAccountType as $tmpType) {
			if( isset($arrTypeMaps[$tmpType]) ) {
				$strAccountTypes.="{$arrTypeMaps[$tmpType]},";
			}
		}
		$strAccountTypes = substr($strAccountTypes, 0, -1);
		$arrParams = array(
			'sdid' => (int)$sdid,
			'key' => $strAccountTypes,
			'osap_user' => $osapUser,
			'timestamp' => $_SERVER['REQUEST_TIME']
		);
		$arrParams = self::buildSign($arrParams, $secretKey);
        $url = "http://hps.sdo.com/apl_account/account.queryUserInfo?" .http_build_query($arrParams, NULL, "&");
        $objResponse = Request::factory($url)->execute();
        if($objResponse->status()!=200) {
        	throw new Model_Data_Exception(__FUNCTION__." service failure, code-".$objResponse->status(), -2001);
        }
        $content = $objResponse->body();
        JKit::$log->debug(__FUNCTION__.", content-".$content);
        $arrResult = json_decode($content, true);
//        echo $content."<br>\n";
//        print_r($arrResult);
        if (isset($arrResult['return_code']) && $arrResult['return_code'] == 0) {
        	$arrKeyValue = $arrResult['data']['keyValue'];
        	$arrReturn = array();
        	$arrTypeMapsFlip = array_flip($arrTypeMaps);
        	foreach($arrKeyValue as $k=>$val) {
        		$tmpKeys = array_keys($val);
        		$arrReturn[$arrTypeMapsFlip[array_shift($tmpKeys)]] = array_shift($val);
        	}
            return $arrReturn;
        } else {
        	JKit::$log->warn(__FUNCTION__.", content-".$content.", params-", $arrParams);
        }
		return false;
	}
	
	/**
     * 帐号转换
     * $ret[0] == 0,正常， $ret[1] = PTID,$ret[2] = SDID
     * $ret[1] != 0, $ret[1] = error
     * 
     * @param string $id | sdid or ptid
     * @return array
     */
    public static function convertId ($id)
    {
        $verifyURL = "http://ptcom.sdo.com/common/ConvertId.aspx?uuid={$id}";
        $message = '';
    	$objResponse = Request::factory($verifyURL)->execute();
        if($objResponse->status()=='200') {
        	throw new Model_Data_Exception("ptcom service failure, code-".$objResponse->status(), -3001);
        }
        $content = $objResponse->body();
        if ( !$content ) {
            # 出错
            return FALSE;
        } else {
            # 正常
            $info = explode('^$^', $content);
            return $info;
        }
    }
    
    
    public static function buildLoginUrl($redirectUrl=NULL, $isIframe=false, $arrExtra=array()) {
    	if($redirectUrl===NULL) {
			$redirectUrl = "http://".DOMAIN_SITE."{$_SERVER['REQUEST_URI']}";
		}
		
		$loginUrl = "http://cas.sdo.com/cas/login?service=".urlencode($redirectUrl);
		
		if($isIframe) {
			#TODO iframe格式的
			$loginUrl = "https://login.sdo.com/sdo/Login/LoginFrameFC.php?returnURL=".urlencode($redirectUrl).
				"&appId=".self::APP_ID."&areaId=".self::APP_AREA;
		} else {
			$loginUrl.="&appId=".self::APP_ID."&appArea=".self::APP_AREA;
		}
		if(!isset($arrExtra['target'])) {
			$arrExtra['target'] = "top";
		}
		
		if($arrExtra) {
			foreach($arrExtra as $k=>$val) {
				$loginUrl.="&{$k}=".urlencode($val);
			}
		}
//		if($this->autoLogin) {
//			$loginUrl.="autologinchecked=".intval($this->autoLogin);
//		}
		return $loginUrl;
    }
    
    
    public static function buildLogoutUrl($redirectUrl=NULL) {
    	if($redirectUrl===NULL) {
			$redirectUrl = "http://".DOMAIN_SITE."/";
		}
    	$logoutUrl = "http://cas.sdo.com/cas/logout?url=".urlencode($redirectUrl);
    	return $logoutUrl;
    }
    
    public static function buildRegisterUrl($redirectUrl=NULL, $isIframe=false, $arrExtra=array()) {
    	if($redirectUrl===NULL) {
			$redirectUrl = "http://".DOMAIN_SITE."{$_SERVER['REQUEST_URI']}";
		}
		$registerUrl = "http://register.sdo.com/register.asp?from=".self::APP_ID."&CUSTOM_REG_SUCC_URL=".urlencode($redirectUrl);
		$arrExtra['zone'] = "web";
		if($isIframe) {
			$arrExtra['zone'] = "home_embed";
		}
		foreach($arrExtra as $field=>$val) {
			$registerUrl.="&{$field}=".urlencode($val);
		}
    	return $registerUrl;
    }
    /**
     * 
     * 没法用
     * @throws Model_Data_Exception
     */
    public static function checkStat() {
    	$url = 'http://cas.sdo.com/cas/loginStateService?method=checkstat';
    	$objResponse = Request::factory($url)->execute();
        if($objResponse->status()!=200) {
        	throw new Model_Data_Exception("cas checkstat service failure, code-".$objResponse->status(), -2001);
        }
        $content = $objResponse->body();
        JKit::$log->debug("checkstat, content-".$content);
        $content = iconv('gb2312', 'utf-8', $content);
		return $content;
    }
    
	/**
     * 
     * 补充签名信息
     * @param array $arrParams
     */
    private static function buildSign($arrParams, $sign) {
    	$arrReturn = $arrParams;
    	$arrReturn["signature_method"] = "md5";
    	ksort($arrReturn);
    	$strQuery = "";
    	foreach ($arrReturn as $k=>$val) {
    		$strQuery.="{$k}={$val}";
    	}
    	$arrReturn['signature'] = md5( $strQuery.$sign );
    	
    	return $arrReturn;
    }
}
