<?php 

/**
 * 用户动态
 * @author xucongbin
 */
class Model_Data_UserFeed extends Model_Data_MongoCollection
{
    const TYPE_WATCH_VIDEO = 1; // 观看视频
    const TYPE_LIKE_VIDEO = 2; // 推视频
    const TYPE_COMMENT_VIDEO = 3; // 评论视频
    const TYPE_SUBSCRIBE_CIRCLE = 4; // 关注圈子
    const TYPE_SHARE_VIDEO = 5; // 分享视频
    const TYPE_MOOD_VIDEO = 6; // 心情视频
    const TYPE_SHARE_CIRCLE = 7; // 分享圈子
    const TYPE_INVITE_CIRCLE = 8; // 邀请好友加入圈子
	
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'user_feed');
	}	
	
	/**
	 * 
	 * Enter description here ...
	 * @param int $uid
	 * @param int $type
	 * @param array $arrParams array(
	 *  circle_id => 圈子ID
	 *  video_id => 视频ID
	 * 	data => 
	 * )
	 */
	public function addFeed($uid, $type, $arrParams) {
		if( !isset($arrParams['create_time']) ) {
			$arrParams['create_time'] = new MongoDate();
		}
		$arrParams['user_id'] = intval($uid);
		$arrParams['type'] = $type;
		if(!isset($arrParams['data'])) {
			$arrParams['data'] = '';
		}
		
		try {
			$ret = $this->getCollection()->insert($arrParams);
		} catch (MongoException $e) {
			JKit::$log->warn("addFeed failure, code-".$e->getCode().", msg-".$e->getMessage().", id-".$id);
		}
		return $ret;
	}
	
	public function removeFeedById($id) {
		$condition = array(
			"_id" => new MongoId($id)
		);
		try {
			$ret = $this->getCollection()->remove($condition);
		} catch (MongoException $e) {
			JKit::$log->warn("removeFeed failure, code-".$e->getCode().", msg-".$e->getMessage().", id-".$id);
		}
		
		
		return $ret;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param int $uid
	 * @param array $arrTypeFilters
	 * @param int $offset
	 * @param int $length
	 * 
	 * return array(
	 * 	count => 记录条数
	 *  data => array(
	 *  数据记录1,...
	 *  )
	 * )
	 */
	public function getFeedList($uid, $arrTypeFilters=NULL, $offset=0, $length=NULL) {
		
		$arrReturn = array(
			'count' => 0,
			'data' => array()
		);
		$query = array("user_id"=>intval($uid));
		if ($arrTypeFilters!==NULL) {
			$query = array(
				'type' => array(
					'$in' => $arrTypeFilters
				)
			);
		}
		$arrReturn['count'] = $this->count($query);
		if( $arrReturn['count'] ) {
			$arrReturn['data'] =  $this->find($query, array(), array("create_time"=>-1), $length, $offset);
		}
		return $arrReturn;
	}
}