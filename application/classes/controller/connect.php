<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 第三方登录
 * @author wangjiajun
 */
class Controller_Connect extends Controller 
{
	protected $_base 		= NULL;
	protected $_config 		= NULL;
	protected $_douban  	= NULL;
	
	private $objModelLogicConnect2;
	
	public function before()
	{
		parent::before();
		// base url
		$strHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : DOMAIN_SITE;
		$this->_base = "http://".$strHost."/connect";
		$this->objModelLogicConnect2 = new Model_Logic_Connect2();
		//$this->_base = "http://www.quanloo.com/connect";
	}
	
	public function action_index(){
		$strCallBackUrl = $this->_base.'/callback';
		
		$type = (int) $this->request->param('type');
		switch($type)
		{
			case Model_Data_UserConnect::TYPE_DOUBAN :
				$url = $this->objModelLogicConnect2->getDoubanRedirectUrl($strCallBackUrl);
				break;
			case Model_Data_UserConnect::TYPE_SINA :
				$url = $this->objModelLogicConnect2->getSinaRedirectUrl($strCallBackUrl);
				break;
			case Model_Data_UserConnect::TYPE_TQQ :
				$url = $this->objModelLogicConnect2->getTqqRedirectUrl($strCallBackUrl);
				break;
			case Model_Data_UserConnect::TYPE_RENREN :
				$url = $this->objModelLogicConnect2->getRenrenRedirectUrl($strCallBackUrl);
				break;
			case Model_Data_UserConnect::TYPE_QQ :
				$url = $this->objModelLogicConnect2->getQqRedirectUrl($strCallBackUrl);
				break;
			default:
				break;
		}
		//echo '<a href="'.$url.'">'.$url.'</a>';
		//exit($url);
		JKit::$log->debug(__FUNCTION__." redirecturl-", $url);
		$this->request->redirect($url);
	}
	
	public function action_callback() {
		
		$type = (int) $this->request->param('type');
		$code = $this->request->param('code','');
		$oauth_verifier = $this->request->param('oauth_verifier','');
		
		$redirect_uri = $this->_base.'/callback?type='.$type;
		$ret = array();
		switch($type)
		{
			case Model_Data_UserConnect::TYPE_DOUBAN :
				$ret = $this->objModelLogicConnect2->doubanCallback();
				break;
			case Model_Data_UserConnect::TYPE_SINA:
				$ret = $this->objModelLogicConnect2->sinaCallback($code,$redirect_uri);
				break;
			case Model_Data_UserConnect::TYPE_TQQ :
				$ret = $this->objModelLogicConnect2->tqqCallback($oauth_verifier);
				break;
			case Model_Data_UserConnect::TYPE_RENREN :
				$ret = $this->objModelLogicConnect2->renrenCallback($code,$redirect_uri);
				break;
			case Model_Data_UserConnect::TYPE_QQ :
				$ret = $this->objModelLogicConnect2->qqCallback($code,$redirect_uri);
				break;
			default:
				$this->err();
				break;
		}
		//oauth异常情况
		if(isset($ret['err'])){
			if ( !$this->_uid || !$this->_user) {
				//exit('login failure!');
				$this->reload();
			}else{
				//exit('bind failure!');
				$this->close();
			}
		}
		//正常情况
		$connectType = $type;
		$bindUser = $ret['bindUser'];
		$accessToken = $ret['token'];
		
		
		if ( !$this->_uid || !$this->_user) {
			//未登陆状态，执行登录操作
			$ret = $this->objModelLogicConnect2->login($connectType, $bindUser, $accessToken);
			if(!$ret){
				//盛大创建帐号失败
				//exit('login failure!');
				$this->reload();
			}
			if($ret['isfirstLogin']){
				//第一次登录，补全个人信息注册圈乐帐号
				Session::instance()->set('nickname',$bindUser['name']);
				$this->redirect("/user/completeinfo");
				//$this->request->redirect("user/completeinfo?f=".urlencode($strTargetUrl));
			}else{
				//非第一次登录，跳到登录后的个人页
				//$this->redirect(Util::userUrl($ret['uid']));
				$this->reload();
			}
		}else{
			//登陆状态，执行绑定操作
			$uid = $this->_uid;
			$bindRet = $this->objModelLogicConnect2->bind($uid, $connectType, $bindUser, $accessToken);
			JKit::$log->debug(__FUNCTION__." bind uid-{$uid},connect_type-{$connectType},connect_id-{$bindUser['id']},token-", $accessToken);
			
			if($bindRet){
				$this->close();
			}else{
				//提示绑定失败，重新绑定
				//exit('bind failure!');
				$this->close();
			}
			
		}
	}
	
