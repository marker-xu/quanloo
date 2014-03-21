<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 搜索相关页面逻辑
 * @author wangjiajun
 */
class Model_Logic_Search extends Model
{
    /**
     * 搜索视频
     * @param string $query 查询词
     * @param int $offset 起始位置
     * @param int $count 结果个数
     * @param string $sort 排序，relevance - 相关性，默认顺序，time - 上传时间
     * @param array $filters 过滤条件
     * 	quality - 清晰度，1 – 普通，2 – 清晰，3 – 标清，4 – 高清，5 – 超清
     *  length - 时长，0 – 1分钟以内，1 – 5分钟以内，2 – 5到10分钟，3 – 10到30分钟，4 – 30到60分钟，5 – 60到120分钟，6 – 120分钟以上
     *  category - 类别，1- 音乐，2 - 动漫，3 - 人物，4 - 教育，5 - 原创，6 - 资讯，7 - 电影，8 - 时尚，9 - 电视剧，10 - 专题，11 - 游戏，12 - 综艺，13 - 娱乐
     *  domain - 来源站点域名，比如youku.com
     *  tag - 标签
     *  source - 请求来源 firefox - 火狐一键应用
     * @param bool $debug 是否使用调试模式
     * @return array|bool
     * 	entity_type - 实体类型，movie - 电影，tv - 电视剧， animation - 动漫，star - 明星
     */
    public function search($query, $offset = 0, $count = 20, $sort = 'relevance', 
        $filters = array(), $debug = FALSE)
    {
        $filterMap = array('quality' => 'hd', 'length' => 'duration', 'category' => 'videotype', 
            'domain' => 'domain', 'tag' => 'label', 'source' => 'source');
        $tmp = array();
        foreach ($filters as $key => $value) {
            if (isset($filterMap[$key])) {
                $tmp[$filterMap[$key]] = $value;
            }
        }
        $filters = $tmp;
        $modelDataSearch = new Model_Data_Search();
        try {
            $result = $modelDataSearch->search($query, $offset, $count, $sort, 
                $filters, $debug);
        } catch (Model_Data_Exception $e) {
            Kohana::$log->error($e);
            return array(
                'query' => $query,
                'query_correct' => '',
                'real_total' => 0,
                'total' => 0,
                'entitys' => array(),
                'videos' => array(),
                'filters' => array(),
                'circle' => array(
                    'data' => array(),
                    'total' => 0
                )
            );
        }
        if ($result) {
            $result['entitys'] = $result['best_match_video'];
            unset($result['best_match_video']);
            
            $result['filters'] = array();
            $map = array_flip($filterMap);
            foreach ($result['filter'] as $key => $value) {
                if (isset($map[$key])) {
                    $result['filters'][$map[$key]] = $value;
                }
            }
            unset($result['filter']);
            
            if (isset($result['circle_result'])) {
                unset($result['circle_result']);
            }
        }
        
        return $result;
    }
    
    /**
     * 搜索圈子
     * @param string $query 查询词
     * @param int $offset 起始位置
     * @param int $count 结果个数
     * @return array
     */
    public function searchCircle($query, $offset = 0, $count = 16)
    {
        $modelDataSearch = new Model_Data_Search();
        try {
            $result = $modelDataSearch->searchCircle($query, $offset, $count);
        } catch (Model_Data_Exception $e) {
            Kohana::$log->error($e->getMessage());
            return array(
                'total' => 0,
                'circles' => array()
            );
        }
        
        $modelLogicCircle = new Model_Logic_Circle();
        $circles = $modelLogicCircle->getMulti($result['id_list'], true);
	    
        return array(
            'total' => $result['total'],
            'circles' => array_values($circles)
        );
    }
    
    /**
     * 相关搜索
     * @param string $query 查询词
     * @return array|bool
     */
    public function relationQuery($query)
    {
        $modelDataSearch = new Model_Data_Search();
        try {
            $result = $modelDataSearch->relationQuery($query);
        } catch (Exception $e) {
            Kohana::$log->warn($e);
            return array();
        }
        $tmp = array();
        if(!isset($result) || !$result['rs']) {
        	return $tmp;
        }
        foreach ($result['rs'] as $row) {
            $tmp[] = $row['name'];
        }
        $result = $tmp;
        return $result;
    }
}