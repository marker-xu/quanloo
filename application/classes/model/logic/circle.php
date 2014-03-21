<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 圈子、圈子视频关系、圈子用户关系等页面逻辑
 * @author wangjiajun
 */
class Model_Logic_Circle extends Model
{   
    const RANK_NEW = 1;
    const RANK_HOT = 2;
    const RANK_VIDEONUM = 3;
    const CIRCLE_HOT_THRESHOLD = 80; //popularity_rank字段是0到99的数字，大于80的认为是热门圈子
    const CIRCLE_HOT_REAL_VALUE = 1000; //popularity字段是整数，不断累加
    const CIRCLE_NEW_THRESHOLD = 259200; //创建时间在3天之内的圈子认为是新圈子
    const CIRCLE_USER_TAG_MAX_NUM = 10; //每个圈子用户最多可以输入10个tag
    const CAT_USER_TAG_MAX_NUM = 10; //每个圈子分类用户最多可以输入10个tag
    
    public static $basicFields = array('category', 'title', 'tn_path', 'certified', 
    	'official', 'status', 'create_time', 'creator', 'logo');
    public static $arrRankField = array(self::RANK_NEW => '_id', self::RANK_HOT => 'popularity_rank',
        self::RANK_VIDEONUM => 'video_count',
    );
    
	/**
	 * 查询单个圈子的基本信息，以及统计信息
	 * @param int $id
	 * @param int $status 返回指定状态的圈子，默认不检查
	 * @param bool $isNeedStat 是否带上统计信息
	 * @param array $fields 返回字段
	 * @return array|null
	 */
	public function get($id, $status = null, $isNeedStat = true, $fields = null)
	{
	    if (is_null($fields)) {
	        $fields = self::$basicFields;
	    }
	    
	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->get($id, $fields, $status);
	    
	    if ($circle) {
	        // 圈主信息
	        $modelLogicUser = new Model_Logic_User();
	        $circle['user'] = $modelLogicUser->get($circle['creator'], false);
	        
	        // 统计信息
	        if($isNeedStat) {
        	    $circles = array($circle);
        	    $modelLogicStat = new Model_Logic_Stat();  
        	    $modelLogicStat->complementCircleStatInfo($circles);
        	    $circle = array_shift($circles);
	        }
	    }
	    
	    return $circle;
	}
	
	/**
	 * 查询多个圈子的基本信息，以及统计信息
	 * @param array $ids
	 * @param bool $keepOrder 是否保持传入参数中ID的顺序
	 * @param int $status 返回指定状态的圈子，默认不检查
	 * @param bool $isNeedStat 是否带上统计信息
	 * @param array $fields 返回字段
	 * @return array
	 */
	public function getMulti($ids, $keepOrder = false, $status = null, $isNeedStat = false, 
	    $fields = null) 
	{
	    if (is_null($fields)) {
	        $fields = self::$basicFields;
	    }
	    
	    $modelDataCircle = new Model_Data_Circle();
	    Profiler::startMethodExec();
	    $circles = $modelDataCircle->getMulti($ids, $fields, $keepOrder, 
	        $status);
		Profiler::endMethodExec(__FUNCTION__.' getMulti');
	    if (!$circles) {
	        return array();
	    }
	    
        // 圈主信息
        $modelLogicUser = new Model_Logic_User();
        $users = $modelLogicUser->getMulti(Arr::pluck($circles, 'creator'), false, 
            false);
        array_walk($circles, function (&$value, $key) use ($users) {
            if (isset($users[$value['creator']])) {
                $value['user'] = $users[$value['creator']];
            } else {
                $value['user'] = null;
            }
        });
	    
        // 统计信息
	    if($isNeedStat) {
    	    $modelLogicStat = new Model_Logic_Stat();  
    	    Profiler::startMethodExec();
    	    $modelLogicStat->complementCircleStatInfo($circles);
    		Profiler::endMethodExec(__FUNCTION__.' complementCircleStatInfo');
	    }
	    
        if ($keepOrder) {
	        $tmp = array();
	        foreach ($ids as $id) {
	            if (isset($circles[$id])) {
	                $tmp[$id] = $circles[$id];
	            }
	        }
	        $circles = $tmp;
	    }
	    
	    return $circles;
	}
    
    /**
     * 相关圈子
     * @param int $type 1 - 关键词相关，2 - 视频相关，3 - 圈子相关
     * @param string|int $query type为1时为关键词，2时为视频ID，3时为圈子ID
     * @param int $userId 用户ID
     * @param int $count
     * @param int $offset
     * @return array
     */
    public function related($type, $query, $userId = null, $count = 3, $offset = 0)
    {
		$result = Model_Data_Recommend::relatedCircles($query, $userId, $offset, 
		    $count, $type);
		$modelCircle = new Model_Logic_Circle();
		$result['circles'] = $modelCircle->getMulti($result['circles'], true);
		return $result;
    }
    
