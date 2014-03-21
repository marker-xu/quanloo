<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 统计、排行榜相关页面逻辑
 * @author xucongbin
 */
class Model_Logic_Stat extends Model
{    
    /**
     * 热推视频
     * @param int $count
     * @return array
     */
    public function mostLikedVideos($count = 10)
    {
        $modelDataVideoStatRecent = new Model_Data_VideoStatRecent();
        $docs = $modelDataVideoStatRecent->find(array(), array('_id'), 
            array('liked_count' => -1), $count);
        $videoIds = Arr::pluck($docs, '_id');
        $modelLogicVideo = new Model_Logic_Video();
        $videos = $modelLogicVideo->getMulti($videoIds, TRUE);
        
        if (count($videos) < $count) {
            Kohana::$log->warn('recent most liked videos count '.count($videos).' is less than '.$count);
            $videos = array_merge($videos, $modelLogicVideo->random($count - count($videos)));
        }
        
        $this->complementVideoCircleInfo($videos);
        
        return $videos;
    }
    
    /**
     * 热播视频
     * @param int $count
     * @return array
     */
    public function mostWatchedVideos($count = 10)
    {
        $modelDataVideoStatRecent = new Model_Data_VideoStatRecent();
        $docs = $modelDataVideoStatRecent->find(array(), array('_id'), 
            array('watched_count' => -1), $count);
        $videoIds = Arr::pluck($docs, '_id');
        $modelLogicVideo = new Model_Logic_Video();
        $videos = $modelLogicVideo->getMulti($videoIds, TRUE);
        
        if (!$videos) {
            Kohana::$log->warn('no recent most watched videos');
            $videos = $modelLogicVideo->random($count);
        }
        
        $this->complementVideoCircleInfo($videos);
        
        return $videos;
    }
     
    /**
     * 圈内热推视频
     * @param int $circleId
     * @param int $count
     * @return array
     */
    public function mostLikedCircleVideos($circleId, $count = 10)
    {
        $modelDataCircleVideoStatRecent = new Model_Data_CircleVideoStatRecent();
        $docs = $modelDataCircleVideoStatRecent->find(array('circle_id' => $circleId), 
            array('_id'), array('liked_count' => -1), $count);
        $videoIds = Arr::pluck($docs, '_id');
        $modelLogicVideo = new Model_Logic_Video();
        $videos = (array) $modelLogicVideo->getMulti($videoIds, TRUE);
        
        return $videos;
        
    }
     
    /**
     * 圈内热播视频
     * @param int $circleId
     * @param int $count
     * @return array
     */
    public function mostWatchedCircleVideos($circleId, $count = 10)
    {
        $modelDataCircleVideoStatRecent = new Model_Data_CircleVideoStatRecent();
        $docs = $modelDataCircleVideoStatRecent->find(array('circle_id' => $circleId), 
            array('_id'), array('watched_count' => -1), $count);
        $videoIds = Arr::pluck($docs, '_id');
        $modelLogicVideo = new Model_Logic_Video();
        $videos = (array) $modelLogicVideo->getMulti($videoIds, TRUE);
        
        return $videos;
        
    }
    
    /**
     * 给定一个视频列表，补上视频的统计信息。
     * @param array $videos
     * @return void
     */
    public function complementVideoStatInfo(&$videos)
    {
	    $videoIds = Arr::pluck($videos, '_id');
	    if (!$videoIds) {
	        return;
	    }
	    $modelDataVideoStatAll = new Model_Data_VideoStatAll();
	    Profiler::startMethodExec();
        $stats = $modelDataVideoStatAll->find(array('_id' => array('$in' => $videoIds)), 
            array('shared_count', 'mooded_count', 'watched_count', 'outside_watched_count'));
        Profiler::endMethodExec(__FUNCTION__." find");
        foreach ($videos as &$video) {
            $stat = array_merge(array(
            	'shared_count' => 0, 
            	'mooded_count' => array(
                    'xh' => 0,
                    'wg' => 0,
                    'dx' => 0,
                    'fn' => 0,
                    'total' => 0,
                ), 
            	'watched_count' => 0, 
            	'outside_watched_count' => 0
            ), isset($stats[$video['_id']]) ? $stats[$video['_id']] : array());
            $stat['watched_count'] = $stat['watched_count'] + $stat['outside_watched_count'];
	        $video = array_merge($video, $stat);
        }
        unset($video);
    }
    
