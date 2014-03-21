<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 视频、电视剧视频关系、专辑视频关系等页面逻辑
 * @author wangjiajun
 */
class Model_Logic_Video extends Model
{
    public static $basicFields = array('type', 'category', 'title', 'quality', 
    	'length', 'thumbnail', 'domain');
    
    public static $basicFieldsForMobile = array('type', 'category', 'title', 'quality', 
    	'length', 'thumbnail', 'domain', 'play_url', 'player_url');
    
    public static $playlists = array(
        'related' => '相关视频',
        'circle' => '圈内视频',
        'watched' => '看过的',
        'commented' => '吐槽过的',
        'watch_later' => '我收藏的',
        'shared' => '分享过的',
        'mooded' => '心情过的',
        'recommend' => '热门推荐',
        'personal_recommend' => '猜你喜欢',
    	'circled' => '圈过的视频'
    );
    
    public static $arrMoodMap = array(
    	//'xh' => '喜欢', 'wg' => '围观', 'dx' => '大笑', 'fn' => '鄙视', 'jn' => '囧',
    	'xh' => '喜欢', 'gx' => '搞笑', 'zj' => '震惊', 'bs' => '鄙视', 'fn' => '愤怒',
    	'ex' => '恶心', 'gd' => '感动', 'ng' => '难过', 'gl' => '给力', 'bj' => '杯具'
    );
  
    const VIDEO_RANK_DEFAULT = 1; //默认排序
    const VIDEO_RANK_COMMENT = 2; //按评论数排序
    const VIDEO_RANK_SHARE = 3; //按分享数排序
    public static $arrVideoRankField = array(
        self::VIDEO_RANK_DEFAULT => '_id',
        self::VIDEO_RANK_COMMENT => 'commented_count',
        self::VIDEO_RANK_SHARE => 'shared_count',
    );    
   
    public static $arrPlayerFinishRecoSite = array('youku.com' => 'logo_youku', 'tudou.com' => 'logo_tudou', 'ku6.com' => 'logo_ku6',
        'sina.com.cn' => 'logo_sina', '56.com' => 'logo_56', 'joy.cn' => 'logo_joy');
    
	/**
	 * 查询单个视频的基本信息，以及统计信息
	 * @param string $id
	 * @param array $fields
	 * @return array|null
	 */
	public function get($id, $fields = array(), $returnStatInfo = true)
	{
	    if (!$fields) {
	        $fields = self::$basicFields;
	    }
	    $modelDataVideo = new Model_Data_Video();
	    $video = $modelDataVideo->get($id, $fields);
	    
	    if ($video) {
    	    $videos = array($video);
    	    if ($returnStatInfo) {
    	    	$modelLogicStat = new Model_Logic_Stat();  
    	    	$modelLogicStat->complementVideoStatInfo($videos);
    	    }
    	    if (in_array('thumbnail', $fields)) {
    	        $this->complementThumbnail($videos);
    	    }
    	    $video = array_shift($videos);
	    }
	    
	    return $video;
	}
	
	/**
	 * 查询多个视频的基本信息，以及统计信息
	 * @param array $ids
	 * @param bool $keepOrder 是否保持传入参数中ID的顺序
	 * @param array $fields
	 * @return array
	 */
	public function getMulti($ids, $keepOrder = false, $fields = null, $returnStatInfo = true) 
	{
	    if (empty($fields)) {
	        $fields = self::$basicFields;
	    }
	    $modelDataVideo = new Model_Data_Video();
	    Profiler::startMethodExec();
	    $videos = $modelDataVideo->getMulti($ids, $fields);
	    Profiler::endMethodExec(__FUNCTION__.' getMulti');
	    $videoIds = Arr::pluck($videos, '_id');
	    if (!$videoIds) {
	        return array();
	    }
	    if (in_array('thumbnail', $fields)) {
	        $this->complementThumbnail($videos);
	    }
	    
	    if ($returnStatInfo) {
    	    $modelLogicStat = new Model_Logic_Stat();
    	    Profiler::startMethodExec();
    	    $modelLogicStat->complementVideoStatInfo($videos);
	        Profiler::endMethodExec(__FUNCTION__.' complementVideoStatInfo');
	    }
	    
        if ($keepOrder) {
	        $tmp = array();
	        foreach ($ids as $id) {
	            if (!is_string($id)) {
	                continue;
	            }
	            if (isset($videos[$id])) {
	                $tmp[$id] = $videos[$id];
	            }
	        }
	        $videos = $tmp;
	    }
	    
	    return $videos;
	}
	