    /**
     * 按类别浏览圈子
     * @return array
     */
    public function groupByCategory($intCat = 0, $intRank = self::RANK_NEW, $intCount = 24, 
        $intOffset = 0, $intUserId = 0, $strTag = '')
    {
        $arrCacheKey = array_merge((array) func_get_args(), array(__CLASS__, __FUNCTION__));
        $arrRet = Util::getCache($arrCacheKey, null);
        if (! empty($arrRet)) {
            //Jkit::$log->notice('hit cache', $arrCacheKey, __FILE__, __LINE__);
            return $arrRet;
        }        
        $arrRet = array('total' => 0, 'data' => array());
        $arrParam = array('query' => $strTag, 'cat' => $intCat, 'offset' => $intOffset, 'size' => $intCount);
        try {
            $strJson = RPC::call('search_circle', '/circle_search/tag.action', array('post_vars' => $arrParam));
        } catch (Exception $e) {
            return $arrRet;
        }
        if (! $strJson || ! ($arrTmp = json_decode($strJson, true))) {
            return $arrRet;
        }
        $arrRet['total'] = (int) $arrTmp['total'];
        $arrCurPageCircleId = $arrTmp['id_list'];
        if (empty($arrCurPageCircleId)) {
            return $arrRet;
        }
        
        $modelLogicCircle = new Model_Logic_Circle();
        $arrCircleStatField = array('_id', 'popularity_rank');
        $arrCond = array('_id' => array('$in' => $arrCurPageCircleId));
        $circles = $modelLogicCircle->getMulti($arrCurPageCircleId, true);
        
        $arrRet['data'] = (array) $circles;        
        if (! empty($arrRet)) {
            Util::setCache($arrCacheKey, $arrRet);
        }
        return $arrRet;
    }
    
    /**
     * 热门圈子
     * @param int $count
     * @return array
     */
    public function mostPopulated($count = 10)
    {
        $modelDataCircleStatAll = new Model_Data_CircleStatAll();
	    Profiler::startMethodExec();
        $stats = $modelDataCircleStatAll->find(array(), array(), array('video_count' => -1), 
            $count);
	    Profiler::endMethodExec(__FUNCTION__.' find');
        $ids = Arr::pluck($stats, '_id');
        $modelDataCircle = new Model_Data_Circle();
	    Profiler::startMethodExec();
        $circles = $modelDataCircle->getMulti($ids, array(), TRUE);
	    Profiler::endMethodExec(__FUNCTION__.' getMulti');
        foreach ($circles as &$circle) {
            $circle = array_merge($circle, $stats[$circle['_id']]);
        }
        return $circles;
    }
	
    /**
     * 随机选择一批圈子，返回基本信息，以及统计信息
     * @param int $count
     * @return array
     */
	public function random($count = 10)
	{
	    $modelDataCircle = new Model_Data_Circle();
	    $circles = $modelDataCircle->random($count, self::$basicFields);
	    
	    $modelLogicStat = new Model_Logic_Stat();  
	    $modelLogicStat->complementCircleStatInfo($circles);
        
	    return $circles;
	}

	/**
	 * 关注圈子
	 * @param int $circleId
	 * @param int $userId
	 * @return bool
	 */
	public function subscribe($circleId, $userId)
	{
	    $modelDataCircleUser = new Model_Data_CircleUser();
	    if ($modelDataCircleUser->subscribedCircleCount($userId) >= 500) {
	        throw new Model_Logic_Exception('关注圈子太多，请先取消关注一些圈子后再操作。');
	    }
	    $modelLogicCircle = new Model_Logic_Circle();
	    $circle = $modelLogicCircle->get($circleId, Model_Data_Circle::STATUS_PUBLIC, 
	        false);
	    if (!$circle) {
	        throw new Model_Logic_Exception('圈子已删除。');
	    }
	
	    try {
	        $modelDataCircleUser->subscribe($userId, $circleId);
	    } catch (MongoCursorException $e) {
	        return true;
	    }
	    
	    $modelLogicFeed = new Model_Logic_Feed();
	    $modelLogicFeed->addCircleFeed($circleId, Model_Data_CircleFeed::TYPE_SUBSCRIBED, 
	        $userId);
	    $modelLogicFeed->addUserFeed($userId, Model_Data_UserFeed::TYPE_SUBSCRIBE_CIRCLE, 
	        NULL, $circleId);
	        
        $modelDataCircleStatAll = new Model_Data_CircleStatAll();
        $modelDataCircleStatAll->inc(array(
        	'_id' => (int) $circleId
        ), 'user_count', 1);
	        
        $modelDataUserStatAll = new Model_Data_UserStatAll();
        $modelDataUserStatAll->inc(array(
        	'_id' => (int) $userId
        ), 'subscribed_circle_count', 1);
        
        // 颁发勋章
        $modelDataUser = new Model_Data_User();
        if (!$modelDataUser->isAwardedMedal($userId, Model_Data_User::MEDAL_SUBSCRIBE_CIRCLE)) {
            $modelDataUser->awardMedal($userId, Model_Data_User::MEDAL_SUBSCRIBE_CIRCLE);
        }
        
        // 清除圈友缓存
        $modelLogicUser = new Model_Logic_User();
        $modelLogicUser->clearCircleFriendsCache($userId);
	    
	    return true;
	}
	