	public function action_unbind(){
		$this->_needLogin;
		$uid = (int) $this->_uid;
		$type = (int) $this->request->param('type');
		
		$ret = $this->objModelLogicConnect2->unBind($type, $uid);
		
		//删除SESSION
		switch($type)
		{
			case Model_Data_UserConnect::TYPE_DOUBAN :
				Model_Douban::setParam(Model_Douban::OAUTH_TOKEN, null);
	    		Model_Douban::setParam(Model_Douban::ACCESS_TOKEN, null);
	    		Model_Douban::setParam(Model_Douban::OAUTH_TOKEN_SECRET, null);
				break;
			case Model_Data_UserConnect::TYPE_SINA :
				Model_Sina::setParam(Model_Sina::ACCESS_TOKEN, null);
    			Model_Sina::setParam(Model_Sina::REFRESH_TOKEN, null);
				break;
			case Model_Data_UserConnect::TYPE_TQQ :
				Model_Tqq::setParam(Model_Tqq::OAUTH_TOKEN, null);
	    		Model_Tqq::setParam(Model_Tqq::ACCESS_TOKEN, null);
	    		Model_Tqq::setParam(Model_Tqq::OAUTH_TOKEN_SECRET, null);
				break;
			case Model_Data_UserConnect::TYPE_RENREN :
				Model_Renren::setParam(Model_Renren::ACCESS_TOKEN, null);
    			Model_Renren::setParam(Model_Renren::REFRESH_TOKEN, null);
				break;
			case Model_Data_UserConnect::TYPE_QQ :
				Model_Qq::setParam(Model_Qq::ACCESS_TOKEN, null);
    			Model_Qq::setParam(Model_Qq::REFRESH_TOKEN, null);
				break;
			default:
				break;
		}
	    
		if($ret){
			$this->request->redirect("/user/syncconnect");
		}else{
			throw new HTTP_Exception_404(':file does not exist', array(':file' => $type));
		}
	}
	