    /**
     * 随机选择一批视频，返回基本信息，以及统计信息
     * @param int $count
     * @return array
     */
	public function random($count = 10)
	{
	    $modelDataVideo = new Model_Data_Video();
	    $videos = $modelDataVideo->random($count, self::$basicFields);
	    
	    $modelLogicStat = new Model_Logic_Stat();  
	    $modelLogicStat->complementVideoStatInfo($videos);
        
	    return $videos;
	}
	
	/**
	 * 观看视频
	 * @param string $videoId
	 * @param int $userId
	 * @param int $circleId 视频所属圈子
	 * @param array $extra 其它额外参数
	 * @return bool
	 */
	public function watch($videoId, $userId = NULL, $circleId = NULL, $extra = NULL)
	{    
	    if ($userId) {
    	    $modelDataUserVideo = new Model_Data_UserVideo($userId);
    	    $modelDataUserVideo->addToWatched($videoId);
	    }
	    
	    $modelLogicFeed = new Model_Logic_Feed();
	    $modelLogicFeed->addVideoFeed($videoId, Model_Data_VideoFeed::TYPE_WATCHED, 
	        $userId, $circleId, null, null, $extra);
	    if ($userId) {
    	    $modelLogicFeed->addUserFeed($userId, Model_Data_UserFeed::TYPE_WATCH_VIDEO, 
    	        $videoId, $circleId, null, null, $extra);
	    }
	    if ($circleId) {
    	    $modelLogicFeed->addCircleFeed($circleId, Model_Data_CircleFeed::TYPE_VIDEO_WATCHED, 
    	        $userId, $videoId, null, null, $extra);
	    }
	    
	    $modelDataVideoStatAll = new Model_Data_VideoStatAll();
	    $modelDataVideoStatAll->inc(array('_id' => $videoId), 'watched_count', 1, 
	        array('upsert' => true));
	    
	    return true;
	}
	
	/**
	 * 推视频
	 * @param string $videoId
	 * @param int $userId
	 * @param int $circleId 视频所属圈子
	 * @return bool
	 */
	public function like($videoId, $userId, $circleId = NULL)
	{
	    $modelDataVideo = new Model_Data_Video();
	    $video = $modelDataVideo->get($videoId, array('_id'));
	    if (!$video) {
	        throw new Model_Logic_Exception('视频未找到。');
	    }
	    
	    $modelDataUserVideo = new Model_Data_UserVideo($userId);
	    $modelDataUserVideo->addToLiked($videoId);
	    
	    $modelLogicFeed = new Model_Logic_Feed();
	    $modelLogicFeed->addVideoFeed($videoId, Model_Data_VideoFeed::TYPE_LIKED, 
	        $userId, $circleId);
	    $modelLogicFeed->addUserFeed($userId, Model_Data_UserFeed::TYPE_LIKE_VIDEO, 
	        $videoId, $circleId);
	    if ($circleId) {
    	    $modelLogicFeed->addCircleFeed($circleId, Model_Data_CircleFeed::TYPE_VIDEO_LIKED, 
    	        $userId, $videoId);
	    }
	    
	    return true;
	}
	