	/**
	 * 取消关注圈子
	 * @param int $circleId
	 * @param int $userId
	 * @return bool
	 */
	public function unsubscribe($circleId, $userId)
	{
	    $modelDataCircleUser = new Model_Data_CircleUser();
	    try {
    	    $result = $modelDataCircleUser->delete(array(
    	    	'circle_id' => (int) $circleId, 
    	    	'user_id' => (int) $userId
    	    ));
	    } catch (MongoCursorException $e) {
	        return false;
	    }
	     
	    if ($result['n'] > 0) {
            $modelDataCircleStatAll = new Model_Data_CircleStatAll();
            $modelDataCircleStatAll->inc(array(
            	'_id' => (int) $circleId
            ), 'user_count', -1);
    	        
            $modelDataUserStatAll = new Model_Data_UserStatAll();
            $modelDataUserStatAll->inc(array(
            	'_id' => (int) $userId
            ), 'subscribed_circle_count', -1);
	    }
        
	    return true;
	}
	
	/**
	 * 
	 * 分享圈子
	 * @param $userId
	 * @param $intCircleId
	 * 
	 * @return boolean
	 */
	public function share($userId, $intCircleId) {
		$intCircleId = intval($intCircleId);
		$objModelUserShareCircle = new Model_Data_Usersharecircle($userId);
		$res = $objModelUserShareCircle->add($intCircleId);
		if($res) {
			#加入用户feed
			$objLogicFeed = new Model_Logic_Feed();
			$objLogicFeed->addUserFeed($userId, Model_Data_UserFeed::TYPE_SHARE_CIRCLE, NULL, $intCircleId);
			#加入圈子feed
			$objLogicFeed->addCircleFeed($intCircleId, Model_Data_CircleFeed::TYPE_SHARED, $userId);
		}
		
		return $res;
	}
	
	/**
	 * 已关注的圈子
	 * @param int $userId
	 * @param int $offset
	 * @param int $count
	 * @return array
	 */
	public function subscribed($userId, $offset = 0, $count = 10)
	{
	    $modelDataCircleUser = new Model_Data_CircleUser();
	    $query = array(
	    	'user_id' => (int) $userId
	    );
	    $total = $modelDataCircleUser->count($query);
	    $docs = $modelDataCircleUser->find($query, array('circle_id'), NULL, $count, 
	        $offset);
	    $circleIds = Arr::pluck($docs, 'circle_id');
	    $circles = $this->getMulti($circleIds, true);
	    return array('total' => $total, 'data' => $circles);
	}
	
	public function topicCircles($temp = TRUE)
	{
	    /* @var $redis Database_Redis */
		$redis = Database::instance("web_redis_master");
		$db = $redis->getRedisDB();
		if ($temp) {
		    $key = 'topic.circles.temp';
		} else {
		    $key = 'topic.circles';
		}
		$value = $db->get($key);
		if (!$value) {
		    $value = array();
		} else {
		    $value = json_decode($value, true);
		}
		return $value;
	}
	
	public function saveTopicCircles($value, $temp = TRUE)
	{
	    /* @var $redis Database_Redis */
		$redis = Database::instance("web_redis_master");
		$db = $redis->getRedisDB();
		if ($temp) {
		    $key = 'topic.circles.temp';
		} else {
		    $key = 'topic.circles';
		}
		$value = json_encode($value);
		$result =  $db->set($key, $value);
		return $result;
	}
	
	/**
	 * 查询推广圈子
	 * @param bool $temp
	 * @return array
	 */
	public function promoteCircles($temp = TRUE)
	{
	    /* @var $redis Database_Redis */
		$redis = Database::instance("web_redis_master");
		$db = $redis->getRedisDB();
		if ($temp) {
		    $key = 'promote.circles.temp';
		} else {
		    $key = 'promote.circles';
		}
		$value = $db->get($key);
		if (!$value) {
		    $value = array();
		} else {
		    $value = json_decode($value, true);
		}
		if ($temp) {
		    $circles = $this->getMulti(Arr::pluck($value, '_id'));
		    foreach ($value as &$circle) {
		        $circle = array_merge($circle, $circles[$circle['_id']]);
		    }
		}
		return $value;
	}
	
