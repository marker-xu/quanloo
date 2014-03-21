<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 用户相关页面，以及Ajax请求接口
 * @author xucongbin
 */
class Controller_User extends Controller 
{
    const USER_FEED_PAGE_COUNT = 20; //动态汇总和我的动态的每页条数
    const FOLLOW_PAGE_COUNT = 28; //关注页面的每页条数
    const FANS_PAGE_COUNT = 28; //关注页面的每页条数
    //用户TAG最大数量
    const USER_TAG_MAX_LIMIT = 20;
	private $objLogicUser;
	
	public function before() {
		
		$action = $this->request->action();
		if( $this->checkActionNeedLogin( $action ) ) {
			JKit::$security['csrf'] = false;
			parent::before();
			$this->_needLogin();
		} else {
			parent::before();
		}
		
		
		$this->objLogicUser = new Model_Logic_User();
	}
	/**
	 * 
	 * 个人feeds页的总入口
	 * 
	 * @param array $_GET = array(
	 * 	id => 用户UID,
	 * 	type => feed的二级分类
	 * )
	 */
	public function action_index()
	{
	    $uid = (int) $this->request->param("id");
	    if (! $uid || $this->_uid == $uid) {
	        $this->template()->set_filename("user/feeds");
	        return $this->action_feeds();	        
	    } else {
	        $this->template()->set_filename("user/_other_people_feeds");
	        return $this->_otherPeople();
	    }
	}

	/**
	 *
	 * 登录后自己的feed页
	 *
	 * @param array $_GET = array(
	 * 	id => 用户UID,
	 * 	type => feed的二级分类
	 * )
	 */
	public function action_feeds()
	{	
        $arrParam = $this->_user_common();
        if (! $arrParam['isAdmin'])	{
            //动态汇总只有主人模式，客人只能进入动态页面
            $this->template()->set_filename("user/_other_people_feeds");
            return $this->_otherPeople();
        }
        $intCurSubtype = (int) $this->request->param('type', 0);
        if (! isset(Model_Logic_Feed2::$arrSubtype[$intCurSubtype])) {
            $intCurSubtype = 0;
        }
        if ($intCurSubtype == Model_Logic_Feed2::SUBTYPE_SELF) {
            $bolNoReduce = true;
        } else {
            $bolNoReduce = false;
        }
		$objFeed2 = new Model_Logic_Feed2();
		$intCount = self::USER_FEED_PAGE_COUNT;
		$intLasttime = time();
		$arrFeed = $objFeed2->getUserFeedList(array('user_id' => $this->_uid, 'offset' => 0, 'count' => $intCount,
		    'lasttime' => $intLasttime, 'type' => $intCurSubtype, 'no_reduce' => $bolNoReduce));
		$this->template->set('feeds', $arrFeed);
		$this->template->set('feeds_page_count', $intCount);
		$this->template->set('feeds_lasttime', $intLasttime);
		$this->template->set('subtype_list', Model_Logic_Feed2::$arrSubtype);
		$this->template->set('cur_subtype', $intCurSubtype);
		$this->template->set('forward_text_max_len', Model_Logic_Feed2::FORWARD_FEED_TEXT_MAX_LEN);
	}

	/**
	 * 以后统一到user/ID页面下
	 * 
	 */
	public function action_profile()
	{
	    $uid = (int) $this->request->param("id");
	    if (! $uid) {
	        $uid = $this->_uid;
	    }
        $this->request->redirect(Util::userUrl($uid), 301);
	}

	/**
	 * 其他人的feed页
	 */
	protected function _otherPeople() {
	    $arrParam = $this->_user_common();
	    $objFeed2 = new Model_Logic_Feed2();
	    $intCount = self::USER_FEED_PAGE_COUNT;
	    $intLasttime = time();
	    $arrFeed = $objFeed2->getUserFeedList(array('user_id' => $arrParam['uid'], 'offset' => 0, 'count' => $intCount,
	            'lasttime' => $intLasttime, 'no_reduce' => $arrParam['isAdmin'], 'type' => Model_Logic_Feed2::SUBTYPE_SELF));
	    $this->template->set('feeds', $arrFeed);
	    $this->template->set('feeds_page_count', $intCount);
	    $this->template->set('feeds_lasttime', $intLasttime);
	    $this->template->set('forward_text_max_len', Model_Logic_Feed2::FORWARD_FEED_TEXT_MAX_LEN);
	}
	
	protected function _user_common() {	    
	    $uid = (int) $this->request->param("id");
	    if($uid < 1) {
	        $uid = $this->_uid;
	    }
	    if ($uid < 1) {
	        $this->request->redirect('/');
	    }
	    Profiler::startMethodExec();
	    $userInfo = $this->objLogicUser->get($uid);
	    Profiler::endMethodExec(__FUNCTION__.' user get');
	    if(! $userInfo) {
	        $this->request->redirect('/');
	    }
	    if ($uid == $this->_uid) {
	        $isAdmin = true;
	    } else {
	        if ($this->_uid) {
	            $userInfo['is_fans'] = $this->objLogicUser->isFollowing($this->_uid, $uid);
	        }
	        $isAdmin = false;
	    }
	    //用户信息
	    $this->template->set('user_info', $userInfo);
	    //是否主人模式
	    $this->template->set("is_admin", $isAdmin);
	    //勋章
	    $objDataUser = new Model_Data_User();
	    Profiler::startMethodExec();
	    $medals = $objDataUser->medals($uid);
	    Profiler::endMethodExec(__FUNCTION__.' medals');
	    $this->template->set("medals", $medals);
	    
	    return array('uid' => $uid, 'isAdmin' => $isAdmin);	    
	}
	
	public function action_follow() {
	    $arrParam = $this->_user_common();
	    $intOffset = (int) $this->request->param('offset', 0);
	    if ($intOffset < 0) $intOffset = 0;
	    $total = 0;
	    $hidden = $arrParam['isAdmin'] ? null : false;
	    $modelLogicUser = new Model_Logic_User();
	    try {
	        $followings = (array) $modelLogicUser->followings($arrParam['uid'], $intOffset, self::FOLLOW_PAGE_COUNT,
	            null, $hidden, $total);
	        if (! empty($followings)) {
	            $users = $modelLogicUser->getMulti(Arr::pluck($followings, 'following'), true);
	        } else {
	            $users = array();
	        }	        
	    } catch (Exception $e) {
	        $users = array();
	    }
	    $this->template->set('following_list', $users);
	    $this->template->set('following_total_num', (int) $total);
	    $this->template->set('following_page_count', self::FOLLOW_PAGE_COUNT);
	    $this->template->set('following_page_offset', $intOffset);
	}
	
	public function action_fans() {
	    $arrParam = $this->_user_common();
	    $intOffset = (int) $this->request->param('offset', 0);
	    if ($intOffset < 0) $intOffset = 0;
	    $total = 0;
	    $hidden = $arrParam['isAdmin'] ? null : false;
	    $modelLogicUser = new Model_Logic_User();
	    try {
	        $followers = $modelLogicUser->followers($arrParam['uid'], $intOffset, self::FANS_PAGE_COUNT,
	                null, $hidden, $total);
	        $arrTmp = array();
	        foreach ($followers as $v) {
	            $arrTmp[$v['user']] = @$v['bidirectional'];
	        }
	        if (! empty($arrTmp)) {
	            $users = $modelLogicUser->getMulti(array_keys($arrTmp), true);
	            if ($arrParam['isAdmin']) {
	                $objMsg = new Model_Logic_Msg();
	                $objMsg->resetNewFansCounter($this->_uid);         
    	            foreach ($users as $k => $v) {
    	                $users[$k]['bidirectional'] = $arrTmp[$k];
    	            }
	            }
	        } else {
	            $users = array();
	        }
	    } catch (Exception $e) {
	        $users = array();
	    }
   
	    $this->template->set('fans_list', $users);
	    $this->template->set('fans_total_num', (int) $total);
	    $this->template->set('fans_page_count', self::FANS_PAGE_COUNT);
	    $this->template->set('fans_page_offset', $intOffset);
	}

