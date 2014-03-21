<?php 
//entity circle thrift
include_once __DIR__ . '/../thrift/entityCircle/EntityCircleRec.php';
/**
 * 实体库
 * @author wangjiajun
 */
class Model_Data_Entity extends Model
{
    /**
     * 对后端返回的实体字段名，转换成跟前端一致
     * @param array $entitys
     * @return array
     */
    public static function fieldsTransform($entitys)
    {
        $map = array('category' => 'entity_type', 'title' => 'title', 'playurl' => 'play_url', 
        	'picpath' => 'thumbnail', 'type' => 'genre', 'area' => 'region', 
        	'director' => 'director', 'actor' => 'cast', 'director_list' => 'director_list', 
        	'actor_list' => 'cast_list', 'desc' => 'desc', 'showdate' => 'released_date', 
        	'duration' => 'length', 'episode' => 'episode', 'episode_num' => 'episode_num', 
        	'finished' => 'finished', 'name' => 'name', 'gender' => 'gender', 'birthday' => 'birthday', 
        	'works' => 'works', 'swfurl' => 'player_url', 'num' => 'order', 'douban_score' => 'score', 
            'douban_comment' => 'comments');
        foreach ($entitys as $index => &$entity) {
            $tmp = array();
            foreach ($entity as $key => $value) {
                if (isset($map[$key])) {
                    if ($key == 'works' || $key == 'episode') {
                        $value = self::fieldsTransform($value);
                    }
                    $tmp[$map[$key]] = $value;
                } else {
                    $tmp[$key] = $value;
                }
            }
            $entity = $tmp;
        }
       return array_values($entitys);
    }
    
    /**
     * 查询单个实体
     * @param string $id
     * @return array|null
     */
    public function get($id)
    {
        $entitys = $this->getMulti(array($id));
        if ($entitys) {
            return array_shift($entitys);
        } else {
            return null;
        }
    }
    
    /**
     * 查询多个实体
     * @param array $ids
     * @param bool $keepOrder
     * @return array
     */
    public function getMulti($ids, $keepOrder = false)
    {        
	    $params = array(
	        'id' => implode(',', $ids),
	    );
        Kohana::$log->debug(__FUNCTION__, $params);
		Profiler::startMethodExec();
		$query = http_build_query($params);
		try {
            $result = RPC::call('entity', '/?'.$query);
		} catch (Exception $e) {
            Kohana::$log->error($e);
            return array();
		}
	    Profiler::endMethodExec(__FUNCTION__." RPC::call entity /?$query");
        Kohana::$log->debug(__FUNCTION__, $result);
        $entitys = json_decode($result, TRUE);
	    if ($keepOrder) {
	        $tmp = array();
	        foreach ($ids as $id) {
	            if (isset($entitys[$id])) {
	                $tmp[$id] = $entitys[$id];
	            }
	        }
	        $entitys = $tmp;
	    }
	    $entitys = self::fieldsTransform($entitys);
        return $entitys;
    }
    
    /**
     * 明星
     * @param string $name 名字
     * @return array
     */
    public function star($name)
    {
	    $params = array(
	        'name' => $name,
	    );
        Kohana::$log->debug(__FUNCTION__, $params);
		Profiler::startMethodExec();
		$query = http_build_query($params);
		try {
            $result = RPC::call('star', '/?'.$query);
		} catch (Exception $e) {
            Kohana::$log->error($e);
            return array();
		}
	    Profiler::endMethodExec(__FUNCTION__." RPC::call star /?$query");
        Kohana::$log->debug(__FUNCTION__, $result);
        $result = json_decode($result, TRUE);
        if ($result) {
            $result = self::fieldsTransform(array($result));
            return array_shift($result);
        } else {
            return array();
        }
    }
    
    /**
     * 长视频检索，包括电影、电视剧、动漫和综艺
     * @param string $type 类型，tv - 电视剧，movie - 电影
     * @param string $sort 排序方式，new - 按上映日期，hot - 按热门程度
     * @param int $count
     * @param int $offset
     * @param array $filters 筛选条件
     * array(
     * 	genre => ..., //风格
     * 	region => ..., //地区
     * 	released_date => ..., //发行日期
     * 	cast => ..., //主演
     * )
     * @return array
     */
    public function longVideos($type, $sort = 'new', $count = 10, $offset = 0, 
        $filters = array())
    {
	    $params = array(
	        'cate' => $type,
	        'sort' => $sort,
	        'count' => $count,
	        'offset' => $offset,
	    );
	    if (isset($filters['genre'])) {
	        $params['type'] = $filters['genre'];
	    }
	    if (isset($filters['region'])) {
	        $params['area'] = $filters['region'];
	    }
	    if (isset($filters['released_date'])) {
	        $params['date'] = $filters['released_date'];
	    }
	    if (isset($filters['cast'])) {
	        $params['player'] = $filters['cast'];
	    }
        Kohana::$log->debug(__FUNCTION__, $params);
		Profiler::startMethodExec();
		$query = http_build_query($params);
		try {
            $result = RPC::call('long_video', '/req?'.$query);
		} catch (Exception $e) {
            Kohana::$log->error($e);
            return array();
		}
	    Profiler::endMethodExec(__FUNCTION__." RPC::call long_video /?$query");
        Kohana::$log->debug(__FUNCTION__, $result);
        $result = json_decode($result, TRUE);
        if ($result) {
            return array(
            	'total' => $result['all_num'], 
            	'data' => self::fieldsTransform($result['entity_list'])
            );
        } else {
            return array('total' => 0, 'data' => array());
        }
    }
    