	/**
	 * 保存推广圈子
	 * @param bool $temp
	 * @return bool
	 */
	public function savePromoteCircles($value, $temp = TRUE)
	{
	    /* @var $redis Database_Redis */
		$redis = Database::instance("web_redis_master");
		$db = $redis->getRedisDB();
		if ($temp) {
		    $key = 'promote.circles.temp';
		} else {
		    $key = 'promote.circles';
		}
		$value = json_encode($value);
		$result =  $db->set($key, $value);
		return $result;
	}
	
	public static function isCircleHot($arrCircle) {
	    if (isset($arrCircle['popularity_rank']) && $arrCircle['popularity_rank'] >= self::CIRCLE_HOT_THRESHOLD) {
	        return true;
	    } elseif (isset($arrCircle['popularity']) && $arrCircle['popularity'] >= self::CIRCLE_HOT_REAL_VALUE) {
	        return true;
	    }	    
	    
	    return false;
	}
	
	public static function isCircleNew($arrCircle) {
	    if ($arrCircle['create_time'] instanceof MongoDate) {
	        $intTimestamp = $arrCircle['create_time']->sec;
	    } else {
	        $intTimestamp = strtotime((string) $arrCircle['create_time']);
	    }
	    if (isset($arrCircle['create_time']) && (time() - $intTimestamp) <= self::CIRCLE_NEW_THRESHOLD) {
	        return true;
	    }
	    
	    return false;
	}
    
    /**
     * 根据Tag查找圈子
     * @param string $tag
     * @param bool $caseSensitive
     * @param int $count
     * @return array
     */
    public function getByTag($tag, $caseSensitive = true, $count = 5)
    {
        $modelDataCircle = new Model_Data_Circle();
        if ($caseSensitive) {
            $circles = $modelDataCircle->find(array(
            	'tag' => $tag,
                'status' => Model_Data_Circle::STATUS_PUBLIC
            ), self::$basicFields, array('create_time' => -1), $count * 10);
        } else {
            $circles = $modelDataCircle->find(array(
            	'tag' => new MongoRegex("/^$tag$/i"),
                'status' => Model_Data_Circle::STATUS_PUBLIC
            ), self::$basicFields, array('create_time' => -1), $count * 10);
        }
        $modelDataCircleStatAll = new Model_Data_CircleStatAll();
        $circleIds = Arr::pluck($circles, '_id');
        $docs = $modelDataCircleStatAll->find(array('_id' => array('$in' => $circleIds)), 
            array('_id'), array('popularity' => -1));
        $tmp = array();
        foreach ($docs as $doc) {
            $tmp[] = $circles[$doc['_id']];
            unset($circles[$doc['_id']]);
        }
        if (count($tmp) >= $count) {
            $circles = $tmp;
        } else {
            $circles = array_merge($tmp, array_values($circles));
        }
        $circles = array_slice($circles, 0, $count);
        return $circles;
    }
    
    /**
     * 创建圈子
	 * @param string $title
	 * @param array|int $category
	 * @param int $creator
	 * @param array $tag
	 * @param int $status
	 * @param array $doc
	 * @return int
     */
    public function create($title, $category, $creator, $tag = array(), $status = Model_Data_Circle::STATUS_PUBLIC, 
	    $doc = array())
    {
        if (Model_Logic_Blackword::instance()->filter($title)) {
	        throw new Model_Logic_Exception("圈名里包含了敏感词。");
        }
        $doc = array_merge($doc, array(
            'title' => $title,
            'category' => is_array($category) ? $category : array((int) $category),
            'tag' => $tag,
            'creator' => (int) $creator,
            'status' => (int) $status,
            'create_time' => new MongoDate(),
        ));
	    if (!isset($doc['_id'])) {
	        $modelDataCounter = new Model_Data_Counters();
	        $doc['_id'] = $modelDataCounter->id('circle');
	    }
        $doc['tag'] = array_values(array_unique(array_filter($doc['tag'])));
        
	    $modelDataUserStatAll = new Model_Data_UserStatAll();
	    if (!isset($doc['official']) || $doc['official'] != 1) {
    	    $stat = $modelDataUserStatAll->findOne(array('_id' => (int) $creator));
    	    if ($stat && isset($stat['created_circle_count']) && $stat['created_circle_count'] >= 100) {
    	        throw new Model_Logic_Exception("创建圈子数超过上限。");
    	    }
	    }
	    $modelDataCircle = new Model_Data_Circle();
	    if ($modelDataCircle->findOne(array(
            'creator' => $doc['creator'],
            'title' => $doc['title'],
            'status' => array('$in' => array(Model_Data_Circle::STATUS_PUBLIC, Model_Data_Circle::STATUS_PRIVATE)),
	    ))) {
	        throw new Model_Logic_Exception("你已创建过该圈子。");
	    }
        $modelDataCircle->insert($doc);
    
        if (in_array($status, array(Model_Data_Circle::STATUS_PUBLIC, 
            Model_Data_Circle::STATUS_PRIVATE))) {
    	    $modelDataUserStatAll->inc(array(
                '_id' => (int) $creator,
            ), 'created_circle_count');
        }
        if (in_array($status, array(Model_Data_Circle::STATUS_PUBLIC))) {
    	    $modelDataUserStatAll->inc(array(
                '_id' => (int) $creator,
            ), 'created_public_circle_count');
        }
        
        return $doc['_id'];
    }
    
