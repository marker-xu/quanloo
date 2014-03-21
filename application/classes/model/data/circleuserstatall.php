<?php 

/**
 * 圈内用户统计（全量）
 * @author wangjiajun
 */
class Model_Data_CircleUserStatAll extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'circle_user_stat_all');
	}
}