	/**
	 * 评论视频
	 * @param string $videoId
	 * @param array $sender array(_id => xx, nick => xx)
	 * @param string $data
	 * @param int $circleId 视频所属圈子
	 * @return bool
	 */
	public function comment($videoId, $sender, $data, $circleId = NULL)
	{
	    $userId = $sender['_id'];
	    $modelDataVideo = new Model_Data_Video();
	    $video = $modelDataVideo->get($videoId, array('_id'));
	    if (!$video) {
	        throw new Model_Logic_Exception('视频未找到');
	    }
	    if (empty($data)) {
	        throw new Model_Logic_Exception('评论内容不能为空');
	    }
	    
	    $arrUserNick = Model_Logic_Feed2::findUserFromText($data);
	    $objComment = new Model_Logic_Comment();
	    if (! $objComment->add(array('user_id' => $userId, 'video_id' => $videoId, 'data' => $data, 'users' => $arrUserNick))) {
	        throw new Exception('系统繁忙，请稍后重试');
	    }
	      
	    $modelDataUserVideo = new Model_Data_UserVideo($userId);
	    $modelDataUserVideo->addToCommented($videoId, $data, $arrUserNick);
	    
	    $modelLogicFeed = new Model_Logic_Feed();
	    $modelLogicFeed->addVideoFeed($videoId, Model_Data_VideoFeed::TYPE_COMMENTED, 
	        $userId, $circleId, $data, $arrUserNick);
	    $modelLogicFeed->addUserFeed($userId, Model_Data_UserFeed::TYPE_COMMENT_VIDEO, 
	        $videoId, $circleId, $data, $arrUserNick);
	    if ($circleId) {
    	    $modelLogicFeed->addCircleFeed($circleId, Model_Data_CircleFeed::TYPE_VIDEO_COMMENTED, 
    	        $userId, $videoId, $data, $arrUserNick);
	    }
	    $objLogicFeed2 = new Model_Logic_Feed2();
	    $objLogicFeed2->addFeedCommentVideo($sender, $videoId, $circleId, $data, $arrUserNick);
	    
	    $modelDataVideoStatAll = new Model_Data_VideoStatAll();
	    $modelDataVideoStatAll->inc(array('_id' => $videoId), 'commented_count', 1, 
	        array('upsert' => true));
	    
	    return true;
	}
	
	/**
	 * 添加视频tag墙的tag
	 * 
	 * @param string $videoId
	 * @param array $sender array(_id => xx, nick => xx)
	 * @param string $data
	 * @param int $circleId 视频所属圈子
	 * @param bool $bolForceAdd
	 * @return bool
	 */
	public function addXComment($videoId, $sender, $data, $circleId = NULL, $bolForceAdd = false)
	{
	    $modelDataVideo = new Model_Data_Video();
	    $video = $modelDataVideo->get($videoId, array('_id'));
	    if (! $video) {
	        throw new Model_Logic_Exception('视频未找到');
	    }
	    if (empty($data)) {
	        throw new Model_Logic_Exception('评论内容不能为空');
	    }
	    
		$strId = "{$videoId}_" . base64_encode($data);
		$objDataXComment = new Model_Data_XComment();
		//登录用户可以新增tag，未登录用户只能顶现有的tag
		$arrOpt = array();
		if ((empty($sender) || $sender['_id'] < 1) && ! $bolForceAdd) {
			$arrTmp = $objDataXComment->findOne(array('_id' => $strId), array('_id'));
			if (empty($arrTmp) || $arrTmp['_id'] != $strId) {
				throw new Exception('NEEDLOGIN');
			}
			$arrOpt['upsert'] = false;
		}
		try {
			$mixedRet = $objDataXComment->inc(array('_id' => $strId), 'num', 1, $arrOpt);
		} catch (Exception $e) {
			JKit::$log->warn($e->getMessage(), null, $e->getFile(), $e->getLine());
			$mixedRet = false;
		}
	    
		if (is_bool($mixedRet)) {
			return $mixedRet;
		} elseif (isset($mixedRet['n']) && $mixedRet['n'] > 0) {
			return true;
		} else {
	    	return false;
		}
	}
	
	/**
	 * 获取视频tag墙的tag
	 *
	 * @param string $videoId
	 * @param int $count
	 * @param int $offset
	 * @return array array('total' => 12, 'data' => array('data' => 'word', 'num' => 2))
	 */
	public function getXComment($videoId, $count, $offset = 0)
	{
		$arrRet = array('total' => 0);
		$objDataXComment = new Model_Data_XComment();
		$arrCond = array('_id' => array('$regex' => "^{$videoId}_"));
		$arrTmp = array();
		try {
			$arrTmp = $objDataXComment->find($arrCond, array(), array('num' => -1), 200, 0);
		} catch (Exception $e) {
			JKit::$log->warn($e->getMessage(), null, $e->getFile(), $e->getLine());
		}

		$arrDefaultTag = Model_Data_XComment::$arrDefaultXComment;
		$intTotalNum = 0;
		$arrTmp2 = array();
		if (! empty($arrTmp)) {			
			foreach ($arrTmp as $v) {
				$arrTmp3 = explode('_', $v['_id'], 2);
				$strData = base64_decode($arrTmp3[1]);
				if (isset($arrDefaultTag[$strData])) {
					unset($arrDefaultTag[$strData]);
				}
				$arrTmp2[] = array('data' => $strData, 'num' => $v['num']);
				$intTotalNum += $v['num'];
			}			
		}
		$intTotalNum += count($arrDefaultTag);
		
		if (($offset + $count) > count($arrTmp2)) {
			//需要处理所有视频都需要出的默认tag
			foreach ($arrDefaultTag as $k => $v) {
				$arrTmp2[] = array('data' => $k, 'num' => 1);
			}
		}
		if (count($arrTmp2) > $count) {
			$arrTmp2 = array_slice($arrTmp2, $offset, $count);
		}
		$arrRet['data'] = $arrTmp2;
		$arrRet['total'] = $intTotalNum;
		
		return $arrRet;
	}	
	
