<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 圈子相关页面，以及Ajax请求接口
 * @author wangjiajun
 */
class Controller_Circle extends Controller 
{
	const CIRCLE_FEED_PAGE_COUNT = 20;
	
	public function action_index()
	{
	    $circleId = (int) $this->request->param('id');
	    $tab = (string) $this->request->param('tab', 'video');
	    $strTag = trim($this->request->param('tag', ''));
	    
	    $this->template->set('cur_tab', $tab);
	    $this->template->set('cur_tag', $strTag);
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    Model_Logic_Circle::$basicFields[] = 'filter_tag';
	    $circle = $modelLogicCircle->get($circleId, null, true, array());
	    if (!$circle) {
	        throw new HTTP_Exception_404();
	    }
	    if ($circle['status'] != Model_Data_Circle::STATUS_PUBLIC) {
	        $this->response->alertBack("圈子已删除。");
	        return;	        
	    }
	    // 圈子信息
	    if (isset($circle['filter_tag'])) {
	        $circle['filter_tag'] = $modelLogicCircle->parseFilterTag($circle['filter_tag']);
	    } else {
	        $circle['filter_tag'] = array();
	    }
	    // 圈内视频数
	    $modelLogicRecommend = new Model_Logic_Recommend();
	    $arrRet = $modelLogicRecommend->getCircleVideosByTag($circleId, $circle['title'], 
	        $strTag, 0, 1);
	    $circle['video_count'] = $arrRet['total'];
	    $this->template->set('circle', $circle);
	    
	    // 顶部广告位
	    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	    if (preg_match('/\/circle\/\d+/', $referer)) {
	        if (isset($_COOKIE['hasCloseCircleLogin']) && $_COOKIE['hasCloseCircleLogin']) {
	            $showAd = false;
	        } else {
	            $showAd = true;
	        }
	    } else {
	        $showAd = true;
	    }
	    $this->template->set('showAd', $showAd);
	    if ($showAd) {
    	    $modelAd = new Model_Logic_Ad();
    	    $topAd = $modelAd->getRandAdByPos(Model_Logic_Adconst::AD_POS_CIRCLE_TOP_1);
    	    $this->template->set('topAd', $topAd);
	    }
	    
	    // 相关圈子
	    $relatedCircles = $modelLogicCircle->related(Model_Data_Recommend::RELATED_CIRCLE_RECO_CIRCLE, 
	        $circleId, $this->_uid, 3);
	    $this->template->set('relatedCircles', array_values($relatedCircles['circles']));
	    
	    // 是否已关注圈子
	    $isSubscribed = false;
	    if ($this->_uid) {
	        $modelDataCircleUser = new Model_Data_CircleUser();
	        $isSubscribed = $modelDataCircleUser->isUserSubscribeCircle($this->_uid, 
	            $circleId);
	        $arrCustTag = $modelLogicCircle->getUserTag($this->_uid, $circleId);
	        $this->template->set('cust_tag', $arrCustTag);
	    }
	    $this->template->set('is_subscribed', $isSubscribed);
	    
	    // 热播视频
	    $modelStat = new Model_Logic_Stat();
	    if (isset($circle['official']) && $circle['official']) {
	        // 依次取今日、本周、本月榜
	        $types = array('day', 'week', 'month');
	        foreach ($types as $type) {
    	        $mostPlayedVideos = $modelStat->mostPlayedVideosInCircle($circleId, 
    	            $type, 14);
    	        if (count($mostPlayedVideos) >= 7) {
    	            break;
    	        }
	        }
	        // 少于7个的不显示
	        if (count($mostPlayedVideos) < 7) {
	            $mostPlayedVideos = array();
	        }
	    } else {
	        $mostPlayedVideos = array();
	    }
	    $this->template->set('mostPlayedVideos', array_values($mostPlayedVideos));
	    if ($mostPlayedVideos) {
	        $this->template->set('mostPlayedVideosType', $type);
	    }
	    
	    // 实体
	    $circleEntity = $modelLogicCircle->entity($circleId);
	    $this->template->set('circleEntity', $circleEntity);
	    
	    $this->template->set('feeds_page_count', self::CIRCLE_FEED_PAGE_COUNT);
	    $this->template->set('feeds_lasttime', $_SERVER['REQUEST_TIME']);
	    $this->template->set('forward_text_max_len', Model_Logic_Feed2::FORWARD_FEED_TEXT_MAX_LEN);
	    
	    if ($tab == 'video') {
    	    // 对来自Spider的访问同步输出页面
    	    if (Util::isSpider()) {
    	        $modelLogicRecommend = new Model_Logic_Recommend();
        	    $arrRet = $modelLogicRecommend->getCircleVideosByTag($circleId, $circle['title'], 
        	        $strTag, 0, 50);
        	    $videos = $arrRet['video'];
            	foreach($videos as &$video) {
            		$video['circle'] = array('_id' => $circleId);
            	}
    	        $this->template->set('videos', $videos);
    	    }
	    } elseif ($tab == 'user') {
	        $pageNo = (int) $this->request->param('pn', 0);
	        $pageCount = 36;
	        
	        $modelUser = new Model_Logic_User();
	        $modelCircleUserStat = new Model_Data_CircleUserStatAll();
	        $modelCircleUser = new Model_Data_CircleUser();
	        
	        // 圈主
	        $creator = $modelUser->get($circle['creator'], false);
    	    $this->template->set('creator', $creator);
    	    
    	    // 活跃圈友
	        $activeFollowers = $modelStat->activeUsersInCircle($circleId, 
	            'week', 9);
    	    $this->template->set('activeFollowers', $activeFollowers);
    	    
    	    // 所有粉丝
    	    $docs = $modelCircleUser->find(array(
    	    	'circle_id' => (int) $circleId
    	    ), array('user_id', 'create_time'), array('create_time' => -1), $pageCount, $pageNo * $pageCount);
    	    $followers = $modelUser->getMulti(Arr::pluck($docs, 'user_id'), true, false);
    	    foreach ($docs as $doc) {
    	        if (isset($followers[$doc['user_id']]) && ((time() - $doc['create_time']->sec) <= 7 * 24 * 3600)) {
    	            $followers[$doc['user_id']]['is_new'] = true;
    	        }
    	    }
    	    $this->template->set('followers', $followers);    	    
	    } elseif ($tab == 'feed') {
	    	$objFeed2 = new Model_Logic_Feed2();
	    	$intCount = self::CIRCLE_FEED_PAGE_COUNT;
	    	$intLasttime = $_SERVER['REQUEST_TIME'];
	    	try {
	    		$arrFeed = $objFeed2->getCircleFeedList(array('circle_id' => $circleId, 'user_id' => $this->_uid, 'offset' => 0, 'count' => $intCount,
	    			'lasttime' => $intLasttime));
	    	} catch (Exception $e) {
	    		JKit::$log->warn($e->getMessage(), null, $e->getFile(), $e->getLine());
	    		$arrFeed = array();
	    	}
	    	$this->template->set('feeds', $arrFeed);        
	    }
	}
/* 	
	public function action_old()
	{
	    $circleId = (int) $this->request->param('id');
	    $strTag = trim($this->request->param('tag', ''));
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    Model_Logic_Circle::$basicFields[] = 'filter_tag';
	    $circle = $modelLogicCircle->get($circleId);
	    if (!$circle) {
	        throw new HTTP_Exception_404();
	    }
	    if ($circle['status'] != Model_Data_Circle::STATUS_PUBLIC) {
	        $this->response->alertBack("圈子已删除。");
	        return;	        
	    }
	    // 圈子信息
	    if (isset($circle['filter_tag'])) {
	        $circle['filter_tag'] = $modelLogicCircle->parseFilterTag($circle['filter_tag']);
	    } else {
	        $circle['filter_tag'] = array();
	    }
	    // 圈内视频数
	    if (isset($circle['official']) && $circle['official']) {
    	    $modelLogicRecommend = new Model_Logic_Recommend();
    	    $arrRet = $modelLogicRecommend->getCircleVideosByTag($circleId, $circle['title'], 
    	        $strTag, 0, 1);
    	    $circle['video_count'] = $arrRet['total'];
	    }
	    $this->template->set('circle', $circle);
	    
	    $isSubscribed = false;
	    if ($this->_uid) {
	        $modelDataCircleUser = new Model_Data_CircleUser();
	        $isSubscribed = $modelDataCircleUser->isUserSubscribeCircle($this->_uid, 
	            $circleId);
	        $arrCustTag = $modelLogicCircle->getUserTag($this->_uid, $circleId);
	        $this->template->set('cust_tag', $arrCustTag);
	    }
	    // 是否已关注圈子
	    $this->template->set('is_subscribed', $isSubscribed);
	    $this->template->set('cur_select_tag', $strTag);
	    
	    //圈子最新动态
	    $objLogicFeed = new Model_Logic_Feed();
	    Profiler::startMethodExec();
	    $feed_list = $objLogicFeed->circleFeeds($circleId, 0, NULL, 12);
		Profiler::endMethodExec(__FUNCTION__.' circleFeeds');
	    $this->template->set("circle_feedlist", $feed_list);
	    
	    // 对来自Spider的访问同步输出页面
	    if (Util::isSpider()) {
	        $modelLogicRecommend = new Model_Logic_Recommend();
    	    $arrRet = $modelLogicRecommend->getCircleVideosByTag($circleId, $circle['title'], 
    	        $strTag, 0, 50);
    	    $videos = $arrRet['video'];
        	foreach($videos as &$video) {
        		$video['circle'] = array('_id' => $circleId);
        	}
	        $this->template->set('videos', $videos);
	    }
	} */
	