    /**
     * 当前热映
     * @param string $type 类型，tv - 电视剧，movie - 电影
     * @return array
     */
    public function hotLongVideos($type)
    {
	    $params = array(
	        'cate' => $type,
	    );
        Kohana::$log->debug(__FUNCTION__, $params);
		Profiler::startMethodExec();
		$query = http_build_query($params);
		try {
            $result = RPC::call('long_video', '/hot_entity?'.$query);
		} catch (Exception $e) {
            Kohana::$log->error($e);
            return array();
		}
	    Profiler::endMethodExec(__FUNCTION__." RPC::call long_video /hot_entity?$query");
        Kohana::$log->debug(__FUNCTION__, $result);
        $result = json_decode($result, TRUE);
        if ($result) {
            return self::fieldsTransform($result);
        } else {
            return array();
        }
    }
    
    /**
     * 热门明星
     * @param string $type 类型，tv - 电视剧，movie - 电影
     * @return array
     */
    public function hotStars($type)
    {
	    $params = array(
	        'cate' => $type,
	    );
        Kohana::$log->debug(__FUNCTION__, $params);
		Profiler::startMethodExec();
		$query = http_build_query($params);
		try {
            $result = RPC::call('long_video', '/hot_actor?'.$query);
		} catch (Exception $e) {
            Kohana::$log->error($e);
            return array();
		}
	    Profiler::endMethodExec(__FUNCTION__." RPC::call long_video /hot_actor?$query");
        Kohana::$log->debug(__FUNCTION__, $result);
        $result = json_decode($result, TRUE);
        if ($result) {
            return array(
                'filter' => $result['actor_filter'],
                'hot' => self::fieldsTransform($result['actor_hot'])
            );
        } else {
            return array('filter' => array(), 'hot' => array());
        }
    }
    
 	/**
     * 
     * 获取热门实体
     * 
     * @param $type 类型，1 电影，2 电视剧
     * 
     * @return array
     */
    public function getHotedEntityList( $type=1 ) {
    	$arrReturn = array();
    	$strHotedKey = "ENTITY_HOT_MOVIE_LIST";
    	if($type==2) {
    		$strHotedKey = "ENTITY_HOT_TV_LIST";
    	}
    	
    	Profiler::startMethodExec();
    	$strResult = Rpc::call('entity_related', $strHotedKey);
    	Profiler::endMethodExec(__FUNCTION__." RPC::call entity_related $strHotedKey");
    	Jkit::$log->debug(__FUNCTION__. " type-{$type},ret-".$strResult);
    	if ( $strResult ) {
    		$arrReturn = $this->processKtJsonString( $strResult );
    	}
    	
    	return $arrReturn;
    }
    /**
     * 
     * 获取相关推荐的热门备选视频
     * 
     * @return array
     */
    public function getHotedVideos() {
    	$arrReturn = array();
    	$strHotedKey = "KEY_RELATE_VIDEO_ENTITY_HOT_SHORT";
    	
    	Profiler::startMethodExec();
    	$strResult = Rpc::call('entity_related', $strHotedKey);
    	Profiler::endMethodExec(__FUNCTION__." RPC::call entity_related $strHotedKey");
    	Jkit::$log->debug(__FUNCTION__. " ret-".$strResult);
    	if ( $strResult ) {
    		$arrReturn = $this->processKtJsonString( $strResult );
    	}
    	
    	return $arrReturn;
    }
    /**
     * 
     * 获取热门推荐圈子
     * @param $uid
     * @param $type 1 电影，2 电视剧
     * @param $count
     * 
     * @return array
     */
    public function getHotCircles( $uid=0, $type=1, $count=10 ) {
    	if(!$uid) {
    		$uid = -1;
    	}
    	$strType = $type===1 ? "movie" : "tv";
    	$arrRpcParam = array($uid, $strType);
    	Profiler::startMethodExec();
    	$arrReturn = RPC::call('entity_circle', array('EntityCircleRecClient', 'hot_rec'), $arrRpcParam);
    	Profiler::endMethodExec(__FUNCTION__." RPC::call entity_circle EntityCircleRecClient hot_rec");
    	if($arrReturn) {
    		$arrReturn = array_slice($arrReturn, 0, $count);
    	}
    	return $arrReturn;
    }
    