	/**
	 * 以后观看
	 * @param string $videoId
	 * @param int $userId
	 * @return bool
	 */
	public function watchLater($videoId, $userId)
	{
	    $modelDataVideo = new Model_Data_Video();
	    $video = $modelDataVideo->get($videoId, array('_id'));
	    if (!$video) {
	        throw new Model_Logic_Exception('视频未找到。');
	    }
	    
	    $modelDataUserVideo = new Model_Data_UserVideo($userId);
	    return $modelDataUserVideo->addToWatchlater($videoId);
	}
	
	/**
	 * 删除以后观看
	 * @param string $videoId
	 * @param int $userId
	 * @return bool
	 */
	public function deleteWatchLater($videoId, $userId)
	{	    
	    $modelDataUserVideo = new Model_Data_UserVideo($userId);
	    return $modelDataUserVideo->deleteFromWatchlater($videoId);
	}
	
	/**
	 * 分享
	 * @param string $videoId
	 * @param int $userId
	 * @param int $circleId
	 * @return bool
	 */
	public function share($videoId, $userId, $circleId = NULL)
	{
	    $modelDataVideo = new Model_Data_Video();
	    $video = $modelDataVideo->get($videoId, array('_id'));
	    if (!$video) {
	        throw new Model_Logic_Exception('视频未找到。');
	    }
	    
	    $modelDataUserVideo = new Model_Data_UserVideo($userId);
	    $modelDataUserVideo->addToShared($videoId);
	    
	    $modelLogicFeed = new Model_Logic_Feed();
	    $modelLogicFeed->addVideoFeed($videoId, Model_Data_VideoFeed::TYPE_SHARED, 
	        $userId, $circleId);
	    $modelLogicFeed->addUserFeed($userId, Model_Data_UserFeed::TYPE_SHARE_VIDEO, 
	        $videoId, $circleId);
	    if ($circleId) {
    	    $modelLogicFeed->addCircleFeed($circleId, Model_Data_CircleFeed::TYPE_VIDEO_SHARED, 
    	        $userId, $videoId);
	    }
	    
	    $modelDataVideoStatAll = new Model_Data_VideoStatAll();
	    $modelDataVideoStatAll->inc(array('_id' => $videoId), 'shared_count', 1, 
	        array('upsert' => true));
	    
	    return true;
	}
	
	/**
	 * 心情
	 * @param string $videoId
	 * @param string $mood 见Model_Data_VideoFeed里的常量定义
	 * @param int $userId
	 * @param int $circleId
	 * @return bool
	 */
	public function mood($videoId, $mood, $userId, $circleId = NULL)
	{
	    $modelDataVideo = new Model_Data_Video();
	    $video = $modelDataVideo->get($videoId, array('_id'));
	    if (!$video) {
	        throw new Model_Logic_Exception('视频未找到。');
	    }

	    $modelDataVideoStatAll = new Model_Data_VideoStatAll();
	    $modelDataVideoStatAll->inc(array('_id' => $videoId), 'mooded_count.'.$mood, 1, array('upsert' => true));
	    $modelDataVideoStatAll->inc(array('_id' => $videoId), 'mooded_count.total');
	    
	    $modelLogicFeed = new Model_Logic_Feed();
	    $modelLogicFeed->addVideoFeed($videoId, Model_Data_VideoFeed::TYPE_MOODED, $userId, $circleId, $mood);
	    
	    if ($userId < 1) {
	    	return true; //未登录的心情
	    }
	    
	    $modelLogicFeed->addUserFeed($userId, Model_Data_UserFeed::TYPE_MOOD_VIDEO, $videoId, $circleId, $mood);
	    if ($circleId) {
    	    $modelLogicFeed->addCircleFeed($circleId, Model_Data_CircleFeed::TYPE_VIDEO_MOODED, $userId, $videoId, $mood);
	    }
	    
	    $modelDataUserVideo = new Model_Data_UserVideo($userId);
	    $modelDataUserVideo->addToMooded($videoId);
	    
	    $objLogicFeed2 = new Model_Logic_Feed2();
	    $objLogicFeed2->addFeedMoodVideo($userId, $videoId, $circleId, $mood);	    
	    
	    return true;
	}
    
