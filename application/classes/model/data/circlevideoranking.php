<?php 

/**
 * 圈内视频排行榜
 * @author wangjiajun
 */
class Model_Data_CircleVideoRanking extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'circleVideoRanking');
	}
}