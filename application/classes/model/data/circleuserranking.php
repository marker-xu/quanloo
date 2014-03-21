<?php 

/**
 * 圈友排行榜
 * @author wangjiajun
 */
class Model_Data_CircleUserRanking extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'circleUserRanking');
	}
}