	/**
	 *
	 * 个人圈子页
	 *
	 * @param array $_GET = array(
	 * 	id => 用户UID,
	 * 	type => 二级tab类型，0：已关注， 1：已共享, 默认，0
	 * 	offset => 起始位置, 默认0
	 * 	count => 数量， 默认12
	 * )
	 */
	public function action_circle()
	{
		$arrGetParam = $this->request->query();
		if (array_key_exists('type', $arrGetParam)) {
			//老的url格式，需要做301跳转 2012-12-31之后可以删除这个判断
			$uid = (int) $this->request->param("id");
			if($uid < 1) {
				$uid = $this->_uid;
			}
			if ($uid < 1) {
				$this->request->redirect('/');
			}
			$this->request->redirect(Util::userUrl($uid, 'circle', $arrGetParam), 301);
			die();
		}
		
	    $arrParam = $this->_user_common();
	    $type = (int) $this->request->param('type', 2);
	    $offset = (int) $this->request->query('offset');
	    $count = (int) $this->request->query('count');
	    if ($offset < 0) $offset = 0;
	    if($count <= 0) $count = 12;

        //圈子列表
        if ($type==2){
        	$objLogicCircle = new Model_Logic_Circle();
        	$total = 0;
			$arrCircleList = $objLogicCircle->created($arrParam['uid'], $offset, $count, 
				$arrParam['isAdmin'] ? 'host':'guest', $total);
			$cids = array_keys($arrCircleList);
	   		$arrCircles = array(
	   			'count' => $total ,
	   			'data' => $this->objLogicUser->getCircleVideos($cids, $this->_uid)
	   		);     
        } else {
        	Profiler::startMethodExec();
        	if($type) {
	            $cids = $this->objLogicUser->getSharedCirclesByUid($arrParam['uid'], true);
	            Profiler::endMethodExec(__FUNCTION__.' getSharedCirclesByUid');
	        } else {
	            $cids = $this->objLogicUser->getUserCirclesByUid($arrParam['uid'], true, 1);
	            Profiler::endMethodExec(__FUNCTION__.' getUserCirclesByUid');
	        }
	        $arrCircles['count'] = count($cids);
	        $cids = array_slice($cids, $offset, $count);
	        Profiler::startMethodExec();
	        $arrCircles['data'] = $this->objLogicUser->getCircleVideos($cids, $this->_uid);
	        Profiler::endMethodExec(__FUNCTION__.' getCircleVideos');	
        }
        //type
        $this->template->set("type", $type);
        $this->template->set("offset", $offset);
        $this->template->set("count", $count);        	
        $this->template->set('user_circle_list', $arrCircles);
	}

	/**
	 *
	 * 个人视频页
	 *
	 * @param array $_GET = array(
	 * 	id => 用户UID,
	 * 	type => 二级tab类型，watch_later：以后观看, watched：已观看, commented：已评论, mooded：已标心情, 默认主人模式为以后观看，客人模式为已标心情
	 * 	offset => 起始位置, 默认0
	 * 	count => 数量， 默认12
	 * )
	 */
	public function action_video()
	{
		$arrGetParam = $this->request->query();
		if (array_key_exists('subtab', $arrGetParam)) {
			//老的url格式，需要做301跳转 2012-12-31之后可以删除这个判断
			$uid = (int) $this->request->param("id");
			if($uid < 1) {
				$uid = $this->_uid;
			}
			if ($uid < 1) {
				$this->request->redirect('/');
			}
			$arrGetParam['type'] = $arrGetParam['subtab'];
			unset($arrGetParam['subtab']);
			$this->request->redirect(Util::userUrl($uid, 'video', $arrGetParam), 301);
			die();
		}		
		
	    $arrParam = $this->_user_common();
	    $subtab = $this->request->param('type');
	    $offset = (int) $this->request->query('offset');
	    $count = (int) $this->request->query('count');
	    if ($offset < 0) $offset = 0;
	    if($count <= 0) $count = 12;
	    if(! isset(Model_Data_UserVideo::$arrType2Name[$subtab])) {
	        if(! $arrParam['isAdmin']) {
	            $subtab = Model_Data_UserVideo::TYPE_MOODED;
	        } else {
	            $subtab = Model_Data_UserVideo::TYPE_WATCHLATER2;
	        }
	    }
	    $this->setTemplateUserVideoNoticeMsg($arrParam['uid'], $subtab, $arrParam['isAdmin']);
	    $this->template->set('cur_selected_tab', $subtab);
	    $this->template->set('tabNameMap', Model_Data_UserVideo::$arrType2Name);	    
	    // 对来自Spider的访问同步输出页面
	    if (Util::isSpider()) {
	        $modelLogicVideo = new Model_Logic_Video();
	        if ($subtab == 'mooded') {
	            $videos = $modelLogicVideo->getMooded($arrParam['uid'], 0, 50);
	            $videos = array_values($videos['data']);
	        } else if ($subtab == 'commented') {
	            $videos = $modelLogicVideo->getCommented($arrParam['uid'], 0, 50);
	            $videos = array_values($videos['data']);
	        } else {
	            $videos = array();
	        }
	        $this->template->set('videos', $videos);
	    }
	}

	/**
	 * 
	 * 用户设置
	 */
	public function action_setting() {
		$arrRules = $this->_formRule(array('@name', '@intro', '@email'));	
		if($this->request->method()=='POST') {
		    $arrPost = $this->request->post();
			$objValidation = new Validation($arrPost, $arrRules);
			if (! $this->valid($objValidation)) {
			    return;
			}
			$objBlackword = Model_Logic_Blackword::instance();
			if ($objBlackword->filter($arrPost['name'])) {
				$this->err(null, array('name'=>"内容包含敏感词"), null, null, "usr.submit.valid");
			}
			if ( $objBlackword->filter($arrPost['intro']) ) {
			    $this->err(null, array('intro'=>"内容包含敏感词"), null, null, "usr.submit.valid");
			}
			if ($objBlackword->filter($arrPost['tags'])) {
				$this->err(null, array('input_tag'=>"内容包含敏感词"), null, null, "usr.submit.valid");
			}
			
			$arrPost = array_map('trim', $arrPost);
			$arrTags = $this->filterTags( explode(",", $arrPost['tags']) );
			if( count($arrTags)>self::USER_TAG_MAX_LIMIT ) {
				$this->err(null, array('input_tag'=>"标签数量不能超过".self::USER_TAG_MAX_LIMIT."个！"), null, null, "usr.submit.valid");
			}		
			$uid = $this->_uid;
			$nick = $arrPost['name'];
			$intro = $arrPost['intro'];
			$res = false;
			$arrParams = array(
				'nick' => $nick,
				'email' => $arrPost['email'],
				'intro' => $intro,
				'tags' => $arrTags, 
				'accept_subscribe_email' =>  intval($arrPost['accept_subscribe'])
			);
			try {
				$res = $this->objLogicUser->modifyUser($uid, $arrParams);
				//更新session
				if($res) {
					$this->objLogicUser->changeSession($this->_uid);
					$this->ok(null, '/user/index');
				}
			} catch (Model_Logic_Exception $e) {
				$code = $e->getCode();
				if($code==-2002) {
					$this->err(null, "用户名已存在！");
				}
				
			}
		}
		$arrUserInfo = $this->objLogicUser->get($this->_uid);
		$objLogicRecommend = new Model_Logic_Recommend();
		    
	    $this->template->set('select_tags', $objLogicRecommend->getRecommendUserTags(0, 10));
		#用户基本信息
		$this->template->set('user_info', $arrUserInfo);
		$this->template->set('valid_rules', json_encode($arrRules));
		$this->setpanellist();
	}
	/**
	 * 
	 * 注册
	 */
	public function action_register() {	
		$strTargetUrl = $this->request->param('f');
		
		if(!$strTargetUrl) {
			$strTargetUrl = "/";
			if( isset($_SERVER['HTTP_REFERER']) ) {
				$tmpPath = parse_url($_SERVER['HTTP_REFERER']);
				$fPath = $tmpPath['path'];
				if(isset($tmpPath['query'])) {
					$fPath.= "?".$tmpPath['query'];
				}
				$strTargetUrl = preg_replace('/^\//', '', $fPath);
			}
		}	
		if($this->_uid) {
			$this->request->redirect('user/index');
			return;
		}
		$sdid = Session::instance()->get('sdid');
		if(!$sdid && $this->request->query("sdid")) {
			$sdid = intval($this->request->query("sdid"));
			Session::instance()->set('sdid', $sdid);
		}
//		if(!$sdid) {
//			$this->request->redirect('index');
//			return;
//		}
		if( $this->request->query("ticket") ) {
			$gotoUrl = "/user/login".URL::query(array('f'=>$strTargetUrl), true);
			$strJs = "<script>window.parent.location.href='$gotoUrl';</script>";
			die($strJs);
		}
		if($sdid) {
			$this->request->redirect('user/completeinfo?f='.urlencode($strTargetUrl));
		}
		$redirectUrl = "http://".DOMAIN_SITE."/user/register?f=".urlencode($strTargetUrl);
		$this->template->set("register_iframe_url", 
			Model_Data_Sndauser::buildRegisterUrl($redirectUrl, true, array('hideos'=>'true','hideidname'=>'true'))
		);
	}
	/**
	 * 
	 * 登录
	 * 
	 */
	public function action_login() {
	    $strTargetUrl = $this->request->param('f', '/');
	    if (strncasecmp($strTargetUrl, 'http://', 7)) {
	        $strTargetUrl = URL::site($strTargetUrl, null, false);
	    }	    
		$isMiddle = preg_match("/[\&\?]middle=1/i", $strTargetUrl);
    	$strTargetUrl = preg_replace("/[\&\?]middle=1/i", "", $strTargetUrl);
		if($this->_uid) {
			if($strTargetUrl=="/") {
		        $strTargetUrl = Util::userUrl($this->_uid);
		    }
		    if($this->request->method() != HTTP_Request::POST) {
			    $this->request->redirect($strTargetUrl);
		    } else {
		        $this->ok(null, $strTargetUrl);		        
		    }
		    return;
		}
		
		$sessionSdid = Session::instance()->get('sdid');
		$ticket = $this->request->query("ticket");
		$authKey = $this->request->query("authKey");
		if($ticket) {
			if($authKey) {
				$gotoUrl = "/user/login".URL::query(array('f'=>$strTargetUrl, 'ticket'=>$ticket), false);
				$strJs = "<script>window.parent.location.href='$gotoUrl';</script>";
				die($strJs);
			}
			$arrSndaInfo = Model_Data_Sndauser::getTicketInfo($ticket);
			if($arrSndaInfo) {
				$intSdid = intval($arrSndaInfo['sdid']);
				$arrUserInfo = $this->objLogicUser->getUserByid($intSdid);
				if($sessionSdid && $sessionSdid==$intSdid) {
					$this->gotoRefer("user/completeinfo?f=".urlencode($strTargetUrl), true, !$isMiddle);
//					$this->request->redirect("user/completeinfo?f=".urlencode($strTargetUrl));
				}
				Session::instance()->set('sdid', $intSdid);				
				#TODO 第三方帐号
				if($arrUserInfo) {
					$this->objLogicUser->login($intSdid);
					//记录日志了
					$modelStat = new Model_Data_Stat();
	    			$arrStat = array(
	    				'page_id'=>'click_login_success',
	    				'user_id' => $intSdid,
	    			);
	    		    $modelStat->log($arrStat);
	    		    if($strTargetUrl=="/") {
	    		        $strTargetUrl = Util::userUrl($intSdid);
	    		    }
	    		    #TODO 改成js页内跳转
	    		    $this->gotoRefer($strTargetUrl, false, !$isMiddle);
				} else {
					$this->gotoRefer("user/completeinfo?f=".urlencode($strTargetUrl), true, !$isMiddle);
//					$this->request->redirect("user/completeinfo?f=".urlencode($strTargetUrl));
				}
			} else {
				if($sessionSdid) {
					$this->gotoRefer("user/completeinfo?f=".urlencode($strTargetUrl), true, !$isMiddle);
				}
				$this->err(null, "登录失败，请重试！");
			}
		}
		if($sessionSdid) {
			$this->request->redirect("user/register");
		}
		$loginUrl = Model_Data_Sndauser::buildLoginUrl(NULL, true);
		$this->template->set("login_url", $loginUrl);
//		$this->request->redirect($loginUrl);
	}
	
