<?php
class Controller_Mobile_Video extends Mobile
{
    /**
     * 发表视频评论
     * 
     * @param $_POST = array(
     *     id: 视频id
     *     circle: 视频所属的圈子ID，没有的话不传
     *     content: 评论内容
     * )
     * @return array 新增评论的信息数组
     */
	public function action_comment()
	{
		$this->_needLogin();
		 
		$arrRules = $this->_commentRule();
		$arrPost = $this->request->post();
		$arrPost['content'] = trim($arrPost['content']);
		if (Model_Logic_Blackword::instance()->filter($arrPost['content'])) {
			$this->err(null, '文明发贴，人人有责');
		}
		$objValidation = new Validation($arrPost, $arrRules);
		if (! $this->valid($objValidation)) {
			return;
		}
		 
		$videoId = (string) $arrPost['id'];
		if (strlen($videoId) != 32) {
			$this->err(null, 'invalid id');
		}
		 
		$circleId = @$arrPost['circle'];
		if (! empty($circleId)) {
			$circleId = (int) $circleId;
			if ($circleId <= 0) {
				$this->err(null, 'invalid circle');
			}
		}
	
		$modelLogicVideo = new Model_Logic_Video();
		try {
			$modelLogicVideo->comment($videoId, $this->_user, $arrPost['content'], $circleId);
		} catch (Model_Exception $e) {
			$this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
		}
	
		$arrCommentInfo = Model_Data_Comment::getLastAddComment();
		$strOwnAvatar = isset($this->_user['avatar'][160]) ? $this->_user['avatar'][160] : '';
		$arrComment = array('data' => array(array('create_time_str' => '此刻', 'avatar' => @Util::userAvatarUrl($strOwnAvatar, 160),
				'nick' => $this->_user['nick']) + $arrCommentInfo));
		$this->ok($arrComment);
	}
	
