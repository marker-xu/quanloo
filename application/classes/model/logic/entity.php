<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 实体（长视频、明显）相关业务逻辑
 * @author wangjiajun
 */
class Model_Logic_Entity extends Model
{
    /**
     * 热门圈子
     * @param string $type 类型，movie - 电影，tv - 电视剧
     * @param int $count
     * @param int $userId 当前登录用户ID，未登录用户为null
     */
    public function hotCircles($type, $count = 10, $userId = null)
    {
	    $modelEntity = new Model_Data_Entity();
	    if ($type == 'movie') {
	        $type = 1;
	    } elseif ($type == 'tv') {
	        $type = 2;
	    }
	    $circleIds = $modelEntity->getHotCircles((is_null($userId) ? 0 : $userId), 
	        $type, $count);
	    $modelCircle = new Model_Logic_Circle();
	    $circles = $modelCircle->getMulti($circleIds, true);
	    if (!is_null($userId)) {
	        $modelUser = new Model_Logic_User();
	        $modelUser->complementUserCircleRel($circles, $userId);
	    }
	    return $circles;
    }
    
    /**
     * 实体相关圈子，实体包括电影、电视剧等
     * @param string $id 实体ID
     * @param int $userId 当前登录用户ID，未登录用户为null
     */
    public function relatedCircles($id, $userId = null)
    {
	    $modelEntity = new Model_Data_Entity();
	    $circleIds = $modelEntity->getEntityRelatedCircles($id, (is_null($userId) ? 0 : $userId));
	    $modelCircle = new Model_Logic_Circle();
	    $circles = $modelCircle->getMulti($circleIds, true);
	    if (!is_null($userId)) {
	        $modelUser = new Model_Logic_User();
	        $modelUser->complementUserCircleRel($circles, $userId);
	    }
	    return $circles;
    }
    
    /**
     * 明星相关圈子
     * @param string $name 明星
     * @param int $count
     * @param int $userId 当前登录用户ID，未登录用户为null
     */
    public function starRelatedCircles($name, $count = 10, $userId = null)
    {
	    $modelEntity = new Model_Data_Entity();
	    $circleIds = $modelEntity->getActorRelatedCircles($name, (is_null($userId) ? 0 : $userId));
	    $modelCircle = new Model_Logic_Circle();
	    $circles = $modelCircle->getMulti($circleIds, true);
	    if (!is_null($userId)) {
	        $modelUser = new Model_Logic_User();
	        $modelUser->complementUserCircleRel($circles, $userId);
	    }
	    return $circles;
    }
    
    /**
     * 实体相关实体，实体包括电影、电视剧等
     * @param string $id 实体ID
     * @return array
     */
    public function relatedEntitys($id)
    {
	    $modelEntity = new Model_Data_Entity();
	    $entityIds = $modelEntity->getEntityRelatedEntity($id);
	    $entitys = $modelEntity->getMulti($entityIds);
	    return $entitys;
    }
    
    /**
     * 实体相关视频，实体包括电影、电视剧等
     * @param string $id 实体ID
     * @return array
     */
    public function relatedVideos($id)
    {
	    $modelEntity = new Model_Data_Entity();
	    $videoIds = $modelEntity->getEntityRelatedVideos($id);
	    $modelVideo = new Model_Logic_Video();
	    $videos = $modelVideo->getMulti($videoIds, true, null, false);
	    return $videos;
    }
}