	/**
	 * 圈内新动态数量
	 */
	public function action_newFeedsCount()
	{
	    $circleId = (int) $this->request->param('id', 0);
	    if ($circleId <= 0) {
	        $this->err(null, 'invalid id');
	    }
	    $tm = (int) $this->request->param('tm', 0);
	    if ($tm <= 0) {
	        $this->err(null, 'invalid tm');
	    }
	    
	    $objFeed2 = new Model_Logic_Feed2();
		try {
	    	$intNum = $objFeed2->getNewCircleFeedNum(array('circle_id' => $circleId, 'lasttime' => $tm));
	    } catch (Exception $e) {
	    	$intNum = 0;
	    }
	    
	    $this->ok($intNum);
	}
	
	/**
	 * 获取圈子feed列表
	 *
	 * @param array $_GET = array(
	 * 	offset => 分页参数，记录的偏移量,
	 * 	count => 分页参数，每页记录数
	 * 	tm => 时间戳，单位秒，表示去这个时间点之前的记录,
	 * 	format => 返回数据格式,
	 * 	circle => 圈子id,
	 * )
	 */
	public function action_morefeed() {
		$offset = (int) $this->request->param('offset', 0);
		$count = (int) $this->request->param('count', self::CIRCLE_FEED_PAGE_COUNT);
		$lasttime = (int) $this->request->param('tm', 0);
		$format = $this->request->param('format');
		$circleId = (int) $this->request->param('circle', 0);
		if ($circleId < 1) {
			$this->err(null, "圈子ID错误");
		}
		 
		$objFeed2 = new Model_Logic_Feed2();
		try {
			$arrFeed = $objFeed2->getCircleFeedList(array('circle_id' => $circleId, 'user_id' => $this->_uid, 'offset' => $offset, 'count' => $count,
				'lasttime' => $lasttime));
		} catch (Exception $e) {
			JKit::$log->warn($e->getMessage(), null, $e->getFile(), $e->getLine());
			$this->err();
		}

		if ($format == 'json') {
			$this->ok($arrFeed);
		} else {
			if (! empty($arrFeed) && ! empty($arrFeed['data'])) {
				$objTpl = self::template();
				$objTpl->set_filename("circle/morefeed");
				$objTpl->set('feeds', $arrFeed);
				$strHtml = $objTpl->render();
			} else {
				$strHtml = '';
			}
			$this->ok(array('has_more' => (bool) @$arrFeed['has_more'], 'data' => $strHtml));
		}
	}
	
