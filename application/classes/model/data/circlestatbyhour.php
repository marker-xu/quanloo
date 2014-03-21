<?php 

/**
 * 圈子统计（按小时）
 * @author wangjiajun
 */
class Model_Data_CircleStatByHour extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'circle_stat_by_hour', 
            true, 500);
	}
}