<?php 

/**
 * 圈内用户统计（最近一段时间）
 * @author wangjiajun
 */
class Model_Data_CircleUserStatRecent extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'circle_user_stat_recent');
	}
}