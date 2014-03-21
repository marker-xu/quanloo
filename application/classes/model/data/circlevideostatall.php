<?php 

/**
 * 圈内视频统计（全量）
 * @author wangjiajun
 */
class Model_Data_CircleVideoStatAll extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'circle_video_stat_all');
	}
}