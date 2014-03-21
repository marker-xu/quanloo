<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 * widget定制
 * @author xucongbin
 *
 */
class Controller_Widget extends Controller {
	
	private $objModelWidgetAdmin;
	private $objModelUserWebsite;
	private $action;
	
	public function before() {
		parent::before();
		$action = $this->request->action();
		if($action!="show" && $action!="default") {
			$this->_needLogin();
		}
		$this->action = $action;
		$this->objModelWidgetAdmin = new Model_Data_Widgetadmin();
		$this->objModelUserWebsite = new Model_Data_Userwebsite();
	}
	public function action_default() {
		if($this->_uid) {
			$this->request->redirect("widget/index");
		}
		$this->template->set("current_action", $this->action);
	}
	/**
	 * 
	 * widget内容
	 */
	public function action_index() {
		if(!( $arrWebsite = $this->objModelUserWebsite->getWebsiteByUserId($this->_uid)) ) {
			$this->request->redirect("/widget/completecompanyinfo");
			return;
		}
		$query = array(
			"user_id" => $this->_uid
		);
		$arrList = $this->objModelWidgetAdmin->find($query, array(), array("create_time"=>-1));
		if ( $arrList ) {
			foreach($arrList as $k=>$row) {
				$arrList[$k]["_id"] = $row["_id"]->{'$id'};
			}
			$this->completeIframeHtml($arrList, $arrWebsite);
		}
		$this->template->set("widget_list", $arrList);
		$this->template->set("current_action", $this->action);
	}
	
	public function action_show() {
		$arrQuery = $this->request->query();
		$strHttpRefer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
		#TODO 没有refer,拜拜
		if(! $strHttpRefer) {
			
		}
		Jkit::$log->debug("widget_show, param", $arrQuery);
		$strSignature = "";
		if( isset( $arrQuery['signature'] ) ) {
			$strSignature = $arrQuery['signature'];
			unset($arrQuery['signature']);
		}
		if( !$strSignature || !isset($arrQuery['domain_id']) || !isset($arrQuery['cid_list']) ) {
			$this->err(null, null, null, null, "sys.param.empty");
		}
		$strDomainId = trim( $arrQuery['domain_id'] );
		
		$arrWebsite = $this->objModelUserWebsite->getWebsiteById($strDomainId);
		if( !$arrWebsite ) {
			$this->err(null, null, null, null, "sys.param.website");
		}
		#TODO 验证refer的host
		$strHost = $this->parseDomain($strHttpRefer);
		if( $this->notMatchDomain($arrWebsite["domain"], $strHost) ) {
			$this->err(null, null, null, null, "sys.param.refer_not_match");
		}
		
		$strSecrect = $arrWebsite['secrect'];
		$strRealSignature = $this->buildSign($arrQuery, $strSecrect);
		if($strRealSignature!=$strSignature) {
			$this->err(null, null, null, null, "sys.signature.not_match");
		}
		$this->initWidgetParam( $arrQuery );
		$arrQuery['wid'] = trim( $arrQuery['wid'] );
		$arrVidList = $this->filterParams($arrQuery["vid_list"]);
		$arrCidList = $this->filterParams( $arrQuery["cid_list"] );
		
		$arrVideos = $this->getVideos($arrCidList, $arrVidList, $arrQuery["video_count"]);
		$this->template->set("widget_info", $arrQuery );
		$this->template->set("video_list", $arrVideos);
		$this->template->set("video_id_list", Arr::pluck($arrVideos, "_id"));
		$this->template->set("margin_left", $this->calculateMarginLeft($arrQuery["skin_type"], 
			$arrQuery["width"], $arrQuery["video_count"], $arrQuery["pic_width"]));
		$this->template->set("margin_top", $this->calculateMarginTop($arrQuery["skin_type"], 
			$arrQuery["height"], $arrQuery["video_count"], $arrQuery["pic_height"], $arrQuery["is_more"]) );
		$this->template->set("current_action", $this->action);
		
		$this->template->set("more_url", $arrCidList ? Util::circleUrl(array_shift($arrCidList)): 'http://'.DOMAIN_SITE);
	}
	