	/**
	 * 展示分享弹窗
	 * */
	public function action_sharetips()
	{
		$this->_needLogin();
		$uid = $this->_uid;
		
		$vid =  $this->request->param('vid');
		$cid = (int) $this->request->param('cid');
		
		$connectInfo = $this->objModelLogicConnect2->getBindList($uid);
		$objTpl = self::template();
		if($cid)
		{
			$modelLogicCircle = new Model_Logic_Circle();
			$circleInfo = $modelLogicCircle->get($cid);
			$objTpl->set_filename("connect/circle_share");
		    $objTpl->circle_info = $circleInfo;
		    $objTpl->bindlist = $connectInfo;
			$content = $objTpl->render();
			$this->ok(array("html"=>$content));
		}
		
		//分享圈子
		if($vid)
		{
			$modelLogicVideo = new Model_Logic_Video();
		    $video = $modelLogicVideo->get($vid, array('type', 'category', 'title', 'tag',
		    	'quality', 'length', 'thumbnail', 'play_url', 'player_url', 'domain', 'status'));
		    
		    if (!$video || $video['status'] != Model_Data_Video::STATUS_VALID) {
		        $this->err($video);
		    }
	    	
			$objTpl->set_filename("connect/video_share");
		    $objTpl->video_info = $video;
		    $objTpl->bindlist = $connectInfo;
			$content = $objTpl->render();
			$this->ok(array("html"=>$content));
		}
		$this->err();
	
	}
	/**
	 * 提交分享
	 * */
	public function action_share()
	{
		$this->_needLogin();
		$uid = $this->_uid;
		
		$content = $this->request->param('content');
		$type = $this->request->param('type');
		$pic_url = $this->request->param('picurl');
		$feed_param = trim($this->request->param('feedparam', ''));
		
		$lenConent = 0;
		if(empty($content)){
			$lenConent = 0;
		}
		if(function_exists('mb_strlen')){
			$lenConent = mb_strlen($lenConent,'utf-8');
		}
		else {
			preg_match_all("/./u", $lenConent, $ar);
			$lenConent = count($ar[0]);
		}
		JKit::$log->debug('content-'.($lenConent > 0 ).' and '.( $lenConent < 140 ).'type-'.(count($type) > 0).'picurl-'.(strlen($pic_url) > 0));
		$valid = $lenConent > 0 && $lenConent < 140 && count($type) > 0 &&
				strlen($pic_url) > 0;
				
		if(!$valid)
		{
			$this->err(array(
				'err'=>'valid.faile',
				'msg'=>'not valid'
			));
		}
		$param = array(
			'content'=>$content,
			'pic_url'=>$pic_url,
		);
		$tqqshare = false;
		$sinaShare = false;
		foreach($type as $k=>$v)
		{
			$connect = $this->objModelLogicConnect2->getBindList($uid);
			JKit::$log->debug('connect-',$connect);
			
			if($v == Model_Data_UserConnect::TYPE_TQQ) {
				$arrAccessTokenTmp = $connect['tqq']['access_token'];
				$access_token = isset( $arrAccessTokenTmp['access_toekn'] ) ? $arrAccessTokenTmp['access_toekn'] : 
					$arrAccessTokenTmp['access_token'];
				$tqqshare = $this->objModelLogicConnect2->tqqShare($param, $access_token, $arrAccessTokenTmp['oauth_token_secret']);
			}
			if($v == Model_Data_UserConnect::TYPE_SINA) {
				$arrAccessTokenTmp = $connect['sina']['access_token'];
				$access_token = isset( $arrAccessTokenTmp['access_toekn'] ) ? $arrAccessTokenTmp['access_toekn'] : 
					$arrAccessTokenTmp['access_token'];
				$sinaShare = $this->objModelLogicConnect2->sinaShare($param, $access_token);
			}
		}
		if( !$tqqshare && !$sinaShare ) {
			$this->err(null, "分享失败，请稍后再试");
		}
		
		//生成站内feed
		if (! empty($feed_param)) {
			$arrFeedParam = null;
			parse_str($feed_param, $arrFeedParam);
			if (is_array($arrFeedParam) && isset($arrFeedParam['type'])) {
				$objFeed2 = new Model_Logic_Feed2();
				if ($arrFeedParam['type'] == 1) {
					//分享圈子
					$objFeed2->addFeedShareCircle($this->_uid, $arrFeedParam['cid']);					
				} elseif ($arrFeedParam['type'] == 2) {
					//分享视频
					$objFeed2->addFeedShareVideo($this->_uid, $arrFeedParam['vid'], @$arrFeedParam['cid']);
				}
			}
		}
		
		$this->ok();

	}
	
	public function action_checkbind()
	{
		$this->_needLogin();
		$uid = (int) $this->_uid;
		$type = (int)$this->request->param('type');
		$objDataConnect = new Model_Data_UserConnect();
		$retConnect = $objDataConnect->getConnectByUid($type,$uid);
		if(!$retConnect){
			$this->err(
				array(
					'err'=>'bind.faile',
					'msg'=>'type '.$type.' not bind'
				)
			);
		}else{
			#TODO 加上过期时间的错误提示
//			$arrTmp = array_shift($retConnect);
//			if( ($arrTmp['access_token']['expires_in']+$arrTmp['update_time']->sec) <= $_SERVER['REQUEST_TIME'] ) {
//				$this->err(
//					array(
//						'err'=>'bind.timeout',
//						'msg'=>'type '.$type.' not bind'
//					)
//				);
//			}
			$this->ok();
		}
	}
	
	private function redirect($url)
	{
		echo '<!doctype html><html><head><meta charset="utf-8">';
		//echo '<meta http-equiv="refresh" content="5;url='.$url.'">';
		echo '</head><body>';
		echo '<script>window.onload = function(){window.opener.location.href= "'.$url.'";window.close();}</script></body></html>';
		exit();
	}
	
	private function reload() {
		echo '<!doctype html><html><head><meta charset="utf-8">';
		//echo '<meta http-equiv="refresh" content="5;url='.$url.'">';
		echo '</head><body>';
		echo '<script>window.onload = function(){window.opener.location.reload();window.close();}</script></body></html>';
		exit();
	}
	
	private function close() {
		echo '<!doctype html><html><head><meta charset="utf-8">';
		//echo '<meta http-equiv="refresh" content="5;url='.$url.'">';
		echo '</head><body>';
		echo '<script>window.onload = function(){window.close();}</script></body></html>';
		exit();
	}
}