    /**
     * 给定一个视频列表，补上视频的圈内统计信息。每个视频的圈子取视频在其中观看次数最多的那一个。
     * @param array $videos
     * @return void
     */
    public function complementVideoCircleInfo(&$videos)
    {
        $keys = array('video_id' => 1);
        $initial = array('circle_id' => 0, 'shared_count' => 0, 'mooded_count' => array(
                'xh' => 0,
                'wg' => 0,
                'dx' => 0,
                'fn' => 0,
                'total' => 0,
            ), 'watched_count' => 0);
        $reduce = "function (doc, prev) { 
        	if (doc.watched_count > prev.watched_count) {
        		prev.circle_id = doc.circle_id;
        		prev.shared_count = doc.shared_count;
        		for (var key in prev.mooded_count) {
            		if (doc.mooded_count[p] != undefined) {
            			prev.mooded_count[p] += doc.mooded_count[p];
            		}
        		}
        		prev.watched_count = doc.watched_count;
        	}
    	}";
        $videoIds = Arr::pluck($videos, '_id');
	    if (!$videoIds) {
	        return;
	    }
        $condition = array('video_id' => array('$in' => $videoIds));
        $modelDataCircleVideoStatAll = new Model_Data_CircleVideoStatAll();
        $result = $modelDataCircleVideoStatAll->group($keys, $initial, $reduce, array(
            'condition' => $condition
        ));
        Kohana::$log->debug(__FUNCTION__, $result);
        if (empty($result) || ! isset($result['retval'])) {
            return;
        }
        $stats = $result['retval'];
        
        $circleIds = Arr::pluck($stats, 'circle_id');
        $modelDataCircle = new Model_Data_Circle();
        $circles = $modelDataCircle->getMulti($circleIds, array('title'));
        