    /**
     * 获取该视频下发过feed的用户信息
     * @param string $videoId
     * @param int $count
     * @param int $offset
     * @return array
     */
    public function feededUsers($videoId, $intType, $count = 10, $offset = 0)
    {
        $modelDataVideoFeed = new Model_Data_VideoFeed();
        $modelDataVideoStatAll = new Model_Data_VideoStatAll();
        $arrCond = array(
            'video_id' => $videoId,
            'type' => $intType,
        );
        $stats = $modelDataVideoStatAll->findOne(array('_id' => $videoId), array('mooded_count'));
        $intTotal = (int) @$stats['mooded_count']['total'];
        if ($intTotal > 0) {
            $feeds = $modelDataVideoFeed->find($arrCond, array('user_id', 'data'), array('create_time' => -1), $count, $offset);
            $arrTmp = array();
            foreach ($feeds as $v) {
                if (isset($v['user_id']) && ! isset($arrTmp[$v['user_id']])) {
                    $arrTmp[$v['user_id']] = $v['data'];
                }
            }
            $userIds = array_keys($arrTmp);
            $users = array();
            if ($userIds) {
                $modelDataUser = new Model_Data_User();
                $users = $modelDataUser->getMulti($userIds, array('nick', 'avatar'), TRUE);
            }
            $users = array_slice($users, 0, $count);
            foreach ($users as $k => $v) {
                $users[$k]['data'] = $arrTmp[$v['_id']];
            }
        } else {
            $users = array();
        }
        return array('total' => $intTotal, 'data' => $users);
    }    
    
    /**
     * 获取已观看视频列表
     * @param int $userId
     * @param int $offset
     * @param int $count
     * @return array
     * array(
     * 	total => 总数,
     *  data => 数据
     * )
     */
    public function getWatched($userId, $offset = 0, $count = 10, $arrField = NULL) 
    {
        $result = $this->getWatchedVid($userId, $offset, $count);
    	$result['total'] = $result['count'];
    	if($result['data']) {
    		$modelLogicVideo = new Model_Logic_Video();
    		$result['data'] = $modelLogicVideo->getMulti(array_keys($result['data']), true, $arrField);
    		$this->complementRecType($result['data']);
    		$this->complementCircleVideoRel($result['data']);
    	}
    	return $result;
    }
    
    public function getWatchedVid($userId, $offset = 0, $count = 10) {
        if (! $userId) {
            if (isset($_COOKIE['watched_list']) && ($strJson = trim($_COOKIE['watched_list']))) {
                $arrTmp = (array) json_decode($strJson, true);
                $result = array_flip($arrTmp);
                $result = array('count' => count($result), 'data' => $result);
            } else {
                $result = array('count' => 0, 'data' => array());
            }
        } else {
            $modelDataUserVideo = new Model_Data_UserVideo($userId);
            $result = $modelDataUserVideo->getWatched($offset, ($offset + $count-1), true);
        } 

        return $result;
    } 
    