	public function action_addwidget() {
		if(!$this->objModelUserWebsite->getWebsiteByUserId($this->_uid)) {
			$this->request->redirect("/widget/completecompanyinfo");
			return;
		}
		if ($this->request->method()!=Request::POST) {
			$this->template->set("current_action", $this->action);
			$this->template->set("widget_info", array());
			return;
		}
		$arrPost = $this->request->post();
		$uid = $this->_uid;
		$type = 1;
		$arrRules = $this->_formRule();
		$objValidation = new Validation($arrPost, $arrRules);
		if (! $this->valid($objValidation)) {
		    return;
		}
		$this->initWidgetParam($arrPost);
		if( !$arrPost["cid_list"] ) {
			$this->err(null, array('cid_list'=>"圈子不能为空"), null, null, "usr.submit.valid");
		}
		#TODO 写入表，生成widget
		$arrData = array(
			"height" => $arrPost["height"],
			"width" => $arrPost["width"],
			"video_count" => $arrPost["video_count"],
//			"skin_type" => $arrPost["skin_type"],
			"cid_list" => $this->filterParams($arrPost["cid_list"]),
			"vid_list" => $this->filterParams($arrPost["vid_list"]),
			"bgcolor" => $arrPost["bgcolor"],
			"is_more" => $arrPost["is_more"],
			"css_url" => $arrPost["css_url"],
			"pic_width" => $arrPost["pic_width"],
			"pic_height" => $arrPost["pic_height"]
		);
		if(! $arrPost['wid'] ) {
			$res = $this->objModelWidgetAdmin->addWidget($uid, $type, $arrPost["name"], $arrData);
		} else {
			$arrParams = array(
				"data" => $arrData,
				"name" => $arrPost["name"]
			);
			$res = $this->objModelWidgetAdmin->modifyById($arrPost['wid'], $arrParams);
		}
		
		if (!$res) {
			$this->err();
		}
		
		$this->ok();
	}
	
