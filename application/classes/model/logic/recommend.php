<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 推荐、缤纷等相关页面逻辑
 * @author xucongbin
 */
class Model_Logic_Recommend {
	const CIRCLE_MAX_VIDEO_NUM = 200; //圈子详情页最多显示200个视频
    
	private $objVideo;
	private $objCircle;
	private $objLogicVideo;
	private $objLogicCircle;
	private $objLogicStat;
	
	public function __construct() {
		$this->objVideo = new Model_Data_Video();
		$this->objCircle = new Model_Data_Circle();
		$this->objLogicVideo = new Model_Logic_Video();
		$this->objLogicCircle = new Model_Logic_Circle();
	}
	/**
	 * 首页推荐视频
	 * @param int $offset
	 * @param int $count
	 * @param int $maxScore 视频分值上限
	 * @param int $type 类型，0 - 综合，1 - 最新，2 - 最热，3 - 最受欢迎
	 * @param array $arrField 需要从video表取回的字段，默认为basicfields
	 * @return array
	 * rec_type 0 - 普通， 1 - 新，2 - 热，3 - 最受欢迎
	 */
	public function getHomepageRecommendVideos($offset = 0, $count = 10, $maxScore = NULL, 
	    $type = 0, $arrField = null) {
	    $arrCacheKey = array_merge((array) func_get_args(), array(__CLASS__, __FUNCTION__));
	    $arrReturn = Util::getCache($arrCacheKey, null);
	    if (! empty($arrReturn)) {
	        //Jkit::$log->notice('hit cache', $arrCacheKey, __FILE__, __LINE__);
	        return $arrReturn;
	    }
		if ($type == 0) {
		    $key = Model_Data_Recommend::HOMEPAGE_REC;
		} else if ($type == 1) {
		    $key = Model_Data_Recommend::HOMEPAGE_REC_NEW;
		} else if ($type == 2) {
		    $key = Model_Data_Recommend::HOMEPAGE_REC_HOT;
		} else if ($type == 3) {
		    $key = Model_Data_Recommend::HOMEPAGE_REC_POP;
		} else {
		    throw new Model_Logic_Exception("unknown type: $type");
		}
		$arrReturn = array();
	    Profiler::startMethodExec();
		$arrMembers = Model_Data_Recommend::getHomepageRecommendVideos($offset, 
		    $offset + $count - 1, $maxScore, true, $key);
	    Profiler::endMethodExec(__FUNCTION__.' getHomepageRecommendVideos');
		if( !$arrMembers ) {
			return $arrReturn;
		}
	    Profiler::startMethodExec();
		$arrReturn = $this->buildSortedVideos($arrMembers, array(), $arrField);
		Profiler::endMethodExec(__FUNCTION__.' buildSortedVideos');
		if (! empty($arrReturn)) {
		    Util::setCache($arrCacheKey, $arrReturn);
		}
		
/* 		// 推荐理由
		$reasons = Model_Data_Recommend::videoRecommendReason(Arr::pluck($arrReturn, '_id'));
		foreach ($arrReturn as &$video) {
		    if (isset($reasons[$video['_id']])) {
		        $video['recommend_reason'] = $reasons[$video['_id']];
		    }
		}
		unset($video); */
		return $arrReturn;
	}
	/**
	 * 
	 * 当前页面的最大序号
	 * @param int $type 类型，0 - 综合，1 - 最新，2 - 最热，3 - 最受欢迎
	 * @return int
	 */
	public function getHomepageMaxCurrent($type = 0) {
		if ($type == 0) {
		    $key = Model_Data_Recommend::HOMEPAGE_CUR_NUM;
		} else if ($type == 1) {
		    $key = Model_Data_Recommend::HOMEPAGE_CUR_NUM_NEW;
		} else if ($type == 2) {
		    $key = Model_Data_Recommend::HOMEPAGE_CUR_NUM_HOT;
		} else if ($type == 3) {
		    $key = Model_Data_Recommend::HOMEPAGE_CUR_NUM_POP;
		} else {
		    throw new Model_Logic_Exception("unknown type: $type");
		}
		return Model_Data_Recommend::getHomepageMaxCurrent($key);
	}

