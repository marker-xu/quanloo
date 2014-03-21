<?php 

/**
 * 圈子动态
 * @author wangjiajun
 */
class Model_Data_CircleFeed extends Model_Data_MongoCollection
{
    const TYPE_VIDEO_WATCHED = 1; // 圈内视频被观看
    const TYPE_VIDEO_LIKED = 2; // 圈内视频被推
    const TYPE_VIDEO_COMMENTED = 3; // 圈内视频被评论
    const TYPE_SUBSCRIBED = 4; // 被关注
    const TYPE_VIDEO_SHARED = 5; // 圈内视频被分享
    const TYPE_VIDEO_MOODED = 6; // 圈内视频被心情
    const TYPE_VIDEO_REPORTED = 7; // 圈内视频被举报
    const TYPE_SHARED = 8; // 被分享
    const TYPE_INVITED = 9; // 被邀请好友加入
    
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'circle_feed');
	}
}