	public function action_completeinfo() {
		if($this->_uid) {
			$this->request->redirect('user/index');
			return;
		}
 		$sdid = Session::instance()->get('sdid');
		if(!$sdid) {
			$this->request->redirect('index');
			return;
		}
		
		if( $this->objLogicUser->getUserByid($sdid) ) {
			$this->objLogicUser->login($sdid);
			$this->request->redirect('user/index');
		}
		
		$arrRules = $this->_formRule(array('@name', '@intro', '@email'));
		if($this->request->method() != HTTP_Request::POST) {
			$strTargetUrl = $this->request->param('f', '/');
			$objLogicRecommend = new Model_Logic_Recommend();
			$this->template->set('goto_f', $strTargetUrl);
		    $this->template->set('select_tags', $objLogicRecommend->getRecommendUserTags(0, 10));
		    //推荐用户关注的圈子
//		    $this->template->set('circle_list', $objLogicRecommend->getGuessCirclesByCookie(0, 6));
		    $this->template->set('valid_rules', json_encode($arrRules));
		    $nickname = Session::instance()->get('nickname');
		    $this->template->set('nickname', $nickname);
		    
		    try {
		    	$arrTmp = Model_Data_Sndauser::accountQueryUserInfo($sdid);
		    	if (isset($arrTmp['Email']) && ! empty($arrTmp['Email'])) {
		    		$this->template->set('email', trim($arrTmp['Email']));
		    	}
		    } catch (Exception $e) {
		    	//do nothing
		    }
		    return;
		}
		$arrPost = $this->request->post();
		$objValidation = new Validation($arrPost, $arrRules);
		if (! $this->valid($objValidation)) {
		    return;
		}
		
		$objBlackword = Model_Logic_Blackword::instance();
		if ($objBlackword->filter($arrPost['name'])) {
			$this->err(null, array('name'=>"内容包含敏感词"), null, null, "usr.submit.valid");
		}
		if ( $objBlackword->filter($arrPost['intro']) ) {
		    $this->err(null, array('intro'=>"内容包含敏感词"), null, null, "usr.submit.valid");
		}
		if ($objBlackword->filter($arrPost['tags'])) {
			$this->err(null, array('input_tag'=>"内容包含敏感词"), null, null, "usr.submit.valid");
		}
		
		$arrPost = array_map('trim', $arrPost);
		$arrTags = $this->filterTags( explode(",", $arrPost['tags']) );
		if( count($arrTags)>self::USER_TAG_MAX_LIMIT ) {
			$this->err(null, array('input_tag'=>"标签数量不能超过".self::USER_TAG_MAX_LIMIT."个！"), null, null, "usr.submit.valid");
		}
		
		try {
			$strTargetUrl = $this->request->post('goto_f');
			if(! $strTargetUrl || $strTargetUrl=="/") {
				$strTargetUrl = Util::userUrl($sdid);
			}
			$strTargetUrl = preg_replace("/[\&\?]middle=1/i", "", $strTargetUrl);
			if( !isset($arrPost['email']) ) {
				$arrPost['email'] = "";
			}
			$arrExtra = array('intro' => $arrPost['intro'], '_id' => $sdid, 'tags'=>$arrTags, 
			'accept_subscribe_email' => !isset($arrPost['accept_subscribe']) ? 0 : intval($arrPost['accept_subscribe']));
			
			
			$uid = $this->objLogicUser->register($arrPost['email'], '', $arrPost['name'], array(), $arrExtra);
		    
			if($uid) {
		        #TODO 验证邮箱
				#TODO 第三方帐号
		        $modelStat = new Model_Data_Stat();
    			$arrStat = array(
    				'page_id'=>'click_register',
    				'user_id' => $uid,
    			);
    		    $modelStat->log($arrStat);
//		        $strTargetUrl = "user/selectcircles?step=2&f=".urlencode(preg_replace('/^\//', '', $strTargetUrl));
				#TODO第三方帐号，头像替换为围脖帐号图片
				$avatar = Session::instance()->get('avatar');
				$arrAvatar = array();
				if($avatar){
					$avatar_org = file_get_contents($avatar);
					$fileName = "/tmp/".uniqid("org").".jpg";
					if(!file_put_contents($fileName, $avatar_org)){
						JKit::$log->warn("Unable to generate the file in the temporary location , file-".$fileName."source-".$avatar_org);
					}else{
						$arrAvatar = $this->objLogicUser->resizeAvatar($fileName);
						
						if( !$this->objLogicUser->changeAvatar($uid, $fileName, $arrAvatar) ) {
							JKit::$log->warn("Unable to generate the file in the temporary location , file-".$fileName."source-".$avatar_org);
						}
						$this->objLogicUser->changeSession($uid);
					}
					
				}
		        $this->ok(null, URL::site($strTargetUrl, null, false));
		    } else {
		        $this->err(null, "用户创建失败！");
		    }		    	
		} catch (Model_Logic_Exception $e) {
		    $code = $e->getCode();
		    if($code == -2001) {
		        $strMsg = "注册的邮箱已存在！";
		    } elseif($code == -2002) {
		        $strMsg = "用户名已存在！";
		    } else {
		        $strMsg = '系统繁忙，请稍候重试！';
		    }
		    $this->err(null, $strMsg);
		}
		
	}
	
