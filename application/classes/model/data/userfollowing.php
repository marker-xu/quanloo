<?php 

/**
 * 用户关注关系
 * @author wangjiajun
 */
class Model_Data_UserFollowing extends Model_Data_MongoCollection
{
    const MAX_FOLLOWINGS_COUNT = 2000; // 关注用户数上限
    
	public function __construct()
	{
        parent::__construct('web_mongo', 'video_search', 'user_following');
	}
}