	public function action_modifywidget() {
		if(!$this->objModelUserWebsite->getWebsiteByUserId($this->_uid)) {
			$this->request->redirect("/widget/completecompanyinfo");
			return;
		}
		$strWidgetId = $this->request->param("wid");
		if(!$strWidgetId || !($arrWidgetInfo = $this->objModelWidgetAdmin->getById($strWidgetId)) 
		|| $arrWidgetInfo["user_id"]!=$this->_uid ) {
			$this->request->redirect("/widget/index");
			return;
		}
		if ($this->request->method()!=Request::POST) {
			$arrWidgetInfo = array_merge($arrWidgetInfo, $arrWidgetInfo['data']);
			unset($arrWidgetInfo['data']);
			$this->template()->set_filename("widget/addwidget");
			$this->template->set("widget_info", $arrWidgetInfo);
			$this->template->set("current_action", $this->action);
			return;
		}
		#TODO post数据验证
		$arrRules = $this->_formRule();
		$arrPost = $this->request->post();
		
		$objValidation = new Validation($arrPost, $arrRules);
		if ( !$this->valid($objValidation) ) {
		    return;
		}
		$this->initWidgetParam( $arrPost );
		if( !$arrPost["cid_list"] ) {
			$this->err(null, array('cid_list'=>"圈子不能为空"), null, null, "usr.submit.valid");
		}
		#TODO 写入表，生成widget
		$arrData = array(
			"height" => $arrPost["height"],
			"width" => $arrPost["width"],
			"video_count" => $arrPost["video_count"],
//			"skin_type" => $arrPost["skin_type"],
			"cid_list" => $this->filterParams($arrPost["cid_list"]),
			"vid_list" => $this->filterParams($arrPost["vid_list"]),
			"bgcolor" => $arrPost["bgcolor"],
			"is_more" => $arrPost["is_more"],
			"css_url" => $arrPost["css_url"],
			"pic_width" => $arrPost["pic_width"],
			"pic_height" => $arrPost["pic_height"]
		);
		
		$arrParams = array(
			"data" => $arrData,
			"name" => $arrPost["name"]
		);
		if( isset($arrPost['type'])) {
			$arrParams['type'] = intval( $arrPost['type'] );
		}
		$res = $this->objModelWidgetAdmin->modifyById($strWidgetId, $arrParams);
		if (!$res) {
			$this->err(null, "修改失败");
		}
		
		$this->ok();
	}
	
	
	public function action_completecompanyinfo() {
		$isAdd = true;
		$uid = $this->_uid;
		if(( $arrWebsite = $this->objModelUserWebsite->getWebsiteByUserId($uid) ) ) {
			$isAdd = false;
		}
		if ($this->request->method()!=Request::POST) {
			
			$this->template->set("website_info", $arrWebsite);
			$this->template->set("domain_type_list", Model_Data_Userwebsite::getDomainTypes());
			$this->template->set("current_action", $this->action);
			return;
		}
		#TODO 编辑商户信息
		
		$arrPost = $this->request->post();
		$arrRules = array(
            '@domain' => array(
                    'datatype' => 'reg',
                    'reqmsg' => '域名',
                    'reg-pattern' => "/([a-z0-9\-]+\.)+[a-z0-9\-]+(\:[0-9]+)?/i",
            ),
            '@site_name' => array(
                    'datatype' => 'text',
                    'reqmsg' => '网站名称',
            		'minlength' => 2,
            		'maxlength' => 18,
            ),
            '@desc' => array(
                    'datatype' => 'text',
//                    'reqmsg' => '简介',
                    'maxlength' => 140
            ),
            '@email' => array(
                    'reqmsg' => '邮箱',
                   	'datatype' => 'email',
                   	'errmsg' => '邮箱格式错误'
            ),
            '@phone' => array(
                    'reqmsg' => '电话号码',
                   	'datatype' => 'reg',
            		'reg-pattern' => "/(^([0-9]{2,4}\-)?[1-9][0-9]{6,7}$)|(^(13|15|18|14)\d{9}$)/i",
                   	'errmsg' => '电话号码'
            ),
		);
		$objValidation = new Validation($arrPost, $arrRules);
		if (! $this->valid($objValidation)) {
		    return;
		}
		$arrType = isset( $arrPost['type_list'] ) ? $arrPost['type_list'] : array();
		if(!$arrType) {
			$this->err(null, array('type_list'=>"亲，请选一个网站类别"), null, null, "usr.submit.valid");
		}
		$strDomain = $this->parseDomain( trim( $arrPost['domain'] ) );
		$name = trim( $arrPost['site_name'] );
		
		$arrExtra = array(
			"type" => $this->filterParams($arrType),
	 		"desc" => trim( $arrPost['desc']),
	 		"icp" => trim( $arrPost['icp'] ),
			"phone" => trim( $arrPost['phone'] ),
			"email" => trim( $arrPost['email'] ),
			"secrect" => $this->_user["password"]
		);
		if($isAdd) {
			$res = $this->objModelUserWebsite->addWebsite($uid, $strDomain, $name, $arrExtra);
		} else {
			$arrParams = array(
				"domain" => $strDomain,
				"name" => $name
			);
			$arrParams = array_merge($arrParams, $arrExtra);
			$res = $this->objModelUserWebsite->modifyByUid($uid, $arrParams);
		}
		if( !$res ) {
			$this->err(null, "更新失败");
		}
		$this->ok();
	}
	
	public function action_getjscode() {
		$arrPost = $this->request->post();
		$wid = $arrPost['wid'];
		$uid = $this->_uid;
		$type = 1;
		$arrRules = $this->_formRule();
		$objValidation = new Validation($arrPost, $arrRules);
		if (! $this->valid($objValidation)) {
		    return;
		}
		$this->initWidgetParam($arrPost);
		if( !$arrPost["cid_list"] ) {
			$this->err(null, array('cid_list'=>"圈子不能为空"), null, null, "usr.submit.valid");
		}
		#TODO 写入表，生成widget
		$arrData = array(
			"height" => $arrPost["height"],
			"width" => $arrPost["width"],
			"video_count" => $arrPost["video_count"],
//			"skin_type" => $arrPost["skin_type"],
			"cid_list" => $this->filterParams($arrPost["cid_list"]),
			"vid_list" => $this->filterParams($arrPost["vid_list"]),
			"bgcolor" => $arrPost["bgcolor"],
			"is_more" => $arrPost["is_more"],
			"css_url" => $arrPost["css_url"],
			"pic_width" => $arrPost["pic_width"],
			"pic_height" => $arrPost["pic_height"]
		);
		$arrUserDomain = $this->objModelUserWebsite->getWebsiteByUserId($uid);
		if( !$wid ) {
			//创建
			$strwidgetId = $this->objModelWidgetAdmin->addWidget($uid, $type, $arrPost["name"], $arrData);
			if (!$strwidgetId) {
				$this->err(null, "保存失败");
			}
			$wid = $strwidgetId;
		}
		#TODO 获取签名
		$arrData['domain_id'] = $arrUserDomain["_id"];
		$arrData['type'] = $type;
		$arrData['name'] = $arrPost["name"];
		$arrData['wid'] = $wid;
		
		$strSign = $this->buildSign($arrData, $arrUserDomain["secrect"]);
		
		#TODO 生成code
		$arrData['signature'] = $strSign;
		$strDestUrl = "http://".
		DOMAIN_API."/widget/show?".http_build_query($arrData, null, "&");	
		$html = $this->buildIframeCode($strDestUrl, $arrData['height'], $arrData['width']);
		$this->ok(array(
			"url" => $strDestUrl,
			"html" => $html,
			"wid" => $wid
		));
	}
	