	public function action_batchsubscribecircles() {
	    $arrCircleIds =  $this->request->post('circle_ids');
		$strTargetUrl = $this->request->post('goto_f');
		if(! $strTargetUrl || $strTargetUrl=="/") {
			$strTargetUrl = Util::userUrl($this->_uid);
		}
	    if ( !$arrCircleIds ) {
	        $arrCircleIds = array();
	    }
	    $modelLogicCircle = new Model_Logic_Circle();
	    try {
		    foreach($arrCircleIds as $circleId) {
		    	$circleId = intval($circleId);
		    	if($circleId<=0) {
		    		continue;
		    	}
		    	$modelLogicCircle->subscribe($circleId, $this->_uid);
		    }
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    $modelDataUserStatAll = new Model_Data_UserStatAll();
	    $modelDataUserStatAll->setSlaveOkay(false);
        $arrStat = $modelDataUserStatAll->findOne(array('_id' => $this->_uid), array('subscribed_circle_count'));
        $arrReturn = array(
        	"subscribed_circle_count" => isset($arrStat['subscribed_circle_count']) ? intval($arrStat['subscribed_circle_count']):0
        );
	    $this->ok( $arrReturn );
	}
	/**
	 * 
	 * 查询用户关注的圈子数量
	 * 
	 */
	public function action_getsubscribedcount() {
		$modelDataUserStatAll = new Model_Data_UserStatAll();
	    $modelDataUserStatAll->setSlaveOkay(false);
        $arrStat = $modelDataUserStatAll->findOne(array('_id' => $this->_uid), array('subscribed_circle_count'));
        $arrReturn = array(
        	"subscribed_circle_count" => isset($arrStat['subscribed_circle_count']) ? intval($arrStat['subscribed_circle_count']):0
        );
	    $this->ok( $arrReturn );
	}
	/**
	 * 
	 * 退出
	 */
	public function action_logout() {
		$redirectUrl = "http://".DOMAIN_SITE;
		$strTargetUrl = $this->request->param('f');
		if( $strTargetUrl ) {
			$objRequest = Request::factory($strTargetUrl);
			$tmpAction = $objRequest->action();
			$tmpController = $objRequest->controller();
			switch($tmpController) {
				case 'user':
					if($tmpAction=="index") {
						$uid = intval( $objRequest->query("id") );
						if($this->_uid && $uid && $uid!=$this->_uid) {
							$redirectUrl.= $strTargetUrl;
						}
					}
					break;
				case 'video':
				case 'circle':
				case 'guesslike':
				case 'search':
					$redirectUrl.= $strTargetUrl;
					break;
				case 'widget':
				case 'star':
				case 'movie':
				case 'tv':
					$redirectUrl.= $strTargetUrl;
					break;
				default:
					$redirectUrl.= "/";
					
			}
		} else {
			$strTargetUrl = "/";
		}
		if(!$this->_uid) {
			$this->request->redirect($redirectUrl);
		}
		$this->objLogicUser->logout($this->_uid);
		
		
		$logoutUrl = Model_Data_Sndauser::buildLogoutUrl($redirectUrl);
//		echo $logoutUrl;
		$this->request->redirect($logoutUrl); //到首页
	}
	
	/**
	 * 
	 * 换帐号
	 */
	public function action_changeaccount() {
		$this->objLogicUser->logout($this->_uid);
		$logoutUrl = Model_Data_Sndauser::buildLogoutUrl("http://".DOMAIN_SITE."/user/login");
		$this->request->redirect($logoutUrl); //到首页
	}
	/**
	 * 
	 * 退出
	 */
	public function action_logoutcb() {
		$this->objLogicUser->logout($this->_uid);
		$arrTmp = explode(":", DOMAIN_SITE);
		$arrDomain = explode(".", $arrTmp[0]);
		$strDomain = implode(".", array_slice($arrDomain, -2));
		die("app_logout_API('{$strDomain}');");
	}
	
	/**
	 * 
	 * 修改头像
	 * 
	 * @param $_FILES => array(
	 * 	'avatar' => 图片file表单内容
	 * )
	 * 
	 * @return array(
	 *  avatar => 图片路径
	 * )
	 */
	public function action_modifyavatar() {
	    $intMaxWidth = 600;
	    $intMaxHeight = 600;
		$uid = $this->_uid;
		$arrUserInfo = $this->objLogicUser->get($uid);
		if($this->request->method()=='POST') {
			$avatar = $_FILES['avatar'];
			$validAvatar = $this->validAvatar($avatar);
			if( !$validAvatar['ok'] ) {
				$this->avatarCB(false, '', $validAvatar['msg']);
			}

			$imageInfo = $validAvatar['attr'];			
			if ($imageInfo[0] > $intMaxWidth && $imageInfo[1] > $intMaxHeight) {
			    $objImage = Image::factory($avatar['tmp_name']);
			    $imageMaster = Image::AUTO;
			    $diff = $objImage->width - $objImage->height;
			    if($diff>0) {
			        $imageMaster = Image::HEIGHT;
			    } elseif($diff<0) {
			        $imageMaster = Image::WIDTH;
			    }
			    $objImage->resize($intMaxWidth, $intMaxHeight, $imageMaster);
			    $objImage->save($avatar['tmp_name']);
			    $avatarOrgReturn = Model_Data_User::uploadAvatar($avatar['tmp_name'], "png");
			    $imageInfo = getimagesize($avatar['tmp_name']);
			    @unlink($avatar['tmp_name']);
			} else {
			    $avatarOrgReturn = Model_Data_User::uploadAvatar($avatar['tmp_name'], "png");
			}			
			$backOrg = Session::instance()->get('avatar_org');
			if(!$avatarOrgReturn) {
				$this->avatarCB(false, '', "上传失败");
			}
					
			$avatar = $avatarOrgReturn['group_name']."/".$avatarOrgReturn['filename'];
			Session::instance()->set('avatar_org', $avatar);
			if($backOrg && isset($arrUserInfo['avatar']['org']) 
			&& $backOrg!=$arrUserInfo['avatar']['org']) {
				$arrTmp = explode("/", $backOrg, 2);
    			Model_Data_User::removeAvatar($arrTmp[0], $arrTmp[1]);
			} 
			$this->avatarCB(true,  array('avatar'=>$avatar, 'width'=>$imageInfo[0], 'height'=>$imageInfo[1]), 
			'');
		}
		#用户基本信息
		$this->template->set('user_info', $arrUserInfo);
		$this->setpanellist();
	}

	/**
	 * 上传头像
	 *@param $_GET = >array(
	 * 	"x" => 
	 * 	"y" => 
	 *  "width" => 
	 *  "height" =>
	 * )
	 * 
	 * @return ok
	 */
	public function action_changeavatar() {
			
		$offsetX = intval( $this->request->query("x") );
		$offsetY = intval( $this->request->query("y") );
		$height = intval( $this->request->query("height") );
		$width = intval( $this->request->query("width") );
		$rate = (float) $this->request->query("quotiety") ;
		$avatarReturn = array();
		$uid = $this->_uid;
		$avatar = Session::instance()->get('avatar_org');
		$arrUserInfo = $this->objLogicUser->getUserByid($uid);
		if(!$avatar && isset($arrUserInfo['avatar']['org'])) {
			$avatar = $arrUserInfo['avatar']['org'];
		}
		if( !$avatar ) {
			$this->err("avatar.empty", '头像不存在');
		}
		if(!$rate) {
			$rate = 2;
		} else {
			$rate = round(1/$rate, 2);
		}
		try {
			$orgTmpAvatarName = "/tmp/".uniqid("avatar").".png";
			$strContent = file_get_contents(Util::userAvatarUrl($avatar));
			if(!$strContent) {
				$this->err("avatar.empty", '头像不存在');
			}
			file_put_contents($orgTmpAvatarName, $strContent);
			$orgTmpName = "/tmp/".uniqid("org").".png";
			if(!file_exists($orgTmpAvatarName)) {
				$this->err(NULL, "头像不存在");
			}
			$objImage = Image::factory($orgTmpAvatarName);
//			echo "$width, $height, $offsetX, $offsetY<br>\n";
			if($width) {
				$width =round($width*$rate); 
			}
			if($height) {
				$height =round($height*$rate); 
			}
			if($offsetX) {
				$offsetX =round($offsetX*$rate); 
			}
			if($offsetY) {
				$offsetY =round($offsetY*$rate); 
			}
//			echo "$width, $height, $offsetX, $offsetY<br>";
			$objImage->crop($width, $height, $offsetX, $offsetY);
			$objImage->save($orgTmpName);
			if( !file_exists($orgTmpName) ) {
				$this->err(NULL, "修改失败");
			}
			
			$arrThumnil = $this->objLogicUser->resizeAvatar($orgTmpName);
			@unlink($orgTmpName);
			if( !$this->objLogicUser->changeAvatar($uid, $orgTmpAvatarName, $arrThumnil) ) {
				$this->err(NULL, "修改失败");
			} else {
				$this->objLogicUser->changeSession($uid);
				$arrUserInfo = Session::instance()->get('user');
				$this->ok(array('avatar'=>$arrUserInfo['avatar']['30']), URL::site('user', null, false));
			}
			
		} catch (Model_Logic_Exception $e) {
			$code = $e->getCode();
			$this->err(NULL, "该用户不存在！");
		}
	}

	/**
	 * 
	 * 设置主页
	 */
/* 	public function action_sethomepage() {
		$objLogicCircle = new Model_Logic_Circle();
		
		$subscribeCircleIds = $this->objLogicUser->getUserCirclesByUid($this->_uid, true);
		$settingCircleIds = array_slice($subscribeCircleIds, 0, 8);
		if($this->request->method()=='POST') {
			if(!$subscribeCircleIds) {
				$this->ok(NULL, URL::site('user', null, false));
			}
			$circlesSet = $this->request->post("circles_set");
			$arrTmpCircles = explode(",", $circlesSet);
			foreach($arrTmpCircles as $k=>$tmpCircleId) {
				if(!$tmpCircleId) {
					unset($arrTmpCircles[$k]);
				} else {
					$arrTmpCircles[$k] = intval($tmpCircleId);
				}
			}
			$tmpDiff = array_diff($subscribeCircleIds, $arrTmpCircles);
			$tmpDiff = array_diff($tmpDiff, $settingCircleIds);
			$tmpSettingCircleIds = array_diff($settingCircleIds, $arrTmpCircles);
			$arrCircleIds = array_values(array_merge($arrTmpCircles, $tmpDiff, $tmpSettingCircleIds));
			$objModelCircleUser = new Model_Data_CircleUser();
			try {
				$objModelCircleUser->setSubscribedCircleOrder($this->_uid, $arrCircleIds);
				$this->ok(NULL, URL::site('user', null, false));
			} catch (Exception $e) {
				$this->err(NULL, "修改失败");
			}
		}
		
		$this->template->set("setting_circles", $objLogicCircle->getMulti($settingCircleIds, true));
		$this->template->set("subscribe_circles", $objLogicCircle->getMulti($subscribeCircleIds, true));
		$this->setpanellist();
	} */
	/**
	 * 
	 * 我的勋章
	 */
	public function action_mymedal() {
		$arrUserInfo = $this->objLogicUser->get($this->_uid);
		$this->template->set("user_info", $arrUserInfo);
		$this->setpanellist();
	}
	
	
	/**
	 * 帐号绑定
	 * 
	 * */
	
	public function action_syncconnect() {
		$arrUserInfo = $this->objLogicUser->get($this->_uid);
		$this->template->set("user_info", $arrUserInfo);
		
		$objDataUserConnect = new Model_Data_UserConnect();
		$arrConnectList = $objDataUserConnect->getConnectsByUid($this->_uid);
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
			}
			if($val['third_party'] == Model_Data_UserConnect::TYPE_TQQ){
				$bindlist['tqq'] = $val;
				$bindlist['tqq']['time'] = date('Y年m月d日',$val['update_time']->sec);
			}
		}
		
