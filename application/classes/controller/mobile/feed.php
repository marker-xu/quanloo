<?php
class Controller_Mobile_Feed extends Mobile
{
    const USER_FEED_PAGE_COUNT = 5;
    /**
     * 获取feed列表
     * 
     * @param $_GET = array(
     *     offset: 分页参数，当前页偏移量，第一页为0
     *     count: 分页参数，每页多少条记录
     *     tm: 时间戳，单位秒，表示获取发表时间比该时间戳早的feed
     *     format: 返回结果格式，目前只支持json
     *     type: feed类型，参考Model_Logic_Feed2::SUBTYPE_*系列常量
     *     uid: 用户ID
     * )
     * @return array(has_more => 是否还有更多, data => feed数组, dict => array(user => 用户id为key的用户信息, circle => 圈子id为key的圈子信息, video => vid为key的视频信息))
     */
    public function action_index()
    {
        $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', self::USER_FEED_PAGE_COUNT);
	    $lasttime = (int) $this->request->param('tm', $_SERVER['REQUEST_TIME']);
	    //$format = $this->request->param('format', 'json');
	    $intSubtype = (int) $this->request->param('type', 0);
	    $uid = (int) $this->request->param('uid', 0);
	    if ($uid < 1) {
	        $this->_needLogin();
	        $uid = $this->_uid;
	    }
	    $bolNoReduce = false;
	    if ($uid == $this->_uid && $intSubtype == Model_Logic_Feed2::SUBTYPE_SELF) {
	        $bolNoReduce = true;
	    }
	    
	    $objFeed2 = new Model_Logic_Feed2();
	    $objLogicUtil = new Model_Logic_Mobile_Util();
		
	    try {
	        $arrData = $objFeed2->getUserFeedList(array('user_id' => $uid, 'offset' => $offset, 'count' => $count,
		    'lasttime' => $lasttime, 'type' => $intSubtype, 'no_reduce' => $bolNoReduce, 'is_mobile' => true));
	        if (isset($arrData['dict']['user']) && is_array($arrData['dict']['user'])) {
	            foreach ($arrData['dict']['user'] as &$v) {
	                if (! isset($v['avatar']) || empty($v['avatar'])) {
	                    unset($v);
	                    continue;
	                }
	                $arrTmp = array();
	                foreach ($v['avatar'] as $k2 => $v2) {
	                    $arrTmp[$k2] = Util::userAvatarUrl($v2);
	                }
	                $v['avatar'] = $arrTmp;
	                unset($v);
	            }
	            unset($v);
	        }
	        if (isset($arrData['dict']['video']) && is_array($arrData['dict']['video'])) {
	        	$objLogicUtil->appendVideoMp4Playurl($arrData['dict']['video']);
	            foreach ($arrData['dict']['video'] as &$v) {
	                if (! isset($v['thumbnail']) || empty($v['thumbnail'])) {
	                    unset($v);
	                    continue;
	                }
	                $v['thumbnail'] = Util::videoThumbnailUrl($v['thumbnail']);
	                unset($v);
	            }
	            unset($v);
	        }
	        if (isset($arrData['dict']['circle']) && is_array($arrData['dict']['circle'])) {
	        	$objLogicCircle = new Model_Logic_Circle();
	            foreach ($arrData['dict']['circle'] as &$v) {
	                if (! isset($v['tn_path']) || empty($v['tn_path'])) {
	                    unset($v);
	                    continue;
	                }
	                $v['tn_path'] = Util::circlePreviewPic($v['tn_path']);
	                unset($v);
	            }
	            unset($v);
	            #补上移动端缩略图
				$objLogicCircle->completeMobilePicPath($arrData['dict']['circle']);
	        }	        	        
	    } catch (Exception $e) {
	        $this->err();
	    }//if (isset($_GET['wlj'])) print_r($arrData);
	     
	    $this->ok($arrData);
    }

    /**
     * 转发feed
     * @param array $_POST = array(
     * 	rootfid => 原始feed id,
     *  curfid => 当前feed id
     * 	content => 用户输入的文本
     * 	orig_feed_data => 显示原始feed的附加数据
     * )
     */
    public function action_doforwardfeed() {
        $this->_needLogin();
        $intRootFid = (int) $this->request->param('rootfid', 0);
        $intCurFid  = (int)  $this->request->param('curfid', 0);
        $strContent = trim($this->request->param('curtext'));
        $strOrigFeedData = trim($this->request->param('orig_feed_data'));
        if ($intRootFid < 1 || $intCurFid < 1 || mb_strlen($strContent, 'utf-8') > Model_Logic_Feed2::FORWARD_FEED_TEXT_MAX_LEN) {
            $this->err(null, '参数错误');
        }
        if (Model_Logic_Blackword::instance()->filter($strContent)) {
            $this->err(null, '抱歉，此内容无法发送，请检查你的输入');
        }
        $objFeed2 = new Model_Logic_Feed2();
        try {
            $mixedRet = $objFeed2->addFeedForward($this->_user, $intRootFid, $intCurFid, $strContent, array('orig_feed_data' => $strOrigFeedData));
        } catch (Exception $e) {
            $mixedRet = false;
            JKit::$log->warn($e->getMessage(), $this->request->post(), $e->getFile(), $e->getLine());
        }
        if ($mixedRet) {
            $this->ok();
        } else {
            $this->err(null, '服务器繁忙，内容无法发送，请稍后重试');
        }
    }
    
    /**
     * 获取指定时间点之后的新feed个数
     *
     * @param tm 时间戳，单位秒，表示这个时间点之后的新feed数目
     */
    public function action_newfeednum() {
        $this->_needLogin();
        $lasttime = (int) $this->request->param('tm', 0);
        $objFeed2 = new Model_Logic_Feed2();
        try {
            $intNum = $objFeed2->getNewUserFeedNum(array('user_id' => $this->_uid, 'lasttime' => $lasttime));
        } catch (Exception $e) {
            $intNum = 0;
        }
        $this->ok($intNum);
    }
}