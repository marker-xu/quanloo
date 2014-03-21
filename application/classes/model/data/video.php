<?php 

/**
 * 视频
 * @author wangjiajun
 */
class Model_Data_Video extends Model_Data_MongoCollection
{
    const TYPE_VIDEO = 1; // 短视频
    const TYPE_MOVIE = 2; // 电影
    const TYPE_TV = 3; // 电视剧
    const TYPE_ALBUM = 4; // 专辑
    
    const QUALITY_NORMAL = 1; // 普通
    const QUALITY_CLEAR = 2; // 清晰
    const QUALITY_SD = 3; // 标清
    const QUALITY_HD = 4; // 高清
    const QUALITY_UD = 5; // 超清
    
    const STATUS_VALID = 1; // 有效
    const STATUS_DEAD = 2; // 死链
    const STATUS_SHIELDED = 3; // 被屏蔽
    
    const SOURCE_FIREFOX = 'firefox'; //火狐一键应用
    
    public static $types = array(self::TYPE_VIDEO => '短视频', self::TYPE_MOVIE => '电影', 
        self::TYPE_TV => '电视剧', self::TYPE_ALBUM => '专辑');
    
    public static $categorys = array(1 => '音乐', 2 => '动漫', 3 => '人物', 4 => '教育', 
        5 => '原创', 6 => '资讯', 7 => '电影', 8 => '时尚', 9 => '电视剧', 10 => '专题', 
        11 => '游戏', 12 => '综艺', 13 => '娱乐');
    
    public static $qualitys = array(self::QUALITY_NORMAL => '普通', self::QUALITY_CLEAR => '清晰', 
        self::QUALITY_SD => '标清', self::QUALITY_HD => '高清', self::QUALITY_UD => '超清');
    
    public static $statuses = array(self::STATUS_VALID => '有效', self::STATUS_DEAD => '死链', 
        self::STATUS_SHIELDED => '被屏蔽');
        
    public static $domains = array('ku6.com' => '酷六', 'youku.com' => '优酷', 'tudou.com' => '土豆', 
    	'sohu.com' => '搜狐', 'iqiyi.com' => '奇艺', 'sina.com.cn' => '新浪', 'letv.com' => '乐视', 
        '56.com' => '56', 'pptv.com' => 'PPTV', 'joy.cn' => '激动', 'xunlei.com' => '迅雷');
    
    public static $durations = array(
//        0 => '1分钟以内',
        1 => '5分钟以内',
        2 => '5到10分钟',
        3 => '10到30分钟',
        4 => '30到60分钟',
        5 => '60到120分钟',
        6 => '120分钟以上'
    );
    
    //请求来源
    public static $source = array(self::SOURCE_FIREFOX => /*火狐一键应用*/true,);
    
	public function __construct()
	{
        parent::__construct('video', 'video_search', 'video');
	}
	
	/**
	 * 查询单个视频的信息
	 * @param string $id
	 * @param array $fields
	 * @return array|null
	 */
	public function get($id, $fields = array())
	{
	    return $this->findOne(array('_id' => $id), $fields);
	}
	
	/**
	 * 查询多个视频的信息
	 * @param array $ids
	 * @param array $fields
	 * @param bool $keepOrder 是否保持传入参数中ID的顺序
	 * @return array
	 */
	public function getMulti($ids, $fields = array(), $keepOrder = false) 
	{
	    if (!$ids) {
	        return array();
	    }
	    $videos = $this->find(array('_id' => array('$in' => $ids)), $fields);
	    if ($keepOrder) {
	        $tmp = array();
	        foreach ($ids as $id) {
	            if (isset($videos[$id])) {
	                $tmp[$id] = $videos[$id];
	            }
	        }
	        $videos = $tmp;
	    }
	    return $videos;
	}

	/**
	 * 查询视频缩略图
	 * @param array $ids
	 * @return array
	 */
    public function getThumbnails($ids)
    {
        if (!$ids) {
            return array();
        }
        $params = array(
            'method' => 'POST',
            'post_vars' => array(
                'keys' => implode(',', $ids)
            )
        );
        Kohana::$log->debug(__FUNCTION__, $params);
		Profiler::startMethodExec();
        $result = RPC::call('video_thumbnail', '/', $params);
	    Profiler::endMethodExec(__FUNCTION__.' video_thumbnail');
        Kohana::$log->debug(__FUNCTION__, $result);
        $result = json_decode($result, true);
        return $result;
    }
    
    /**
     * 随机选择一批视频
     * @param int $count
     * @param array $fields
     * @return array
     */
    public function random($count, $fields = array())
    {
        $videos = $this->find(array(), $fields, NULL, $count, rand(0, 10000));
        return $videos;
    }
    
    /**
     * 对后端返回的视频字段名，转换成跟前端一致
     * @param array $videos
     * @return array
     */
    public static function fieldsTransform($videos)
    {
        $map = array('v_id' => '_id', 'v_type' => 'type', 'v_category' => 'category', 
        	'v_highlight_title' => 'highlight_title', 'v_title' => 'title', 'v_tags' => 'tag', 
        	'v_quality' => 'quality', 'v_picpath' => 'thumbnail', 'v_mid_thumbnail' => 'mid_thumbnail', 
        	'v_big_thumbnail' => 'big_thumbnail', 'v_site' => 'domain', 'v_duration' => 'length', 
        	'v_player_url' => 'player_url', 'v_playurl' => 'play_url');
        foreach ($videos as $index => &$video) {
            $tmp = array();
            foreach ($video as $key => $value) {
                if (isset($map[$key])) {
                    $tmp[$map[$key]] = $value;
                } else {
                    $tmp[$key] = $value;
                }
            }
            if (isset($tmp['title'])) {
                $video = $tmp;
            } else {
                unset($videos[$index]);
            }
        }
       return array_values($videos);
    }
}