	/**
	 * 获取根据Tag过滤后的圈内视频 
	 * @param int $intCircleId 圈子ID
	 * @param string $strCircleTitle 圈子标题，如果传入null的话，会自动根据id去查找标题
	 * @param string $strTag 要找的视频tag
	 * @param int $offset 分页参数，当前页开始偏移量
	 * @param int $count 分页参数，每页多少条记录
	 * @param int $intRank 视频排序方式，参考Model_Logic_Video::VIDEO_RANK_*
	 * @param array $arrField 视频信息的字段列表
	 * 
	 * @return array(total => 视频总数, video => array())
	 */
	public function getCircleVideosByTag($intCircleId, $strCircleTitle, $strTag, $offset=0, $count=10, $intRank = Model_Logic_Video::VIDEO_RANK_DEFAULT, $arrField = null, $arrOpt = array()) 
	{
	    // 自建圈子圈内视频暂时直接查询数据库
	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->get($intCircleId);
	    if (!isset($circle['official']) || $circle['official'] == 0) {
	        $modelLogicCircleVideo = new Model_Logic_CircleVideo();
	        $total = 0;
	        $videos = $modelLogicCircleVideo->circledVideosByCircle($intCircleId, 
	            $offset, $count, $total);
	        $videoIds = Arr::pluck($videos, 'video_id');
	        
    	    $modelDataCircleVideoByUser = new Model_Data_CircleVideoByUser();
            $circleVideos = $modelDataCircleVideoByUser->find(array(
                'circle_id' => (int) $intCircleId,
            ), array(), array('create_time' => -1), $count, $offset);
            $tmp = array();
            foreach ($circleVideos as $circleVideo) {
                $tmp[$circleVideo['video_id']] = $circleVideo;
            }
            $circleVideos = $tmp;
	        $videoIds = Arr::pluck($circleVideos, 'video_id');
    	    
	        $arrMembers = array();
	        foreach ($videoIds as $videoId) {
	            $arrMembers[$videoId] = array('vid' => $videoId);
	        }
	        if (count($arrMembers) > 0) {
	            $arrMembers = $this->buildSortedVideos($arrMembers , array($intCircleId), $arrField);
	        }
	        foreach ($arrMembers as &$arrMember) {
	            if (isset($circleVideos[$arrMember['_id']]['note'])) {
	                $arrMember['note'] = $circleVideos[$arrMember['_id']]['note'];
	            }
	        }
	        return array('total' => $total, 'video' => $arrMembers);
	    }
	    $arrCacheKey = array_merge((array) func_get_args(), array(__CLASS__, __FUNCTION__));
	    $arrReturn = Util::getCache($arrCacheKey, null);
	    if (! empty($arrReturn)) {
	        //Jkit::$log->notice('hit cache', $arrCacheKey, __FILE__, __LINE__);
	        return $arrReturn;
	    }
	    	
	    $arrReturn = array('total' => 0, 'video' => array());
	    if ($strCircleTitle === null) {
	        //如果用户传入的圈子标题是null的话，程序要自己去取圈子标题
	        $objDataCircle = new Model_Data_Circle();
	        $arrCircleInfo = $objDataCircle->get($intCircleId, array('title'));
	        if (empty($arrCircleInfo)) {
	            return $arrReturn;
	        }
	        $strCircleTitle = $arrCircleInfo['title'];	        
	    } 
	       
	    include_once __DIR__ . '/../thrift/circle/CircleVideoService.php';
	    $arrParam = array($intCircleId, $strCircleTitle, $intRank, array($strTag), 
	        (!isset($circle['official']) || $circle['official'] == 0));
	    $mixedRes = RPC::call('circle_video', array('CircleVideoServiceClient', 'search'), $arrParam);
	    if(empty($mixedRes)) {
	        return $arrReturn;
	    } else {
	        $intTotal = count($mixedRes);
	        if ($intTotal > $offset) {
	            $mixedRes = (array) array_slice($mixedRes, $offset, $count);
	        } else {
	            $mixedRes = array();
	        }
	        $arrMembers = array();
	        foreach ($mixedRes as $v) {
	            $arrMembers[$v] = array('vid' => $v);
	        }
	        if (count($arrMembers) > 0) {
	            $arrMembers = $this->buildSortedVideos($arrMembers , array($intCircleId), $arrField, $arrOpt);
	        }
	        $arrReturn = array('total' => $intTotal, 'video' => $arrMembers);
	        if (! empty($arrReturn)) {
	            Util::setCache($arrCacheKey, $arrReturn);
	        }
	        return $arrReturn;
	    }	    
	}	
	
