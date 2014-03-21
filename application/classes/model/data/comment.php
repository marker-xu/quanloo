<?php
/**
 * 视频评论
 */
class Model_Data_Comment extends Model_Data_MongoCollection
{
    protected $_strCountCacheKey = 'video_comment_count:%s';
    protected $_arrBasicField = array('user_id', 'data', 'mood', 'create_time', 'nick', 'site', 'users');
    protected static $_arrLastAddComment = null; //最后一次通过self::add添加的评论
    
    public function __construct() {
         parent::__construct('web_mongo', 'video_search', 'video_comment');
    }

    /**
     * 添加视频评论
     * 
     * @param array $arrParams array(
     *  user_id => 用户ID，站外评论的用户ID为0
     *  video_id => 视频ID
     * 	data => 评论内容
     *  mood => 心情
     *  score => 评论质量分数，越高表示评论越好
     *  create_time => 创建时间，不填默认当前时间，支持传入时间戳
     *  users => 评论内容中的@用户的列表，id为key，昵称为value
     *  //站外评论的特有字段
     *  nick => 站外评论的昵称
     *  site => 站外评论的来源站点
     * )
     */
    public function add($arrParams) {
        $arrParams['user_id'] = (int) $arrParams['user_id'];
        if(! isset($arrParams['create_time'])) {
            $arrParams['create_time'] = new MongoDate();
        } elseif (! $arrParams['create_time'] instanceof MongoDate) {
            $arrParams['create_time'] = new MongoDate($arrParams['create_time']);
        }
        if (! isset($arrParams['mood'])) {
            $arrParams['mood'] = '';
        }
        
        $ret = false;
        try {
            $ret = $this->getCollection()->insert($arrParams);
/*             $cache = Cache::instance('web');
            $key = sprintf($this->_strCountCacheKey, $arrParams['video_id']);
            $cache->increment($key, 1); */
            JKit::$log->debug('add comment', $arrParams, __FILE__, __LINE__);
            self::$_arrLastAddComment = $arrParams;            
        } catch (MongoException $e) {
            JKit::$log->warn("add comment fail, code-".$e->getCode().", msg-".$e->getMessage());
        }
        return $ret;
    }
    
    public function remove($id) {
        $condition = array(
                "_id" => new MongoId($id)
        );
        $ret = false;
        try {
            $ret = $this->getCollection()->remove($condition);
            
/*             $cache = Cache::instance('web');
            $key = sprintf($this->_strCountCacheKey, $id);
            $cache->decrement($key, 1);  */          
        } catch (MongoException $e) {
            JKit::$log->warn("remove comment fail, code-".$e->getCode().", msg-".$e->getMessage().", id-".$id);
        }   
        return $ret;
    }
    /**
     * 获取每个vid的前N个评论
     * 
     * @param array $arrVid vid数组
     * @param int $intCount
     *
     * return array(
     *     vid => array(array(每条评论信息), array(), ...)
     * )
     */
    public function topN(array $arrVid, $intCount = NULL, &$arrUserId = array()) {    
        $arrReturn = array();
        foreach ($arrVid as $v) {
            $query = array('video_id' => $v, 'score' => array('$gt' => 0));
            $arrTmp = (array) $this->find($query, $this->_arrBasicField, array('score' => -1, 'create_time' => -1), $intCount);
            foreach ($arrTmp as $v2) {
                if ($v2['user_id'] > 0) {
                    $arrUserId[] = $v2['user_id'];
                }
            }
            $arrReturn[$v] = $arrTmp;
        }
        
        return $arrReturn;
    }

    /**
     * 获取一个视频的评论
     * 
     * @param string $strVid
     * @param int $intCount
     * @param int $intOffset
     * @return array(count => 评论总数, data => array(array(每条评论信息), ...))
     */
    public function get($strVid, $intCount = NULL, $intOffset = NULL) {    
        $arrReturn = array(
            'total' => 0,
            'data' => array(),
        );
        $query = array("video_id" => $strVid);
        
/*         $cache = Cache::instance('web');
        $key = sprintf($this->_strCountCacheKey, $strVid);
        $intTotal = $cache->get($key);
        if ($intTotal === NULL || $intTotal < 0) {
            $intTotal = (int) $this->count($query);
            $cache->set($key, $intTotal, 300);
        }
        $arrReturn['total'] = (int) $intTotal; */
        $arrReturn['total'] = (int) $this->count($query);
        
        if ($arrReturn['total']) {
            $arrReturn['data'] = (array) $this->find($query, $this->_arrBasicField, array('create_time' => -1), $intCount, $intOffset);
        }
        
        return $arrReturn;
    }
    
    public static function getLastAddComment() {
        return self::$_arrLastAddComment;
    }
}