    /**
     * 删除圈子（逻辑删除）
	 * @param int $id
	 * @return bool
     */
    public function delete($id)
    {
	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->get($id);
	    if (!$circle) {
	        return true;
	    }
	    
        $modelDataCircle->update(array(
        	'_id' => (int) $id
        ), array(
            'status' => (int) Model_Data_Circle::STATUS_DELETED,
        ));
        
    	$modelDataUserStatAll = new Model_Data_UserStatAll();
        if (in_array($circle['status'], array(Model_Data_Circle::STATUS_PUBLIC, 
            Model_Data_Circle::STATUS_PRIVATE))) {
    	    $modelDataUserStatAll->inc(array(
                '_id' => (int) $circle['creator'],
            ), 'created_circle_count', -1);
        }
        if (in_array($circle['status'], array(Model_Data_Circle::STATUS_PUBLIC))) {
    	    $modelDataUserStatAll->inc(array(
                '_id' => (int) $circle['creator'],
            ), 'created_public_circle_count', -1);
        }
        
        return true;
    }
	
	/**
	 * 修改圈子
	 * @param int $id
	 * @param array $doc
	 * @return bool
	 */
	public function modify($id, $doc)
	{
        $modelDataCircle = new Model_Data_Circle();
        $circle = $modelDataCircle->get($id);
        if (!$circle) {
            return true;
        }
	    if (isset($doc['tag'])) {
            $doc['tag'] = array_values(array_unique(array_filter($doc['tag'])));
	    }
	    if (isset($doc['category']) && !is_array($doc['category'])) {
            $doc['category'] = array((int) $doc['category']);
	    }
	    
	    $modelDataCircle->update(array('_id' => (int) $id), $doc);
	    if (isset($doc['status'])) {
        	$modelDataUserStatAll = new Model_Data_UserStatAll();
    	    if (in_array($circle['status'], array(Model_Data_Circle::STATUS_PUBLIC, Model_Data_Circle::STATUS_PRIVATE))
    	        && !in_array($doc['status'], array(Model_Data_Circle::STATUS_PUBLIC, Model_Data_Circle::STATUS_PRIVATE))) {
        	    $modelDataUserStatAll->inc(array(
                    '_id' => (int) $circle['creator'],
                ), 'created_circle_count', -1);
            } elseif (!in_array($circle['status'], array(Model_Data_Circle::STATUS_PUBLIC, Model_Data_Circle::STATUS_PRIVATE))
    	        && in_array($doc['status'], array(Model_Data_Circle::STATUS_PUBLIC, Model_Data_Circle::STATUS_PRIVATE))) {
        	    $modelDataUserStatAll->inc(array(
                    '_id' => (int) $circle['creator'],
                ), 'created_circle_count', 1);
            }
    	    if (in_array($circle['status'], array(Model_Data_Circle::STATUS_PUBLIC))
    	        && !in_array($doc['status'], array(Model_Data_Circle::STATUS_PUBLIC))) {
        	    $modelDataUserStatAll->inc(array(
                    '_id' => (int) $circle['creator'],
                ), 'created_public_circle_count', -1);
            } elseif (!in_array($circle['status'], array(Model_Data_Circle::STATUS_PUBLIC))
    	        && in_array($doc['status'], array(Model_Data_Circle::STATUS_PUBLIC))) {
        	    $modelDataUserStatAll->inc(array(
                    '_id' => (int) $circle['creator'],
                ), 'created_public_circle_count', 1);
            }
	    }
        
	    return true;
	}
	