	/**
	 * 
	 * 获取圈子页，圈内视频 , rec_type=>0：无任何标记（默认值）；1：“新”视频；2：“热”视频
	 * @param int $intCircleId
	 * @param int $offset
	 * @param int $count
	 * @deprecated 请使用self::getCircleVideosByTag替代
	 * @return array(
	 * )
	 */
	public function getCircleVideos($intCircleId, $offset=0, $count=10, $intRank = Model_Logic_Video::VIDEO_RANK_DEFAULT, $arrField = null) {
		$arrReturn = $this->getCircleVideosByTag($intCircleId, null, '', $offset, $count, $intRank, $arrField);
		$arrReturn = @$arrReturn['video'];		

		return $arrReturn;
	}
	
	public function getCircleVideoCount($intCircleId) {
		$count =  Model_Data_Recommend::getRedisDb()->zSize(Model_Data_Recommend::getCircleCatKey($intCircleId));
		if($count > self::CIRCLE_MAX_VIDEO_NUM) {
			$count = self::CIRCLE_MAX_VIDEO_NUM;
		}
		
		return intval( $count );
	}
	
	public function getAllCircleVideos($uid, $offset=0, $count=10) {
		$arrReturn = array(
			'total' => 0,
			'data' => array()
		);
		$objLogicUser = new Model_Logic_User();
		$arrCids = $objLogicUser->getUserCirclesByUid($uid, true);
		if(!$arrCids) {
			return $arrReturn;
		}
		$cidsLength = count($arrCids);
		foreach($arrCids as $cid) {
			$arrReturn['total']+= $this->getCircleVideoCount($cid);
		}
//		$intPage = floor($offset/($count*4))+1;
		if($offset>=$arrReturn['total']) {
			return $arrReturn;
		}
		$start = ceil($offset/$cidsLength);
		$end = ceil( $count/$cidsLength )+$start-1;
		$arrMembers = array();
		foreach($arrCids as $cid) {
			$arrMembers = array_merge($arrMembers, 
				Model_Data_Recommend::getCircleVideos($cid, $start, $end)
			);
		} 
		if(!$arrMembers) {
			return $arrReturn;
		}
		$objModelVideo = new Model_Data_Video();
		$query = array(
			"_id"=>array(
				'$in' => array_keys($arrMembers)
			)
		);
		$arrResult = $objModelVideo->find($query, array("_id"), array("upload_time"=>-1));
		$arrTmpMembers = array();
		foreach($arrResult as $vid=>$tmp) {
			$arrTmpMembers[] = $arrMembers[$vid];
		}
		$arrTmpMembers = array_slice($arrTmpMembers, 0, $count);
		$arrReturn['data'] = $this->buildSortedVideos($arrTmpMembers, $arrCids);
		return $arrReturn;
	}
	/**
	 * 
	 * 获取用户推荐的圈子
	 * @param int $uid
	 * 
	 * @param int $offset
	 * @param int $count
	 * 
	 * @return array(
	 * )
	 */
	public function getGuessCirclesByUid($uid, $offset=0, $count=4) {
	    $arrCacheKey = array_merge((array) func_get_args(), array(__CLASS__, __FUNCTION__));
	    $arrCircles = Util::getCache($arrCacheKey, null);
	    if (! empty($arrCircles)) {
	        //Jkit::$log->notice('hit cache', $arrCacheKey, __FILE__, __LINE__);
	        return $arrCircles;
	    }	    
		$objModelUserGuessRecommend = new Model_Data_Userguessrecommend($uid);
		$objLogicCircle = new Model_Logic_Circle();
		$objLogicUser = new Model_Logic_User();
	    Profiler::startMethodExec();
		$arrCircleIds = $objModelUserGuessRecommend->getGuessCircleIds();
		Profiler::endMethodExec(__FUNCTION__.' getGuessCircleIds');
		
		$intLength = $arrCircleIds ? count($arrCircleIds):1;
		$offset = $offset%$intLength;
		$arrRecommendCircleIds = array_slice( $arrCircleIds, $offset, $count );
	    Profiler::startMethodExec();
		$arrCircles =  $objLogicCircle->getMulti($arrRecommendCircleIds, true);
		Profiler::endMethodExec(__FUNCTION__.' getMulti');
	    Profiler::startMethodExec();
		$objLogicUser->complementUserCircleRel($arrCircles, $uid);
		Profiler::endMethodExec(__FUNCTION__.' complementUserCircleRel');
		if (! empty($arrCircles)) {
		    Util::setCache($arrCacheKey, $arrCircles);
		}
		return $arrCircles;
		
	}
	