    /**
     * 
     * 热门圈子，补齐明星推荐圈子
     * 
     * @return array
     */
    public function getHotActorCircles() {
    	$strKey = "HOT_ACTORCIRCLE";
    	$arrReturn = array();
    	
    	Profiler::startMethodExec();
    	$strResult = Rpc::call('entity_related', $strKey);
    	Profiler::endMethodExec(__FUNCTION__." RPC::call entity_related $strKey");
    	Jkit::$log->debug(__FUNCTION__. " ret-".$strResult);
    	if ( $strResult ) {
    		$arrReturn = $this->processKtJsonString( $strResult );
    	}
    	if($arrReturn) {
    		$arrReturn = array_map("intval", $arrReturn);
    	}
    	return $arrReturn;
    }
    /**
     * 
     * 基于明星的推荐圈子
     * 
     * @param string $strName
     * @param int $uid
     * 
     * @return array
     */
	public function getActorRelatedCircles( $strName, $uid=0 ) {
		if(!$uid) {
    		$uid = -1;
    	}
    	$arrRpcParam = array($uid, $strName);
    	Profiler::startMethodExec();
    	$arrReturn = RPC::call('entity_circle', array('EntityCircleRecClient', 'actor_rec'), $arrRpcParam);
    	Profiler::endMethodExec(__FUNCTION__." RPC::call entity_circle EntityCircleRecClient actor_rec");
    	return $arrReturn;
    }
    
    /**
     * 
     * 基于长视频推荐的长视频
     * @param string $strEntityId
     * 
     * @return array
     */
    public function getEntityRelatedEntity($strEntityId) {
    	$arrReturn = array();
    	$strKtKeySuffix = "_ENTITY";
    	$strKey = $strEntityId.$strKtKeySuffix;
    	Profiler::startMethodExec();
    	$strResult = Rpc::call('entity_related', $strKey);
    	Profiler::endMethodExec(__FUNCTION__." RPC::call entity_related $strKey");
    	Jkit::$log->debug(__FUNCTION__. " key-{$strKey}, ret-".$strResult);
    	if ( $strResult ) {
    		$arrReturn = $this->processKtJsonString( $strResult );
    	} else {
    		$this->writeEntiyPendingList($strEntityId, 1);
    	}
    	
    	return $arrReturn;
    }
    
	/**
     * 
     * 基于长视频推荐的短视频
     * @param string $strEntityId
     * 
     * @return array
     */
    public function getEntityRelatedVideos($strEntityId) {
    	$arrReturn = array();
    	$strKtKeySuffix = "_SHORT";
    	$strKey = $strEntityId.$strKtKeySuffix;
    	Profiler::startMethodExec();
    	$strResult = Rpc::call('entity_related', $strKey);
    	Profiler::endMethodExec(__FUNCTION__." RPC::call entity_related $strKey");
    	Jkit::$log->debug(__FUNCTION__. " key-{$strKey} ret-".$strResult);
    	if ( $strResult ) {
    		$arrReturn = $this->processKtJsonString( $strResult );
    	} 
    	
    	return $arrReturn;
    }
    
	/**
     * 
     * 基于长视频推荐的圈子
     * @param string $strEntityId
     * @param int $uid
     * 
     * @return array
     */
    public function getEntityRelatedCircles($strEntityId, $uid=0) {
    	if(!$uid) {
    		$uid = -1;
    	}
    	$arrRpcParam = array($uid, $strEntityId);
    	Profiler::startMethodExec();
    	$arrReturn = RPC::call('entity_circle', array('EntityCircleRecClient', 'video_rec'), $arrRpcParam);
    	Profiler::endMethodExec(__FUNCTION__." RPC::call entity_circle EntityCircleRecClient video_rec");
    	return $arrReturn;
    }
    /**
     * 
     * 写入长视频推荐待处理列表
     * @param string $strEntityId
     * @param int $type  等待类型 1 长视频，2 相关圈子， 3短视频
     * 
     * @return boolean
     */
    public function writeEntiyPendingList($strEntityId, $type) {
    	$intNum = 3;
    	$strConfigName = "entity_pending_list";
    	$strKey = "KEY_VIDEOLIST_LONG";
    	if($type==2) {
    		$strKey = "KEY_VIDEOLIST_CIRCLE";
    	}
    	$redis = new Model_Data_RedisDB($strConfigName, $intNum);
    	try {
    		$objRedis = $redis->getDb();
    	} catch (Exception $e) {
    		Jkit::$log->error($e);
            return false;
    	}
    	
    	return $objRedis->rPush($strKey, $strEntityId);
    }
    /**
     * 
     * 处理 kt源串,转化成对应的json,纯 字符串和整型目前无法处理
     * @param string $strInput
     */
    private function processKtJsonString( $strInput ) {
    	if(!$strInput) {
    		return $strInput;
    	}
    	preg_match("/[\[\{][^\[\]\{\}]+[\]\}]/i", $strInput, $arrTmp);
    	return json_decode($arrTmp[0], true);
    }
}