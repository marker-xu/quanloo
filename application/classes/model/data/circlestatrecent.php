<?php 

/**
 * 圈子统计（最近一段时间）
 * @author wangjiajun
 */
class Model_Data_CircleStatRecent extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'circle_stat_recent', 
            true, 500);
	}
}