	/**
	 * 
	 * 用户登录情况下的猜你喜欢
	 * @param int $uid
	 * @param int $count
	 * 
	 * @reutrn array
	 */
	public function getGuessVideosByUid( $uid, $offset, $count=10, $arrField = NULL) {
	    $arrCacheKey = array_merge((array) func_get_args(), array(__CLASS__, __FUNCTION__));
	    $arrReturn = Util::getCache($arrCacheKey, null);
	    if (! empty($arrReturn)) {
	        //Jkit::$log->notice('hit cache', $arrCacheKey, __FILE__, __LINE__);
	        return $arrReturn;
	    }	    
		$objModelUserGuessRecommend = new Model_Data_Userguessrecommend($uid);
		$arrTmpMerge = $objModelUserGuessRecommend->getGuessVids();
		
		$arrReturn =  $this->buildSortedVideos( $this->buildSortedMembers( array_slice( $arrTmpMerge, $offset, $count)), 
		array(), $arrField);
		if (! empty($arrReturn)) {
		    Util::setCache($arrCacheKey, $arrReturn);
		}		
		return $arrReturn;
	}
	/**
	 * 
	 * 返回未登录情况下，圈子列表
	 * @param int $offset
	 * @param int $count
	 * 
	 * @return array
	 */
	public function getGuessCirclesByCookie($offset=0, $count=4) {
	    $arrCacheKey = array_merge((array) func_get_args(), array(__CLASS__, __FUNCTION__, Session::instance()->id()));
	    $arrCircles = Util::getCache($arrCacheKey, null);
	    if (! empty($arrCircles)) {
	        //Jkit::$log->notice('hit cache', $arrCacheKey, __FILE__, __LINE__);
	        return $arrCircles;
	    }	    
		$watchedVids = isset( $_COOKIE['watched_list'] ) ? json_decode($_COOKIE['watched_list'], true) : array();
		$watchlaterVids = isset($_COOKIE['watchlater_list']) ? json_decode($_COOKIE['watchlater_list'], true) : array();
		#TODO 排序
		if($watchedVids) {
			ksort($watchedVids);
		}
		if($watchlaterVids) {
			ksort($watchlaterVids);
		}
		$objModelGuessRecommend = new Model_Data_Guessrecommend($watchedVids, $watchlaterVids);
		$objLogicCircle = new Model_Logic_Circle();
		$objLogicUser = new Model_Logic_User();
	    Profiler::startMethodExec();
		$arrCircleIds = $objModelGuessRecommend->getGuessCircleIds();
		Profiler::endMethodExec(__FUNCTION__.' getGuessCircleIds');
		
		$intLength = $arrCircleIds ? count($arrCircleIds):1;
		$offset = $offset%$intLength;
		$arrRecommendCircleIds = array_slice( $arrCircleIds, $offset, $count );
	    Profiler::startMethodExec();
		$arrCircles =  $objLogicCircle->getMulti($arrRecommendCircleIds, true);
		Profiler::endMethodExec(__FUNCTION__.' getMulti');
	    Profiler::startMethodExec();
		$objLogicUser->complementUserCircleRel($arrCircles, 0);
		Profiler::endMethodExec(__FUNCTION__.' complementUserCircleRel');
		if (! empty($arrCircles)) {
		    Util::setCache($arrCacheKey, $arrCircles);
		}
		return $arrCircles;
	}
	
