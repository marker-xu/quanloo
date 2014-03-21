<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 推荐
 * @author xucongbin
 */
class Model_Data_Recommend extends Model {
    // 首页推荐视频
	const HOMEPAGE_REC = 'homepage_rec'; // 综合
	const HOMEPAGE_REC_NEW = 'homepage_rec_new'; // 最新
	const HOMEPAGE_REC_HOT = 'homepage_rec_hot'; // 最热
	const HOMEPAGE_REC_POP = 'homepage_rec_pop'; // 最受欢迎
	
	// 首页推荐视频最大分值
	const HOMEPAGE_CUR_NUM = 'homepage_cur_num';
	const HOMEPAGE_CUR_NUM_NEW = 'homepage_cur_num_new';
	const HOMEPAGE_CUR_NUM_HOT = 'homepage_cur_num_hot';
	const HOMEPAGE_CUR_NUM_POP = 'homepage_cur_num_pop';
	
	//waiting userid tb
	const KEY_RECOMMEND_VIDEOLIST = 'KEY_RECOMMEND_VIDEOLIST';
	
	const RELATED_CIRCLE_RECO_SUBSCRIBE = 1; //加关注圈子推荐type值为1
	const RELATED_CIRCLE_RECO_VIDEO = 2; //视频圈子推荐type值为2
	const RELATED_CIRCLE_RECO_CIRCLE = 3; //圈子的相关圈子推荐type值为3
	
	/**
	 * 
	 * @param int $dbNum
	 * @param string $dbType
	 * @return Redis
	 */
	public static function getRedisDb($dbNum=NULL, $dbType=NULL) {
		if($dbNum===NULL) {
			$dbNum = 11;
		}
		if($dbType===NULL) {
			$dbType = "recommend";
		}
		$objRedis = Cache::instance($dbType);
		return $objRedis->getRedisDB($dbNum);
	}
	/**
	 * 
	 * 圈内视频，每个圈子下当前最热门的top200条结果，按热门程度由高到低排列
	 * @param int $circleId
	 * 
	 * @return string
	 */
	public static function getCircleCatKey($circleId, $intRank = Model_Logic_Video::VIDEO_RANK_DEFAULT) {
	    if ($intRank == Model_Logic_Video::VIDEO_RANK_COMMENT) {
	        return "circle_{$circleId}_comment";
	    } elseif ($intRank == Model_Logic_Video::VIDEO_RANK_SHARE) {
	        return "circle_{$circleId}_share";
	    } else {
		    return "circle_{$circleId}_rec";
	    }
	}
	/**
	 * 
	 * 播放页的相关视频推荐
	 * @param string $vid
	 */
	public static function getVideoRelateKey($vid) {
		return "relate_{$vid}_rec";
	}
	
	public static function getUserCircleKey($uid) {
		return $uid;
	}
	
	public static function get($key, $test=false) {
		if($test) {
			$redis = Database::instance('web_redis_master');
        	$objRedisDb = $redis->getRedisDB(2);
        	
		} else {
			$objRedisDb = self::getRedisDb(1);
			
		}
		if ( is_array($key) ) {
        	$objRedisDb = $objRedisDb->multi();
        	foreach($key as $subKey) {
        		$objRedisDb = $objRedisDb->get($subKey);
        	}
        	$ret = $objRedisDb->exec();
        	$ret = array_combine($key, $ret);
        	foreach($ret as $subKey=>$value) {
        		if( $value===false ) {
        			unset($ret[$subKey]);
        		}
        	}
        	return $ret;
        } else {
        	return $objRedisDb->get($key);
        }
	}
	
	public static function getList($key) {
		$objRedisDb = self::getRedisDb(1);
		if ( is_array($key) ) {
        	$objRedisDb = $objRedisDb->multi();
        	foreach($key as $subKey) {
        		$objRedisDb = $objRedisDb->lRange($subKey, 0, -1);
        	}
        	$ret = $objRedisDb->exec();
        	$ret = array_combine($key, $ret);
        	foreach($ret as $subKey=>$value) {
        		if( !$value ) {
        			unset($ret[$subKey]);
        		}
        	}
        	return $ret;
        } else {
        	return $objRedisDb->lRange($key, 0, -1);
        }
	}
	
	/**
	 * 
	 * 当前页面的最大序号
	 * 
	 * @return int
	 */
	public static function getHomepageMaxCurrent($key = self::HOMEPAGE_CUR_NUM) {
		if (Kohana::$environment == Kohana::DEVELOPMENT) {
		    $dbNo = 3;
		} else {
		    $dbNo = NULL;
		}
		return self::getRedisDb($dbNo)->get($key);
	}
	
