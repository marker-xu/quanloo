<?php 

/**
 * 圈内视频统计（最近一段时间）
 * @author wangjiajun
 */
class Model_Data_CircleVideoStatRecent extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'circle_video_stat_recent');
	}
}