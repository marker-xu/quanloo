<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 圈子视频关系逻辑
 * @author wangjiajun
 */
class Model_Logic_CircleVideo extends Model
{
	/**
	 * 添加视频
	 * @param int $circleId
	 * @param string $videoId
	 * @param array $doc 其它数据
	 * @param array $adder 添加者 array(_id => xx, nick => xx)
	 * @return bool
	 */
	public function add($circleId, $videoId, $doc = array(), $adder = null)
	{
	    if (isset($doc['tag'])) {
            $doc['tag'] = array_values(array_unique(array_filter($doc['tag'])));
	    }
	    if (isset($doc['note']) && mb_strlen($doc['note']) > 100) {
	        throw new Model_Logic_Exception('描述信息太长。');
	    }
        
	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->get($circleId);	    
	    if (!$circle) {
	        throw new Model_Logic_Exception('圈子不存在。');
	    }
	    $modelDataVideo = new Model_Data_Video();
	    $video = $modelDataVideo->get($videoId);	    
	    if (!$video) {
	        throw new Model_Logic_Exception('视频不存在。');
	    }
	    $modelDataCircleVideoByUser = new Model_Data_CircleVideoByUser();
	    if ($modelDataCircleVideoByUser->findOne(array(
            'circle_id' => (int) $circleId,
            'video_id' => $videoId,
	    ))) {
	        throw new Model_Logic_Exception('视频已在圈内。');
	    }
	    $modelDataCircleStatAll = new Model_Data_CircleStatAll();
	    $stat = $modelDataCircleStatAll->findOne(array(
            '_id' => (int) $circleId,
        ));
        if ($stat && isset($stat['video_count']) && $stat['video_count'] >= 1000) {
	        throw new Model_Logic_Exception('圈内视频数超过上限。');
        }
        $doc = array_merge(array(
            'circle_id' => (int) $circleId,
            'video_id' => $videoId,
            'title' => $video['title'],
            'tag' => $video['tag'],
            'user_id' => (int) $circle['creator'],
        	'create_time' => new MongoDate()
        ), $doc);
	    try {
	        $modelDataCircleVideoByUser->insert($doc);
	    } catch (Exception $e) {
	        Kohana::$log->error($e);
	        throw new Model_Logic_Exception('圈视频失败。');
	    }
	    $modelDataCircleStatAll->inc(array(
            '_id' => (int) $circleId,
        ), 'video_count');
	    $modelDataUserStatAll = new Model_Data_UserStatAll();
	    $modelDataUserStatAll->inc(array(
            '_id' => (int) $circle['creator'],
        ), 'circled_video_count');
        
        // 发送圈子预览图更新消息
        $model = new Model_Data_WebRedis();
        $model->sendCirclePreviewUpdateMsg($circleId);
        
        if ($adder !== null) {
        	$objFeed2 = new Model_Logic_Feed2();
       		$objFeed2->addFeedQuanVideo($adder['_id'], $videoId, $circleId);
        }
        
	    return true;
	}
	
	/**
	 * 修改圈内视频
	 * @param int $circleId
	 * @param string $videoId
	 * @param array $doc 其它数据
	 * @return bool
	 */
	public function modify($circleId, $videoId, $doc = array())
	{
	    unset($doc['_id']);
	    if (isset($doc['circle_id']) && $doc['circle_id'] == $circleId) {
	        unset($doc['circle_id']);
	    }
	    unset($doc['video_id']);
	    unset($doc['user_id']);
	    if (isset($doc['tag'])) {
            $doc['tag'] = array_values(array_unique(array_filter($doc['tag'])));
	    }
	    if (isset($doc['note']) && mb_strlen($doc['note']) > 100) {
	        throw new Model_Logic_Exception('描述信息太长。');
	    }
	    
	    $old = $this->get($circleId, $videoId);
	    if (!$old) {
	        throw new Model_Logic_Exception('圈内视频不存在。');
	    }
	    if (isset($doc['circle_id'])) {
	        $this->remove($circleId, $videoId);
	        unset($old['_id']);
	        unset($old['circle_id']);
	        unset($old['video_id']);
	        unset($old['create_time']);
	        $doc = array_merge($old, $doc);
	        $this->add($doc['circle_id'], $videoId, $doc);
	    } else {
	        $modelDataCircleVideoByUser = new Model_Data_CircleVideoByUser();
    	    try {
    	        $modelDataCircleVideoByUser->update(array(
                    'circle_id' => (int) $circleId,
                    'video_id' => $videoId,
                ), $doc);
    	    } catch (Exception $e) {
    	        Kohana::$log->error($e);
    	        throw new Model_Logic_Exception('编辑圈内视频失败。');
    	    }
	    }
        
        // 发送圈子预览图更新消息
        $model = new Model_Data_WebRedis();
        $model->sendCirclePreviewUpdateMsg($circleId);
	    if (isset($doc['circle_id'])) {
            $model->sendCirclePreviewUpdateMsg($doc['circle_id']);
	    }
        
	    return true;
	}
	