	public static function getHomepageRecommendVideos($start = 0, $end = -1, $maxScore = NULL, 
	    $withscores = true, $key = self::HOMEPAGE_REC) 
	{
		if($maxScore === NULL) {
	        Profiler::startMethodExec();
	        switch ($key) {
	        	case self::HOMEPAGE_REC:
	        		$strRecNumKey = self::HOMEPAGE_CUR_NUM;
	        		break;
	        	case self::HOMEPAGE_REC_HOT:
	        		$strRecNumKey = self::HOMEPAGE_CUR_NUM_HOT;
	        		break;
	        	case self::HOMEPAGE_REC_NEW:
	        		$strRecNumKey = self::HOMEPAGE_CUR_NUM_NEW;
	        		break;
	        	case self::HOMEPAGE_REC_POP:
	        		$strRecNumKey = self::HOMEPAGE_CUR_NUM_POP;
	        		break;
	        	default:
	        		$strRecNumKey = self::HOMEPAGE_CUR_NUM;
	        		break;
	        }	        
			$maxScore = self::getHomepageMaxCurrent($strRecNumKey);
		    Profiler::endMethodExec(__FUNCTION__.' getHomepageMaxCurrent');
		}
		if (Kohana::$environment == Kohana::DEVELOPMENT) {
		    $dbNo = 3;
		} else {
		    $dbNo = NULL;
		}
		$db = self::getRedisDb($dbNo);
		if(is_null($maxScore)) {
	        Profiler::startMethodExec();
			$arrMembers = $db->zRevRange($key, $start, $end, $withscores);
		    Profiler::endMethodExec(__FUNCTION__.' zRevRange');
		} else {
			$extra = array(
	    		'withscores' => $withscores
	    	);
	    	$extra['limit'] = array($start, $end - $start + 1);
	        Profiler::startMethodExec();
			$arrMembers = $db->zRevRangeByScore($key, $maxScore, 0, $extra);
		    Profiler::endMethodExec(__FUNCTION__.' zRevRangeByScore');
		}
		return self::parseSortedMembers( $arrMembers, $withscores, true );
	}
	
	/**
	 * 
	 * 获取圈子页，圈内视频 , rec_type=>0：无任何标记（默认值）；1：“新”视频；2：“热”视频
	 * @param int $intCircleId
	 * @param int $strt
	 * @param int $end
	 * 
	 * @return array(
	 * )
	 */
	public static function getCircleVideos($intCircleId, $start=0, $end=-1, $withscores=true, $intRank = Model_Logic_Video::VIDEO_RANK_DEFAULT) {
		$arrReturn = array();
		$objRecommendRedis = self::getRedisDb();
		$strKey = Model_Data_Recommend::getCircleCatKey($intCircleId, $intRank);
		$arrMembers = $objRecommendRedis->zRevRange($strKey, intval($start), intval($end), $withscores);
		return self::parseSortedMembers( $arrMembers, $withscores );
	}
	
	public static function writeWaitingVidTB($vid) {
		$res = self::getRedisDb(1, "recommend_video")->rPush(self::KEY_RECOMMEND_VIDEOLIST, $vid);
		return $res;
	}
	
	public static function getRecommendVideos($vid) {
		$arrReturn = array();
		$objKt = Cache::instance("recommend_kt");
		$strResult = $objKt->get($vid);
		JKit::$log->debug(__FUNCTION__." vid-".$vid.", result-".$strResult);
		$strartPos = 7;
		$arrTmp = false;
		if (strlen($strResult) > 0) {
			$arrTmp = json_decode(trim(substr($strResult, $strartPos, -3)), true);
		}
		if (! $arrTmp) {
			Model_Data_Recommend::writeWaitingVidTB($vid);
	        Profiler::startMethodExec();
			$strResult = RPC::call('related_video_realtime_reco', '/relate_video_realtime', array('post_vars' => array('vid' => $vid)));
		    Profiler::endMethodExec(__FUNCTION__.' related_video_realtime_reco /relate_video_realtime');
			if (strlen($strResult) > 0) {
				$arrTmp = json_decode($strResult, true);
			}		
		}		
		if($arrTmp) {
			foreach($arrTmp as $vid) {
				$arrReturn[] = $vid;
			}
		}
		
		return $arrReturn;
	}
	