	/**
	 * 创建的圈子
	 * @param int $userId
	 * @param int $offset
	 * @param int $count
	 * @param string $mode 模式，guest为客人，只返回公开圈子，host为主人，返回公开和私有圈子
	 * @param int $total
	 * @return array
	 */
	public function created($userId, $offset = 0, $count = 10, $mode = 'guest', 
	    &$total = null)
	{
	    $modelDataCircle = new Model_Data_Circle();
	    if ($mode == 'host') {
    	    $query = array(
    	    	'creator' => (int) $userId,
    	        'status' => array('$in' => array(Model_Data_Circle::STATUS_PUBLIC, 
    	            Model_Data_Circle::STATUS_PRIVATE))
    	    );
	    } else {
    	    $query = array(
    	    	'creator' => (int) $userId,
    	        'status' => array('$in' => array(Model_Data_Circle::STATUS_PUBLIC))
    	    );
	    }
	    $circles = $modelDataCircle->find($query, self::$basicFields, array('create_time' => -1), 
	        $count, $offset);
	    
	    if (!is_null($total)) {
    	    $modelDataUserStatAll = new Model_Data_UserStatAll();
    	    $stat = $modelDataUserStatAll->findOne(array(
                '_id' => (int) $userId,
            ));
            if (!$stat) {
                $total = 0;
            } else {
                if ($mode == 'host') {
                    $total = isset($stat['created_circle_count']) ? $stat['created_circle_count'] : 0;
                } else {
                    $total = isset($stat['created_public_circle_count']) ? $stat['created_public_circle_count'] : 0;
                }
            }
	    }
	    
	    return $circles;
	}
    
	public function getCircleByTitle($title, $uid=null, $notCircleId=NULL) {
		$modelDataCircle = new Model_Data_Circle();
		$query = array(
            'title' => $title,
			'status' => array('$in'=>array(
				Model_Data_Circle::STATUS_PUBLIC,
				Model_Data_Circle::STATUS_PRIVATE
			))
        );
		if($uid!==NULL) {
        	$query['creator'] = (int) $uid;
        }
        if($notCircleId!==NULL) {
        	$query['_id'] = array( '$ne'=>(int)$notCircleId );
        }
        $arr = $modelDataCircle->findOne($query);
        
        return $arr;
	}
	
	
    /**
     * 创建候选圈子
	 * @param string $title
	 * @param int $source
	 * @param int $creator
	 * @param int $status
	 * @param array $doc
	 * @return MongoId
     */
    public function createCandidateCircle($title, $source, $creator = 0, $status = Model_Data_CircleCandidate::STATUS_PENDING, 
	    $doc = array())
    {
	    $modelDataCircleCandidate = new Model_Data_CircleCandidate();
	    if ($modelDataCircleCandidate->getByTitle($title)) {
	        $modelDataCircleCandidate->inc(array('title' => $title), 'submitted_count');
	        return true;
	    }
	    if (!isset($doc['tag'])) {
	        $doc['tag'] = array();
	    }
        $doc['tag'] = array_values(array_unique(array_filter(($doc['tag']))));
	    
	    $modelLogicSearch = new Model_Logic_Search();
        $doc['video_count'] = 0;
        foreach ($doc['tag'] as $tag) {
            $result = $modelLogicSearch->search($tag);
            $doc['video_count'] += isset($result['real_total']) ? (int) $result['real_total'] : 0;
        }
        return $modelDataCircleCandidate->create($title, $source, $creator, $status, $doc);
    }
    
    /**
     * 为圈子添加用户自定义的tag
     *
     * @param array $arrParams array(
     *  user_id => 用户ID
     *  circle_id => 圈子ID
     * 	tag => 标签内容
     * )
     */
    public function addUserTag($arrParams) {
        $strId = $this->_circleUserTagId($arrParams);
        return $this->_addCircleConfigTag($strId, $arrParams, self::CIRCLE_USER_TAG_MAX_NUM);
    }

    /**
     * 删除圈子中用户自定义的tag
     *
     * @param array $中arrParams array(
     *  user_id => 用户ID
     *  circle_id => 圈子ID
     * 	tag => 标签内容
     * )
     */
    public function delUserTag($arrParams) {
        $strId = $this->_circleUserTagId($arrParams);
        return $this->_delCircleConfigTag($strId, $arrParams);
    }

    /**
     * 获取圈子中用户自定义的tag
     *
     * @param array $中arrParams array(
     *  user_id => 用户ID
     *  circle_id => 圈子ID
     * )
     */
    public function getUserTag($intUserId, $intCircleId) {
        $strId = $this->_circleUserTagId(array('circle_id' => $intCircleId, 'user_id' => $intUserId));
        return $this->_getCircleConfigTag($strId);
    }
    
    /**
     * 为圈子添加用户自定义的tag
     *
     * @param array $arrParams array(
     *  user_id => 用户ID
     *  circle_id => 圈子ID
     * 	tag => 标签内容
     * )
     */
    public function addCatTag($arrParams) {
        $strId = $this->_catUserTagId($arrParams);
        return $this->_addCircleConfigTag($strId, $arrParams, self::CAT_USER_TAG_MAX_NUM);
    }
    
    /**
     * 删除圈子中用户自定义的tag
     *
     * @param array $中arrParams array(
     *  user_id => 用户ID
     *  circle_id => 圈子ID
     * 	tag => 标签内容
     * )
     */
    public function delCatTag($arrParams) {
        $strId = $this->_catUserTagId($arrParams);
        return $this->_delCircleConfigTag($strId, $arrParams);
    }
    