	/**
	 * 圈内视频
	 */
	public function action_videos()
	{
	    $circleId = (int) $this->request->param('id');
	    if ($circleId <= 0) {
	        $this->err(null, 'invalid id');
	    }
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 10);
	    $intRank = (int) $this->request->param('r', Model_Logic_Video::VIDEO_RANK_DEFAULT);
	    $strTag = trim($this->request->param('tag', ''));
	    $strTitle = trim($this->request->param('title', ''));
	    if ($offset < 0) {
	        $offset = 0;
	    }
	    if ($count < 1) {
	        $count = 10;
	    }
	    if (! isset(Model_Logic_Video::$arrVideoRankField[$intRank])) {
	        $intRank = Model_Logic_Video::VIDEO_RANK_DEFAULT;
	    }
	    
	    $modelLogicRecommend = new Model_Logic_Recommend();
	    $arrRet = $modelLogicRecommend->getCircleVideosByTag($circleId, $strTitle, $strTag, $offset, $count, $intRank);
	    $arrVideo = $arrRet['video'];
    	foreach($arrVideo as $k => $row) {
    		$arrVideo[$k]['circle'] = array('_id' => $circleId);
    	}
	    $this->ok(array('total' => (int) $arrRet['total'], 'data' => $arrVideo));
	}
	
	/**
	 * 圈内热播视频
	 */
	public function action_mostPlayedVideos()
	{
	    $circleId = (int) $this->request->param('circle');
	    $type = (string) $this->request->param('type', 'day');
	    $count = (int) $this->request->param('count', 14);
	    
	    $modelStat = new Model_Logic_Stat();
	    $mostPlayedVideos = $modelStat->mostPlayedVideosInCircle($circleId, $type, $count);
	    $this->ok(array_values($mostPlayedVideos));
	}
	
	/**
	 * 圈友
	 */
	public function action_followers()
	{
	    $circleId = (int) $this->request->param('circle');
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 36);
	    
        $modelCircleUser = new Model_Data_CircleUser();
	    $modelUser = new Model_Logic_User();
	    
	    $docs = $modelCircleUser->find(array(
	    	'circle_id' => (int) $circleId
	    ), array('user_id'), array('create_time' => -1), $count, $offset);
	    $followers = $modelUser->getMulti(Arr::pluck($docs, 'user_id'), true, false);
	    
	    $this->ok(array_values($followers));
	}
	
	/**
	 * 关注圈子
	 */
	public function action_subscribe()
	{
	    $this->_needLogin();
	    
	    $circleId = (int) $this->request->param('id');
	    if ($circleId <= 0) {
	        $this->err(null, 'invalid id');
	    }
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    try {
	        $modelLogicCircle->subscribe($circleId, $this->_uid);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    
	    $this->ok();
	}
	
	/**
	 * 批量关注圈子
	 * 
	 * @param $_GET = array(
	 * 	ids => 圈子id列表，","分割
	 * )
	 * 
	 * @return ok
	 */
	public function action_batchsubscribe()
	{
	    $this->_needLogin();
	    
	    $tmpIds =  $this->request->param('ids');
	    
	    if ( !$tmpIds ) {
	        $this->err();
	    }
	    $arrCircleIds = explode(",", $tmpIds);
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
	    
	    $this->ok();
	}
	
	/**
	 * 取消关注圈子
	 */
	public function action_unsubscribe()
	{
	    $this->_needLogin();
	    
	    $circleId = (int) $this->request->param('id');
	    if ($circleId <= 0) {
	        $this->err(null, 'invalid id');
	    }
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    try {
	        $modelLogicCircle->unsubscribe($circleId, $this->_uid);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    
	    $this->ok();
	}
	
	/**
	 * 已关注的圈子
	 */
	public function action_subscribed()
	{	    
	    $userId = (int) $this->request->param('user', $this->_uid);
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 10);
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    try {
	        $result = $modelLogicCircle->subscribed($userId, $offset, $count);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    
	    $this->ok(array('total' => $result['total'], 'data' => array_values($result['data'])));
	}
	
	/**
	 * 圈子浏览页
	 */
	public function action_browse()
	{
	    $strRouteName = $this->request->param('route_name');
	    if ($strRouteName != 'circle_cat') {
	        //兼容老的URL，做301跳转，2012-12-31之后可以删除这个判断逻辑
	        $arrGetParam = $this->request->query();
	        $intCat = (int) $this->request->param('cat', 0);
	        $strCat = @Model_Data_Circle::$arrUrlKeyForCategorys[$intCat];
	        if (! $strCat) $strCat = 'all';
	        $strTag = trim($this->request->param('tag', ''));
	        $strTmp = "/category/{$strCat}";
	        if (strlen($strTag) > 0) $strTmp .= "/$strTag";
	        unset($arrGetParam['cat']);
	        unset($arrGetParam['tag']);
	        if (! empty($arrGetParam)) $strTmp .= '?' . http_build_query($arrGetParam);
	        $this->request->redirect($strTmp, 301);
	        exit();
	    }
	    
	    $strCat = trim($this->request->param('cat', 'all'));	    
	    $arrCatToIdMap = array_flip(Model_Data_Circle::$arrUrlKeyForCategorys);
	    if (isset($arrCatToIdMap[$strCat])) {
	        $intCat = (int) $arrCatToIdMap[$strCat];
	    } else {
	        $intCat = 0;
	        $strCat = 'all';
	    }
	    //$intRank = $this->request->param('r') == Model_Logic_Circle::RANK_HOT ? Model_Logic_Circle::RANK_HOT : Model_Logic_Circle::RANK_NEW;
	    $strTag = trim($this->request->param('tag', ''));
	    $intRank = Model_Logic_Circle::RANK_HOT;
	    $intOffset = (int) $this->request->param('offset', 0);
	    $intCount = 12; //每页16个
	    if (! isset(Model_Data_Circle::$categorys[$intCat])) {
	        $intCat = 0; //全部分类
	        $strCurCatName = '';
	    } else {
	        $strCurCatName = Model_Data_Circle::$categorys[$intCat];
	    }
	    if ($intOffset < 0) {
	        $intOffset = 0;
	    }
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    $circles = $modelLogicCircle->groupByCategory($intCat, $intRank, $intCount, $intOffset, (int) $this->_uid, $strTag);
	    $this->template->set('circle_list', $circles);
	    $this->template->set('pager', array('count' => $intCount, 'offset' => $intOffset, 'total' => $circles['total']));
	    $this->template->set('cur_cat_name', $strCurCatName);
	    $this->template->set('cur_cat_key', $strCat);
	    $this->template->set('cur_cat_id', $intCat);
	    $this->template->set('cur_select_tag', $strTag);
	    $this->template->set('cat_sys_tag', $modelLogicCircle->getCatSysTag($intCat));
	    if ($this->_uid) {
	        $this->template->set('cat_user_tag', $modelLogicCircle->getCatTag($this->_uid, $intCat));
	    }
	}
	/**
	 * 
	 * 邀请好友加入圈子
	 * 
	 * @param $_GET = array(
	 * 	circle_id => 圈子ID,
	 * )
	 */
	public function action_invitefriend() {
		$this->_needLogin();
		$intCircleId = $this->request->query("circle_id");
		
		if(!$intCircleId) {
			$this->err();
		}
		$intCircleId = intval($intCircleId);
		$modelLogicCircle = new Model_Logic_Circle();
		$circleInfo = $modelLogicCircle->get($intCircleId);
		if(!$circleInfo) {
			$this->err();
		}
		$inviteCode = Model_Logic_User::buildInviteUrl($this->_uid, "circle", array("circle_id"=>$circleInfo["_id"]));
		$objTpl = self::template();
		$objTpl->set_filename("circle/invitefriend");
		$objTpl->invite_code = $inviteCode;
	    $objTpl->circle_info = $circleInfo;
		$content = $objTpl->render();
		$this->ok(array("html"=>$content));
	}
	/**
	 * 
	 * Enter description here ...
	 * @param $_GET = array(
	 * 	circle_id => 圈子ID,
	 * )
	 */
	public function action_share() {
		$this->_needLogin();
		$intCircleId = $this->request->query("circle_id");
		if(!$intCircleId) {
			$this->err();
		}
		$intCircleId = intval($intCircleId);
		$modelLogicCircle = new Model_Logic_Circle();
		$circleInfo = $modelLogicCircle->get($intCircleId);
		if(!$circleInfo) {
			$this->err();
		}
		JKit::addAfterRequestFinish(function ($userId, $circleId) use ($modelLogicCircle) {
		    $modelLogicCircle->share($userId, $circleId);
		}, $this->_uid, $intCircleId);		
		
		#TODO 用户分享了圈子
		$objTpl = self::template();
		$objTpl->set_filename("circle/share");
	    $objTpl->circle_info = $circleInfo;
		$content = $objTpl->render();
		$this->ok(array("html"=>$content));
	}
	
	/**
	 * 关注Ta
	 */
	public function action_concern()
	{
	    $this->_needLogin();
	
	    $title = (string) $this->request->param('title');
	    $title = mb_substr(trim($title), 0, 50);
	    if (strlen($title) == 0) {
	        $this->err(null, null, null, null,  'circle.title_invalid');
	    }
	    
	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->getByTitle($title);
	    if (!$circle) {
	        $this->err(null, null, null, null,  'circle.not_exist');
	    }
	    
	    $modelDataCircleUser = new Model_Data_CircleUser();
	    if ($modelDataCircleUser->isUserSubscribeCircle($this->_uid, $circle['_id'])) {
	        $this->err($circle, null, null, null, 'circle.already_subscribed');
	    }
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    try {
	        $modelLogicCircle->subscribe($circle['_id'], $this->_uid);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    $this->ok($circle);
	}
	
	/**
	 * 申请创建圈子
	 */
	public function action_applyToCreate()
	{	    
	    $this->_needLogin();
	    
	    $title = (string) $this->request->param('title');
	    $title = mb_substr(trim($title), 0, 50);
	    if (strlen($title) == 0) {
	        $this->err(null, 'title invalid');
	    }
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    try {
	        $id = $modelLogicCircle->createCandidateCircle($title, Model_Data_CircleCandidate::SOURCE_USER_SUBMIT, 
	            $this->_uid);
	    } catch (Exception $e) {
	        $this->err(null, $e->getMessage());
	    }
	    
	    $this->ok();
	}
	
	/**
	 * 相关圈子
	 */
	public function action_related()
	{
	    $this->_needLogin();
	    
	    $type = (int) $this->request->param('type', Model_Data_Recommend::RELATED_CIRCLE_RECO_SUBSCRIBE);
	    $query = (string) $this->request->param('query');
        if ($type == Model_Data_Recommend::RELATED_CIRCLE_RECO_SUBSCRIBE) {
	        $query = mb_substr(trim($query), 0, 20);
        } elseif ($type == Model_Data_Recommend::RELATED_CIRCLE_RECO_VIDEO) {
            if (strlen($query) != 32) {
                $this->err(null, 'invalid video id');
            }
        } elseif ($type == Model_Data_Recommend::RELATED_CIRCLE_RECO_CIRCLE) {
            $query = (int) $query;
        }
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 3);
	    $format = (string) $this->request->param('format', 'json');
	    		
	    $modelCircle = new Model_Logic_Circle();
        $result = $modelCircle->related($type, $query, $this->_uid, $count, $offset);

		if($format == 'json') {
			$this->ok(array(
				'type' => $result['type'],
				'circles' => array_values($result['circles']) 
			));
		}
	}
	
	/**
	 * 给圈子添加私有tag
	 * @param tag tag内容
	 * @param circle 圈子id
	 */
	public function action_addTag()
	{
	    $this->_needLogin();
	    $arrRules = array(
            '@tag' => array(
                    'datatype' => 'text',
                    'reqmsg' => '标签',
                    'maxlength' => 10,
            ),
            '@circle' => array(
                    'datatype' => 'n',
                    'reqmsg' => '圈子ID',
                    'minvalue' => 1,
            ),
	    );
	    $arrPost = $this->request->post();
	    $arrPost['tag'] = trim($arrPost['tag']);
	    $objValidation = new Validation($arrPost, $arrRules);
	    if (! $this->valid($objValidation)) {
	        return;
	    }
	    $objCircle = new Model_Logic_Circle();
	    $circle = $objCircle->get($arrPost['circle']);
	    if ($this->_uid == $circle['creator']) {
	        $result = $objCircle->addFilterTag($arrPost['circle'], $arrPost['tag']);
	        if ($result === false) {
	            $this->err('标签添加失败');
	        } else {
    	        $this->ok(array('total' => $result));
	        }
	    } else {
	        $arrRet = $objCircle->addUserTag(array('user_id' => $this->_uid, 
	        	'circle_id' => (int) $arrPost['circle'], 'tag' => $arrPost['tag']));
    	    if ($arrRet && $arrRet['ret']) {
    	        $this->ok(array('total' => (int) $arrRet['total']));
    	    } else {
    	        if (isset($arrRet['msg'])) {
    	            $strMsg = $arrRet['msg'];
    	        } else {
    	            $strMsg = '标签添加失败';
    	        }
    	        $this->err(array('total' => (int) $arrRet['total']), $strMsg);
    	    }	    
	    }
	}
	
	/**
	 * 删除圈子的私有tag
	 * @param tag tag内容
	 * @param circle 圈子id
	 */
	public function action_delTag()
	{
		$this->_needLogin();
	    $arrRules = array(
            '@tag' => array(
                    'datatype' => 'text',
                    'reqmsg' => '标签',
                    'maxlength' => 10,
            ),
            '@circle' => array(
                    'datatype' => 'n',
                    'reqmsg' => '圈子ID',
                    'minvalue' => 1,
            ),
	    );
	    $arrPost = $this->request->post();
	    $arrPost['tag'] = trim($arrPost['tag']);
	    $objValidation = new Validation($arrPost, $arrRules);
	    if (! $this->valid($objValidation)) {
	        return;
	    }
	    $objCircle = new Model_Logic_Circle();
	    $circle = $objCircle->get($arrPost['circle']);
	    if ($this->_uid == $circle['creator']) {
	        $result = $objCircle->deleteFilterTag($arrPost['circle'], $arrPost['tag']);
	        if ($result === false) {
	            $this->err('Tag删除失败');
	        } else {
    	        $this->ok(array('total' => $result));
	        }
	    } else {
    	    $arrRet = $objCircle->delUserTag(array('user_id' => $this->_uid, 'circle_id' => (int) $arrPost['circle'], 'tag' => $arrPost['tag']));
    	    if ($arrRet && $arrRet['ret']) {
    	        $this->ok(array('total' => (int) $arrRet['total']));
    	    } else {
    	        $this->err(array('total' => (int) $arrRet['total']), 'Tag删除失败');
    	    }
	    }
	}

	/**
	 * 给圈子分类添加私有tag
	 * @param tag tag内容
	 * @param cat 圈子分类id
	 */
	public function action_addCatTag()
	{
	    $this->_needLogin();
	    $arrRules = array(
            '@tag' => array(
                    'datatype' => 'text',
                    'reqmsg' => '标签',
                    'maxlength' => 10,
            ),
            '@cat' => array(
                    'datatype' => 'n',
                    'reqmsg' => '圈子分类ID',
                    'minvalue' => 0,
            ),
	    );
	    $arrPost = $this->request->post();
	    $arrPost['tag'] = trim($arrPost['tag']);
	    $objValidation = new Validation($arrPost, $arrRules);
	    if (! $this->valid($objValidation)) {
	        return;
	    }
	    $objCircle = new Model_Logic_Circle();
	    $arrRet = $objCircle->addCatTag(array('user_id' => $this->_uid, 'cat' => (int) $arrPost['cat'], 'tag' => $arrPost['tag']));
	    if ($arrRet && $arrRet['ret']) {
	        $this->ok(array('total' => (int) $arrRet['total']));
	    } else {
	        if (isset($arrRet['msg'])) {
	            $strMsg = $arrRet['msg'];
	        } else {
	            $strMsg = '标签添加失败';
	        }
	        $this->err(array('total' => (int) $arrRet['total']), $strMsg);
	    }
	}
	
	/**
	 * 删除圈子分类的私有tag
	 * @param tag tag内容
	 * @param cat 圈子分类id
	 */
	public function action_delCatTag()
	{
	    $this->_needLogin();
	    $arrRules = array(
            '@tag' => array(
                    'datatype' => 'text',
                    'reqmsg' => '标签',
                    'maxlength' => 10,
            ),
            '@cat' => array(
                    'datatype' => 'n',
                    'reqmsg' => '圈子分类ID',
                    'minvalue' => 0,
            ),
	    );
	    $arrPost = $this->request->post();
	    $arrPost['tag'] = trim($arrPost['tag']);
	    $objValidation = new Validation($arrPost, $arrRules);
	    if (! $this->valid($objValidation)) {
	        return;
	    }
	    $objCircle = new Model_Logic_Circle();
	    $arrRet = $objCircle->delCatTag(array('user_id' => $this->_uid, 'cat' => (int) $arrPost['cat'], 'tag' => $arrPost['tag']));
	    if ($arrRet && $arrRet['ret']) {
	        $this->ok(array('total' => (int) $arrRet['total']));
	    } else {
	        $this->err(array('total' => (int) $arrRet['total']), 'Tag删除失败');
	    }
	}
	
	public function action_addcircle() {
		$this->_needLogin();
		$objLogicCircle = new Model_Logic_Circle();
		$uid = $this->_uid;
		if($this->request->method()!=Request::POST) {
			$objTpl = self::template();
			$objTpl->set_filename("circle/addcircle");
			$objTpl->cat_list = Model_Data_Circle::$categorys;
			$content = $objTpl->render();
			$this->ok( array("html"=>$content) );	
		}
		$arrRules = $this->_formRule(array('@title', '@cat'));
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
		if( $objLogicCircle->getCircleByTitle($arrPost['title'], $uid)) {
			$this->err(null, array('title'=>"已存在"), null, null, "usr.submit.valid");
		}
	   	$arrTags = $this->filterTags( explode(",",$arrPost['tags']) );
		if( !$this->validTagsLength($arrTags) ) {
	   		$this->err(null, array('tags'=>"亲，单个标签不能超过10个中英文"), null, null, "usr.submit.valid");
	   	}
	   	if(count($arrTags)>10) {
	   		$this->err(null, array('tags'=>"超过10个了"), null, null, "usr.submit.valid");
	   	}
	   	try {
		   	$res = $objLogicCircle->create($arrPost['title'], $arrPost['cat'], $uid, $arrTags);
		   	
		   	if(!$res) {
		   		$this->err(null, "创建失败");
		   	} else {
		   	    $modelDataCircle = new Model_Data_Circle();
		   	    $modelDataCircle->setSlaveOkay(false);
		   	    $circle = $modelDataCircle->get($res);
		   		$this->ok(array("cid"=>$res), Util::circleUrl($res, null, $circle));
		   	}	
	   	} catch (Model_Logic_Exception $e) {
	   		$this->err(null, $e->getMessage());
	   	}
	   	
	}
	/**
	 * 
	 * 检测圈子名称是否被使用
	 * @param array $_GET = array(
	 * 	title => 圈子名称
	 * 	cid => 圈子ID，创建，不用填
	 * )
	 */
	public function action_is_circle_reged() {
		$title = trim( $this->request->param('title') );
		$intCircleId = $this->request->param('cid');
		if($intCircleId!==NULL) {
			$intCircleId = intval($intCircleId);
		}
		if(!$title){
			$this->err(null, '圈子名不能为空');
		} elseif (Model_Logic_Blackword::instance()->filter($title)) {
		    $this->err(null, '文明上网，人人有责', null, null, "sys.forbidden.blackword");
		}
		$objLogicCircle = new Model_Logic_Circle();
		if( $objLogicCircle->getCircleByTitle($title, $this->_uid, $intCircleId)) {
			$this->err(null, '该名称已被使用');
		}
		$this->ok();
	}
	
	/**
	 * 
	 * 返回创圈一下模板
	 * @param array $_GET = array(
	 * 	vid => 视频ID,
	 * ), 
	 * @param array $_POST = array(
	 * 	vid => 视频ID,
	 * 	cid => 圈子ID
	 * )
	 */
	public function action_circleone() {
		$this->_needLogin();
		$objLogicCircle = new Model_Logic_Circle();
		$objLogicCircleVideo = new Model_Logic_CircleVideo();
		$objLogicVideo = new Model_Logic_Video();
		$uid = $this->_uid;
		$vid = $this->request->param("vid");
		if(!$vid) {
			$this->err();
		}
		$arrVideoInfo = $objLogicVideo->get($vid);
		if(!$arrVideoInfo) {
			$this->err(NULL, "视频不存在");
		}
		if($this->request->method()!=Request::POST) {
			$objTpl = self::template();
			$objTpl->set_filename("circle/circleone");
			$circleList = $objLogicCircle->created($uid, 0, 100, 'host');
			$circleList = $circleList ? array_values($circleList): array();
			$circle_one = $circleList ? $circleList[0]: array();
			$objTpl->circle_list = $circleList;
			$objTpl->circle_one = $circle_one;
			$objTpl->video_info = $arrVideoInfo;
			$content = $objTpl->render();
			$this->ok( array("html"=>$content) );	
		}
		$cid = (int)$this->request->param("cid");
		if(!$cid) {
			$this->err();
		}
		$note = (string) $this->request->param("note", '');
		#TODO 检查圈子是否为他的
		$objModelCircle = new Model_Data_Circle();
		$arrCircleInfo = $objModelCircle->get($cid);
		if(!$arrCircleInfo || $arrCircleInfo['creator']!=$uid) {
			$this->err(NULL, "圈子不存在");
		}
		//检查是否存在了
		$modelDataCircleVideoByUser = new Model_Data_CircleVideoByUser();
		$modelDataCircleVideoByUser->setSlaveOkay(false);
	    $doc = $modelDataCircleVideoByUser->findOne(array(
            'circle_id' => $cid,
            'video_id' => $vid,
	    )); 
	    $modelDataCircleVideoByUser->setSlaveOkay(true);
		try {
		    // 已在圈内，编辑
    	    if($doc) {
    			$res = $objLogicCircleVideo->modify($cid, $vid, array('note' => $note));
    	    // 未在圈内，新增
    	    } else {
    			$res = $objLogicCircleVideo->add($cid, $vid, array('note' => $note), 
    			    $this->_user);
    	    }
			if($res) {				
				$this->ok();
			}
		} catch (Model_Logic_Exception $e) {
		    $this->err(NULL, $e->getMessage());
		}
		
	}
	/**
	 * 
	 * 删除圈过的视频
	 * 
	 * @param array $_GET = array(
	 * 	vid => 视频ID,
	 *  cid => 圈子ID
	 * )
	 */
	public function action_removecircledvideo() {
		$cid = (int)$this->request->param("cid");
		$vid = trim( $this->request->param("vid") );
		$objLogicCircle = new Model_Logic_Circle();
		$objLogicCircleVideo = new Model_Logic_CircleVideo();
		$arrCircleInfo = $objLogicCircle->get($cid);
		if(!$arrCircleInfo || $arrCircleInfo['creator']!=$this->_uid) {
			$this->err(NULL, "圈子不存在");
		}
		try {
			$res = $objLogicCircleVideo->remove($cid, $vid);
			if(!$res) {
				$this->err(NULL, "删除失败");
			}
		} catch (Model_Logic_Exception $e) {
			$this->err(NULL, $e->getMessage());
		}
		
		$this->ok();		
	}
	/**
	 * 
	 * 快速创建圈子
	 * @param array $_GET = array(
	 * 	title => 圈子名称
	 * )
	 */
	public function action_quickaddcircle() {
		$this->_needLogin();
		$title = trim( $this->request->param("title") );
		$catList = array_keys(Model_Data_Circle::$categorys);
		$defaultCat = $catList[0];
		$objLogicCircle = new Model_Logic_Circle();
		$uid = $this->_uid;
		$arrRules = $this->_formRule( array('@title') );
	    $arrPost = array("title" => $title);
		$objValidation = new Validation($arrPost, $arrRules);
	    if (! $this->valid($objValidation)) {
	        return;
	    }
	   	$arrPost['cat'] = array();
		if (Model_Logic_Blackword::instance()->filter($arrPost['title'])) {
			$this->err(null, array('title'=>"内容包含敏感词"), null, null, "usr.submit.valid");
		}
		//检测圈子名称是否存在
		if( $objLogicCircle->getCircleByTitle($arrPost['title'], $uid)) {
			$this->err(null, array('title'=>"已存在"), null, null, "usr.submit.valid");
		}
	   	$res = $objLogicCircle->create($arrPost['title'], $arrPost['cat'], $uid);
	   	
	   	if(!$res) {
	   		$this->err(null, "创建失败");
	   	} else {
	   		#TODO circle_list
	   		$objTpl = self::template();
			$objTpl->set_filename("circle/circleone_select");
			$circleList = $objLogicCircle->created($uid, 0, 100, 'host');
			$circleList = $circleList ? array_values($circleList): array();
			$circle_one = array("_id" => intval($res), "title" => $title);
			$objTpl->circle_list = $circleList;
			$objTpl->circle_one = $circle_one;
			$content = $objTpl->render();
	   		$arrReturn = array(
	   			'circle_list' => $content ,
	   			'current_circle' => $circle_one
	   		);
	   		$this->ok($arrReturn);
	   	}
	}
	
	public function action_editVideo()
	{
		$this->_needLogin();
		
	    $modelCircle = new Model_Logic_Circle();
	    $modelCircleVideo = new Model_Logic_CircleVideo();
	    
		if($this->request->method() == Request::POST) {
    		$circleId = (int) $this->request->param('old_cid');
    		$videoId = (string) $this->request->param('vid');
		    $newCircleId = (int) $this->request->param('cid', $circleId);
		    $note = (string) $this->request->param('note', '');
    	    
    	    $circleVideo = $modelCircleVideo->get($circleId, $videoId);
    	    if (!$circleVideo) {
    	        $this->err(null, '圈内视频未找到。');
    	    }
    	    if (!$circleVideo['circle']) {
    	        $this->err(null, '圈子不存在。');
    	    }
    	    if ($circleVideo['circle']['creator'] != $this->_uid) {
    	        $this->err(null, '没有权限。');
    	    }
    	    
		    try {
		        $modelCircleVideo->modify($circleId, $videoId, array(
		            'circle_id' => $newCircleId,
		            'note' => $note,
		        ));
		    } catch (Model_Logic_Exception $e) {
		        $this->err(NULL, $e->getMessage());
		    }
		    $this->ok(array('url' => Util::circleUrl($circleId, null, $circleVideo['circle'])));
		} else {
    		$circleId = (int) $this->request->param('circle');
    		$videoId = (string) $this->request->param('video');
    	    
    	    $circleVideo = $modelCircleVideo->get($circleId, $videoId);
    		$circleList = $modelCircle->created($this->_uid, 0, 100, 'host');
    	    $this->template->set('circle_list', $circleList);
    	    $this->template->set('circle_one', $circleVideo['circle']);
    	    $this->template->set('video_info', $circleVideo['video']);
    	    $this->template->set('circle_video', $circleVideo);
		}
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
	
	private function _formRule($arrField = null) {
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
}