	public static function parseSortedMembers($arrMembers, $includeScore=true, $includeCircleId=false) {
		$arrReturn = array();
		if(!$arrMembers) {
			return $arrReturn;
		}
		$arrTmp = array();
		if($includeScore) {
			foreach($arrMembers as $value=>$score) {
				$arrTmp = explode("\3", $value);
				$arrReturn[$arrTmp[0]] = array(
					'vid' => $arrTmp[0], 
					'rec_type' => $arrTmp[1], 
					'timestamp' => isset($arrTmp[2]) ? $arrTmp[2]:0,
					'sort_num' => $score
				);
			}
		} else {
			foreach($arrMembers as $value) {
				$arrTmp = explode("\3", $value);
				$arrReturn[$arrTmp[0]] = array(
					'vid' => $arrTmp[0], 
					'rec_type' => $arrTmp[1], 
					'timestamp' => isset($arrTmp[2]) ? $arrTmp[2]:0,
				);
			}
		}
		if($includeCircleId) {
			foreach($arrReturn as $k=>$val) {
				$arrReturn[$k]['circle_id'] = $val['timestamp'];
			}
		}
		
		return $arrReturn;
	}
	
	/**
	 * 
	 * 获取用户感兴趣的圈子
	 * @param $uid
	 * @param $offset 起始位置
	 * @param $count 数量
	 * 
	 * @return array(
	 * )
	 */
	public static function getUserCircles( $uid, $start=0, $end=-1 ) {
		$arrReturn = array(
		);
		$objRecommendRedis = self::getRedisDb(14);
		$strKey = self::getUserCircleKey($uid);
		$arrMembers = $objRecommendRedis->zRevRange($strKey, $start, $end);
		if (!$arrMembers) {
			return $arrReturn;
		}
		foreach($arrMembers as $tmpCid) {
			$arrReturn[] = intval($tmpCid);
		}
		return $arrReturn;
	}
	
	/**
	 * 
	 * 获取未登录用户的个性化圈子
	 * @param string $sessionId
	 * @param int $start
	 * @param int $end
	 */
	public static function getSessionCircles($sessionId, $start=0, $end=-1) {
		$arrReturn = array(
		);
		$objRecommendRedis = self::getRedisDb(13);
		$strKey = $sessionId;
		$arrMembers = $objRecommendRedis->zRevRange($strKey, $start, $end);
		if (!$arrMembers) {
			return $arrReturn;
		}
		foreach($arrMembers as $tmpCid) {
			$arrReturn[] = intval($tmpCid);
		}
		return $arrReturn;		
	}
	
	/**
	 * 相关圈子
	 * @param string|array $query 查询词
	 * @param int|string $uid 用户ID，如果为null则自动取Session ID
	 * @param int $offset
	 * @param int $count
	 * @return array
	 */
	public static function relatedCircles($query, $uid = null, $offset = 0, $count = 10, $intType = self::RELATED_CIRCLE_RECO_SUBSCRIBE)
	{
        require_once Kohana::find_file('classes/model/thrift/recommend/CircleRec', 'circle_recom');
        
        $request = array('query' => is_array($query) ? $query : array($query));
        if (!is_null($uid)) {
            $request['uid'] = (int) $uid;
        } else {
            $request['session_id'] = Session::instance()->id();
        }
        $params = array($intType, json_encode($request), $offset, $count);
        Kohana::$log->debug(__FUNCTION__, $params);
		Profiler::startMethodExec();
		try {
            $result = RPC::call('related_circles', array('circle_recomClient', 'get_recom'), 
                $params);
		} catch (Exception $e) {
            Kohana::$log->error($e);
            return array('type' => '', 'circles' => array());
		}
	    Profiler::endMethodExec(__FUNCTION__." related_circles");
        Kohana::$log->debug(__FUNCTION__, $result);
        $result = json_decode(array_shift($result), true);
        return array('type' => $result['return_type'], 'circles' => $result['circles']);
	}
	
	/**
	 * 视频推荐理由
	 * @param array $videoIds
	 * @return array
	 */
	public static function videoRecommendReason($videoIds)
	{
		if (Kohana::$environment == Kohana::DEVELOPMENT) {
		    $db = 2;
		} else {
		    $db = 12;
		}
	    /* @var $redis Cache_Redis */
		$redis = Cache::instance('recommend_reason');
		$db = $redis->getRedisDB($db);
		$db->multi();
		foreach ($videoIds as $videoId) {
		    $db->get($videoId);
		}
		$values = $db->exec();
		$reasons = array();
		foreach ($videoIds as $videoId) {
		    $value = array_shift($values);
		    if ($value) {
		        $reasons[$videoId] = json_decode($value, true);
		    }
		}
		return $reasons;
	}
	