	private function buildSign($arrParams, $strSecrect) {
		$arrParams['signature_method']	= 'md5';
		foreach($arrParams as $k=>$val) {
			if(is_string($val)) {
				$arrParams[$k] = trim($val);
			} elseif(is_array($val)) {
				$arrParams[$k] = array_map('trim', $val);
			}
		}
		ksort($arrParams);
		$strQuery = http_build_query($arrParams, null, "&");
		return md5($strQuery.$strSecrect);
	}
	/**
	 * 
	 * 验证域名
	 * 
	 */
	public function action_testdomain() {
		$strDomain = $this->request->param("domain");
		if(!$strDomain) {
			$this->err();
		}
		if(!strstr(strtolower($strDomain), "http://")) {
			$strDomain = "http://".$strDomain;
		}
		$arrPath = parse_url($strDomain);
		if( !isset($arrPath['host']) ) {
			$this->err(NULL, "格式错误");
		}
		$strHost = trim( $arrPath['host'] );
		$intPort = isset( $arrPath['port'] ) ? $arrPath['port']:80;
		$fp = @fsockopen($strHost, $intPort, $errno, $errstr, 3);
		if (!$fp) {
			Jkit::$log->debug("ping ({$strHost}:{$intPort}), {$errstr} ({$errno})");
		    $this->err(NULL, "网站不存在");
		} else {
		    $out = "GET / HTTP/1.1\r\n";
		    $out .= "Host: {$strHost}\r\n";
		    $out .= "Connection: Close\r\n\r\n";
		    stream_set_timeout($fp, 2);
		    fwrite($fp, $out);
		    $result = fgets($fp, 128);
		    $info = stream_get_meta_data($fp);
		    fclose($fp);
		}
		if( isset( $info['time_out'] ) && $info['time_out'] ) {
			$this->err(NULL, "网站不存在");
		} 
		$this->ok();
	}
	
	public function action_removewidget() {
		$strWidgetId = $this->request->param("wid");
		if(!$strWidgetId || !($arrWidgetInfo = $this->objModelWidgetAdmin->getById($strWidgetId)) 
		|| $arrWidgetInfo["user_id"]!=$this->_uid ) {
			$this->err(null, "widget not exists");
		}
		
		$res = $this->objModelWidgetAdmin->removeWidget($strWidgetId);
		if(!$res) {
			$this->err(null, "删除失败");
		}
		$this->ok();
	}
	