	/**
	 * 以后观看
	 * 
	 * @param array $_GET = array(
	 *  vid => 视频ID
	 * )
	 * 
	 * @return 
	 */
	public function action_watchLater()
	{
	    $this->_needLogin();
	    
	    $videoId = (string) $this->request->param('vid');
	    if (strlen($videoId) != 32) {
	        $this->err(null, 'invalid vid');
	    }
	    
	    $modelLogicVideo = new Model_Logic_Video();
	    try {
    	    $modelLogicVideo->watchLater($videoId, $this->_uid);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    
	    $this->ok();
	}
	
	/**
	 * 删除以后观看
	 * 
	 * @param array $_GET = array(
	 * 	vid => 视频ID
	 * )
	 */
	public function action_deletewatchlater()
	{
	    $this->_needLogin();
	    
	    $videoId = (string) $this->request->param('vid');
	    if (strlen($videoId) != 32) {
	        $this->err(null, 'invalid vid');
	    }
	    
	    $modelLogicVideo = new Model_Logic_Video();
	    try {
    	    $modelLogicVideo->deleteWatchLater($videoId, $this->_uid);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    
	    $this->ok();
	}
	/**
	 * 
	 * 判断是否添加到以后观看
	 * 
	 * @param array $_GET = array(
	 * 	vid => 视频ID
	 * )
	 */
	public function action_iswatchlater() {
		$this->_needLogin();
	    
	    $videoId = (string) $this->request->param('vid');
	    if (strlen($videoId) != 32) {
	        $this->err(null, 'invalid vid');
	    }
	    
	    $modelLogicVideo = new Model_Data_UserVideo($this->_uid);
    	$res = $modelLogicVideo->zScore(Model_Data_UserVideo::TYPE_WATCHLATER, $videoId);
	    if(!$res) {
	    	$this->err();
	    }
	    $this->ok();
	}
    /**
     * 获取视频评论列表
     * 
     * @param $_GET = array(
     *     id: 视频id
     *     offset: 分页参数，当前页偏移量，第一页为0
     *     count: 分页参数，每页多少条记录
     * )
     * @return array array(total => 评论总数, count => 当前返回记录数, data => 评论列表)
     */
	public function action_comments()
	{
		$videoId = (string) $this->request->param('id');
		if (strlen($videoId) != 32) {
			$this->err(null, 'invalid id');
		}
		$offset = (int) $this->request->param('offset', 0);
		$count = (int) $this->request->param('count', 3);
		 
		$objComment = new Model_Logic_Comment();
		$arrComment = $objComment->get($videoId, $count, $offset, array('avatarSize' => 160));
		$arrComment['count'] = count($arrComment['data']);
	
		$this->ok($arrComment);
	}	

    /**
     * 相关视频
     * 
     * @param $_GET = array(
     *     id: 视频id
     *     offset: 分页参数，当前页偏移量，第一页为0
     *     count: 分页参数，每页多少条记录
     * )
     * @return array array(total => 总数, data => 视频列表)
     */
	public function action_related()
	{
		$videoId = (string) $this->request->param('id');
		if (strlen($videoId) != 32) {
			$this->err(null, 'invalid id');
		}
		$offset = (int) $this->request->param('offset', 0);
		$count = (int) $this->request->param('count', 5);		
		
        $modelLogicRecommend = new Model_Logic_Recommend();
        $result = $modelLogicRecommend->getRecommendVideos($videoId, $offset, $count, 
            Model_Logic_Video::$basicFieldsForMobile);
		foreach ($result['data'] as &$video) {
		    $video['thumbnail'] = Util::videoThumbnailUrl($video['thumbnail']);
		}
		$arrVideo = array_values($result['data']);
		$objLogicUtil = new Model_Logic_Mobile_Util();
		$objLogicUtil->appendVideoMp4Playurl($arrVideo);
		$this->ok(array('total' => $result['total'], 'data' => $arrVideo));
	}

    /**
     * 动态用户列表
     * 
     * @param $_GET = array(
     *     id: 视频id
     *     type: 类型，3 - 被评论， 4 - 被分享，5 - 被心情
     *     offset: 分页参数，当前页偏移量，第一页为0
     *     count: 分页参数，每页多少条记录
     * )
     * @return array array(total => 总数, data => 用户列表)
     */
	public function action_feedUsers()
	{
		$videoId = (string) $this->request->param('id');
		if (strlen($videoId) != 32) {
			$this->err(null, 'invalid id');
		}
		$type = (int) $this->request->param('type');
		$offset = (int) $this->request->param('offset', 0);
		$count = (int) $this->request->param('count', 10);

		$modelLogicVideo = new Model_Logic_Video();
		$result = $modelLogicVideo->feededUsers($videoId, $type, $count, $offset);
		foreach ($result['data'] as &$user) {
    		foreach ($user['avatar'] as &$avatar) {
		        $avatar = Util::userAvatarUrl($avatar);
    		}
		}
		
		$this->ok(array('total' => $result['total'], 'data' => array_values($result['data'])));
	}

    /**
     * 发表心情
     * 
     * @param $_GET = array(
     *     id: 视频id
     *     mood: 心情类型，喜欢（xh）、围观（wg）、大笑（dx）、愤怒（fn）、囧（jn）
     *     circle: 视频所属圈子，可选
     * )
     * @return array array()
     */
	public function action_mood()
	{
	    $this->_needLogin();
	    
	    $videoId = (string) $this->request->param('id');
	    if (strlen($videoId) != 32) {
	        $this->err(null, 'invalid id');
	    }
	    $mood = (string) $this->request->param('mood');
	    if (!$mood) {
	        $this->err(null, 'invalid mood');
	    }
	    $circleId = trim($this->request->param('circle'));
	    if (!empty($circleId)) {
	        $circleId = (int) $circleId;
    	    if ($circleId <= 0) {
    	        $this->err(null, 'invalid circle');
    	    }
	    }
	    
	    $modelLogicVideo = new Model_Logic_Video();
	    try {
    	    $modelLogicVideo->mood($videoId, $mood, $this->_uid, $circleId);
	    } catch (Model_Exception $e) {
	        $this->err(NULL, $e->getMessage(), NULL, NULL, $e->getCode());
	    }
	    
	    $this->ok();
	}

	protected function _commentRule() {
		return array(
			'@content' => array(
				'datatype' => 'text',
				'reqmsg' => '评论内容',
				'maxlength' => 200,
			),
		);
	}
}