	/**
	 * 
	 * 用户登录情况下的猜你喜欢
	 * @param int $uid
	 * @param int $count
	 * 
	 * @reutrn array
	 */
	public function getGuessVideosByCookie( $offset, $count=10, $arrField = NULL) {
	    $arrCacheKey = array_merge((array) func_get_args(), array(__CLASS__, __FUNCTION__, Session::instance()->id()));
	    $arrReturn = Util::getCache($arrCacheKey, null);
	    if (! empty($arrReturn)) {
	        //Jkit::$log->notice('hit cache', $arrCacheKey, __FILE__, __LINE__);
	        return $arrReturn;
	    }
		$arrReturn = array();
		#TODO 用户以后观看的视频，收藏的视频来自cookie
		$watchedVids = isset( $_COOKIE['watched_list'] ) ? json_decode($_COOKIE['watched_list'], true) : array();
		$watchlaterVids = isset($_COOKIE['watchlater_list']) ? json_decode($_COOKIE['watchlater_list'], true) : array();
		#TODO 排序
		if($watchedVids) {
			ksort($watchedVids);
		}
		if($watchlaterVids) {
			ksort($watchlaterVids);
		}
		$objModelGuessRecommend = new Model_Data_Guessrecommend($watchedVids, $watchlaterVids);
		$arrTmpMerge = $objModelGuessRecommend->getGuessVids();
		$arrReturn = $this->buildSortedVideos( $this->buildSortedMembers( array_slice( $arrTmpMerge, $offset, $count)), 
			array(), $arrField);
		if (! empty($arrReturn)) {
		    Util::setCache($arrCacheKey, $arrReturn);
		}
		return $arrReturn;
	}
	/**
	 * 
	 * 播放页的相关视频推荐
	 * @param int $vid
	 * 
	 * @return array(
	 * 	'data' => 视频列表
	 * 	'total' => 视频总数
	 * 	'is_relate' => 是否为相关视频
	 * )
	 */
	public function getRecommendVideos($vid, $offset=0, $count=10, $arrField = null, $returnStatInfo = true) {
		$arrReturn = array('data' => array(), 'total' => 0,);
		$arrVids = Model_Data_Recommend::getRecommendVideos($vid);
		if(! empty($arrVids)) {
			$arrReturn['total'] = count($arrVids);
			$arrVids = array_slice($arrVids, $offset, $count);
			$arrReturn['data'] = $this->objLogicVideo->getMulti($arrVids, true, $arrField, $returnStatInfo);
		}
		return $arrReturn;
	}
	/**
	 * 
	 * 获取推荐用户选取的tag
	 */
	public function getRecommendUserTags($offset=0, $count=10) {
		$strKey = "RECOMMEND_USER_TAGS";
		$objRedis = Model_Data_Recommend::getRedisDb(14);
		$arrTags = $objRedis->zRange($strKey, 0, -1);
		$intLength = $arrTags ? count($arrTags):1;
		$offset = $offset%$intLength;
		$arrTags = array_merge($arrTags, array_slice($arrTags, 0, $count));
		return array_slice($arrTags, $offset, $count);
	}

	/**
	 * 获取大家都在看模块的推荐视频
	 * @param string $strVid 视频id
	 * @param int $intOffset 分页参数，偏移量
	 * @param int $intCount 分页参数，每页条数
	 * @param int $intTotalPage 需要的视频页数
	 */
	public function getCurPeopleWatchedVideo($strVid, $intOffset, $intCount, $intTotalPage) {
		//多取两个，以防有的vid已经失效导致一页个数不足
		$intTmpCount = $intCount + 2;
		$intTotal = $intTmpCount * $intTotalPage;
		if ($intOffset > 0) {
			$intOffset += 2;
		}
		
		$arrVid = Model_Data_Recommend::getCurPeopleWatchedVid($strVid, $intOffset, $intTmpCount, $intTotal);
		if (empty($arrVid)) {
			return array();
		}
		
		$arrReturn = $this->objLogicVideo->getMulti($arrVid, true, null, false);
		if (is_array($arrReturn) && count($arrReturn) > $intCount) {
			$arrReturn = array_slice($arrReturn, 0, $intCount, true);
		}		
		return $arrReturn;
	}

	/**
	 * 
	 * Enter description here ...
	 * @param array $arrInput
	 * 
	 * @return Array
	 */
	private function buildCircleVideoRec($arrInput) {
		$arrReturn = array();
		if(!$arrInput) {
			return $arrReturn;
		}
		$arrTmp = array();
		foreach($arrInput as $circleId=>$vids) {
			$arrTmp['circle'] = $this->objCircle->get($circleId);
			$arrTmp['video_list'] = $this->objLogicVideo->getMulti($vids, true);
			if($arrTmp['video_list']) {
				$arrTmp['video_list'] = array_slice($arrTmp['video_list'], 0, 5);
			}
			
			$arrReturn[$circleId] = $arrTmp;
		}
		return $arrReturn;
	}
	
