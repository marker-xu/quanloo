<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 视频相关页面，以及Ajax请求接口
 * @author wangjiajun
 */
class Controller_Video extends Controller 
{
    const COMMENT_PAGE_COUNT = 3;
    /**
     * 观看视频
     */
	public function action_index()
	{
	    $videoId = (string) $this->request->param('id');
	    if (strlen($videoId) != 32) {
	    	return $this->_404_page(array('_id' => $videoId));
	    }
	    
	    $strRouteName = $this->request->param('route_name');
	    if ($strRouteName != 'player_page') {
	    	//兼容老的URL，做301跳转，2012-12-31之后可以删除这个判断逻辑
	    	$this->request->redirect(Util::videoPlayUrl($videoId), 301);
	    	exit();
	    }
	    
	    $modelLogicVideo = new Model_Logic_Video();
	    $video = $modelLogicVideo->get($videoId, array('type', 'category', 'title', 'tag',
	    		'quality', 'length', 'thumbnail', 'play_url', 'player_url', 'domain', 'status', 'iosid'), false);
	    if (!$video || $video['status'] != Model_Data_Video::STATUS_VALID) {
	    	if (empty($video)) {
	    		$video = array('_id' => $videoId);
	    	}
	    	return $this->_404_page($video);
	    }	    
	    
	    $this->_update_unlogin_watched_playlist($videoId);

	    $playlist = (string) $this->request->param('playlist', '');
	    if ($playlist != '' && ! in_array($playlist, array('circle'))) {
	        $playlist = '';
	    }

	    $circleId = $this->request->param('circle');
	    if (! empty($circleId)) {
	        $circleId = (int) $circleId;
    	    if ($circleId <= 0) {
	            throw new Controller_Exception('invalid circle');
    	    }
	    }
	    $userId = $this->request->param('user', $this->_uid);
	    if (! empty($userId)) {
	        $userId = (int) $userId;
	    }
	    $offset = (int) $this->request->param('offset');	    
	    
	    //非(压力测试和spider)的情况下记录
		$strUserAgent = $this->request->headers('user-agent');
	    if (! Util::isSpider($strUserAgent)) {
    	    JKit::addAfterRequestFinish(function ($videoId, $userId, $circleId, $params) use ($modelLogicVideo) {
    	        try {
    	            $modelLogicVideo->watch($videoId, $userId, $circleId, $params);
    	        } catch (Model_Exception $e) {
    	            Kohana::$log->warn('add watch feed failed');
    	        }	        
    	    }, $videoId, $this->_uid, $circleId, $this->request->param());
	    }

	    $this->_process_html5_player($strUserAgent, $video);
	    
	    if (! empty($video['player_url']) || ($video['is_html5'] && ! empty($video['player_html5_param']))) {
	        //内嵌播放器
	        $this->template()->set_filename('video/index');
	        $intLeftPlaylistCount = 15; //播放列表15个一页
	        $intRelatedVideoCount = 8; //相关视频8个一页
	        $intCommentCount = 20;
	    } else {
	        //内嵌iframe
	        $this->template()->set_filename('video/index2');
	        $intLeftPlaylistCount = 8; //播放列表8个一页
	        $intRelatedVideoCount = 8; //相关视频8个一页
	        $intCommentCount = self::COMMENT_PAGE_COUNT;
	    }
	    
	    // 视频信息
	    $this->template->set('video', $video);   	    

	    //每个播放列表array(id, name, videos, has_offset => 是否要把offset参数带入到播放url, has_pager => 是否可以显示换一批, rec_zone => 日志上报用)
	    $arrPlaylistOpt = array('has_video_stat' => false, 'has_circle_info' => false, 'has_video_comment' => false);
	    $arrLeftPlaylist = array();
	    $arrTmp = $this->_playlist('related', $videoId, $circleId, $userId, $intRelatedVideoCount, 0, $arrPlaylistOpt);
	    if ($arrTmp['count'] > 0) {
	    	$arrTmp2 = array('id' => 'related', 'name' => Model_Logic_Video::$playlists['related'], 'videos' => $arrTmp);
	    	if (Model_Logic_Video::isPlaylistHasPager('related') && $arrTmp['count'] >= $intRelatedVideoCount) {
	    		$arrTmp2['has_pager'] = true;
	    	}
	    	$arrTmp2['rec_zone'] = $this->_rec_zone('related');
	    	$arrLeftPlaylist[] = $arrTmp2;
	    }	    
/* 	    if ($playlist != '') {
	        $arrTmp = array('id' => $playlist, 'name' => Model_Logic_Video::$playlists[$playlist]);
	        $bolPlaylistHasOffset = $this->_playlistHasOffset($playlist);
	        if ($bolPlaylistHasOffset) {
	            $arrTmp['has_offset'] = true;
	        }
	        $arrTmp['videos'] = $this->_playlist($playlist, $videoId, $circleId, $userId, $intLeftPlaylistCount + 1, ($bolPlaylistHasOffset ? $offset : 0), $arrPlaylistOpt);

	        //对于圈内视频这种播放列表，当前正在播放的视频会出现在列表中，需要主动去掉
	        foreach ($arrTmp['videos']['data'] as $k => $v) {
	        	if ($v['_id'] == $videoId) {
	        		unset($arrTmp['videos']['data'][$k]);
	        		$arrTmp['videos']['count']--;
	        	}
	        }
	        if ($arrTmp['videos']['count'] > $intLeftPlaylistCount) {
	        	$arrTmp['videos']['count'] = $intLeftPlaylistCount;
	        	$arrTmp['videos']['data'] = array_slice($arrTmp['videos']['data'], 0, $intLeftPlaylistCount);
	        }
	        if ($arrTmp['videos']['count'] > 0) {
	            if (Model_Logic_Video::isPlaylistHasPager($playlist) && $arrTmp['videos']['count'] >= $intLeftPlaylistCount) {
	                $arrTmp['has_pager'] = true;
	            }
	            $arrTmp['rec_zone'] = $this->_rec_zone($playlist);
	            $arrLeftPlaylist[] = $arrTmp;	            
	        }
	    } */
	    
	    $arrFirstLeftPlaylist = @$arrLeftPlaylist[0];
	    if (! empty($video['player_url'])) {
	    	$objLogicRecommend = new Model_Logic_Recommend();
	    	try {
	    		if ($circleId > 0) {
	    			$objLogicCircle = new Model_Logic_Circle();
	    			$arrCircleInfo = $objLogicCircle->get($circleId, Model_Data_Circle::STATUS_PUBLIC, false);    			
	    		} else {
	    			$arrCircleInfo = $objLogicRecommend->getCircleInfoByVids($videoId);
	    		}
                if (! empty($arrCircleInfo)) {                    
/*                     if ($this->_uid) {
                        $modelDataCircleUser = new Model_Data_CircleUser();
                        $isSubscribed = $modelDataCircleUser->isUserSubscribeCircle($this->_uid, $arrCircleInfo['_id']);
                        $arrCircleInfo['is_subscribed'] = $isSubscribed;
                    } */
                    $this->template->set('circle', $arrCircleInfo);
                }
            } catch (Exception $e) {
                //do nothing
            }
            
            if ($this->_uid > 0) {
            	$modelDataUserVideo = new Model_Data_UserVideo($this->_uid);
            	$this->template->set('is_in_watchlater', $modelDataUserVideo->isInWatchlater($videoId));
            }
/*            
            $mixedUid = ($this->_uid > 0) ? $this->_uid : Session::instance()->id();            
            $arrRelatedCircle = $modelLogicVideo->getRelatedCircle($videoId, $mixedUid);
            if ($this->_uid > 0 && ! empty($arrRelatedCircle)) {
            	$modelDataCircleUser = new Model_Data_CircleUser();
            	foreach ($arrRelatedCircle as &$v) {
            		$isSubscribed = $modelDataCircleUser->isUserSubscribeCircle($this->_uid, $v['_id']);
            		$v['is_subscribed'] = $isSubscribed;            		
            	}
            	unset($v);
            }
            $this->template->set('reco_related_circle', $arrRelatedCircle); */
            
            //最新最热随机5条视频
/*             $arrTmp = $modelLogicVideo->getHomepageRecoRandomVideo(array(Model_Data_Recommend::HOMEPAGE_REC_HOT, Model_Data_Recommend::HOMEPAGE_REC_NEW), 5);
            if (! empty($arrTmp)) {
            	$arrTmp2 = array();
            	foreach ($arrTmp as $k => $v) {
            		$arrTmp2[$k] = array('rec_zone' => $this->_rec_zone($k), 'video' => $v);
            	}
            	$this->template->set('rand_reco_video', $arrTmp2);
            }  */
            
            //大家都在看视频            
            $arrTmp = null;
            $intCount = 6;
            try {
            	$arrTmp = $objLogicRecommend->getCurPeopleWatchedVideo($videoId, 0, $intCount, 1);           	
            } catch (Exception $e) {
            	//do nothing
            }
            if ($arrTmp) {
            	$this->template->set('people_watched_video', $arrTmp);
            }
            
           	//获取右侧顶部广告
           	$objLogicAd = new Model_Logic_Ad();
           	$this->template->set('ad_right_top_1', $objLogicAd->getRandAdByPos(Model_Logic_Adconst::AD_POS_PLAYER_RIGHT_1));
           	
           	$this->template->set('search_word', $this->_getSearchWord());
	    } else {
	        //内嵌iframe的特殊元素
	        if (! empty($arrFirstLeftPlaylist['videos']['data'])) {
	        	$arrNextPlayVideo = array_pop($arrFirstLeftPlaylist['videos']['data']);
	        	$arrFirstLeftPlaylist['videos']['count']--;
	        	$this->template->set('left_playlist_first_video', $arrNextPlayVideo);
	        }	        
	    }
	    if ($arrFirstLeftPlaylist['videos']['count'] < 1) {
	        if (count($arrLeftPlaylist) > 1) {
	            //如果有两个播放列表，那么第一个列表没有视频的话就不显示
	            array_shift($arrLeftPlaylist);
	        }
	    } else {
	        $arrLeftPlaylist[0] = $arrFirstLeftPlaylist;
	    }	    	    
	    $this->template->set('left_playlist', $arrLeftPlaylist);
	    $this->template->set('left_playlist_page_count', $intLeftPlaylistCount);
	    
	    $objComment = new Model_Logic_Comment();
	    $arrComment = $objComment->get($videoId, $intCommentCount, 0, array('avatarSize' => 48));
	    $this->template->set('comments', $arrComment);

	    $bolIsAdmin = false;
	    if ($this->_uid && in_array($this->_uid, Kohana::$config->load('admin')->administrators)) {
	        $bolIsAdmin = true;
	    }
	    $this->template->set('is_admin_user', $bolIsAdmin);
	}
	
