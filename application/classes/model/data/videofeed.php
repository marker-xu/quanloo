<?php 

/**
 * 视频动态
 * @author wangjiajun
 */
class Model_Data_VideoFeed extends Model_Data_MongoCollection
{
    const TYPE_WATCHED = 1; // 被观看
    const TYPE_LIKED = 2; // 被推
    const TYPE_COMMENTED = 3; // 被评论
    const TYPE_SHARED = 4; // 被分享
    const TYPE_MOODED = 5; // 被心情
    
    const MOOD_TYPE_XIHUAN = 'xh'; // 喜欢
    const MOOD_TYPE_WEIGUAN = 'wg'; // 围观
    const MOOD_TYPE_DAXIAO = 'dx'; // 大笑
    const MOOD_TYPE_FENNU = 'fn'; // 愤怒
    const MOOD_TYPE_JIONG = 'jn'; // 囧
    
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'video_feed');
	}
}