	private function completeCircleInfoByVideos(&$arrResult, $onlyCircleId=false, $vcRelation=NULL) {
		if(!$arrResult) {
			return ;
		}
		if($vcRelation) {
			$arrTmpCids = array();
			foreach($vcRelation as $tmpCid) {
				if($tmpCid) {
					$arrTmpCids[] = intval($tmpCid);
				}
			}
			if($arrTmpCids) {
				$circleList = $this->objLogicCircle->getMulti($arrTmpCids, false, 
				    Model_Data_Circle::STATUS_PUBLIC, false);
				foreach($arrResult as $k=>$row) {
					$tmpRel = $vcRelation[$row["_id"]];
					$arrResult[$k]['circle_list'] = isset($circleList[$tmpRel]) ? array($circleList[$tmpRel]):array();
				}
			}
			
			return;
		}
		if($onlyCircleId) {
            Profiler::startMethodExec();
			$circleInfo = $this->objLogicCircle->get($onlyCircleId, Model_Data_Circle::STATUS_PUBLIC, 
			    false);
            Profiler::endMethodExec(__FUNCTION__.' get');
			foreach($arrResult as $k=>$row) {
				$arrResult[$k]['circle_list'] = array($circleInfo);
			}
		} else {
            Profiler::startMethodExec();
            $arrInput = Arr::pluck($arrResult, "_id");
			$arrTmpCircles = $this->getCircleInfoByVids($arrInput);
            Profiler::endMethodExec(__FUNCTION__.' getCircleInfoByVids');
			foreach($arrResult as $k=>$row) {
				$arrResult[$k]['circle_list'] = array();
				if( isset($arrTmpCircles[$k]) ) {
					$arrResult[$k]['circle_list'] = array_values( $arrTmpCircles[$k] );
				}
			}
		}
	}

	/**
	 * 获取每个vid对应的圈子信息
	 * 
	 * @param string|array $mixedVid 单个vid或者vid数组
	 * @return array 如果是单个vid，返回的是对应圈子的信息数组，否则返回array({$vid} => {$cid} => 圈子信息数组)
	 */
	public function getCircleInfoByVids($mixedVid) {
		$arrReturn = array();
		if (empty($mixedVid)) {
			return $arrReturn;
		}
		
		$bolSingleVid = false;
		if (! is_array($mixedVid)) {
			$bolSingleVid = true;
			$mixedVid = array($mixedVid);
		}
		
		$arrTmp = Model_Data_Recommend::getCidByVid($mixedVid);
		if (empty($arrTmp)) {
			return $arrReturn;
		}

	    Profiler::startMethodExec();
		$arrResult = $this->objCircle->getMulti(array_values($arrTmp), Model_Logic_Circle::$basicFields, false, Model_Data_Circle::STATUS_PUBLIC);
	    Profiler::endMethodExec(__FUNCTION__.'::getMulti');
	    if ($bolSingleVid) {
	    	$intCid = array_pop($arrTmp);
	    	if (isset($arrResult[$intCid])) {
	    		return $arrResult[$intCid];
	    	}
	    } else {
			foreach($arrTmp as $strVid => $intCid) {
				if (! isset($arrResult[$intCid])) {
					continue;
				}
				$arrReturn[$strVid][$intCid] = $arrResult[$intCid];
			}
	    }
	    
		return $arrReturn;
	}
	/**
	 * 
	 * 没有视频推荐情况下的替代品
	 * @param string $vid
	 * 
	 * @return array
	 */
	public function getNullRecommendAlternative($vid, $arrField = null) {
		$arrCircles = Model_Data_Recommend::getMultiCidByVid($vid);
		//没圈子，就返回上升最快的10个视频
		if(! $arrCircles) {
			$arrTmpResult = $this->getHomepageRecommendVideos(0, 100, null, 0, $arrField);
			shuffle($arrTmpResult);
			$arrTmpResult = array_slice($arrTmpResult, 0, 10);
		} else {	    		    
			if(count($arrCircles)>=2) {
				shuffle($arrCircles);
				$arrRet = $this->getCircleVideosByTag($arrCircles[0], null, '', 0, 10, Model_Logic_Video::VIDEO_RANK_DEFAULT, $arrField);
				$arrTmpResult = $arrRet['video'];
			} else {
			    $arrRet = $this->getCircleVideosByTag($arrCircles[0], null, '', 0, 30, Model_Logic_Video::VIDEO_RANK_DEFAULT, $arrField);
			    $arrTmpResult = $arrRet['video'];
			    shuffle($arrTmpResult);
				$arrTmpResult = array_slice($arrTmpResult, 0, 10);
			}
			
		}
		return $arrTmpResult;
	}
	