	/**
	 * 查询单个视频信息
	 */
	public function action_get()
	{
	    $videoId = (string) $this->request->param('id');
	    if (strlen($videoId) != 32) {
	        $this->err(null, 'invalid id');
	    }
	    $modelLogicVideo = new Model_Logic_Video();
	    $video = $modelLogicVideo->get($videoId);
	    $this->ok($video);
	}
	
	/**
	 * 查询多个视频信息
	 */
	public function action_getMulti()
	{
	    $videoIds = (string) $this->request->param('ids');
	    $videoIds = explode(',', $videoIds);
	    $tmp = array();
	    foreach ($videoIds as $videoId) {
    	    if (strlen($videoId) == 32) {
    	        $tmp[] = $videoId;
    	    }
	    }
	    $videoIds = $tmp;
	    if ($videoIds) {
    	    $modelLogicVideo = new Model_Logic_Video();
    	    $videos = $modelLogicVideo->getMulti($videoIds);
	    } else {
	        $videos = array();
	    }
	    $this->ok($videos);
	}
	
	/**
	 * 推视频
	 */
	public function action_like()
	{
	    $this->_needLogin();
	    
	    $videoId = (string) $this->request->param('id');
	    if (strlen($videoId) != 32) {
	        $this->err(null, 'invalid id');
	    }
	    $circleId = $this->request->param('circle');
	    if (! empty($circleId)) {
	        $circleId = (int) $circleId;
    	    if ($circleId <= 0) {
    	        $this->err(null, 'invalid circle');
    	    }
	    }
	    
	    $modelLogicVideo = new Model_Logic_Video();
	    try {
    	    $modelLogicVideo->like($videoId, $this->_uid, $circleId);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    
	    $this->ok();
	}

	public function action_delComment()
	{
	    $this->_needLogin();
	    $bolIsAdmin = false;
	    if ($this->_uid && in_array($this->_uid, Kohana::$config->load('admin')->administrators)) {
	        $bolIsAdmin = true;
	    }
	    if (! $bolIsAdmin) {
	        $this->err(NULL, 'forbidden');
	    }
        $strCommentId = $this->request->post('id');
        $bolRet = false;
        if ($strCommentId) {
	        $objComment = new Model_Logic_Comment();
	        $bolRet = $objComment->remove($strCommentId);
        }
	    if ($bolRet) {
	        $this->ok();
	    } else {
	        $this->err(NULL, '删除失败');
	    }
	}
	
	/**
	 * 评论视频
	 */
	public function action_comment()
	{	    
	    $this->_needLogin();
	    
	    $arrRules = $this->_commentRule();
	    $arrPost = $this->request->post();
	    $arrPost['content'] = trim($arrPost['content']);
	    if (Model_Logic_Blackword::instance()->filter($arrPost['content'])) {
	        $this->err(null, '文明发贴，人人有责');
	    }
	    $objValidation = new Validation($arrPost, $arrRules);
	    if (! $this->valid($objValidation)) {
	        return;
	    }	    
	    
	    $videoId = (string) $arrPost['id'];
	    if (strlen($videoId) != 32) {
	        $this->err(null, 'invalid id');
	    }
	    
	    $circleId = $arrPost['circle'];
	    if (! empty($circleId)) {
	        $circleId = (int) $circleId;
    	    if ($circleId <= 0) {
    	        $this->err(null, 'invalid circle');
    	    }
	    }
	    	    
	    $modelLogicVideo = new Model_Logic_Video();
	    try {
	        $modelLogicVideo->comment($videoId, $this->_user, $arrPost['content'], $circleId);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, '系统繁忙，请稍后重试');
	    }

	    $arrCommentInfo = Model_Data_Comment::getLastAddComment();
	    $strOwnAvatar = isset($this->_user['avatar'][48]) ? $this->_user['avatar'][48] : '';
	    $arrComment = array('data' => array(array('create_time_str' => '此刻', 'avatar' => @Util::userAvatarUrl($strOwnAvatar, 48),
	        'nick' => $this->_user['nick']) + $arrCommentInfo));
	    if ($this->request->param('format') == 'json') {
	        $this->ok($arrComment);
	    } else {
	        $strTpl = $this->request->param('template');
	        if ($strTpl) {
	            $strTpl = "video/{$strTpl}";
	        } else {
	            $strTpl = "video/comments";
	        }
	        $objTpl = self::template();
	        $objTpl->set_filename($strTpl);
	        $objTpl->set('comments', $arrComment);
	        $arrComment['data'] = $objTpl->render();
	        $this->ok($arrComment);
	    }
	}
	