    /**
     * 获取推过的视频列表
     * @param int $userId
     * @param int $offset
     * @param int $count
     * @return array
     * array(
     * 	total => 总数,
     *  data => 数据
     * )
     */
    public function getLiked($userId, $offset = 0, $count = 10) 
    {
    	$modelDataUserVideo = new Model_Data_UserVideo($userId);
    	$result = $modelDataUserVideo->getLiked($offset, ($offset + $count-1), true);
    	$result['total'] = $result['count'];
    	if($result['data']) {
    		$modelLogicVideo = new Model_Logic_Video();
    		$result['data'] = $modelLogicVideo->getMulti(array_keys($result['data']), true);
    		$this->complementRecType($result['data']);
    		$this->complementCircleVideoRel($result['data']);
    	}
    	return $result;
    }
    
    /**
     * 获取评论过的视频列表
     * @param int $userId
     * @param int $offset
     * @param int $count
     * @return array
     * array(
     * 	total => 总数,
     *  data => 数据
     * )
     */
    public function getCommented($userId, $offset = 0, $count = 10, $vidUnique=true, $arrField = NULL) 
    {
    	$modelDataUserVideo = new Model_Data_UserVideo($userId);
    	$result = $modelDataUserVideo->getCommented($offset, ($offset + $count-1), true);
    	Kohana::$log->debug(__FUNCTION__, $result);
    	$result['total'] = $result['count'];
    	if($result['data']) {
    		$modelLogicVideo = new Model_Logic_Video();
    		$videos = $modelLogicVideo->getMulti(Arr::pluck($result['data'], 'video_id'), true, $arrField);
    		$this->complementRecType($videos);
    		$this->complementCircleVideoRel($videos);
    		if($vidUnique) {
    			$tmp = array();
    			foreach ($result['data'] as $index => $value) {
    				if(!isset($tmp[$value['video_id']])) {
    					$tmp[$value['video_id']] = 1;
    				} else {
    					 unset($result['data'][$index]);
    				}
	    		}
    		} 
    		
    		foreach ($result['data'] as $index => &$value) {
    		    if (isset($videos[$value['video_id']])) {
    		        $value = array_merge($value, $videos[$value['video_id']]);
    		    } else {
    		        unset($result['data'][$index]);
    		    }
    		}
    		
    		$result['data'] = array_values($result['data']);
    		
    	}
    	return $result;
    }
    
    /**
     * 获取以后观看视频列表
     * @param int $userId
     * @param int $offset
     * @param int $count
     * @return array
     * array(
     * 	total => 总数,
     *  data => 数据
     * )
     */
    public function getWatchLater($userId, $offset = 0, $count = 10, $arrField = NULL) 
    {
        if (! $userId) {
            if (isset($_COOKIE['watchlater_list']) && ($strJson = trim($_COOKIE['watchlater_list']))) {
                $arrTmp = (array) json_decode($strJson, true);
                $result = array_flip($arrTmp);
                $result = array('count' => count($result), 'data' => $result);
            } else {
                $result = array('count' => 0, 'data' => array());
            }          
        } else {
    	    $modelDataUserVideo = new Model_Data_UserVideo($userId);
    	    $result = $modelDataUserVideo->getWatchlater($offset, ($offset + $count-1), true);
        }
    	$result['total'] = $result['count'];
    	if($result['data']) {
    		$modelLogicVideo = new Model_Logic_Video();
    		$result['data'] = $modelLogicVideo->getMulti(array_keys($result['data']), true, $arrField);
    		$this->complementRecType($result['data']);
    		$this->complementCircleVideoRel($result['data']);
    	}
    	return $result;
    }
    
    /**
     * 获取分享过的视频列表
     * @param int $userId
     * @param int $offset
     * @param int $count
     * @return array
     * array(
     * 	total => 总数,
     *  data => 数据
     * )
     */
    public function getShared($userId, $offset = 0, $count = 10, $arrField = NULL) 
    {
    	$modelDataUserVideo = new Model_Data_UserVideo($userId);
    	$result = $modelDataUserVideo->getShared($offset, ($offset + $count-1), true);
    	$result['total'] = $result['count'];
    	if($result['data']) {
    		$modelLogicVideo = new Model_Logic_Video();
    		$result['data'] = $modelLogicVideo->getMulti(array_keys($result['data']), true, $arrField);
    		$this->complementRecType($result['data']);
    		$this->complementCircleVideoRel($result['data']);
    	}
    	return $result;
    }
    