    /**
     * 获取圈子中用户自定义的tag
     *
     * @param array $中arrParams array(
     *  user_id => 用户ID
     *  circle_id => 圈子ID
     * )
     */
    public function getCatTag($intUserId, $intCat) {
        $strId = $this->_catUserTagId(array('cat' => $intCat, 'user_id' => $intUserId));;
        return $this->_getCircleConfigTag($strId);
    }    
    
    /**
     * 添加过滤Tag
     * @param int $circleId
     * @param string|array $tag
     * @param string $dimension 维度
     * @return int|bool
     */
    public function addFilterTag($circleId, $tag, $dimension = '热门')
    {
        if (!is_array($tag)) {
            $tag = array($tag);
        }
        $modelDataCircle = new Model_Data_Circle();
        $circle = $modelDataCircle->get($circleId, array('filter_tag'));
        if (!$circle) {
            return false;
        }
        if (!isset($circle['filter_tag']) || !$circle['filter_tag']) {
            $circle['filter_tag'] = array(
                array('name' => '热门', 'tag' => array(), 'default' => true)
            );
        }
        $found = false;
        array_walk($circle['filter_tag'], function (&$value, $key) use ($tag, 
            $dimension, &$found) {
            if ($value['name'] == $dimension) {
                $value['tag'] = array_values(array_unique(array_filter(array_merge($value['tag'], $tag))));
                $found = true;
            }
        });
        if (!$found) {
            array_push($circle['filter_tag'], array(
            	'name' => $dimension, 
            	'tag' => array_values(array_unique(array_filter($tag)))
            ));
        }
        try {
            $modelDataCircle->update(array(
                '_id' => (int) $circleId
            ), array(
                'filter_tag' => $circle['filter_tag']
            ));
        } catch (MongoException $e) {
            return false;
        }
        $total = 0;
        array_walk($circle['filter_tag'], function ($value, $key) use (&$total) {
            $total += count($value['tag']);
        });
        return $total;
    }
    
    /**
     * 删除过滤Tag
     * @param int $circleId
     * @param string|array $tag
     * @param string $dimension 维度
     * @return bool
     */
    public function deleteFilterTag($circleId, $tag, $dimension = '热门')
    {
        if (!is_array($tag)) {
            $tag = array($tag);
        }
        $modelDataCircle = new Model_Data_Circle();
        $circle = $modelDataCircle->get($circleId, array('filter_tag'));
        if (!$circle) {
            return false;
        }
        if (!isset($circle['filter_tag'])) {
            $circle['filter_tag'] = array();
        }
        array_walk($circle['filter_tag'], function (&$value, $key) use ($tag, $dimension) {
            if ($value['name'] == $dimension) {
                $value['tag'] = array_values(array_unique(array_filter(array_diff($value['tag'], $tag))));
            }
        });
        try {
            $modelDataCircle->update(array(
                '_id' => (int) $circleId
            ), array(
                'filter_tag' => $circle['filter_tag']
            ));
        } catch (MongoException $e) {
            return false;
        }
        $total = 0;
        array_walk($circle['filter_tag'], function (&$value, $key) use (&$total) {
            $total += count($value['tag']);
        });
        return $total;
    }
    
    protected function _addCircleConfigTag($strId, $arrParams, $intMaxNum) {
        $arrQuery = array('_id' => $strId);
        
        $ret = false;
        $objConf = new Model_Data_CircleUserConfig();
        try {
            $ret = $objConf->findOne($arrQuery, array('tag' => true));
            if ($ret) {
                $arrTag = (array) $ret['tag'];
            } else {
                $arrTag = array();
            }
            $strAddTag = trim($arrParams['tag']);
            $intTagNum = count($arrTag);
            if ($intTagNum >= $intMaxNum) {
                return array('ret' => false, 'total' => $intMaxNum, 'msg' => "最多添加{$intMaxNum}个标签");
            }
            foreach ($arrTag as $v) {
                if ($v['name'] == $strAddTag) {
                    return array('ret' => false, 'total' => $intTagNum, 'msg' => '不能添加重复的标签');
                }
            }
            $arrTag[] = array('name' => $strAddTag, 'ctime' => time());
            $ret = (bool) $objConf->update($arrQuery, array('tag' => $arrTag), array('upsert' => true));
            if ($ret) {
                $intTagNum++;
            }
        } catch (Exception $e) {
            JKit::$log->warn("add tag fail, code-".$e->getCode().", msg-".$e->getMessage());
            $ret = false;
        }
        return array('ret' => $ret, 'total' => (int) $intTagNum);        
    }
    
