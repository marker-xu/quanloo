<?php 

/**
 * 视频统计（全量）
 * @author wangjiajun
 */
class Model_Data_VideoStatAll extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('video_stat_all', 'video_search', 'video_stat_all', 
            true, 500);
	}
}