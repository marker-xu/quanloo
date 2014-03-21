<?php 

/**
 * 圈子视频关系（用户手动添加）
 * @author wangjiajun
 */
class Model_Data_CircleVideoByUser extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('circle_video', 'video_search', 'circle_video_by_user', 
            false);
	}
}