		$this->template->set('bindlist',$bindlist);
		$this->setpanellist();
	}
	/**
	 * 
	 * 验证邮箱是否被注册
	 */
	public function action_is_email_reged() {
		$email = $this->request->param('email');
		if(!$email){
			$this->err(null, '邮箱不能为空');
		}
		$objModelUser = new Model_Data_User();
		if( $objModelUser->getByEmail($email) ){
			$this->err(null, '该email帐号已经注册');
		}
		$this->ok();
	}
	/**
	 * 
	 * 验证昵称是否被使用
	 */
	public function action_is_nick_reged() {
		$nick = trim( $this->request->param('nick') );
		if(!$nick){
			$this->err(null, '用户名不能为空');
		} elseif (Model_Logic_Blackword::instance()->filter($nick)) {
		    $this->err(null, '文明上网，人人有责', null, null, "sys.forbidden.blackword");
		}
		$objModelUser = new Model_Data_User();
		if( $objModelUser->getByNick($nick, $this->_uid) ){
			$this->err(null, '该用户名已被使用');
		}
		$this->ok();
	}

	/**
	 * 
	 * 获取圈子信息和圈子里的视频信息
	 * 
	 * @param $_GET = array(
	 *  format => 数据格式, json和html两种类型, 默认html
	 *  type => 类型，0：已关注， 1：已共享, 2：猜你喜欢，3:用户创建的。 默认，0 ,
	 * 	offset => 起始位置, 默认0
	 * 	count => 数量， 默认8
 	 * )
	 * 
	 * @return array(
	 * 	total => 数量
	 * 	data => array(
	 * 		圈子信息array(
	 * 		is_focus => 是否被关注
	 * 		is_shared => 是否被分享
	 * 		...
	 * 		),
	 * 	)
	 * )
	 */
	public function action_getcirclelist() {
		$arrReturn = array();
		//$uid = $this->request->query("uid");
		$format = $this->request->query("format");
		$type = intval( $this->request->query("type") );
		$offset = $this->request->query("offset");
		$count = $this->request->query("count");
		//if(!$uid) {
			//$this->err();
		//}
		$uid = $this->_uid;
		if($offset===NULL) {
			$offset = 0;
		}
		$offset = intval($offset);
		
		if($count===NULL) {
			$count = 8;
		}
		$count = intval($count);
		if($type==3) {
			$objLogicCircle = new Model_Logic_Circle();
			Profiler::startMethodExec();
        	$total = 0;
			$arrCircles = $objLogicCircle->created($uid, $offset, $count, 
				'host', $total);
			Profiler::endMethodExec(__FUNCTION__.' getGuessCirclesByUid');
			$arrReturn['total'] = $total;
		} elseif($type==2) {
			$objLogicRecommend = new Model_Logic_Recommend();
	        Profiler::startMethodExec();
			$arrCircles = $objLogicRecommend->getGuessCirclesByUid($uid, $offset, $count);
		    Profiler::endMethodExec(__FUNCTION__.' getGuessCirclesByUid');
			$arrReturn['total'] = 40;
		} else {
	        Profiler::startMethodExec();
			$cids = $type ? $this->objLogicUser->getSharedCirclesByUid($uid, true):
				$this->objLogicUser->getUserCirclesByUid($uid, true, 1);	
			if ($type) {
		        Profiler::endMethodExec(__FUNCTION__.' getSharedCirclesByUid');
		    } else {
		        Profiler::endMethodExec(__FUNCTION__.' getUserCirclesByUid');
		    }
			$arrReturn['total'] = count($cids);
			$cids = array_slice($cids, $offset, $count);
			$objLogicCircle = new Model_Logic_Circle();
	        Profiler::startMethodExec();
			$arrCircles = $objLogicCircle->getMulti($cids, true);
		    Profiler::endMethodExec(__FUNCTION__.' getMulti');
	        Profiler::startMethodExec();
			$this->objLogicUser->complementUserCircleRel($arrCircles, $uid);
		    Profiler::endMethodExec(__FUNCTION__.' complementUserCircleRel');
			
		}
		$arrReturn['data'] = $arrCircles;
		if($format=="json") {
			$this->ok($arrReturn);
		}
		
		$objTpl = self::template();
		$objTpl->set_filename("user/circle_item");
	    $objTpl->circle_list = $arrCircles;
	    $objTpl->type = $type;
		$content = $objTpl->render();
		$this->ok(array("html"=>$content, "total" => $arrReturn['total']));
		
	}
	/**
	 * 
	 * 获取用户注册时，基于tag的推荐列表
	 * 
	 * @param $_GET = array(
	 *  format => 数据格式, json和html两种类型, 默认html
	 * 	offset => 起始位置, 默认0
	 * 	count => 数量， 默认6
	 * )
	 * 
	 * @return array(total=>总数, data=>圈子列表 / html => html内容)
	 */
	public function action_getregistercirclelist() {
		$arrReturn = array(
			"total" => 24
		);
		$offset = (int)$this->request->param("offset", 0);
		$count = (int)$this->request->param("count", 6);
		$format = $this->request->param("format", "html");
		$arrTags = $this->_user['tags'] ? $this->_user['tags'] : array();
		$arrCircles = $this->objLogicUser->getUserRegisterCirclesByTags($arrTags, $offset, $count, $this->_uid, true);
		if($format=="json") {
			$arrReturn['data'] = $arrCircles;
		} else {
			$objTpl = self::template();
			$objTpl->set_filename("user/inc/ul_select_circles");
		    $objTpl->circle_list = $arrCircles;
			$content = $objTpl->render();
			$arrReturn['html'] = $content;	
		}
		$this->ok($arrReturn);
	}
	/**
	 * 
	 * 获取圈子信息和圈子里的视频信息
	 * 
	 * @param $_GET = array(
	 *  uid => 用户ID,
	 *  type => 类型，见Model_Data_UserVideo::TYPE_XX，默认： commented：已评论  
	 * 	offset => 起始位置, 默认0
	 * 	count => 数量， 默认10
 	 * )
	 * 
	 * @return array(
	 * 
	 * 	data => array(
	 * 			array(视频基本信息),
	 * 				...
	 * 			)
	 *  		...
	 *  	),
	 *  count => 视频数量
	 * )
	 */
	public function action_getuservideos() {
		$arrReturn = array();
		$uid = $this->request->query("uid");
		$type =  $this->request->query("type");
		$offset = $this->request->query("offset");
		$count = $this->request->query("count");
		if(!$uid) {
			$this->err();
		}
		if($offset===NULL) {
			$offset = 0;
		}
		if($count===NULL) {
			$count = 10;
		}
		if($type===NULL) {
			$type = Model_Data_UserVideo::TYPE_COMMENTED;
		}
		
		try {
			$arrReturn = $this->objLogicUser->getUserVideos($uid, $type, $offset, $count);
		} catch (Model_Logic_Exception $e) {
			$this->err();
		}
		$this->ok($arrReturn);
	}

	/*查询当前登录用户信息*/
	public function action_getinfo(){
		if(!$this->_uid){			
			$this->err(null, '请先登录');
		} else {
			try{
				$userinfo = $this->objLogicUser->get($this->_uid);
			} catch(Model_Logic_Exception $e) {
				$this->err();
			}
			$this->ok($userinfo);
		}
	}
	/**
	 * 
	 * 邀请好友
	 * 
	 * 
	 */
	public function action_invite() {
		
	}
	/**
	 * 
	 * 发送邀请邮件
	 * 
	 * @param $_GET = array(
	 * 	type => 邀请类型，圈子邀请：circle
	 * 	content => 邮件内容,
	 * 	mail_list => 邮箱列表,","分割,
	 *  circle_id => 圈子ID
	 * )
	 */
	public function action_sendinvite() {
		$emailList = $this->request->query("mail_list");
		$type = $this->request->query("type");
		$content = $this->request->query("content");
		$intCircleId = $this->request->query("circle_id");
		if( !$emailList || !$content || !$type ) {
			$this->err();
		}
		$data = NULL;
		$objLogicCircle = new Model_Logic_Circle();
		switch ($type) {
			case 'circle':
				$arrMailList = explode(",", $emailList);
				$userInfo = $this->objLogicUser->getUserByid($this->_uid);
				$firstInvite = !isset($userInfo['medal']) || !in_array(Model_Data_User::MEDAL_INVITE_FRIEND, $userInfo['medal']);
				$res = $this->objLogicUser->inviteFriendSubscribeCircle($this->_uid, $intCircleId, $content, $arrMailList);
				$objTpl = self::template();
				if($firstInvite) {
					$objTpl->set_filename("circle/invitefriendsucc");
				    
				} else {
					$objTpl->set_filename("circle/invitefriendsuccnomedal");
				}
				$objTpl->circle_info = $objLogicCircle->get($intCircleId);
				$content = $objTpl->render();
				$data["html"] = $content;
				break;
			default:
				$this->err();
		}
		$this->ok($data);
	}
	
	public function action_addtag() {
		$strTag = trim( $this->request->param("tag") );
		$uid = $this->_uid;
		if(!$strTag) {
			$this->err();
		}
		if(mb_strlen($strTag)>10) {
			$this->err(null, '亲，标签太长了！');
		}
		$objBlackword = Model_Logic_Blackword::instance();
		if ($objBlackword->filter($strTag)) {
		    $this->err(null, "文明上网，人人有责", null, null, "sys.forbidden.blackword");
		}
		$arrUserInfo = $this->objLogicUser->getUserByid($uid, true);
		$arrTags = isset($arrUserInfo['tags']) ? $arrUserInfo['tags'] : array();
		if(in_array($strTag, $arrTags)) {
			$this->err(array("total"=>count($arrTags)), '亲，不能添加重复的标签！');
		}
		if(count($arrTags)>=self::USER_TAG_MAX_LIMIT) {
			$this->err(null, '亲，最多只能添加'.self::USER_TAG_MAX_LIMIT.'个TAG！', null, null, "user.tags.maxlimit");
		}
		$arrTags[] = $strTag;
		$res = $this->objLogicUser->modifyUser($uid, array("tags"=>$arrTags));
		if(!$res) {
			$this->err( array('total' => count($arrTags)-1));
		}
		$this->objLogicUser->changeSession($uid);
		$arrUserInfo = Session::instance()->get('user');
		$arrTags = isset($arrUserInfo['tags']) ? $arrUserInfo['tags'] : array();
		$this->ok(array("total"=>count($arrTags)));
		
	}
	
	public function action_removetag() {
		$strTag = trim( $this->request->param("tag") );
		$uid = $this->_uid;
		if(!$strTag) {
			$this->err();
		}
		$objModelUser = new Model_Data_User();
		$query = array(
			"_id" => $this->_uid
		);
		$res = $objModelUser->removeFromSet($query, "tags", html_entity_decode($strTag));
		if(!$res) {
			$this->err();
		}
		$this->objLogicUser->changeSession($uid);
		$arrUserInfo = Session::instance()->get('user');
		$arrTags = isset($arrUserInfo['tags']) ? $arrUserInfo['tags'] : array();
		$this->ok(array("total"=>count($arrTags)));
	}
	
	public function action_addfeedback() {
		if($this->request->method()!="POST") {
			$this->objLogicUser->logFeedbackCount();
			$objTpl = self::template();
			$objTpl->set_filename("user/feedback");
			$content = $objTpl->render();
			$data["html"] = $content;
			$this->ok($data);
		}
		$arrRules = array(
            '@content' => array(
                    'datatype' => 'text',
                    'reqmsg' => '内容',
					'minlength' => 1,
                    'maxlength' => 500,
            ),
	    );
	    $arrPost = $this->request->post();
	    $arrPost["content"] = trim($arrPost["content"]);
		$objValidation = new Validation($arrPost, $arrRules);
	    if (! $this->valid($objValidation)) {
	        return;
	    }
		$content = trim($arrPost["content"]);
		$arrExtra = array(
			"user_id" => $this->_uid,
		);
		$objModelFeedback = new Model_Data_Feedback();
		$res = $objModelFeedback->addFeedback($content, $arrExtra);
		if(!$res) {
			$this->err();
		}
		$this->ok();
	}
	
	/**
	 * 关注用户
	 */
	public function action_dofollow()
	{
	    $this->_needLogin();
	    
	    $following = (int) $this->request->param('following');
	    if ($following <= 0) {
	        $this->err(null, 'invalid following');
	    }
	    $hidden = (bool) $this->request->param('hidden', false);
	    
	    $modelLogicUser = new Model_Logic_User();	    
	    try {
	        $modelLogicUser->follow($this->_uid, $following, $hidden, $this->_user['nick']);
	        if (! $hidden) {
	            $objMsg = new Model_Logic_Msg();
	            $objMsg->sendFollowMsg($this->_uid, $this->_user['nick'], $following);
	        }
	    } catch (Model_Logic_Exception $e) {
	        $this->err(array('err' => $e->getCode(), 'msg' => $e->getMessage()));
	    }
	    $this->ok();
	}
	
	/**
	 * 取消关注
	 */
	public function action_unfollow()
	{
	    $this->_needLogin();
	    
	    $following = (int) $this->request->param('following');
	    if ($following <= 0) {
	        $this->err(null, 'invalid following');
	    }
	    
	    $modelLogicUser = new Model_Logic_User();
	    try {
	        $modelLogicUser->unfollow($this->_uid, $following);
	    } catch (Model_Logic_Exception $e) {
	        $this->err(array('err' => $e->getCode(), 'msg' => $e->getMessage()));
	    }
	    $this->ok();
	}
	
	/**
	 * 正在关注的用户
	 */
	public function action_followings()
	{
	    $this->_needLogin();
	    
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 50);
	    $bidirectional = $this->request->param('bidirectional');
	    if (!is_null($bidirectional)) {
	        $bidirectional = (bool) $bidirectional;
	    }
	    $hidden = $this->request->param('hidden');
	    if (!is_null($hidden)) {
	        $hidden = (bool) $hidden;
	    }
	    
	    $modelLogicUser = new Model_Logic_User();
	    try {
	        $total = 0;
	        $followings = $modelLogicUser->followings($this->_uid, $offset, $count, 
	            $bidirectional, $hidden, $total);
	        $users = $modelLogicUser->getMulti(Arr::pluck($followings, 'following'), 
	            true);
	    } catch (Model_Logic_Exception $e) {
	        $this->err(array('err' => $e->getCode(), 'msg' => $e->getMessage()));
	    }
	    $this->ok(array('data' => array_values($users), 'total' => $total));
	}
	
	/**
	 * 粉丝
	 */
	public function action_followers()
	{
	    $this->_needLogin();
	    
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 50);
	    
	    $modelLogicUser = new Model_Logic_User();
	    try {
	        $total = 0;
	        $followers = $modelLogicUser->followers($this->_uid, $offset, $count, 
	            null, false, $total);
	        $users = $modelLogicUser->getMulti(Arr::pluck($followers, 'user'), 
	            true);
	    } catch (Model_Logic_Exception $e) {
	        $this->err(array('err' => $e->getCode(), 'msg' => $e->getMessage()));
	    }
	    $this->ok(array('data' => array_values($users), 'total' => $total));
	}
	
	/**
	 * 获取用户的动态
	 */
	public function action_userfeed() {
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', self::USER_FEED_PAGE_COUNT);
	    $lasttime = (int) $this->request->param('tm', 0);
	    $format = $this->request->param('format');
	    $intSubtype = (int) $this->request->param('type', 0);
	    $uid = (int) $this->request->param('uid', 0);
	    if ($uid < 1) {
	        $this->_needLogin();
	        $uid = $this->_uid;
	    }
	    $bolNoReduce = false;
	    if ($uid == $this->_uid && $intSubtype == Model_Logic_Feed2::SUBTYPE_SELF) {
	        $bolNoReduce = true;
	    }
	    
	    $objFeed2 = new Model_Logic_Feed2();
	    try {
	        $arrData = $objFeed2->getUserFeedList(array('user_id' => $uid, 'offset' => $offset, 'count' => $count,
		    'lasttime' => $lasttime, 'type' => $intSubtype, 'no_reduce' => $bolNoReduce));
	    } catch (Exception $e) {
	        $this->err();
	    }
	     
	    if ($format == 'json') {
	        $this->ok($arrData);        
	    } else {
	        if (! empty($arrData) && ! empty($arrData['data'])) {
	            $objTpl = self::template();
	            $objTpl->set_filename("user/userfeed");
	            $objTpl->set('feeds', $arrData);
	            $strHtml = $objTpl->render();
	        } else {
	            $strHtml = '';
	        }
	        $this->ok(array('has_more' => (bool) @$arrData['has_more'], 'data' => $strHtml));
	    }	    	    
	}
	
	/**
	 * 登录后header上的消息和feed的提示框
	 */
	public function action_headerfeedtip() {
	    $this->_needLogin();
	    $arrMsgCountInfo = null;
	    Profiler::startMethodExec();
	    $intUnreadMsgNum = Model_Logic_User::getUserMsgCount($this->_uid, Model_Logic_User::$USER_UNREAD_MSG_COUNT_KEY, $arrMsgCountInfo);
	    Profiler::endMethodExec('Model_Logic_User::getUserMsgCount');
	    
		$objFeed2 = new Model_Logic_Feed2();
	    try {
	    	Profiler::startMethodExec();
	        $arrData = $objFeed2->getUserFeedList(array('user_id' => $this->_uid, 'offset' => 0, 'count' => 20,
		    'lasttime' => time()));
	        Profiler::endMethodExec('Model_Logic_Feed2::getUserFeedList');
	        if (isset($arrData['data']) && is_array($arrData['data']) && count($arrData['data']) > 8) {
	            $arrData['data'] = array_slice($arrData['data'], 0, 8, true);
	        }
	    } catch (Exception $e) {
	        $this->err();
	    }

        $objTpl = self::template();
        $objTpl->set_filename("user/headerfeedtip");
        $objTpl->set('feeds', $arrData);
        $objTpl->set('msg_count', $arrMsgCountInfo);
        $strHtml = $objTpl->render();
	    $arrRet = array('unreadmsgnum' => $intUnreadMsgNum, 'data' => $strHtml,);
        $this->ok($arrRet);       
	}
	
	/**
	 * 鼠标放在用户名/头像上时的弹出层
	 * @param array $_GET = array(
	 * 	id => 用户UID,
	 * )
	 */
	public function action_userinfotip() {
		$uid = (int) $this->request->param("id");
		if($uid < 1) {
			$this->err();
		}
		Profiler::startMethodExec();
		$userInfo = $this->objLogicUser->get($uid);
		Profiler::endMethodExec(__FUNCTION__.' user get');
		if(! $userInfo) {
			$this->err();
		}
		if ($this->_uid && $uid != $this->_uid) {
			$userInfo['is_fans'] = $this->objLogicUser->isFollowing($this->_uid, $uid);
		}
		$isAdmin = $this->_uid == $uid;

		$objTpl = self::template();
		$objTpl->set('user_info', $userInfo);
		$objTpl->set('is_admin', $isAdmin);
		$strHtml = $objTpl->render();
		$this->ok(array('data' => $strHtml));
	}
	
	/**
	 * 获取指定时间点之后的新feed个数
	 * 
	 * @param tm 时间戳，单位秒，表示这个时间点之后的新feed数目
	 */
	public function action_newfeednum() {
	    $this->_needLogin();    
	    $lasttime = (int) $this->request->param('tm', 0);
	    $objFeed2 = new Model_Logic_Feed2();
	    try {
	        $intNum = $objFeed2->getNewUserFeedNum(array('user_id' => $this->_uid, 'lasttime' => $lasttime));
	    } catch (Exception $e) {
	        $intNum = 0;
	    }
	    $this->ok($intNum);
	}

	/**
	 * 转发feed
	 * @param array $_POST = array(
	 * 	rootfid => 原始feed id,
	 *  curfid => 当前feed id
	 * 	content => 用户输入的文本
	 * 	orig_feed_data => 显示原始feed的附加数据
	 * )
	 */
	public function action_doforwardfeed() {
	    $this->_needLogin();
	    $intRootFid = (int) $this->request->param('rootfid', 0);
	    $intCurFid  = (int)  $this->request->param('curfid', 0);
	    $strContent = trim($this->request->param('curtext'));
	    $strOrigFeedData = trim($this->request->param('orig_feed_data'));
	    if ($intRootFid < 1 || $intCurFid < 1 || mb_strlen($strContent, 'utf-8') > Model_Logic_Feed2::FORWARD_FEED_TEXT_MAX_LEN) {
	        $this->err(null, '参数错误');
	    }
	    if (Model_Logic_Blackword::instance()->filter($strContent)) {
	        $this->err(null, '您转发的部分内容可能不符合社区规范，请尝试删除一些关键字');
	    }
	    $objFeed2 = new Model_Logic_Feed2();
	    try {
	        $mixedRet = $objFeed2->addFeedForward($this->_user, $intRootFid, $intCurFid, $strContent, array('orig_feed_data' => $strOrigFeedData));
	    } catch (Exception $e) {
	        $mixedRet = false;
	        JKit::$log->warn($e->getMessage(), $this->request->post(), $e->getFile(), $e->getLine());
	    }
	    if ($mixedRet) {
	        $this->ok();
	    } else {
	        $this->err(null, '服务器繁忙，内容无法发送，请稍后重试');
	    }
	}
	/**
	 * 
	 * 编辑圈子
	 */
	public function action_editcircle() {
		$cid = (int) $this->request->param("cid");
		$refer = (string) $this->request->param("refer", '/user/circle?type=2');
		$uid = $this->_uid;
		$objLogicCircle = new Model_Logic_Circle();
		$objModelCircle = new Model_Data_Circle();
		$arrCircleInfo = $objModelCircle->get($cid);
		if(!$arrCircleInfo || $arrCircleInfo['creator']!=$uid 
		|| $arrCircleInfo['status']==Model_Data_Circle::STATUS_DELETED) {
			$this->request->redirect('/user/circle?type=2');
		}
		if($this->request->method()!=Request::POST) {
			$this->template->set("cat_list", Model_Data_Circle::$categorys);
			$this->template->set("circle_info", $arrCircleInfo);
			$this->template->set("refer", $refer);
			return;
		}
		$arrRules = array(
            '@title' => array(
                    'datatype' => 'text',
                    'reqmsg' => '圈子名',
                    'maxlength' => 32,
            ),
            '@cat' => array(
                    'datatype' => 'n',
                    'reqmsg' => '圈子分类ID',
                    'minvalue' => 0,
            )
	    );
	    $objBlackword = Model_Logic_Blackword::instance();
	    $arrPost = $this->request->post();
//	    $arrPost = $this->request->query();
		$objValidation = new Validation($arrPost, $arrRules);
	    if (! $this->valid($objValidation)) {
	        return;
	    }
	   	$arrPost['title'] = trim($arrPost['title']);
	   	$arrPost['cat'] = intval($arrPost['cat']);
	   	$arrPost['tags'] = isset($arrPost['tags']) ? trim($arrPost['tags']) : "";
		if ($objBlackword->filter($arrPost['title'])) {
			$this->err(null, array('title'=>"内容包含敏感词"), null, null, "usr.submit.valid");
		}
		if ($objBlackword->filter($arrPost['tags'])) {
			$this->err(null, array('tags'=>"内容包含敏感词"), null, null, "usr.submit.valid");
		}
		//检测圈子名称是否存在
		if( $objLogicCircle->getCircleByTitle($arrPost['title'], $uid, $cid)) {
			$this->err(null, array('title'=>"已存在"), null, null, "usr.submit.valid");
		}
	   	$arrTags = $this->filterTags( explode(",", $arrPost['tags']) );

	   	if( !$this->validTagsLength($arrTags) ) {
	   		$this->err(null, array('tags'=>"亲，单个标签不能超过10个中英文"), null, null, "usr.submit.valid");
	   	}
	   	if(count($arrTags)>10) {
	   		$this->err(null, array('tags'=>"超过10个了"), null, null, "usr.submit.valid");
	   	}
	   	$arrParams = array(
	   		"title" => $arrPost['title'],
	   		"category" => $arrPost['cat'],
	   		"tag" => $arrTags
	   	);
	   	$res = $objLogicCircle->modify($cid, $arrParams);
	   	if(!$res) {
	   		$this->err(null, "修改失败");
	   	} else {
	   		$this->ok(null, isset($arrPost['refer']) ? $arrPost['refer'] : '/user/circle?type=2');
	   	}
	}
	/**
	 * 
	 * 删除圈子
	 */
	public function action_removecircle() {
		$cid = (int) $this->request->param("cid");
		$uid = $this->_uid;
		$objLogicCircle = new Model_Logic_Circle();
		$arrCircleInfo = $objLogicCircle->get($cid);
		if(!$arrCircleInfo || $arrCircleInfo['creator']!=$uid) {
			$this->err(NULL, "圈子不存在！");
		}
		$res = $objLogicCircle->delete($cid);
		if($res) {
			$this->ok();
		} else {
			$this->err(NULL, "删除失败");
		}
	}
	
	/**
	 * 
	 * 验证敏感词
	 * 
	 * @param array $_GET = array(
	 * 	word => 词语
	 * )
	 * 
	 */
	public function action_blackword() {
		$word = $this->request->param("word");
		if (Model_Logic_Blackword::instance()->filter($word)) {
		    $this->err(null, '内容包含敏感词', null, null, "sys.forbidden.blackword");
		}
	    $this->ok();
	}
	
	public function action_getheadcontent() {
		$strPage = $this->request->param("page");
		$objTpl = self::template();
		$objTpl->set_filename("user/header");
		if( isset($_SERVER['HTTP_REFERER']) ) {
			$tmpPath = parse_url($_SERVER['HTTP_REFERER']);
			$fPath = $tmpPath['path'];
			if(isset($tmpPath['query'])) {
				$fPath.= "?".$tmpPath['query'];
			}
//			$strTargetUrl = preg_replace('/^\//', '', $fPath);
			if( $strPage ) {
				$strPage = preg_match("/\/v\/[^\.]+\.html/i", $fPath) ? "play" : "";
			}
			$objTpl->set("http_refer", $fPath);
			$objTpl->set("current_page", $strPage);
		}
		$content = $objTpl->render();
		$this->ok( array("html"=>$content) );
	}
	
	private function setTemplateUserVideoNoticeMsg($uid, $type, $isAdmin) {
		$strMsg = "";
		$objModelUserVideo = new Model_Data_UserVideo($uid);
		$realTypeList = array(
			'commented' => Model_Data_UserVideo::TYPE_COMMENTED,
			'watched' => Model_Data_UserVideo::TYPE_WATCHED,
			'mooded' => Model_Data_UserVideo::TYPE_MOODED,
			'watch_later' => Model_Data_UserVideo::TYPE_WATCHLATER,
			'circled' => Model_Data_UserVideo::TYPE_CIRCLED,
		);
		if($type!="circled") {
			$arrData = $objModelUserVideo->getListByType($realTypeList[$type], 0, 1);
			if($arrData['count']) {
				return ;
			}
		} else {
			$objLogicCircleVideo = new Model_Logic_CircleVideo();
			$total = 0;
			$objLogicCircleVideo->circledVideosByUser($uid, 0, 1, $total);
			if($total) {
				return ;
			}
		}
		
		if($isAdmin) {
			switch($type) {
				case 'commented':
					$strMsg = "你还没有对任何视频进行评论哦！";
					break;
				case 'watched':
					$strMsg = "你还没有观看任何视频哦！";
					break;
				case 'mooded':
					$strMsg = "你还没有对任何视频标记心情哦！";
					break;
				case 'watch_later':
					$strMsg = "你还没有添加任何视频到我的收藏哦！";
					break;
				case 'circled':
					$strMsg = "你还没有圈过视频哦！";
					break;
				default:
					
			}
		} else {
			switch($type) {
				case 'commented':
					$strMsg = "他还没有对任何视频进行评论。";
					break;
				case 'watched':
					$strMsg = "他还没有观看任何视频。";
					break;
				case 'mooded':
					$strMsg = "他还没有对任何视频标记心情。";
					break;
				case 'watch_later':
					$strMsg = "";
					break;
				case 'circled':
					$strMsg = "他还没有圈过任何视频。";
					break;
				default:
					
			}
		}
		$this->template->set('notice_msg', $strMsg);
	}
	
	/**
	 * @param array $avatar Array (
            [name] => Water lilies.jpg
            [type] => image/jpeg
            [tmp_name] => /tmp/phpeFK8jV
            [error] => 0
            [size] => 83794
        )
	 */
	private function validAvatar($avatar) {
		$arrReturn = array(
			'ok' => false,
			'msg' => ''
		);
		if(! $avatar['tmp_name']) {
			$arrReturn['msg'] = "头像不能为空";
			return $arrReturn;
		}
		
		if($avatar['error'] !== UPLOAD_ERR_OK) {
			$arrReturn['msg'] = "头像上传失败";
			return $arrReturn;
		}
		
		$arrImgAttr = getimagesize($avatar['tmp_name']);
		$arrValidType = array(IMAGETYPE_GIF => true, IMAGETYPE_PNG => true,
		    IMAGETYPE_JPEG => true);
		if(! is_array($arrImgAttr) || ! isset($arrValidType[$arrImgAttr[2]])) {
			$arrReturn['msg'] = "图片格式错误";
			return $arrReturn;
		}
		$arrReturn['attr'] = $arrImgAttr;

		$maxSizeLimit = 5242880;
		if($avatar['size'] > $maxSizeLimit) {
			$arrReturn['msg'] = "图片大小不能超过5M";
			return $arrReturn;
		} 
		$arrReturn['ok'] = true;
		return $arrReturn;
	}
	
	protected function _formRule($arrField = null) {
	    $arrRules = array(
            '@email' => array(
                   	'datatype' => 'email',
                   	'errmsg' => '邮箱格式错误',
            ),
            '@name' => array(
                    'datatype' => 'reg',
                    'reqmsg' => '用户名',
            		'reg-pattern' => "/^[\u4e00-\u9fa5\_a-zA-Z\d\-0-9]{2,10}$/",
            ),
            '@intro' => array(
                    'datatype' => 'text',
//                    'reqmsg' => '简介',
                    'maxlength' => 100
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
	
	private function setpanellist() {
		$arrPanelList = array(
			'setting' => array(
				'url' => '/user/setting',
				'name' => '个人设置'
			),
			'modifyavatar' => array(
				'url' => '/user/modifyavatar',
				'name' => '修改头像'
			),
//			'modifypassword' => array(
//				'url' => '/user/modifypassword',
//				'name' => '修改密码'
//			),
/* 			'sethomepage' => array(
				'url' => '/user/sethomepage',
				'name' => '制定主页'
			), */
			'mymedal' => array(
				'url' => '/user/mymedal',
				'name' => '我的勋章'
			),
			'syncconnect' => array(
				'url' => '/user/syncconnect',
				'name' => '帐号绑定'
			),
		);
		$action = $this->request->action();
		$this->template->set("panel_list", $arrPanelList);
		$this->template->set("panel_action", $action);
	}
	
	private function avatarCB($isOk, $avatar, $msg='') {
		$err = $isOk?'ok':$msg;
		$data = $isOk? $avatar:NULL;
		$strJson = json_encode(array("err"=>$err, "data"=>$data));
		$html = '<html>
  <head>
  </head>
  <body>  
	<script type="text/javascript">
    	parent.initImgarae('.$strJson.');
    </script>
  </body>
</html>';
		die($html);
	}
	
	private function filterTags($arrTags) {
		$arrReturn = array();
		if(!$arrTags) {
			return $arrTags;
		}
		foreach($arrTags as $tagTmp) {
			$tagTmp = trim($tagTmp);
			if($tagTmp!=="") {
				$arrReturn[] = $tagTmp;
			}
		}
		return array_unique($arrReturn);
	}
	
	private function validTagsLength( $arrTags ) {
		foreach($arrTags as $strTag) {
			if(mb_strlen($strTag)>10) {
				return false;
			}
		}
		return true;
	}
	
	private function gotoRefer( $strTargetUrl, $isParentRedirect=false, $isRedirect=false ) {
		if( $isRedirect ) {
			$this->request->redirect($strTargetUrl);
		}
		$strHtml = '<script>
        if( window.parent && parent != window && parent.XLogin )
        {
            parent.XLogin.fireAction
            (
                {
                    "err": "ok", "url": "'.urlencode($strTargetUrl).'", "msg": ""
                }
            );
        }
		</script>';
		
		if( $isParentRedirect ) {
			if(!strstr($strTargetUrl, "http") && $strTargetUrl[0]!="/") {
				$strTargetUrl = "/".$strTargetUrl;
			}
			$strHtml = '<!doctype html><html><head><meta charset="utf-8"></head><body>'.
				'<script>window.onload = function(){window.parent.location.href= "'.$strTargetUrl.
				'";}</script></body></html>';
		}
		die($strHtml);
	}
	
	private function checkActionNeedLogin( $strAction ) {
		$arrNeedLogin = array(
		    'watchlater' => 1,
			'setting' => 1,
			'modifyavatar' => 1,
			'modifypassword' => 1,
//			'logout' => 1,
			'getcirclelist' =>1,
			'check_password' => 1,
			'invite' => 1,
			'sendinvite' => 1,
 			'mymedal' => 1,
//			'sethomepage' => 1,
			'changeavatar' => 1,
			'addtag' => 1,
			'removetag' => 1,
			'batchsubscribecircles' => 1,
			'editcircle' => 1,
			'removecircle' => 1,
			'getregistercirclelist'=>1,
			'batchsubscribecircles' => 1,
			'getsubscribedcount' => 1,
			'getheadcontent' => 1
		);
		return isset( $arrNeedLogin[$strAction] );
	}
}