	protected function _formRule( $arrField = null ) {
	    $arrRules = array(
            '@name' => array(
                    'datatype' => 'text',
                    'reqmsg' => '名称',
	    			'minlength' => 2,
            		'maxlength' => 30,
            ),
            '@height' => array(
                    'datatype' => 'n',
                    'reqmsg' => '高度',
                    'minvalue' => 0,
            ),
            '@width' => array(
                    'datatype' => 'n',
                    'reqmsg' => '宽度',
                    'minvalue' => 0,
            ),
            '@video_count' => array(
                    'datatype' => 'n',
                    'reqmsg' => '视频数量',
                    'minvalue' => 1,
            		'maxvalue' => 10,
            ),
            '@skin_type' => array(
                    'datatype' => 'n',
//                    'reqmsg' => '皮肤类型',
                    'minvalue' => 0,
            ),
            '@css_url' => array(
                    'datatype' => 'reg',
//                    'reqmsg' => '皮肤类型',
                    'reg-pattern' => '/^https?\:\/\/([a-z0-9\-]+\.)+[a-z0-9\-]+(\:[0-9]+)?/i',
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
	
	private function filterParams($arrParams) {
		$arrReturn = array();
		if(!$arrParams) {
			return $arrParams;
		}
		foreach($arrParams as $tmp) {
			$tmp = trim($tmp);
			if($tmp!=="") {
				$arrReturn[] = $tmp;
			}
		}
		return array_unique($arrReturn);
	}
	
	private function parseCidList($strCidList) {
		$arrReturn = array();
		$reg = "/\/circle\/([0-9]+)\??/i";
		preg_match_all($reg, $strCidList, $matches);
		if( isset($matches[1]) ) {
			$arrReturn = $matches[1];
		}
		return $arrReturn;
	}
	
	private function parseVidList() {
		$arrReturn = array();
		$reg = "/\/v\/([0-9a-z]+)\.+/i";
		preg_match_all($reg, $strCidList, $matches);
		if( isset($matches[1]) ) {
			$arrReturn = $matches[1];
		}
		return $arrReturn;
	}
	
	private function parseDomain($strUrl) {
		$strReturn = "";
		if(!strstr(strtolower($strUrl), "http://")) {
			$strUrl = "http://".$strUrl;
		}
		$arrPath = parse_url($strUrl);
		if( !isset($arrPath['host']) ) {
			return $strReturn;
		}
		$strReturn = $arrPath['host'];
		if( isset( $arrPath['port'] ) && $arrPath['port']!=80) {
			$strReturn.=":".$arrPath['port'];
		}
		
		return $strReturn;
	}
	
	/**
	 * 
	 * 获取指定数量的视频列表
	 * @param array $arrCids
	 * @param array $arrVids
	 * @param int $intVideoCount
	 */
	private function getVideos($arrCids, $arrVids, $intVideoCount) {
		
		$arrReturn = array();
		if($intVideoCount<=0) {
			$intVideoCount = 4;
		}
		$objLogicVideo = new Model_Logic_Video();
		$objLogicRecommend = new Model_Logic_Recommend();
		$arrVideos = array();
		if( $arrVids ) {
			$arrVideos = $objLogicVideo->getMulti($arrVids, true, null, true);
			if( !$arrVideos ) {
				$arrVideos = array();
			}
		}
		$intDiff = $intVideoCount-count($arrVideos);
		if( $intDiff <= 0 ) {
			return array_slice($arrVideos, 0, $intVideoCount);
		}
		
		foreach($arrCids as $intCid) {
			try {
				$intLength = $intVideoCount*6+3;
				if($intLength>100) {
					$intLength = 100;
				}
				$arrRet = $objLogicRecommend->getCircleVideosByTag($intCid, null, '', 0, $intLength );
				$arrCircleVideos = isset($arrRet['video']) && $arrRet['video'] ? $arrRet['video'] : array();
				if( !$arrCircleVideos ) {
					continue;
				}
				shuffle($arrCircleVideos);
				foreach($arrCircleVideos as $row) {
					if( !isset($arrVideos[$row["_id"]]) ) {
						$arrVideos[$row["_id"]] = $row;
					}
				}
				break;
			} catch (Exception $e) {
				continue;
			}
		}
		
		return array_slice($arrVideos, 0, $intVideoCount);
	}
	
	private function completeIframeHtml( &$arrData, $arrWebsite ) {
		if(!$arrData) {
			return;
		}
		
		foreach($arrData as $k=>$row) {
			$arrParams = $row["data"];
			$arrParams["type"] = $row["type"];
			$arrParams["name"] = $row["name"];
			$arrParams['domain_id'] = $arrWebsite["_id"];
			$arrParams['wid'] = $row["_id"];
			$strSign = $this->buildSign($arrParams, $arrWebsite["secrect"]);
		
			#TODO 生成code
			$arrParams['signature'] = $strSign;
			$strDestUrl = "http://".DOMAIN_API."/widget/show?".http_build_query($arrParams, null, "&");
			$row['iframe'] = $this->buildIframeCode($strDestUrl, $arrParams['height'], $arrParams['width']);
			$arrData[$k] = $row;
		}
	}
	
	private function notMatchDomain($strNeedHost, $strReferHost) {
		$regPattern = "/([^\.]+\.)+".preg_quote($strNeedHost)."/i";
		return $strReferHost && ($strReferHost!=DOMAIN_SITE) 
		&& !($strReferHost==$strNeedHost || preg_match($regPattern, $strReferHost));
	}
	
	/**
	 * 
	 * 登录判断，跳转,ajax返回，错误
	 */
	protected function _needLogin() {
		if ( !$this->_uid || !$this->_user) {
			//deny not logined user
			if($this->request->is_ajax()) {
				$this->err(null, '请先登录！', null, null, self::ERROR_NEED_LOGIN);
			} else {
				$this->request->redirect('widget/default');
			}
			exit();
		}
	}
	
	private function calculateMarginLeft($intSkinType, $intWidth, $intVideoCount, $intBaseWidth=null) {
		$arrReturn = array(
			'left'=>0,
			'right' => 0
		);
		$intReturn = 0;
		if(!$intBaseWidth) {
			$intBaseWidth = 150;
		}
		$intLineCount = floor($intWidth/$intBaseWidth);
		if($intLineCount>$intVideoCount) {
			$intLineCount = $intVideoCount;
		}
		if($intLineCount) {
			$intReturn = $intWidth-$intBaseWidth*$intLineCount;
			$arrReturn['right'] = floor($intReturn/($intLineCount+1));
//			if( $arrReturn['right']>10 ) {
//				$arrReturn['right'] = 10;
//			}
			$arrReturn['left'] = round( ($intReturn-$intLineCount*2*$arrReturn['right'])/2 );
		} 
		return $arrReturn;
	}
	
	private function calculateMarginTop($intSkinType, $intHeight, $intVideoCount, $intBaseHeight=null, $isMore=false) {
		$arrReturn = array(
			'top'=>0,
			'bottom' => 0
		);
		$intReturn = 0;
		if(!$intBaseHeight) {
			$intBaseHeight = 167;
		} else {
			$intBaseHeight+=54;
		}
		
		if($isMore) {
			$intHeight-= 26;
		}
		$intLineCount = floor($intHeight/$intBaseHeight);
		if($intLineCount>$intVideoCount) {
			$intLineCount = $intVideoCount;
		}
		if( $intLineCount ) {
			$intReturn = $intHeight-$intBaseHeight*$intLineCount;
			$arrReturn['bottom'] = floor($intReturn/2);
			
			$arrReturn['top'] = round( ($intReturn-$intLineCount*2*$arrReturn['bottom'])/2 );
		}
		return $arrReturn;
	}
	
	private function initWidgetParam( &$arrParams ) {
		
		$arrParams['name'] = trim($arrParams['name']);
		$arrParams["height"] = (int)$arrParams["height"];
		$arrParams["width"] = (int)$arrParams["width"];
		$arrParams["video_count"] = (int)$arrParams["video_count"];
		$arrParams["skin_type"] = isset($arrParams["skin_type"]) ? (int)$arrParams["skin_type"] : 0;
		$arrParams["pic_height"] = (int)$arrParams["pic_height"];
		$arrParams["pic_width"] = (int)$arrParams["pic_width"];
		$arrParams["bgcolor"] = trim( $arrParams["bgcolor"] );
		$arrParams["cid_list"] = isset( $arrParams["cid_list"] ) ? $arrParams["cid_list"]:array();
		$arrParams["vid_list"] = isset( $arrParams["vid_list"] ) ? $arrParams["vid_list"]:array();
		$arrParams["css_url"] = isset( $arrParams["css_url"] ) ? $arrParams["css_url"]:'';
		$arrParams["is_more"] = isset( $arrParams["is_more"] ) ? intval( $arrParams["is_more"]) : 0;
	}
	
	private function buildIframeCode($strDestUrl, $intHeight, $intWidth) {
		return '<iframe width='.$intWidth.' height='.$intHeight.
			' style="border:1px solid black;" scrolling=no src="'.$strDestUrl.'"></iframe>';
	}
}