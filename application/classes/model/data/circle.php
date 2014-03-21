<?php 

/**
 * 圈子
 * @author wangjiajun
 */
class Model_Data_Circle extends Model_Data_MongoCollection
{
    const STATUS_UNINITIALIZED = 0;
    const STATUS_PUBLIC = 1;
    const STATUS_PRIVATE = 2;
    const STATUS_DELETED = 3;
    
    public static $statuses = array(
        self::STATUS_UNINITIALIZED => '待处理',
        self::STATUS_PUBLIC => '公开',
        self::STATUS_PRIVATE => '私有',
        self::STATUS_DELETED => '删除'
    );

    //分类ID到分类英文名字的对应关系
    public static $arrUrlKeyForCategorys = array(0 => 'all', 1 => 'music', 7 => 'movie', 9 => 'tv', 19 => 'fun',
            12 => 'variety', 6 => 'news', 14 => 'sports', 13 => 'star', 8 => 'fashion', 2 => 'comic',
            11 => 'game', 16 => 'car', 18 => 'creativity', 23 => 'finance', 21 => 'travel',
            15 => 'life', 17 => 'tech', 20 => 'documentary', 22 => 'health', 24 => 'military',
            4 => 'edu', 5 => 'dv');
    
    public static $categorys = array(1 => '音乐MV', 7 => '电影', 9 => '电视剧', 19 => '搞笑', 
        12 => '综艺', 6 => '资讯', 14 => '体育', 13 => '明星', 8 => '时尚', 2 => '动漫', 
        11 => '游戏', 16 => '汽车', 18 => '创意', 23 => '财经', 21 => '旅游', 
        15 => '生活', 17 => '科技', 20 => '纪录片', 22 => '健康', 24 => '军事', 
        4 => '教育', 5 => '原创');
    
    public static $adminCategorys = array(1 => '音乐MV', 2 => '动漫', 3 => '人物', 4 => '教育', 
        5 => '原创', 6 => '资讯', 7 => '电影', 8 => '时尚', 9 => '电视剧', 10 => '专题', 
        11 => '游戏', 12 => '综艺', 13 => '明星', 14 => '体育', 15 => '生活', 16 => '汽车', 
        17 => '科技', 18 => '创意', 19 => '搞笑', 20 => '纪录片', 21 => '旅游', 22 => '健康', 
        23 => '财经', 24 => '军事');
    
	public function __construct()
	{
        parent::__construct('circle', 'video_search', 'circle');
	}
	
	/**
	 * 查询单个圈子的信息
	 * @param string $id
	 * @param array $fields
	 * @param int|array $status 返回指定状态的圈子，默认不检查
	 * @return array|null
	 */
	public function get($id, $fields = array(), $status = null)
	{
	    $query = array('_id' => (int) $id);
	    if (!is_null($status)) {
    	    if (!is_array($status)) {
    	        $status = array((int) $status);
    	    }
	        $query['status'] = array('$in' => $status);
	    }
	    return $this->findOne($query, $fields);
	}
    
    /**
     * 根据名称查找圈子
     * @param string $title
     * @param bool $caseSensitive
     * @param array $fields
     * @param int|array $status
     * @return array|null
     */
    public function getByTitle($title, $caseSensitive = true, $fields = array(), 
        $status = self::STATUS_PUBLIC)
    {
        if (!is_array($status)) {
            $status = array($status);
        }
        if ($caseSensitive) {
            $circle = $this->findOne(array(
            	'title' => $title,
                'status' => array('$in' => $status)
            ), $fields);
        } else {
            $circle = $this->findOne(array(
            	'title' => new MongoRegex("/^$title$/i"),
                'status' => array('$in' => $status)
            ), $fields);
        }
        return $circle;
    }
	
	/**
	 * 查询多个圈子的信息
	 * @param array $ids
	 * @param array $fields
	 * @param bool $keepOrder 是否保持传入参数中ID的顺序
	 * @param int|array $status 返回指定状态的圈子，默认不检查
	 * @return array
	 */
	public function getMulti($ids, $fields = array(), $keepOrder = false, $status = null) 
	{
	    if (!$ids) {
	        return array();
	    }
	    $query = array('_id' => array('$in' => $ids));
	    if (!is_null($status)) {
    	    if (!is_array($status)) {
    	        $status = array((int) $status);
    	    }
	        $query['status'] = array('$in' => $status);
	    }
	    $circles = $this->find($query, $fields);
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
     * 随机选择一批圈子
     * @param int $count
     * @param array $fields
     * @param array $query
     * @return array
     */
    public function random($count, $fields = array(), $query = array())
    {
        $circles = $this->find($query, array('_id'), array('_id' => 1), 1);
        $circle = array_shift($circles);
        $minId = $circle['_id'];
        $circles = $this->find($query, array('_id'), array('_id' => -1), 1);
        $circle = array_shift($circles);
        $maxId = $circle['_id'];
        $circles = array();
        $loop = 0;
        while (count($circles) < $count) {
            $id = rand($minId, $maxId);
            $query = array_merge($query, array(
            	'_id' => array(
            		'$gte' => $id - intval($count / 2), 
            		'$lte' => $id + intval($count / 2)
                )
            ));
            $docs = $this->find($query, $fields);
            foreach ($docs as $doc) {
                if (count($circles) == $count) {
                    break;
                }
                if (!isset($circles[$doc['_id']])) {
                    $circles[$doc['_id']] = $doc;
                }
            }
            if (++$loop == 2) {
                break;
            }
        }
        return $circles;
    }
    
    /**
     * 圈子分类
     * @param null|array $categorys 不传表示读取，传递则为设置
     * @return array|bool
     */
    public static function categorys($categorys = null)
    {
        $key = "circle_category_tags";
        if (is_null($categorys)) {
            $names = array('web_redis_master', 'web_redis_slave');
			$redis = Database::instance($names[array_rand($names)]);
			/* @var $db Redis */
			$db = $redis->getRedisDB();
			$value = $db->get($key);
			if ($value) {
			    $categorys = json_decode($value, true);
			} else {
			    $categorys = array();
			    foreach (self::$categorys as $key => $value) {
			        $categorys[$key] = array(
			            'id' => $key,
			            'name' => $value,
			            'tags' => array()
			        );
			    }
			}
			return $categorys;
        } else {
			$redis = Database::instance('web_redis_master');
			/* @var $db Redis */
			$db = $redis->getRedisDB();
			return $db->set($key, json_encode($categorys));
        }
    }
    
    /**
     * 圈子分类候选Tag
     * @param int $category 不传表示读取，传递则为设置
     * @return array
     */
    public static function categoryCandidateTags($category = 0)
    {
        $redis = Cache::instance('recommend');
        /* @var $db Redis */
        $db = $redis->getRedisDB(9);
        $key = "cate".$category;
        $value = $db->zRevRange($key, 0, -1);
        return $value;
    }
}