	/**
	 * 添加视频tag墙的tag
	 * 
	 * @param array $_POST = array(
	 * 	id => 视频vid,
	 * 	circle => 圈子id，无则为空
	 * 	content => tag内容
	 *  type => up：顶现有的tag；submit：提交新tag
	 * )
	 */
	public function action_addXComment()
	{
		$arrRules = $this->_xCommentRule();
		$arrPost = $this->request->post();
		$arrPost['content'] = trim($arrPost['content']);
		if (Model_Logic_Blackword::instance()->filter($arrPost['content'])) {
			$this->err(null, '文明发贴，人人有责');
		}
		$objValidation = new Validation($arrPost, $arrRules);
		if (! $this->valid($objValidation)) {
			return;
		}
		if (isset($arrPost['type']) && $arrPost['type'] == 'submit') {
			$type = 'submit';
		} else {
			$type = 'up';
		}
		if ($type == 'submit') {
			$this->_needLogin();
		}
		 
		$videoId = (string) $arrPost['id'];
		if (strlen($videoId) != 32) {
			$this->err(null, 'invalid id');
		}
		 
		$circleId = $arrPost['circle'];
		if (! empty($circleId)) {
			$circleId = (int) $circleId;
			if ($circleId <= 0) {
				$this->err(null, 'invalid circle');
			}
		}

		$objActs = new Model_Logic_Acts();
		if ($this->_uid > 0) {
			$strUserKey = $this->_uid;
		} else {
			$strUserKey = Session::instance()->id();
		}
		$strActsKey = "{$strUserKey}:{$videoId}:{$type}";
		if (! $objActs->day(Model_Logic_Acts::CMD_VIDEO_TAG, $strActsKey)) {
			$this->err(null, '你今天已经对该视频发表过观点', null, null, Model_Logic_Acts::ERR_STATUS);
		}		
		
		$modelLogicVideo = new Model_Logic_Video();
		try {
			$bolForceAdd = isset(Model_Data_XComment::$arrDefaultXComment[$arrPost['content']]);
			$modelLogicVideo->addXComment($videoId, $this->_user, $arrPost['content'], $circleId, $bolForceAdd);
			$objActs->dayUpdate(Model_Logic_Acts::CMD_VIDEO_TAG, $strActsKey);
		} catch (Exception $e) {
			if ($e->getMessage() == 'NEEDLOGIN') {
	    		$this->_needLogin();
	    	} else {
	        	$this->err(NULL, '系统繁忙，请稍后重试');
	    	}
		}
	
		$this->ok();
	}	
	
