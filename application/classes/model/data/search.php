<?php

require_once Kohana::find_file('vendor/thrift', 'Thrift');
require_once Kohana::find_file('classes/model/thrift/search', 'SearchService');
require_once Kohana::find_file('classes/model/thrift/search', 'RelationQuery');

/**
 * 搜索
 * @author wangjiajun
 */
class Model_Data_Search extends Model_Data_Thrift
{
    
    /**
     * 搜索视频
     * @param string $query 查询词
     * @param int $offset 起始位置
     * @param int $count 结果个数
     * @param string $sort 排序，relevance - 相关性，默认顺序，time - 上传时间
     * @param array $filters 过滤条件
     * 	 hd - 清晰度，1 – 普通，2 – 清晰，3 – 标清，4 – 高清，5 – 超清
     *   duration - 时长，0 – 1分钟以内，1 – 5分钟以内，2 – 5到10分钟，3 – 10到30分钟，4 – 30到60分钟，5 – 60到120分钟，6 – 120分钟以上
     *   videotype - 类别，1- 音乐，2 - 动漫，3 - 人物，4 - 教育，5 - 原创，6 - 资讯，7 - 电影，8 - 时尚，9 - 电视剧，10 - 专题，11 - 游戏，12 - 综艺，13 - 娱乐
     *   domain - 来源站点域名，比如youku.com
     *   label - 标签
     * @param bool $debug 是否使用调试模式
     * @return array|bool
     */
    public function search($query, $offset = 0, $count = 20, $sort = 'relevance', 
        $filters = array(), $debug = FALSE)
    {
        $params = array($query, $sort, $filters, $offset, $count, $debug);
        Kohana::$log->debug(__FUNCTION__, $params);
		Profiler::startMethodExec();
        $result = RPC::call('search', array('SearchServiceClient', 'search'), $params);
	    Profiler::endMethodExec(__FUNCTION__." search $query");
        Kohana::$log->debug($result);
        if (!$result) {
            throw new Model_Data_Exception("search failed: query-$query");
        }
        $result = json_decode($result, TRUE);
        if ($result) {
            $result['best_match_video'] = Model_Data_Entity::fieldsTransform($result['best_match_video']);            
            $result['videos'] = Model_Data_Video::fieldsTransform($result['videos']);
        }
        return $result;
    }
    
    /**
     * 搜索圈子
     * @param string $query 查询词
     * @param int $offset 起始位置
     * @param int $count 结果个数
     * @return array|bool
     */
    public function searchCircle($query, $offset = 0, $count = 12)
    {        
	    $params = array(
	        'query' => $query,
	        'offset' => $offset,
	        'size' => $count
	    );
        Kohana::$log->debug(__FUNCTION__, $params);
		Profiler::startMethodExec();
        $result = RPC::call('search_circle', '/circle_search/search.action?'.http_build_query($params));
	    Profiler::endMethodExec(__FUNCTION__." search_circle $query");
        Kohana::$log->debug(__FUNCTION__, $result);
        $result = json_decode($result, TRUE);
        return $result;
    }
    
    /**
     * 相关搜索
     * @param string $query 查询词
     * @return array|bool
     */
    public function relationQuery($query)
    {
        $params = array($query);
        Kohana::$log->debug(__FUNCTION__, $params);
		Profiler::startMethodExec();
        $result = RPC::call('relation_query', array('RelationQueryClient', 'getRelationQuery'), 
            $params);
	    Profiler::endMethodExec(__FUNCTION__." relation_query $query");
        Kohana::$log->debug(__FUNCTION__, $result);
        if (!$result) {
            throw new Model_Data_Exception("relation query failed: query-$query");
        }
        $result = $result->result;
        $result = json_decode($result, true);
        Kohana::$log->debug(__FUNCTION__, $result);
        return $result;
    }
    
    /**
     * 过滤Tag
     * @param string $query 查询词
     * @param int $offset
     * @param int $count
     * @return array
     */
    public function filterTags($query, $offset = 0, $count = 10)
    {
	    $cache = Cache::instance('web');
	    $db = $cache->getRedisDB(1, $query);
	    $value = $db->lRange($query, $offset, $offset + $count - 1);
        Kohana::$log->debug(__FUNCTION__, $value);
        return $value;
    }
}