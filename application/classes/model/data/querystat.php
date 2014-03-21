<?php defined('SYSPATH') or die('No direct script access.');

class Model_Data_QueryStat extends Model_Data_MongoCollection 
{
	#类型
	//热搜词
	const CATEGORY_KEYWORDS = 1;
	//男明星
	const CATEGORY_STAR = 2;
	//女明星
	const CATEGORY_ACTRESS = 3;
	//电影
	const CATEGORY_MOVIE = 4;
	//电视剧
	const CATEGORY_TV = 5;
	//热点人物
	const CATEGORY_PEOPLE = 6;
	
	#时间段
	//最近一小时
	const RECENT_HOUR = 1;
	//最近一天
	const RECENT_DAY = 2;
	//最近一周
	const RECENT_WEEK = 3;
	//最近一月
	const RECENT_MONTH = 4;
	
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'query_stat_recent');
	}
	
	public function getRecentPeriod( $recent = NULL, $category=NULL ) {
		$query = array(
			'period' => array(
				'$gt' => 0
			)
		);
		
		if ($recent!==NULL) {
			$query['recent'] = $recent;
		} 
		
		if ( $category!==NULL ) {
			if( !isset($query['recent']) ) {
				$query['recent'] = array(
					'$gt' => 0
				);
			}
			$query['category'] = $category;
		}
		$periodTmp = $this->find($query, array("period", "_id"), array("period"=>-1), 1, 0);
		if( !$periodTmp ) {
			return false;
		}
		$periodTmp = array_values($periodTmp);
		return $periodTmp[0]['period'];
	}
	/**
	 * 
	 * 批量查询最近的期数, 查询条件为空取全部
	 * @param array $arrConds array(
	 * 	array(
	 * 		'recent' => 1,
	 * 		'category' => 1
	 * 	)
	 * )
	 * 
	 * @return array array(
	 * 	recent_category=>period
	 * ...
	 * )
	 */
	public function batchGetPeriodBySameRecentAndType( $arrConds ) {
		$arrReturn = array();
		$group = array(
			'recent' => 1,
			'category' => 1
		);
		$reduce = 'function(obj,prev) { if(prev.latest_period<obj.period){
			prev.latest_period=obj.period;
		}}';
		$initial = array(
			"latest_period" => -1
		);
		$query = array(
			'period' => array(
				'$gt' => 0
			)
		);
		$condition = array(
			'period' => array(
				'$gt' => 0
			),
			'recent' => array(
				'$in' => array()
			),
			'category' => array(
				'$in' => array()
			)
		);
		$arrTmpKeys = array();
		if( $arrConds ) {
			foreach($arrConds as $row) {
				$arrTmpKeys[] = $row['recent']."_".$row['category'];
				$condition['recent']['$in'][] = $row['recent'];
				$condition['category']['$in'][] = $row['category'];
			}
		}
		
		if( !$condition['recent']['$in'] ) {
			$condition = array();
		}
		$periodTmp = $this->getCollection()->group($group, $initial, $reduce, $condition);
		if( !$periodTmp || !$periodTmp['retval'] ) {
			return $arrReturn;
		}
		foreach($periodTmp['retval'] as $row) {
			$k = $row['recent']."_".$row['category'];
			if( $arrTmpKeys ) {
				if( isset($arrTmpKeys[$k]) ) {
					$arrReturn[$k] = $row['latest_period'];
				}
			} else {
				$arrReturn[$k] = $row['latest_period'];
			}
			
		}
		
		return $arrReturn;
	}
	
	/**
	 * 热搜词白名单读写
	 * @param array $value
	 * @return array|bool
	 */
	public function hotQueryWhitelist($value = null)
	{
	    /* @var $redis Database_Redis */
		$redis = Database::instance("web_redis_master");
		$db = $redis->getRedisDB();
		$key = 'hot.query:whitelist';
		if (is_null($value)) {
		    $value = $db->get($key);
		    if ($value) {
		        $value = json_decode($value, true);
		    } else {
		        $value = array();
		    }
		    return $value;
		} else {
		    $value = json_encode($value);
		    return $db->set($key, $value);
		}
	}
	
	/**
	 * 热搜词黑名单读写
	 * @param array $value
	 * @return array|bool
	 */
	public function hotQueryBlacklist($value = null)
	{
	    /* @var $redis Database_Redis */
		$redis = Database::instance("web_redis_master");
		$db = $redis->getRedisDB();
		$key = 'hot.query:blacklist';
		if (is_null($value)) {
		    $value = $db->get($key);
		    if ($value) {
		        $value = json_decode($value, true);
		    } else {
		        $value = array();
		    }
		    return $value;
		} else {
		    $value = json_encode($value);
		    return $db->set($key, $value);
		}
	}
}