        foreach ($stats as $stat) {
            if (!isset($circles[$stat['circle_id']])) {
                continue;
            }
            $videos[$stat['video_id']]['circle'] = array_merge($circles[$stat['circle_id']], 
                array(
                	'shared_count' => $stat['shared_count'],
                	'mooded_count' => $stat['mooded_count'],
                	'watched_count' => $stat['watched_count']
                )
            );
        }
    }
    
    /**
     * 给定一个圈子列表，补上圈子的统计信息。
     * @param array $circles
     * @return void
     */
    public function complementCircleStatInfo(&$circles)
    {
	    $circleIds = Arr::pluck($circles, '_id');
	    if (!$circleIds) {
	        return;
	    }
	    $modelDataCircleStatAll = new Model_Data_CircleStatAll();
        $stats = $modelDataCircleStatAll->find(array('_id' => array('$in' => $circleIds)), 
            array('video_count', 'user_count', 'shared_count', 'mooded_count', 'watched_count'));
        $modelLogicCircle = new Model_Logic_Circle();
        foreach ($circles as &$circle) {
            $stat = array_merge(array(
            	'video_count' => 0,
            	'user_count' => 0,
            	'shared_count' => 0, 
	            'mooded_count' => array(
                    'xh' => 0,
                    'wg' => 0,
                    'dx' => 0,
                    'fn' => 0,
                    'total' => 0,
                ), 
            	'watched_count' => 0
            ), isset($stats[$circle['_id']]) ? $stats[$circle['_id']] : array());
	        $circle = array_merge($circle, $stat);
        }
        unset($circle);
    }
    
    /**
     * 给定一个用户列表，补上用户的统计信息。
     * @param array $circles
     * @return void
     */
    public function complementUserStatInfo(&$users)
    {
	    $userIds = Arr::pluck($users, '_id');
	    if (!$userIds) {
	        return;
	    }
	    $modelDataUserStatAll = new Model_Data_UserStatAll();
        $stats = $modelDataUserStatAll->find(array('_id' => array('$in' => $userIds)), 
            array('share_count', 'mood_count', 'watch_count', 'subscribed_circle_count', 
            'followings_count', 'followers_count'));
        foreach ($users as &$user) {
            $stat = array_merge(array(
            	'share_count' => 0, 
	            'mood_count' => array(
                    'xh' => 0,
                    'wg' => 0,
                    'dx' => 0,
                    'fn' => 0,
	            'jn' => 0,    
                    'total' => 0,
                ),
            	'watch_count' => 0,  
	            'subscribed_circle_count' => 0,  
	            'followings_count' => 0,  
	            'followers_count' => 0
            ), isset($stats[$user['_id']]) ? $stats[$user['_id']] : array());
	        $user = array_merge($user, $stat);
        }
        unset($user);
    }
    
 	/**
     * 
     * 首页悬浮
     * @param array $arrTypes 见Model_Data_QueryStat::CATEGORY_XXX
     * 
     * @return Array
     */
    public function getRecentQueryStatsByHour($arrTypes) {
    	$arrReturn = array();
    	
    	$objModelQueryStat = new Model_Data_QueryStat();
    	$tmpQuery = array(
    		'recent' => Model_Data_QueryStat::RECENT_HOUR
    	);
    	$order = array(
    		"order" => 1
    	);
    	$fields = array(
    	);
    	foreach($arrTypes as $strType) {
    		$period = $objModelQueryStat->getRecentPeriod(Model_Data_QueryStat::RECENT_HOUR, $strType);
    		if( !$period ) {
    			continue;
    		}
    		$query = array_merge(array("period" => $period), $tmpQuery);
    		$query['category'] = $strType;
    		$arrReturn[$strType] = $objModelQueryStat->find($query, $fields, array(), 10);	
    	}
    	
    	return $arrReturn;
    }
    
    /**
     * 
     * Enter description here ...
     * 
     * @return array(
     * 	recent_hot => 实时热点
     *  week_focus => 一周关注,
     *  hot_people => array(
     *  	men => 男明星,
     *  	women => 女明星
     *  ),
     *  
     *  day_tv => 电视剧,
     *  day_movie => 电影,
     * )
     */
    public function getQueryTopContent() {
    	$arrReturn = array();
    	
    	$objModelQueryStat = new Model_Data_QueryStat();
    	$arrPeriods = $objModelQueryStat->batchGetPeriodBySameRecentAndType(NULL);
    	$sort = array(
    		"order" => 1
    	);
    	//1 实时热点, recent_hot
    	$tmpKey = Model_Data_QueryStat::RECENT_HOUR."_".Model_Data_QueryStat::CATEGORY_KEYWORDS;
    	if( isset($arrPeriods[$tmpKey]) ) {
    		$queryRecentHot = array(
	    		'period' => $arrPeriods[$tmpKey],
	    		'recent' => Model_Data_QueryStat::RECENT_HOUR,
	    		'category' => Model_Data_QueryStat::CATEGORY_KEYWORDS
	    	);
	    	$arrReturn['recent_hot'] = $objModelQueryStat->find($queryRecentHot, array(), $sort, 20);
    	}
    	
    	//2 一周关注, week_focus
    	$tmpKey = Model_Data_QueryStat::RECENT_WEEK."_".Model_Data_QueryStat::CATEGORY_KEYWORDS;
    	if( isset($arrPeriods[$tmpKey]) ) {
    		$queryWeekFocus = array(
	    		'period' => $arrPeriods[$tmpKey],
	    		'recent' => Model_Data_QueryStat::RECENT_WEEK,
	    		'category' => Model_Data_QueryStat::CATEGORY_KEYWORDS
	    	);
	    	$arrReturn['week_focus'] = $objModelQueryStat->find($queryWeekFocus, array(), $sort, 20);
    	}
    	
    	
    	//3 热点人物, hot_people
    	$arrReturn['hot_people'] = array(
    		'men' => array(),
    		'women' => array()
    	);
    	$tmpKey = Model_Data_QueryStat::RECENT_DAY."_".Model_Data_QueryStat::CATEGORY_STAR;
    	if( isset($arrPeriods[$tmpKey]) ) {
    		$queryStar = array(
	    		'period' => $arrPeriods[$tmpKey],
	    		'recent' => Model_Data_QueryStat::RECENT_DAY,
	    		'category' => Model_Data_QueryStat::CATEGORY_STAR
	    	);
	    	$arrReturn['hot_people']['men'] = $objModelQueryStat->find($queryStar, array(), $sort, 20);
    	}
    	$tmpKey = Model_Data_QueryStat::RECENT_DAY."_".Model_Data_QueryStat::CATEGORY_ACTRESS;
    	if( isset($arrPeriods[$tmpKey]) ) {
    		$queryActress = array(
	    		'period' => $arrPeriods[$tmpKey],
	    		'recent' => Model_Data_QueryStat::RECENT_DAY,
	    		'category' => Model_Data_QueryStat::CATEGORY_ACTRESS
	    	);
	    	$arrReturn['hot_people']['women'] = $objModelQueryStat->find($queryActress, array(), $sort, 20);
    	}
    	
    	//4 电视剧 day_tv
    	$tmpKey = Model_Data_QueryStat::RECENT_DAY."_".Model_Data_QueryStat::CATEGORY_TV;
    	if( isset($arrPeriods[$tmpKey]) ) {
    		$queryDayTv = array(
	    		'period' => $arrPeriods[$tmpKey],
	    		'recent' => Model_Data_QueryStat::RECENT_DAY,
	    		'category' => Model_Data_QueryStat::CATEGORY_TV
	    	);
	    	$arrReturn['day_tv'] = $objModelQueryStat->find($queryDayTv, array(), $sort, 20);
    	}
    	
    	//4 电影 day_movie
    	$tmpKey = Model_Data_QueryStat::RECENT_DAY."_".Model_Data_QueryStat::CATEGORY_MOVIE;
    	if( isset($arrPeriods[$tmpKey]) ) {
    		$queryDayMovie = array(
	    		'period' => $arrPeriods[$tmpKey],
	    		'recent' => Model_Data_QueryStat::RECENT_DAY,
	    		'category' => Model_Data_QueryStat::CATEGORY_MOVIE
	    	);
	    	$arrReturn['day_tv'] = $objModelQueryStat->find($queryDayMovie, array(), $sort, 20);
    	}
    	
    	return $arrReturn;
    }
    
    public function mostQueryKeywords($count = 5)
    {
    	$modelDataQueryStat = new Model_Data_QueryStat();
    	$docs = $modelDataQueryStat->find(array(
    	    'recent' => Model_Data_QueryStat::RECENT_DAY,
    	    'category' => Model_Data_QueryStat::CATEGORY_KEYWORDS
    	), array('query'), array('period' => -1, 'order' => 1), $count * 2);
    	Kohana::$log->debug(__FUNCTION__, $docs);
    	$keywords = Arr::pluck($docs, 'query');
    	$whitelist = $modelDataQueryStat->hotQueryWhitelist();
    	$keywords = array_unique(array_merge($whitelist, $keywords));
    	$blacklist = $modelDataQueryStat->hotQueryBlacklist();
    	$keywords = array_values(array_diff($keywords, $blacklist));
    	$keywords = array_slice($keywords, 0, $count);
    	return $keywords;
    }
    
	/**
	 * 发送统计日志
	 * @param string $pageId
	 * @param string $url
	 * @param int $userId
	 * @param string $videoId
	 * @param string $outUrl
	 * @return bool
	 */
	public function log($pageId, $userId = NULL, $ip = NULL, $videoId = NULL, 
	    $url = NULL, $outUrl = NULL, array $arrExtra = null)
	{
	    $modelDataStat = new Model_Data_Stat();
	    $data = array(
	        'page_id' => $pageId,
	    );
	    if ($userId !== null) {
	        $data['user_id'] = $userId;
	    }
	    if ($ip !== null) {
	        $data['ip'] = $ip;
	    }
	    if ($videoId !== null) {
	        $data['video_id'] = $videoId;
	    }
	    if ($url !== null) {
	        $data['url'] = urlencode($url);
	    }
	    if ($outUrl !== null) {
	        $data['out_url'] = $outUrl;
	    }
	    if ($arrExtra !== null) {
	    	$data += $arrExtra;
	    }
	    return $modelDataStat->log($data);
	}
	
	/**
	 * 圈内热播视频
	 * @param int $circleId
	 * @param string $type
	 * @param int $count
	 * @param int $offset
	 * @return array
	 */
	public function mostPlayedVideosInCircle($circleId, $type = 'day', $count = null, 
	    $offset = null)
	{
	    $modelCircleVideoRanking = new Model_Data_CircleVideoRanking();
	    $docs = $modelCircleVideoRanking->find(array(
	        'period' => strtoupper($type),
	        'type' => 'PV',
	        'circle_id' => (int) $circleId
	    ), array('date'), array('date' => -1), 1);
	    if ($docs) {
	        $doc = array_shift($docs);
	        $date = $doc['date'];
	    } else {
	        $date = (int) date('Ymd', time() - 24 * 3600);
	    }
	    $docs = $modelCircleVideoRanking->find(array(
	        'date' => $date,
	        'period' => strtoupper($type),
	        'type' => 'PV',
	        'circle_id' => (int) $circleId
	    ), array(), array('order' => 1), $count, $offset);
	    $videoIds = Arr::pluck($docs, 'vid');
	    $modelVideo = new Model_Logic_Video();
	    return $modelVideo->getMulti($videoIds, true, null, false);
	}
	
	/**
	 * 圈内热播视频
	 * @param int $circleId
	 * @param string $type
	 * @param int $count
	 * @param int $offset
	 * @return array
	 */
	public function activeUsersInCircle($circleId, $type = 'day', $count = null, 
	    $offset = null)
	{
	    $modelCircleUserRanking = new Model_Data_CircleUserRanking();
	    $docs = $modelCircleUserRanking->find(array(
	        'period' => strtoupper($type),
	        'type' => 'POP',
	        'circle_id' => (int) $circleId
	    ), array('date'), array('date' => -1), 1);
	    if ($docs) {
	        $doc = array_shift($docs);
	        $date = $doc['date'];
	    } else {
	        $date = (int) date('Ymd', time() - 24 * 3600);
	    }
	    $docs = $modelCircleUserRanking->find(array(
	        'date' => $date,
	        'period' => strtoupper($type),
	        'type' => 'POP',
	        'circle_id' => (int) $circleId
	    ), array(), array('order' => 1), $count, $offset);
	    $userIds = Arr::pluck($docs, 'uid');
	    $modelUser = new Model_Logic_User();
	    return $modelUser->getMulti($userIds, true, false);
	}
}