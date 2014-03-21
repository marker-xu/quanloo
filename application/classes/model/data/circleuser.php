<?php 

/**
 * 圈子用户关系
 * @author wangjiajun
 */
class Model_Data_CircleUser extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('circle', 'video_search', 'circle_user');
	}
	
	/**
	 * 关注圈子
	 * @param int $userId
	 * @param int $circleId
	 * @return bool
	 */
	public function subscribe($userId, $circleId)
	{	
	    $docs = $this->find(array(
	    	'user_id' => $userId
	    ), array('order'), array('order' => -1), 1);
	    if ($docs) {
	        $doc = current($docs);
	        $order = isset($doc['order']) ? $doc['order'] : 0;
	    } else {
	        $order = 0;
	    }
	    $order += 1;
	    $result = $this->insert(array(
	    	'circle_id' => (int) $circleId, 
	    	'user_id' => (int) $userId,
            'order' => $order,
            'create_time' => new MongoDate()
	    ), array('safe' => TRUE));
	    return true;
	}
	
	/**
	 * 设置关注圈子的显示顺序
	 * @param int $userId
	 * @param array $circleIds 关注圈子ID列表，按显示顺序排序
	 * @return bool
	 */
	public function setSubscribedCircleOrder($userId, $circleIds)
	{
	    foreach ($circleIds as $index => $circleId) {
	        $this->update(array(
    	    	'circle_id' => (int) $circleId, 
    	    	'user_id' => (int) $userId,
	        ), array('order' => $index + 1));
	    }
	    return true;
	}
	
	/**
	 * 圈内用户数
	 * @param int $circleId
	 * @return int
	 */
	public function circleUserCount($circleId)
	{
	    return $this->count(array('circle_id' => $circleId));
	}
	
	/**
	 * 关注圈子数
	 * @param int $userId
	 * @return int
	 */
	public function subscribedCircleCount($userId)
	{
	    return $this->count(array('user_id' => $userId));
	}
	
	/**
	 * 某个用户是否关注过某个圈子
	 * @param int $userId
	 * @param int $circleId
	 * @return bool
	 */
	public function isUserSubscribeCircle($userId, $circleId)
	{
	    return (bool) $this->count(array('circle_id' => $circleId, 'user_id' => $userId));
	}
}