	private function buildSortedMembers($arrVids) {
		$arrReturn = array();
		if(!$arrVids) {
			return $arrReturn;
		}
		$arrTmp = array();
		$i=1;
		foreach($arrVids as $vid) {
			$arrReturn[] = array(
				'vid' => $vid, 
				'rec_type' => rand(0,2), 
				'timestamp' => $_SERVER['REQUEST_TIME'],
			);
		}
		return $arrReturn;
	}
	
	
	
	private function buildSortedVideos($arrMembers, $appointCids=array(), $arrField = null, $arrOpt = array()) {
		$arrReturn = array();
		if(!$arrMembers) {
			return $arrReturn;
		}
		$arrTmpMemberInfo = $this->fetchMembers($arrMembers);
		$arrTmpVids = $arrTmpMemberInfo["vid_list"];
		$onlyCid = false;
		if(count($appointCids)==1) {
			$onlyCid = $appointCids[0];
		}
		
		$returnStatInfo = true;
		if (isset($arrOpt['has_video_stat']) && $arrOpt['has_video_stat'] === false) {
			$returnStatInfo = false;
		}
	    Profiler::startMethodExec();
	    $arrVideos = $this->objLogicVideo->getMulti($arrTmpVids, true, $arrField, $returnStatInfo);
	    Profiler::endMethodExec(__FUNCTION__.' getMulti');
	    
	    if (! isset($arrOpt['has_circle_info']) || $arrOpt['has_circle_info'] !== false) {
		    Profiler::startMethodExec();
			$this->completeCircleInfoByVideos($arrVideos, $onlyCid, $arrTmpMemberInfo["vc_relation"]);
		    Profiler::endMethodExec(__FUNCTION__.' completeCircleInfoByVideos');
	    }	    

		$objComment = new Model_Logic_Comment();
		$arrComment = array();		
		if (! isset($arrOpt['has_video_comment']) || $arrOpt['has_video_comment'] !== false) {
	    	Profiler::startMethodExec();
			$arrComment = $objComment->topN($arrTmpVids, 3, array('avatarSize' => 30));
	    	Profiler::endMethodExec(__FUNCTION__.' topN');
		}
		
		$appointCids = array_flip($appointCids);
		if($arrVideos) {
			foreach($arrMembers as $vid => $row) {
				if( isset($arrVideos[$row['vid']]) ) {
					$row = array_merge($row, $arrVideos[$row['vid']]);
					if (!isset($row['circle_list'])) {
					    $row['circle_list'] = array();
					}
					$row['circle'] = $this->appointCircle($row['circle_list'], $appointCids);
					unset($row['circle_list']);
					if (isset($arrComment[$row['vid']])) {
					    $row['comments'] = $arrComment[$row['vid']];
					} else {
					    $row['comments'] = array();
					}
					$arrReturn[] = $row;
				}
			}
		}
		return $arrReturn;
	}
	
	private function decodeSplitData($strInput) {
		return explode("\4", $strInput);
	}
	
	private function fetchMembers($arrMembers) {
		$arrReturn = array(
			"vid_list" => array(),
			"vc_relation" => array(),
		);
		$includeVcrel = true;
		foreach($arrMembers as $row) {
			$arrReturn["vid_list"][] =  $row["vid"];
			if($includeVcrel && !isset($row["circle_id"])) {
				$includeVcrel = false;
				continue;
			}
			if($includeVcrel) {
				$arrReturn["vc_relation"][$row["vid"]] = $row["circle_id"];
			}
		}	

		return $arrReturn;
	}
	
	private function appointCircle($arrCircles, $appointCids=array()) {
		if(!$appointCids) {
			if(count($arrCircles)>1) {
				shuffle($arrCircles);
			}
			return $arrCircles ? $arrCircles[0] : NULL;
		}
		if(!$arrCircles) {
			return NULL;
		}
		if(count($arrCircles)>1) {
			shuffle($arrCircles);
		}
		$arrReturn = $arrCircles[0];
		foreach($arrCircles as $row) {
			if( isset($appointCids[$row['_id']]) ) {
				$arrReturn = $row;
			}
		}
		
		return $arrReturn;
	}
}