	public static function getCurPeopleWatchedVid($strVid, $intOffset, $intCount, $intTotal) {
		$intDbNum = 14;
		$strAttrRecKey = 'attr_rec';
		$objCacheRedis = self::getRedisDb($intDbNum, 'recommend_cur_watched');
		$arrCandidateVid = $objCacheRedis->zRevRange($strAttrRecKey, 0, 2000, false);
		if (! is_array($arrCandidateVid)) {
			return array();
		} elseif (count($arrCandidateVid) <= $intCount) {
			return $arrCandidateVid;
		}				

		/**
		 * 保证同一个vid，在一个推荐周期内出的视频列表是一致的
		 */	
		$intVidNum = count($arrCandidateVid); //当前sorted set的元素个数
		$intCrc32 = abs(crc32($strVid));
		$intStart = $intCrc32 % $intVidNum;
		$intCrc32 = abs(crc32(substr($strVid, 0, 10)));
		$intStep  = $intCrc32 % 20 + 1; //支持20个不同的步长，这样即使$intStart一致也能出不同视频
		$arrMembers = array();
		for ($i = 0; $i < $intCount; $i++) {
			$arrMembers[] = $arrCandidateVid[$intStart % $intVidNum];
			unset($arrCandidateVid[$intStart]);
			$intStart += $intStep;
		}
		
		$intEnd = $intStart + $intCount;
		
		$arrMembers = $objCacheRedis->zRevRange($strAttrRecKey, $intStart, $intEnd, false);
/* 		$arrMembers = array('e292d41edff3e9ca24bef35dbd768f66', '764387d78285958e5440930d8aa1e7cb', '0b60054a1a5f9781cc6af61277f1a21c',
				 'be756c357d028f9ce15978b97d45978b', '3304e81f4c63f795ce02328c4cb3e8b3', 'f88d19dbf497c0045dc58cb376f44df0',); */
		return $arrMembers;
	}
	
	public static function getCidByVid($arrVid) {
		$arrTmp = array();
		$intDbNum = 11;
		$objCacheRedis = self::getRedisDb($intDbNum, 'recommend_vid2cid');
		foreach ($arrVid as $strVid) {
			$strKey = "vid_{$strVid}";
			$arrCandidateCid = $objCacheRedis->sMembers($strKey);
			if (is_array($arrCandidateCid) && count($arrCandidateCid) > 0) {
				$arrTmp[$strVid] = (int) $arrCandidateCid[0];
			}
		}

		return $arrTmp;
	}
	
	public static function getMultiCidByVid($strVid) {
		$arrTmp = array();
		$intDbNum = 11;
		$objCacheRedis = self::getRedisDb($intDbNum, 'recommend_vid2cid');
		$strKey = "vid_{$strVid}";
		$arrCandidateCid = $objCacheRedis->sMembers($strKey);
		if (is_array($arrCandidateCid) && count($arrCandidateCid) > 0) {
			foreach ($arrCandidateCid as $v) {
				$arrTmp[] = (int) $v;
			}
		}
	
		return $arrTmp;
	}	

	/**
	 * 
	 * 获取用户注册时推荐的圈子（基于tag）
	 * @param array $arrTags
	 * @param $offset
	 * @param $count
	 * 
	 * @return array
	 */
	public static function getUserRegisterCirclesByTags($arrTags, $offset=0, $count=24) {
		$arrReturn = array();
		$arrCircleReasons = array();
		Profiler::startMethodExec();
		$strResult = RPC::call('register_recommend_circles', '/circle_coldstart', array(
			"post_vars"=>array( 'tags' => implode(":", $arrTags) )
		));
		Profiler::endMethodExec('register_recommend_circles');
		if (strlen($strResult) > 0) {
			$arrCidList = json_decode($strResult, true);
			if($arrCidList) {
				foreach($arrCidList as $intCid) {
					$intCid = intval($intCid);
					$arrCircleReasons[] = array("cid"=>$intCid, "reason"=>"");
				}
			}
		}	
		
		$arrCircleReasons = array_slice($arrCircleReasons, $offset, $count);
		if($arrCircleReasons) {
			foreach($arrCircleReasons as $row) {
				$arrReturn[$row['cid']] = $row;
			}
		}
		
		return $arrReturn;
	}
}