    /**
     * 获取心情过的视频列表
     * @param int $userId
     * @param int $offset
     * @param int $count
     * @return array
     * array(
     * 	total => 总数,
     *  data => 数据
     * )
     */
    public function getMooded($userId, $offset = 0, $count = 10, $arrField = NULL) 
    {
    	$modelDataUserVideo = new Model_Data_UserVideo($userId);
    	$result = $modelDataUserVideo->getMooded($offset, ($offset + $count -1) , true);
    	$result['total'] = $result['count'];
    	if($result['data']) {
    		$modelLogicVideo = new Model_Logic_Video();
    		$result['data'] = $modelLogicVideo->getMulti(array_keys($result['data']), true, $arrField);
    		$this->complementRecType($result['data']);
    		$this->complementCircleVideoRel($result['data']);
    	}
    	return $result;
    }
    
    /**
     * 给定一个视频列表，补上视频的缩略图。
     * @param array $videos
     * @return void
     */
    public function complementThumbnail(&$videos)
    {
	    $modelDataVideo = new Model_Data_Video();
        $thumbnails = $modelDataVideo->getThumbnails(Arr::pluck($videos, '_id'));
        foreach ($videos as &$video) {
            if ($thumbnails[$video['_id']]) {
                $video['thumbnail'] = 'group1/'.$thumbnails[$video['_id']]['picpath'];
                $video['tn_width'] = $thumbnails[$video['_id']]['w'];
                $video['tn_height'] = $thumbnails[$video['_id']]['h'];
            } else {
                $video['thumbnail'] = '';
                $video['tn_width'] = 0;
                $video['tn_height'] = 0;
            }
        }
    }
    
	/**
     * 给定一个视频列表，补上rec_type
     * @param array $videos
     * @return void
     */
    public function complementRecType(&$videos, $recType=0)
    {
	    if(!$videos) {
	    	return;
	    }
	    
	    foreach($videos as $k=>$row) {
	    	$videos[$k]['rec_type'] = $recType;
	    }
    }
    
    
	/**
     * 给定一个视频列表，补上圈子信息
     * @param array $videos
     * @param string 
     * 
     * @return void
     */
    public function complementCircleVideoRel(&$videos)
    {
	    if(!$videos) {
	    	return;
	    }
	    $arrVids = Arr::pluck($videos, "_id");
	    $objLogicRecommend = new Model_Logic_Recommend();
	    $arrTmpCircleVideoRel = $objLogicRecommend->getCircleInfoByVids($arrVids);
	    $objComment = new Model_Logic_Comment();
		$arrComment = $objComment->topN($arrVids, 3, array('avatarSize' => 30));
	    foreach($videos as $k=>$row) {
	    	$tmpVid = $row["_id"];
	    	$tmpCircles = isset($arrTmpCircleVideoRel[$tmpVid]) ? $arrTmpCircleVideoRel[$tmpVid]:array() ;
	    	if($tmpCircles) {
	    		$row['circle'] = array_pop($tmpCircles);
	    	} else {
	    		$row['circle'] = array();
	    	}
	    	if (isset($arrComment[$tmpVid])) {
				$row['comments'] = $arrComment[$tmpVid];
			} else {
				$row['comments'] = array();
			}
			$videos[$k] = $row;
	    }
    }
    
