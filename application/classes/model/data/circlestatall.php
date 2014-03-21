<?php 

/**
 * 圈子统计（全量）
 * @author wangjiajun
 */
class Model_Data_CircleStatAll extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'circle_stat_all', 
            true, 500);
	}
	
	public function getPopularCircleIds($offset=0, $count=10) {
		$arrReturn = array();
		$sort = array('popularity' => -1);
		$fields = array("_id");
		$query = array();
		$arrCircles = $this->find($query, $fields, $sort, $count, $offset);
		
		if( $arrCircles ) {
			$arrReturn = array_keys($arrCircles);
		}
		
		return $arrReturn;
	}
}