    protected function _delCircleConfigTag($strId, $arrParams) {
        $arrQuery = array('_id' => $strId);
        
        $ret = false;
        $objConf = new Model_Data_CircleUserConfig();
        try {
            $ret = $objConf->findOne($arrQuery, array('tag' => true));
            if ($ret) {
                $arrTag = (array) $ret['tag'];
            } else {
                $arrTag = array();
            }
            $strDelTag = trim($arrParams['tag']);
            foreach ($arrTag as $k => $v) {
                if ($v['name'] == $strDelTag) {
                    unset($arrTag[$k]);
                    break;
                }
            }
        
            $intTagNum = count($arrTag);
            $ret = (bool) $objConf->update($arrQuery, array('tag' => array_values($arrTag)), array('upsert' => true));
        } catch (Exception $e) {
            JKit::$log->warn("del tag fail, code-".$e->getCode().", msg-".$e->getMessage());
            $ret = false;
        }
        return array('ret' => $ret, 'total' => (int) $intTagNum);    
    }
    
    protected function _getCircleConfigTag($strId) {
        $arrQuery = array('_id' => $strId);
        $objConf = new Model_Data_CircleUserConfig();
        $arrTag = array();
        try {
            $ret = $objConf->findOne($arrQuery, array('tag' => true));
            if ($ret) {
                $arrTag = (array) $ret['tag'];
                $arrTmp = array();
                foreach ($arrTag as $k => $v) {
                    $arrTmp[] = $v['name'];
                }
                $arrTag = $arrTmp;
            }
        } catch (Exception $e) {
            JKit::$log->warn("get tag fail, code-".$e->getCode().", msg-".$e->getMessage());
        }
        return $arrTag;
    }
    
    protected function _circleUserTagId($arrParams) {
        return "{$arrParams['circle_id']}_{$arrParams['user_id']}";
    }
    
    protected function _catUserTagId($arrParams) {
        $arrParams['cat'] = intval($arrParams['cat']);
        return "cat_{$arrParams['cat']}_{$arrParams['user_id']}";
    }
    
    public function getCatSysTag($intCat) {
        $intCat = (int) $intCat;
        $arrTmp = (array) Model_Data_Circle::categorys();
        $arrTmp2 = array();
        foreach ($arrTmp as $v) {
            if ($v['id'] == $intCat) {
                $arrTmp2 = (array) $v['tags'];
                break;
            }
        }
        return $arrTmp2;
    }
    
    public function parseFilterTag($arrTag) {
        $arrTmp = array();
        if (! empty($arrTag)) {
            foreach ($arrTag as $v) {
                if (!isset($v['tag'])) {
                    $v['tag'] = array();
                }
                if (!isset($v['weight'])) {
                    $v['weight'] = array();
                }
                foreach ($v['tag'] as $tag) {
                    if (isset($v['weight'][$tag])) {
                        $arrTmp[$tag] = $v['weight'][$tag];
                    } else {
                        $arrTmp[$tag] = 0;
                    }
                }
            }
        }
        return $arrTmp;
    }
    
    public function completeMobilePicPath( &$arrCircles ) {
    	if(!$arrCircles) {
    		return;
    	}
    	$arrCids = Arr::pluck($arrCircles, "_id");
		$objModelCircle = new Model_Data_Circle();
		$modelLogicRecommend = new Model_Logic_Recommend();
		$arrMobilePicList = $objModelCircle->getMulti($arrCids, array("mobile_picpath"));
		$arrVideoOpt = array('has_video_stat' => false, 'has_circle_info' => false, 'has_video_comment' => false);
		foreach ($arrCircles as $k=>$v) {
			if( isset( $arrMobilePicList[$v["_id"]]["mobile_picpath"] ) ) {
				$arrCircles[$k]['video_thumbnail'] = $arrMobilePicList[$v["_id"]]["mobile_picpath"];
			} else {
				try {
					$arrRet = $modelLogicRecommend->getCircleVideosByTag($v['_id'], $v['title'], '', 0, 1, Model_Logic_Video::VIDEO_RANK_DEFAULT, null, $arrVideoOpt);
					if (isset($arrRet['video']) && is_array($arrRet['video'])) {
						$arrTmp = current($arrRet['video']);
						$arrCircles[$k]['video_thumbnail'] = Util::videoThumbnailUrl($arrTmp['thumbnail']);
					}
				} catch (Exception $e) {
					continue;
				}
			}
		}
    		
    }
    
    /**
     * 圈内实体
     * @param int $circleId
     * @return array|null
     */
    public function entity($circleId)
    {
        $modelCircleEntity = new Model_Data_CircleEntity(Model_Data_CircleEntity::DB_CIRCLE_ENTITY);
        $entityId = $modelCircleEntity->get($circleId);
        if (!$entityId) {
            return null;
        }
        $modelEntity = new Model_Data_Entity();
        return $modelEntity->get($entityId);
    }
}