    /**
     * 指定播放列表是否支持分页
     * @param string $strName
     * @return bool
     */
    public static function isPlaylistHasPager($strName) {
        $playlists = array(
            'related' => true,
            'circle' => true,
            'watched' => true,
            'commented' => true,
            'watch_later' => true,
            'shared' => true,
            'mooded' => true,
        );
        if (isset($playlists[$strName])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取播放结束后的推荐视频
     * @param array $arrParam array(id => video id, uid => 当前用户id, 'count' => 返回值视频个数, 'skip_num' => 需要跳过相关推荐视频的个数)
     * @return array
     */
    public function getPlayerFinishRec($arrParam) {
        $arrRet = null;
        $strRecZone = null;
        $bolFoundVids = false;
        $arrVids = null;
        if (isset($arrParam['skip_num'])) {
        	$intSkipNum = (int) $arrParam['skip_num'];
        } else {
        	$intSkipNum = 6; //跳过6个视频，以避免和播放器旁边已经出现的相关视频第一页重复
        }
        
        $arrVids = Model_Data_Recommend::getRecommendVideos($arrParam['id']);
        if (! empty($arrVids)) {
        	if (count($arrVids) > $intSkipNum) {        		
        		$arrVids = array_slice($arrVids, $intSkipNum);  
        	}
            $bolFoundVids = true;
            $strRecZone = 'player_related_rec';
        }
        if (! $bolFoundVids) {
            //获取最受欢迎的视频
            $arrMembers = Model_Data_Recommend::getHomepageRecommendVideos(0, 99, null, true, Model_Data_Recommend::HOMEPAGE_REC_POP);
            if (empty($arrMembers)) {
                return $arrRet;
            }
            foreach ($arrMembers as $v) {
                $arrVids[] = $v['vid'];
            }
            $strRecZone = 'player_hot_rec';
        }
        if (empty($arrVids)) {
            return $arrRet;
        }
        
        //去掉最近看过的15个视频
        $arrRecentWatchedVid = $this->getWatchedVid($arrParam['uid'], 0, 15);
        $intCount = $arrParam['count'];
        if (isset($arrRecentWatchedVid['data']) && ! empty($arrRecentWatchedVid['data'])) {
            $arrRecentWatchedVid = $arrRecentWatchedVid['data'];
            $arrVidsTmp = array();
            $intTmpCount = count($arrVids);
            foreach ($arrVids as $v) {
            	if (! isset($arrRecentWatchedVid[$v]) || $intTmpCount < 6) {
            		$arrVidsTmp[] = $v;
            	} else {
            		$intTmpCount--;
            	}
            }
            if (! empty($arrVidsTmp)) {
            	$arrVids = $arrVidsTmp;
            }
        }
        
        $arrRet = $this->getMulti($arrVids, true, null, true);
        
        //域名过滤
        $arrTmp = array();
        $i = 0;
        foreach ($arrRet as $k => $v) {
            if (isset(self::$arrPlayerFinishRecoSite[$v['domain']])) {
                $arrTmp[$k] = $v;
                $i++;
            }
            if ($i >= $intCount) {
                break;
            }
        }
        if (empty($arrTmp)) {
        	return null;
        } else {
        	$arrRet = array('video' => $arrTmp, 'rec_zone' => $strRecZone);
        }
        
        return $arrRet;
    }
    
    public function getHomepageRecoRandomVideo($arrType, $intCount) {
    	$arrRet = array();
    	foreach ($arrType as $key) {
    	    $arrMembers = Model_Data_Recommend::getHomepageRecommendVideos(0, 500, null, true, $key);
            if (empty($arrMembers)) {
                continue;
            }
            $arrVids = array();
            if (count($arrMembers) <= $intCount) {
	            foreach ($arrMembers as $v) {
	                $arrVids[] = $v['vid'];
	            }
            } else {
            	$arrTmpKey = array_rand($arrMembers, $intCount * 2); //多取一倍，防止某些vid取不到信息导致结果少于预定个数的问题
            	foreach ($arrTmpKey as $v) {
            		$arrVids[] = $arrMembers[$v]['vid'];
            	}
            }
            if (empty($arrVids)) {
            	continue;
            }
            
            $arrTmp = $this->getMulti($arrVids, false, null, false);
            if (! empty($arrTmp)) {
            	if (count($arrTmp) > $intCount) {
            		$arrTmp = array_slice($arrTmp, 0, $intCount, true);
            	}
            	$arrRet[$key] = $arrTmp;
            }
    	}
    	
    	return $arrRet;
    }
    
    public function getRelatedCircle($strVid, $mixedUid = null, $intCount = 3) {
    	$arrRet = null;
    	try {
    		$arrTmp = Model_Data_Recommend::relatedCircles($strVid, $mixedUid, 0, $intCount, Model_Data_Recommend::RELATED_CIRCLE_RECO_VIDEO);
    		if (empty($arrTmp['circles'])) {
    			return $arrRet;
    		}
    		
    		$objDataCircle = new Model_Data_Circle();
    		$arrRet = $objDataCircle->getMulti($arrTmp['circles'], Model_Logic_Circle::$basicFields, false, Model_Data_Circle::STATUS_PUBLIC);    		
    	} catch (Exception $e) {
    		JKit::$log->warn($e->getMessage(), null, $e->getFile(), $e->getLine());
    		return $arrRet;
    	}
    	
    	return $arrRet;
    }
}