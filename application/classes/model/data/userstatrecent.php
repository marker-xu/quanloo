<?php 

/**
 * 用户统计（最近一段时间）
 * @author xucongbin
 */
class Model_Data_UserStatRecent extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'user_stat_recent', 
            true, 500);
	}
	
	
}