	/**
	 * 以后观看
	 */
	public function action_watchLater()
	{
	    $this->_needLogin();
	    
	    $videoId = (string) $this->request->param('id');
	    if (strlen($videoId) != 32) {
	        $this->err(null, 'invalid id');
	    }
	    
	    $modelLogicVideo = new Model_Logic_Video();
	    try {
    	    $modelLogicVideo->watchLater($videoId, $this->_uid);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    
	    $this->ok();
	}
	
	/**
	 * 删除以后观看
	 */
	public function action_deleteWatchLater()
	{
	    $this->_needLogin();
	    
	    $videoId = (string) $this->request->param('id');
	    if (strlen($videoId) != 32) {
	        $this->err(null, 'invalid id');
	    }
	    
	    $modelLogicVideo = new Model_Logic_Video();
	    try {
    	    $modelLogicVideo->deleteWatchLater($videoId, $this->_uid);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    
	    $this->ok();
	}
	
	/**
	 * 分享视频
	 */
	public function action_share()
	{
	    $this->_needLogin();
	    
	    $videoId = (string) $this->request->param('id');
	    if (strlen($videoId) != 32) {
	        $this->err(null, 'invalid id');
	    }
	    $circleId = $this->request->param('circle');
	    if (! empty($circleId)) {
	        $circleId = (int) $circleId;
    	    if ($circleId <= 0) {
    	        $this->err(null, 'invalid circle');
    	    }
	    }
	    
	    $modelLogicVideo = new Model_Logic_Video();
	    try {
    	    $modelLogicVideo->share($videoId, $this->_uid, $circleId);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    
	    $this->ok();
	}
	
	/**
	 * 生成视频分享的feed
	 */
	public function action_sharefeed()
	{
	    $this->_needLogin();
	    $id = trim($this->request->param('id', ''));
	    if (! $id) {
	        $this->err(null, 'invalid id');
	    }
	    $strShareType = $this->request->param('shareType');
	         
	    $objLogicFeed2 = new Model_Logic_Feed2();
	    try {
        	if ($strShareType == 'circle') {
        	    $objLogicFeed2->addFeedShareCircle($this->_uid, $id);
        	} elseif ($strShareType == 'video') {
        	    $objLogicFeed2->addFeedShareVideo($this->_uid, $id, 0);
        	}
    	} catch (Exception $e) {
    	    $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
    	}
	     
	    $this->ok();	    
	}	
	
	/**
	 * 心情视频
	 */
	public function action_mood()
	{
	    $videoId = (string) $this->request->param('id');
	    if (strlen($videoId) != 32) {
	        $this->err(null, 'invalid id');
	    }
	    $mood = (string) $this->request->param('mood');
	    if (!$mood) {
	        $this->err(null, 'invalid mood');
	    }
	    $circleId = trim($this->request->param('circle'));
	    if (! empty($circleId)) {
	        $circleId = (int) $circleId;
    	    if ($circleId <= 0) {
    	        $this->err(null, 'invalid circle');
    	    }
	    }
	    
	    $objActs = new Model_Logic_Acts();
	    if ($this->_uid > 0) {
	    	$strUserKey = $this->_uid;
	    } else {
	    	$strUserKey = Session::instance()->id();
	    }
	    $strActsKey = "{$strUserKey}:{$videoId}";
	    if (! $objActs->day(Model_Logic_Acts::CMD_VIDEO_MOOD, $strActsKey)) {
	    	$this->err(null, '你今天已经心情过该视频', null, null, Model_Logic_Acts::ERR_STATUS);
	    }
	    
	    $modelLogicVideo = new Model_Logic_Video();
	    try {
    	    $modelLogicVideo->mood($videoId, $mood, $this->_uid, $circleId);
    	    $objActs->dayUpdate(Model_Logic_Acts::CMD_VIDEO_MOOD, $strActsKey);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    $arrRet = array('uid' => $this->_uid, 'data' => $mood);
		if ($this->_uid > 0) {
			$arrRet['avatar'] = Util::userAvatarUrl(@$this->_user['avatar'][30], 30);
		}	    
	    $this->ok($arrRet);
	}

	/**
	 * 获取视频的心情数据和用户
	 *
	 * @param array $_GET = array(
	 * 	id => 视频vid,
	 * 	type => 1表示心情加用户，2表示只要用户
	 * 	offset => 分页参数，用于用户列表的分页
	 * 	count => 分页参数，表示每页条数
	 * )
	 * @return {"err":"ok", "data":{"moodData": [{"id":"dx", "name":"大笑", "num":32}], "userTotal":33, "userData":[{
	 * 		"uid":3434553554, "avatar":"http://x.com/xxx.jpg", "data":"dx"}]}}
	 */
	public function action_moods2()
	{
		$videoId = (string) $this->request->param('id');
		if (strlen($videoId) != 32) {
			$this->err(null, 'invalid id');
		}
		$count = (int) $this->request->param('count', 10);
		$offset = (int) $this->request->param('offset', 0);
		$type = (int) $this->request->param('type', 1);
	
		$arrRet = array();
		$modelLogicVideo = new Model_Logic_Video();
		try {
			$arrFeedMoodedUser = $modelLogicVideo->feededUsers($videoId, Model_Data_VideoFeed::TYPE_MOODED, $count, $offset);
		} catch (Exception $e) {
			$arrFeedMoodedUser = null;
		}
		$arrTmp = array();
		if (! empty($arrFeedMoodedUser) && is_array($arrFeedMoodedUser['data'])) {		
			foreach ($arrFeedMoodedUser['data'] as $v) {
				$arrTmp[] = array('uid' => $v['_id'], 'avatar' => Util::userAvatarUrl(@$v['avatar']['30'], 30), 'data' => $v['data']);
			}
		}
		$arrRet['userData'] = $arrTmp;
		$arrRet['userTotal'] = (int) @$arrFeedMoodedUser['total'];
		
		if ($type == 1) {
			$arrTmp = array();
			$modelDataVideoStatAll = new Model_Data_VideoStatAll();
			try {
				$stats = $modelDataVideoStatAll->findOne(array('_id' => $videoId), array('mooded_count'));
			} catch (Exception $e) {
				$stats = array();
			}
			foreach (Model_Logic_Video::$arrMoodMap as $k => $v) {
				$arrTmp[] = array('id' => $k, 'name' => $v, 'num' => (int) @$stats['mooded_count'][$k]);
			}
			$arrRet['moodData'] = $arrTmp;
		}
		
		$this->ok($arrRet);
	}
	
	/**
	 * 获取视频的tag墙列表
	 *
	 * @param array $_GET = array(
	 * 	id => 视频vid,
	 * 	offset => 分页参数，当前页开始偏移量
	 * 	count => 分页参数，表示每页条数
	 * )
	 * @return {"err":"ok", "data":{"total": 12, "data":[{"data":"word", "num":33}]}}
	 */
	public function action_xComments()
	{
		$videoId = (string) $this->request->param('id');
		if (strlen($videoId) != 32) {
			$this->err(null, 'invalid id');
		}
		$count = (int) $this->request->param('count', 10);
		$offset = (int) $this->request->param('offset', 0);
	
		$modelLogicVideo = new Model_Logic_Video();
		try {
			$arrXComment = $modelLogicVideo->getXComment($videoId, $count, $offset);
		} catch (Exception $e) {
			$arrXComment = array('total' => 0);
		}
	
		$this->ok($arrXComment);
	}
	
	/**
	 * 视频播放列表
	 */
	public function action_playlist()
	{	    
	    $name = (string) $this->request->param('name');
	    if (!in_array($name, array_keys(Model_Logic_Video::$playlists))) {
	        $this->err(NULL, 'unknown name');
	    }
	    $strFormat = $this->request->param('format');
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 20);
	    $videoId = (string) $this->request->param('video');
	    $circleId = (int) $this->request->param('circle');
	    $userId = (int) $this->request->param('user', $this->_uid);
	    
        $videos = $this->_playlist($name, $videoId, $circleId, $userId, $count, $offset);
	    
	    if ($strFormat == 'html') {
	        $objTpl = self::template();
	        $objTpl->set_filename("video/playlist");
	        $objTpl->set('playlist', $name);
	        $objTpl->set('offset', $offset);
	        $arrTmp = array('circle' => $this->request->param('circle'));
	        if ($this->_playlistHasOffset($name)) {
	            $arrTmp['offset'] = $offset;
	        }
	        $objTpl->set('playUrlParam', $arrTmp);
	        $objTpl->set('videos', $videos);
	        
	        $videos['data'] = $objTpl->render();
	        
	    }
	    
	    $this->ok($videos);
	}
	
	/**
	 * 评论列表
	 */
	public function action_comments()
	{
	    $videoId = (string) $this->request->param('id');
	    if (strlen($videoId) != 32) {
	        $this->err(null, 'invalid id');
	    }
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', self::COMMENT_PAGE_COUNT);
	    
		$objComment = new Model_Logic_Comment();
		$arrComment = $objComment->get($videoId, $count, $offset, array('avatarSize' => 48));

		if ($this->request->param('format') == 'json') {
		    $this->ok($arrComment);
		} else {
		    $strTpl = $this->request->param('template');
		    if ($strTpl) {
		        $strTpl = "video/{$strTpl}";
		    } else {
		        $strTpl = "video/comments";
		    }
		    $bolIsAdmin = false;
		    if ($this->_uid && in_array($this->_uid, Kohana::$config->load('admin')->administrators)) {
		        $bolIsAdmin = true;
		    }
		    $objTpl = self::template();
		    $objTpl->set_filename($strTpl);
		    $objTpl->set('comments', $arrComment);
		    $objTpl->set('is_admin_user', $bolIsAdmin);
		    $arrComment['data'] = $objTpl->render();
		    $this->ok($arrComment);
		}
	}
	
	/**
	 * 心情用户列表
	 */
	public function action_moods()
	{
		$videoId = (string) $this->request->param('id');
		if (strlen($videoId) != 32) {
			$this->err(null, 'invalid id');
		}
		$count = (int) $this->request->param('count', 20);

		$modelLogicVideo = new Model_Logic_Video();
		$arrFeedMoodedUser = $modelLogicVideo->feededUsers($videoId, Model_Data_VideoFeed::TYPE_MOODED, $count);
			
		if ($this->request->param('format') == 'json') {
			$this->ok($arrFeedMoodedUser);
		} else {
			$strTpl = $this->request->param('template');
			if ($strTpl) {
				$strTpl = "video/{$strTpl}";
			} else {
				$strTpl = "video/moods";
			}
			$objTpl = self::template();
			$objTpl->set_filename($strTpl);
			$objTpl->set('feed_mooded_user', $arrFeedMoodedUser);
			$this->ok(array('data' => $objTpl->render()));
		}
	}	
	
	/**
	 * 播放结束时的视频推荐
	 * @param $_GET = array(id => video id, circle => 圈子id
	 * )
	 */
	public function action_player_finish() {
	    $videoId = (string) $this->request->param('id');
	    $circleId = (int) $this->request->param('circle', 0);
	    $reqType = trim($this->request->param('reqtype'));
	    $strReferer = trim($this->request->param('referer'));
	    if (empty($videoId)) {
	        $this->err();
	    }

	    $modelLogicVideo = new Model_Logic_Video();
	    try {
	        $video = $modelLogicVideo->get($videoId, array('type', 'category', 'title', 'tag',
	            'quality', 'length', 'thumbnail', 'play_url', 'player_url', 'domain', 'status'));
	    } catch (Exception $e) {
	        $this->err();
	    }
	    if (! $video || $video['status'] != Model_Data_Video::STATUS_VALID) {
	        $this->err();
	    }	

	    $arrCircleInfo = null;
	    if ($circleId > 0) {
	        $objTmp = new Model_Data_Circle();
	        try {
	            $arrCircleInfo = $objTmp->get($circleId, array('title'));
	        } catch (Exception $e) {
	            //do nothing
	        }
	    }
	    
	    $objTmp = new Model_Logic_Video();
	    try {
	        $arrRecVideo = $objTmp->getPlayerFinishRec(array('id' => $videoId, 'uid' => $this->_uid, 'count' => 18));
	    } catch (Exception $e) {
	        $this->err();
	    }
	    if (empty($arrRecVideo)) {
	        $this->err();
	    }
	    $this->template->set('search_word', $this->_getSearchWord($strReferer));
	    $this->template->set('video', $video);
	    $this->template->set('circle', $arrCircleInfo);
	    $this->template->set('rec_videos', $arrRecVideo);
	    $this->template->set('reqtype', $reqType);
	    $this->ok($this->template->render());	    
	}
	
	protected function _404_page($video) {
		$videoId = $video['_id'];			
		$this->template->set_filename('video/_404_page');		 
		$this->template->set('video', $video);
	
		$modelLogicVideo = new Model_Logic_Video();
		//最新最热随机5条视频
		$arrTmp = $modelLogicVideo->getHomepageRecoRandomVideo(array(Model_Data_Recommend::HOMEPAGE_REC_HOT, Model_Data_Recommend::HOMEPAGE_REC_NEW), 5);
		if (! empty($arrTmp)) {
			$arrTmp2 = array();
			foreach ($arrTmp as $k => $v) {
				$arrTmp2[$k] = array('rec_zone' => $this->_rec_zone($k), 'video' => $v);
			}
			$this->template->set('rand_reco_video', $arrTmp2);
		}
	
		$objLogicRecommend = new Model_Logic_Recommend();
		//大家都在看视频
		$arrTmp = null;
		$intCount = 6;
		try {
			$arrTmp = $objLogicRecommend->getCurPeopleWatchedVideo($videoId, 0, $intCount, 1);
		} catch (Exception $e) {
			//do nothing
		}
		if ($arrTmp) {
			$this->template->set('people_watched_video', $arrTmp);
		}
		
		try {
			$arrRecVideo = $modelLogicVideo->getPlayerFinishRec(array('id' => $videoId, 'uid' => $this->_uid, 'count' => 18, 'skip_num' => 0));
		} catch (Exception $e) {
			//do nothing
		}
		$this->template->set('rec_videos', $arrRecVideo);
		
		//获取右侧顶部广告
		$objLogicAd = new Model_Logic_Ad();
		$this->template->set('ad_right_top_1', $objLogicAd->getRandAdByPos(Model_Logic_Adconst::AD_POS_PLAYER_RIGHT_1));
		
		$this->template->set('search_word', $this->_getSearchWord());
		
		$bolIsAdmin = false;
		if ($this->_uid && in_array($this->_uid, Kohana::$config->load('admin')->administrators)) {
			$bolIsAdmin = true;
		}
		$this->template->set('is_admin_user', $bolIsAdmin);				
	}
	
	protected function _getSearchWord($strUrl = null) {
		if ($strUrl === null && isset($_SERVER['HTTP_REFERER'])) {
			$strUrl = trim($_SERVER['HTTP_REFERER']);
		}
		if (empty($strUrl)) {
			return null;
		}
		 
		$objSearchword = new Model_Logic_Searchword();
		$strWord = $objSearchword->getWord($strUrl);
		 
		return $strWord;
	}	
/* 	
 * 暂时没用
	public function action_people_watched_video() {
		$intOffset = (int) $this->request->param('offset', 0);
		$videoId = (string) $this->request->param('id');
				
		$objLogicRecommend = new Model_Logic_Recommend();
		$intCount = 6;
		$arrTmp = null;
		try {
			$arrTmp = $objLogicRecommend->getCurPeopleWatchedVideo($videoId, $intOffset, $intCount, 3);
		} catch (Exception $e) {
			//do nothing
		}
		
		$strMormat = null;
		if (! empty($arrTmp)) {
			$arrMormat = array('page_id' => 'recommendation', 'rec_zone' => 'viewvideo_righthot_rec');
			$arrVid = array();
			foreach ($arrTmp as $v) {
				$arrVid[] = $v['_id'];
			}
			$arrMormat['item_list'] = implode(',', $arrVid);
			$strMormat = json_encode($arrMormat);
		}
		
		$this->template->set_filename('video/playlist_inner');
		$this->template->set('videos', array('data' => $arrTmp));
		$this->template->set('rec_zone', 'viewvideo_righthot_rec');
		$this->template->set('offset', $intOffset);
		$this->ok(array('data' => $this->template->render(), 'marmot' => $strMormat));
	} */
	
	protected function _playlist($name, $videoId, $circleId, $userId, $count, $offset, $arrOpt = array()) {
	    $arrFieldTmp = Model_Logic_Video::$basicFields;
	    $arrFieldTmp[] = 'player_url';
	    try {
		    if ($name == 'related') {
		    	$returnStatInfo = (isset($arrOpt['has_video_stat']) && $arrOpt['has_video_stat'] == false) ? false : true;
		        $modelLogicRecommend = new Model_Logic_Recommend();
		        $videos = $modelLogicRecommend->getRecommendVideos($videoId, $offset, $count, $arrFieldTmp, $returnStatInfo);
		        $videos = array('total' => $videos['total'], 'data' => $videos['data']);
		    } else if ($name == 'circle') {
		        $modelLogicRecommend = new Model_Logic_Recommend();
		        $arrRet = $modelLogicRecommend->getCircleVideosByTag($circleId, null, '', $offset, $count, Model_Logic_Video::VIDEO_RANK_DEFAULT, $arrFieldTmp, $arrOpt);
		        $videos = array('total' => $arrRet['total'], 'data' => array_values($arrRet['video']));
		    } else if ($name == 'watched') {
		        $modelLogicVideo = new Model_Logic_Video();
		        $videos = $modelLogicVideo->getWatched($userId, $offset, $count, $arrFieldTmp);
		        $videos = array('total' => $videos['total'], 'data' => array_values($videos['data']));
		    } else if ($name == 'commented') {
		        $modelLogicVideo = new Model_Logic_Video();
		        $videos = $modelLogicVideo->getCommented($userId, $offset, $count, true, $arrFieldTmp);
		        $videos = array('total' => $videos['total'], 'data' => array_values($videos['data']));
		    } else if ($name == 'watch_later') {
		        $modelLogicVideo = new Model_Logic_Video();
		        $videos = $modelLogicVideo->getWatchLater($userId, $offset, $count, $arrFieldTmp);
		        $videos = array('total' => $videos['total'], 'data' => array_values($videos['data']));
		    } else if ($name == 'shared') {
		        $modelLogicVideo = new Model_Logic_Video();
		        $videos = $modelLogicVideo->getShared($userId, $offset, $count, $arrFieldTmp);
		        $videos = array('total' => $videos['total'], 'data' => array_values($videos['data']));
		    } else if ($name == 'mooded') {
		        $modelLogicVideo = new Model_Logic_Video();
		        $videos = $modelLogicVideo->getMooded($userId, $offset, $count, $arrFieldTmp);
		        $videos = array('total' => $videos['total'], 'data' => array_values($videos['data']));
		    } else if ($name == 'recommend') {
	    	    $modelLogicRecommend = new Model_Logic_Recommend();
	    	    if($videoId) {
	    	    	$videos = $modelLogicRecommend->getNullRecommendAlternative($videoId, $arrFieldTmp);
	    	    } else {
	    	    	$videos = $modelLogicRecommend->getHomepageRecommendVideos($offset, $count, null, 0, $arrFieldTmp);
	    	    }
	    	    $tmpCount = count($videos);
	    	    if($tmpCount>200) {
	    	    	$tmpCount = 200;
	    	    }
		        $videos = array('total' => $tmpCount, 'data' => array_values($videos));
		    } else if ($name == 'personal_recommend') {
	    	    $modelLogicRecommend = new Model_Logic_Recommend();
	    		if($userId) {
	    			$videos = $modelLogicRecommend->getGuessVideosByUid($userId, $offset, $count, $arrFieldTmp);
	    		} else {
	    			$videos = $modelLogicRecommend->getGuessVideosByCookie($offset, $count, $arrFieldTmp);
	    		}
		        $videos = array('total' => 100, 'data' => array_values($videos));
		    } else if($name == 'circled') {
		    	$objLogicCircleVideo = new Model_Logic_CircleVideo();
				$total = 0;
				$arrVideos = $objLogicCircleVideo->circledVideosByUser($this->_uid, $offset, $count, $total);
				$tmpVideo = array();
				$videos = array(
					"total" => $total,
					"data" => array()
				);
				$videos['total'] = $total;
				foreach($arrVideos as $row) {
					$tmpVideo = $row['video'];
					$tmpVideo['circle'] = $row['circle'];
					$videos['data'][] = $tmpVideo;
				} 
		    }
	    } catch (Exception $e) {
	    	JKit::$log->warn($e->getMessage(), null, $e->getFile(), $e->getLine());
	    	$videos = array('total' => 0, 'data' => array());
	    }
	    $intRealCount = count($videos['data']);	    
	    if ($intRealCount <= $count) {
	        $videos['count'] = $intRealCount;
	    } else {
	        $videos['count'] = $count;
	        $videos['data'] = array_slice($videos['data'], 0, $count, true);
	    }
	    
	    return $videos;
	}
	
	protected function _commentRule() {
	    return array(
            '@content' => array(
                'datatype' => 'text',
                'reqmsg' => '评论内容',
                'maxlength' => 401,
            ),	            
	    );
	}
	
	protected function _xCommentRule() {
		return array(
				'@content' => array(
						'datatype' => 'text',
						'reqmsg' => '评论内容',
						'maxlength' => 30,
				),
		);
	}
	
	protected function _playlistHasOffset($playlist) {
	    return $playlist == 'circle';
	}
	
	protected function _rec_zone($playlist) {
	    switch ($playlist) {
	        case 'circle':
	            $strRes = 'viewvideo_circle_rec';
	            break;
	        case 'watch_later':
	            $strRes = 'viewvideo_watchlater_rec';
	            break;
            case 'watched':
                $strRes = 'viewvideo_watched_rec';
                break;
            case 'personal_recommend':
                $strRes = 'viewvideo_guess_rec';
                break;
            case 'related':
                $strRes = 'related_rec';
                break;
            case 'recommend':
                $strRes = 'viewvideo_hot_rec';
                break;
            case Model_Data_Recommend::HOMEPAGE_REC_HOT:
            	$strRes = 'viewvideo_hot_rec';
            	break;
            case Model_Data_Recommend::HOMEPAGE_REC_NEW:
            	$strRes = 'viewvideo_new_rec';
            	break;
            default:
                $strRes = '';
                break;
	    }
	    return $strRes;
	}

	/*添加未登录的时候已观看过的playlist*/
	protected function _update_unlogin_watched_playlist($videoId) {	
		if ($this->_uid > 0) {
			return;
		}	
		$_max        = 10; //最大播放列表长度
		$_cookie_key = 'watched_list'; //播放列表存cookie的key
        if (isset( $_COOKIE[$_cookie_key] ) && $_COOKIE[$_cookie_key]) {
    	    $watched_list = (array) json_decode($_COOKIE[$_cookie_key], true);
        } else {
            $watched_list = array();
        }	    	
    	foreach ($watched_list as $c_time => $c_vid) {
    		if($videoId == $c_vid){
    			unset($watched_list[$c_time]);
    		}
    	}
    	$time_key = time();
    	$watched_list[$time_key] = $videoId;
    	if (count($watched_list) > $_max){
    		$watched_list = array_slice($watched_list, -$_max, $_max, true);
    	}	    	
    	setcookie($_cookie_key, json_encode($watched_list));
	}
	
	/**
	 * 判断user-agent是否需要生成HTML5播放器所需的字段，从而适应iPhone和iPad的播放，如果这个视频需要HTML播放器，
	 * 但是无法生成HTML5播放器所需参数的话，则强制使用iframe播放页
	 * 
	 * @param string $strAgent user-agent header
	 * @param array $video 视频信息数组
	 * @return 如果需要生成HTML5播放器参数的话，会直接在$video参数中增加is_html5 和 player_html5_param字段
	 */
	protected function _process_html5_player($strAgent, &$video) {
		$bolNeedHtml5 = false;
		$strDeviceName = '';
		if (strpos($strAgent, 'iPhone') !== false) {
			$bolNeedHtml5 = true;
			$strDeviceName = 'iphone';
		} elseif (strpos($strAgent, 'iPad') !== false) {
			$bolNeedHtml5 = true;
			$strDeviceName = 'ipad';
		} elseif (strpos($strAgent, 'iPod') !== false) {
			$bolNeedHtml5 = true;
			$strDeviceName = 'ipod';
		} elseif ($this->request->param('debug_ipad')) {
			$bolNeedHtml5 = true;
			$strDeviceName = 'ipad';
		}
		$video['is_html5'] = $bolNeedHtml5;
		if (! $bolNeedHtml5) {			
			return;
		}
		
		$arrHtml5Param = null;
		$strPlayUrl = $video['play_url'];
		$intSharpCharPos = strpos($strPlayUrl, '#');
		if ($intSharpCharPos !== false) {
			$strPlayUrl = substr($strPlayUrl, 0, $intSharpCharPos);
		}
		switch ($video['domain']) {
			case 'ku6.com':
				/**
				 * [play_url] => http://v.ku6.com/show/wTgx6hAQ0TIPLaWs.html
				 * 				 http://my.ku6.com/watch?loc=datajinrijiaodian&v=7WyQ1RPcJNEKdY5ctn0Zsg..
				 * http://v.ku6.com/fetchwebm/wTgx6hAQ0TIPLaWs.m3u8
				 */
				if (strpos($strPlayUrl, '/show/') !== false) {
					$strVid = basename($video['play_url'], '.html');
				} else {
					$strQueryStr = parse_url($strPlayUrl, PHP_URL_QUERY);					
					$arrTmp = null;
					if ($strQueryStr) {
						parse_str($strQueryStr, $arrTmp);
						$strVid = $arrTmp['v'];
					}
				}
				if (strlen($strVid) > 0) {
					$arrHtml5Param = array(
						'src' => "http://v.ku6.com/fetchwebm/{$strVid}.m3u8",
						'device' => $strDeviceName,
					);
				}				
				break;
			case 'youku.com':
				/**
				 * [play_url] => http://v.youku.com/v_show/id_XNDExNDA2Mjg0.html
				 * http://v.youku.com/player/getRealM3U8/vid/XNDA5Nzg5NzU2/video.m3u8
				 */
				$strVid = basename($strPlayUrl, '.html');
				if (strncmp($strVid, 'id_', 3) == 0) {
					$strVid = substr($strVid, 3);
				}
				if (strlen($strVid) > 0) {
					$arrHtml5Param = array(
						'src' => "http://v.youku.com/player/getRealM3U8/vid/{$strVid}/video.m3u8",
						'device' => $strDeviceName,
					);
				}
				break;
			case 'tudou.com':
				/**
				 * [play_url] => http://www.tudou.com/programs/view/jzwIjJb8F2M/ 
				 */
				$strVid = basename(trim($strPlayUrl, '/'));
				if (strlen($strVid) > 0) {
					$arrHtml5Param = array(
						'code' => $strVid,
						'device' => $strDeviceName,
					);
				}
				break;
			case 'ifeng.com':
			
				break;
			case '56.com':
				/**
				 * [play_url] => http://www.56.com/u89/v_NjkwNTkyMjI.html
				 */
				$strVid = basename($strPlayUrl, '.html');
				if (strncmp($strVid, 'v_', 2) == 0) {
					$strVid = substr($strVid, 2);
				}
				if (strlen($strVid) > 0) {
					$strVid = base64_decode($strVid);
					if ($strVid) {
						$arrHtml5Param = array(
							'src' => "http://vxml.56.com/m3u8/{$strVid}/",
							'device' => $strDeviceName,
						);
					}
				}			
				break;
			case 'sohu.com':
				/**
				 * [player_url] => http://share.vrs.sohu.com/686050/v.swf
				 */
				$strRegex = '#/(\d+)/v\\.swf#';
				$intNum = preg_match($strRegex, $video['player_url'], $arrTmp);
				if ($intNum > 0) {
					$strVid = $arrTmp[1];
					if (strlen($strVid) > 0) {
						$arrHtml5Param = array(
							'src' => "http://hot.vrs.sohu.com/ipad{$strVid}.m3u8",
							'device' => $strDeviceName,
						);
					}
				}				
				break;
			case 'sina.com.cn':
				//http://v.iask.com/v_play_ipad.php?vid={iosid}
				if (isset($video['iosid']) && $video['iosid']) {
					$arrHtml5Param = array(
						'src' => "http://v.iask.com/v_play_ipad.php?vid={$video['iosid']}",
						'device' => $strDeviceName,
					);					
				}
				break;
			case 'qq.com':
				
				break;
			case '6.cn':
				
				break;
			case 'pptv.com':
				//http://api.v.pptv.com/api/ipad/play.js?rid={iosid}&cb=pptv_player_rand&r=0.1412951403938234
				if (isset($video['iosid']) && $video['iosid']) {
					$arrHtml5Param = array(
						'code' => $video['iosid'],
						'device' => $strDeviceName,
					);
				}				
				break;
			case 'joy.cn':
				
				break;
		}
		
		if ($arrHtml5Param !== null) {
			$video['player_html5_param'] = $arrHtml5Param;
		} else {
			//强制使用iframe页面播放
			$video['player_url'] = null;
		}
	}
}
