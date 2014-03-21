<?php
class Controller_Mobile_Circle extends Mobile
{
	/**
	 * 圈子浏览页
     * 
     * @param $_GET = array(
     *     cat: 圈子分类名
     *     offset: 分页参数，当前页偏移量，第一页为0
     *     count: 分页参数，每页多少条记录
     *     format: 返回结果格式，目前只支持json
     *     tag: 圈子标签
     * )
     * @return array(total => 圈子总数, data => 圈子数组)
	 */
	public function action_browse()
	{	 
		$strCat = trim($this->request->param('cat', 'all'));
		$arrCatToIdMap = array_flip(Model_Data_Circle::$arrUrlKeyForCategorys);
		if (isset($arrCatToIdMap[$strCat])) {
			$intCat = (int) $arrCatToIdMap[$strCat];
		} else {
			$intCat = 0;
			$strCat = 'all';
		}
		//$intRank = $this->request->param('r') == Model_Logic_Circle::RANK_HOT ? Model_Logic_Circle::RANK_HOT : Model_Logic_Circle::RANK_NEW;
		$strTag = trim($this->request->param('tag', ''));
		$intRank = Model_Logic_Circle::RANK_HOT;
		$intOffset = (int) $this->request->param('offset', 0);
		$intCount = (int) $this->request->param('count', 2);
		if (! isset(Model_Data_Circle::$categorys[$intCat])) {
			$intCat = 0; //全部分类
			$strCurCatName = '';
		} else {
			$strCurCatName = Model_Data_Circle::$categorys[$intCat];
		}
		if ($intOffset < 0) {
			$intOffset = 0;
		}
		 
		$modelLogicCircle = new Model_Logic_Circle();
		$circles = $modelLogicCircle->groupByCategory($intCat, $intRank, $intCount, $intOffset, (int) $this->_uid, $strTag);
		foreach ($circles['data'] as &$v) {
			if (isset($v['tn_path'])) {
				$v['tn_path'] = Util::circlePreviewPic($v['tn_path']);
			}
		}
		unset($v);
		#补上移动端缩略图
		$modelLogicCircle->completeMobilePicPath($circles['data']);
		$this->ok($circles);
	}

    /**
     * 获取圈内视频列表
     * 
     * @param $_GET = array(
     *     id: 圈子id
     *     offset: 分页参数，当前页偏移量，第一页为0
     *     count: 分页参数，每页多少条记录
     *     r: 视频排序方式，参考Model_Logic_Video::VIDEO_RANK_*系列常量，默认值为Model_Logic_Video::VIDEO_RANK_DEFAULT
     *     format: 返回结果格式，目前只支持json
     *     tag: 视频标签
     *     title: 圈子标题，可以不传
     * )
     * @return array(total => 视频总数, data => 视频数组, 'is_subscribed' => 是否关注过)
     */
	public function action_videos()
	{
		$circleId = (int) $this->request->param('id');
		if ($circleId <= 0) {
			$this->err(null, 'invalid id');
		}
		$offset = (int) $this->request->param('offset', 0);
		$count = (int) $this->request->param('count', 10);
		$intRank = (int) $this->request->param('r', Model_Logic_Video::VIDEO_RANK_DEFAULT);
		$strTag = trim($this->request->param('tag', ''));
		$strTitle = trim($this->request->param('title', null));
		if ($offset < 0) {
			$offset = 0;
		}
		if ($count < 1) {
			$count = 10;
		}
		if (! isset(Model_Logic_Video::$arrVideoRankField[$intRank])) {
			$intRank = Model_Logic_Video::VIDEO_RANK_DEFAULT;
		}
		 
		$modelLogicRecommend = new Model_Logic_Recommend();
		$arrRet = $modelLogicRecommend->getCircleVideosByTag($circleId, $strTitle, $strTag, $offset, $count, $intRank, Model_Logic_Video::$basicFieldsForMobile);
		$arrVideo = $arrRet['video'];
		foreach($arrVideo as &$v) {
			$v['circle'] = array('_id' => $circleId);
			$v['thumbnail'] = Util::videoThumbnailUrl($v['thumbnail']);
		}
		//是否关注过这个圈子
		$objModelCircleUser = new Model_Data_CircleUser();
		$query = array(
    		"circle_id" => $circleId,
    		"user_id" => $this->_uid
    	);
		$isSubscribed = 0;
		if( $objModelCircleUser->findOne($query) ) {
			$isSubscribed = 1;
		}
		$objLogicUtil = new Model_Logic_Mobile_Util();
		$objLogicUtil->appendVideoMp4Playurl($arrVideo);
		$this->ok(array('total' => (int) $arrRet['total'], 'data' => $arrVideo, 'is_subscribed'=>$isSubscribed));
	}	
	
	/**
	 * 关注圈子
	 */
	public function action_subscribe()
	{
	    $this->_needLogin();
	    
	    $circleId = (int) $this->request->param('id');
	    if ($circleId <= 0) {
	        $this->err(null, 'invalid id');
	    }
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    try {
	        $modelLogicCircle->subscribe($circleId, $this->_uid);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    
	    $this->ok();
	}
	
	/**
	 * 取消关注圈子
	 */
	public function action_unsubscribe()
	{
	    $this->_needLogin();
	    
	    $circleId = (int) $this->request->param('id');
	    if ($circleId <= 0) {
	        $this->err(null, 'invalid id');
	    }
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    try {
	        $modelLogicCircle->unsubscribe($circleId, $this->_uid);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    
	    $this->ok();
	}
}