	/**
	 * 移除视频
	 * @param int $circleId
	 * @param string $videoId
	 * @return bool
	 */
	public function remove($circleId, $videoId)
	{
	    $modelDataCircleVideoByUser = new Model_Data_CircleVideoByUser();
	    $doc = $modelDataCircleVideoByUser->findOne(array(
            'circle_id' => (int) $circleId,
            'video_id' => $videoId,
        ));
        if (!$doc) {
            return true;
        }
	    try {
	        $result = $modelDataCircleVideoByUser->delete(array(
	            'circle_id' => (int) $circleId,
	            'video_id' => $videoId,
	        ));
	    } catch (Exception $e) {
	        Kohana::$log->error($e);
	        throw new Model_Logic_Exception('移除视频失败。');
	    }
	    $modelDataCircleStatAll = new Model_Data_CircleStatAll();
	    $modelDataCircleStatAll->inc(array(
            '_id' => (int) $circleId,
        ), 'video_count', -1);
	    $modelDataUserStatAll = new Model_Data_UserStatAll();
	    $modelDataUserStatAll->inc(array(
            '_id' => (int) $doc['user_id'],
        ), 'circled_video_count', -1);
        
        // 发送圈子预览图更新消息
        $model = new Model_Data_WebRedis();
        $model->sendCirclePreviewUpdateMsg($circleId);
	    
	    return true;
	}
	
	/**
	 * 获取某个圈内视频
	 * @param int $circleId
	 * @param string $videoId
	 * @return array
	 */
	public function get($circleId, $videoId)
	{
	    $modelDataCircleVideoByUser = new Model_Data_CircleVideoByUser();
	    $doc = $modelDataCircleVideoByUser->findOne(array(
            'circle_id' => (int) $circleId,
            'video_id' => $videoId
	    ));
	    if ($doc) {
	        $modelCircle = new Model_Logic_Circle();
	        $doc['circle'] = $modelCircle->get($circleId);
	        $modelVideo = new Model_Logic_Video();
	        $doc['video'] = $modelVideo->get($videoId);
	    }
	    return $doc;
	}
	
	/**
	 * 圈过的视频（按圈）
	 * @param int $circleId
	 * @param int $offset
	 * @param int $count
	 * @param int $total
	 * @return array
	 */
	public function circledVideosByCircle($circleId, $offset = 0, $count = 20, 
	    &$total = null)
	{
	    $modelDataCircleVideoByUser = new Model_Data_CircleVideoByUser();
        $docs = $modelDataCircleVideoByUser->find(array(
            'circle_id' => (int) $circleId,
        ), array(), array('create_time' => -1), $count, $offset);
        $modelLogicVideo = new Model_Logic_Video();
        $videoIds = Arr::pluck($docs, 'video_id');
        $videos = $modelLogicVideo->getMulti($videoIds);
        foreach ($docs as &$doc) {
            $doc['video'] = isset($videos[$doc['video_id']]) ? $videos[$doc['video_id']] : null;
        }
        if (!is_null($total)) {
    	    $modelDataCircleStatAll = new Model_Data_CircleStatAll();
    	    $stat = $modelDataCircleStatAll->findOne(array('_id' => (int) $circleId));
    	    if ($stat && isset($stat['video_count'])) {
    	        $total = $stat['video_count'];
    	    } else {
    	        $total = 0;
    	    }
        }
        return $docs;
	}
	
	/**
	 * 圈过的视频（按用户）
	 * @param int $userId
	 * @param int $offset
	 * @param int $count
	 * @param int $total
	 * @return array
	 */
	public function circledVideosByUser($userId, $offset = 0, $count = 20, 
	    &$total = null)
	{
	    $modelDataCircleVideoByUser = new Model_Data_CircleVideoByUser();
        $docs = $modelDataCircleVideoByUser->find(array(
            'user_id' => (int) $userId,
        ), array(), array('create_time' => -1), $count, $offset);
        
        $modelLogicVideo = new Model_Logic_Video();
        $videoIds = Arr::pluck($docs, 'video_id');
        $videos = $modelLogicVideo->getMulti($videoIds);
        $objComment = new Model_Logic_Comment();
		$arrComment = $objComment->topN($videoIds, 3, array('avatarSize' => 30));
        foreach ($docs as &$doc) {
            $doc['video'] = isset($videos[$doc['video_id']]) ? $videos[$doc['video_id']] : null;
        	if (isset($arrComment[$doc['video_id']])) {
				$doc['video']['comments'] = $arrComment[$doc['video_id']];
			} else {
				$doc['video']['comments'] = array();
			}
        }
        unset($doc);
        
        $modelLogicCircle = new Model_Logic_Circle();
        $circleIds = Arr::pluck($docs, 'circle_id');
        $circles = $modelLogicCircle->getMulti($circleIds);
        foreach ($docs as &$doc) {
            $doc['circle'] = isset($circles[$doc['circle_id']]) ? $circles[$doc['circle_id']] : null;
        }
        unset($doc);
        
        if (!is_null($total)) {
    	    $modelDataUserStatAll = new Model_Data_UserStatAll();
    	    $stat = $modelDataUserStatAll->findOne(array('_id' => (int) $userId));
    	    if ($stat && isset($stat['circled_video_count'])) {
    	        $total = $stat['circled_video_count'];
    	    } else {
    	        $total = 0;
    	    }
        }
        
        return $docs;
	}
}