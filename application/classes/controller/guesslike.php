<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 * 猜你喜欢
 * @author xucongbin
 *
 */
class Controller_Guesslike extends Controller 
{
	private $objLogicRecommend;
	
	public function before() {
		parent::before();
		$this->objLogicRecommend = new Model_Logic_Recommend();
	}
	
	public function action_index() {
		$uid = $this->_uid;
		$watchedVids = null;
		if(!$uid) {
			$watchedVids = isset( $_COOKIE['watched_list'] ) ? json_decode($_COOKIE['watched_list'], true) : array();
			if($watchedVids) {
				ksort($watchedVids);
			}
		}
		
		$intRound = 3;
		$objModelGuesslike = new Model_Data_Guesslike($uid, $watchedVids);
		$objLogicVideo = new Model_Logic_Video();
		$objLogicCircle = new Model_Logic_Circle();
		$objLogicUser = new Model_Logic_User();
		
		$arrRecommendTypeMap = array(
			"video_circleFollow" =>	"来自你关注的圈子",
			"video_friend" => "来自你关注的用户",
			"circle_userrec" => "你可能感兴趣的圈子",
			"video_relative" => "来自你观看的视频",
			"video_rank" => "来自视频排行榜",
			"video_usercircle" => "来自你可能感兴趣的圈子",
			"video_hot"	=> "来自热点视频",	
			"video_comment" => "来自热议视频",
			"video_usertag" => "来自你的兴趣标签"
		);
		$arrResult = $objModelGuesslike->getRecommendData( $intRound );
		$arrCommentIds = $objModelGuesslike->getCommentList();
		$arrTmp = $this->separateVidsAndCidsFromResult($arrResult);
		$arrVids = $arrTmp["vids"];
		$arrCids = $arrTmp["cids"];
		Profiler::startMethodExec();
		$arrVideos = $objLogicVideo->getMulti( $arrVids, false, null, false );
		Profiler::endMethodExec('Controller_Guesslike getMulti videos');
		
//		$objLogicVideo->complementCircleVideoRel( $arrVideos, false );
		Profiler::startMethodExec();
		$this->completeGuesslikeComments($arrVideos, $arrCommentIds);
		Profiler::endMethodExec('Controller_Guesslike completeGuesslikeComments');
		Profiler::startMethodExec();
		$arrCircles = $objLogicCircle->getMulti($arrCids);
		Profiler::endMethodExec('Controller_Guesslike getMulti circles');
		$objLogicUser->complementUserCircleRel($arrCircles, $uid);
		$arrData = $this->rebuildGuesslikeResult($arrResult, $arrCircles, $arrVideos);
		$this->template->set("guess_result", $arrData);
		$this->template->set("recommend_type_map", $arrRecommendTypeMap);
	}
	/**
	 * 
	 * 猜你喜欢的圈子
	 * 
	 * @param $_GET => array(
	 *  format => 数据格式, json和html两种类型, 默认html
	 * 	offset => 起始位置, 默认0
	 *  count => 数量， 默认4
	 * )
	 * 
	 * @return array(
	 * 	圈子信息array(
	 * 		is_focus => 是否被关注
	 * 		...
	 * 	),
	 * 	...
	 * )
	 */
	public function action_likecircles() {
		$format = $this->request->query("format");
		$offset = $this->request->query("offset");
		$count = $this->request->query("count");
		if($offset===NULL) {
			$offset = 0;
		}
		if($count===NULL) {
			$count = 4;
		}
		if($this->_uid) {
	        Profiler::startMethodExec();
			$arrCircles = $this->objLogicRecommend->getGuessCirclesByUid($this->_uid, $offset, $count);
		    Profiler::endMethodExec(__FUNCTION__.' getGuessCirclesByUid');
		} else {
	        Profiler::startMethodExec();
			$arrCircles = $this->objLogicRecommend->getGuessCirclesByCookie($offset, $count);
		    Profiler::endMethodExec(__FUNCTION__.' getGuessCirclesByCookie');
		}
		if(! empty($arrCircles)) {
		    JKit::addAfterRequestFinish(function ($userId) use ($arrCircles) {
    			$modelStat = new Model_Data_Stat();
    			$arrCircleIds = Arr::pluck($arrCircles, "_id");
    			$arrStat = array(
    				'page_id'=>'recommendation',
    				'ip' => Request::$client_ip,
    				'user_id' => $userId,
    				'sort_id' => 1,
    				'rec_zone' => 'circle_rec',
    				'item_list' => implode(",", $arrCircleIds)
    			);
    	        Profiler::startMethodExec();
    		    $modelStat->log($arrStat);
    		    Profiler::endMethodExec(__FUNCTION__.' log');
		    }, ($this->_uid ? $this->_uid : -1));
		}
		if($format=="json") {
			$arrReturn = array(
				'total' => 40,
				'data' => $arrCircles
			);
			$this->ok($arrReturn);
		}
		
		$objTpl = self::template();
		$objTpl->set_filename("guesslike/topcircles");
	    $objTpl->circle_list = $arrCircles;
	    $objTpl->offset = intval($offset);
		$content = $objTpl->render();
		$this->ok(array("html"=>$content, "total" => 40));
	}
	/**
	 * 
	 * 猜你喜欢的视频
	 * 
	 * @param $_GET => array(
	 * 	offset => 起始位置, 默认0
	 *  count => 数量， 默认10
	 * )
	 * 
	 * @return array(
	 * 	视频信息,
	 * 	...
	 * )
	 */
	public function action_likevideos() {
		$offset = $this->request->query("offset");
		$count = $this->request->query("count");
		if($offset===NULL) {
			$offset = 0;
		}
		if($count===NULL) {
			$count = 10;
		}
		if($this->_uid) {
			$arrVideos = $this->objLogicRecommend->getGuessVideosByUid($this->_uid, $offset, $count);
		} else {
			$arrVideos = $this->objLogicRecommend->getGuessVideosByCookie($offset, $count);
		}
		if(! empty($arrVideos)) {
		    JKit::addAfterRequestFinish(function ($userId) use ($arrVideos) {
		        $modelStat = new Model_Data_Stat();
		        $arrVids = Arr::pluck($arrVideos, "_id");
		        $arrStat = array(
		                'page_id'=>'recommendation',
		                'ip' => Request::$client_ip,
		                'user_id' => $userId,
		                'sort_id' => 1,
		                'rec_zone' => 'video_rec',
		                'item_list' => implode(",", $arrVids)
		        );
		        Profiler::startMethodExec();
		        $modelStat->log($arrStat);
		        Profiler::endMethodExec(__FUNCTION__.' log');
		    }, ($this->_uid ? $this->_uid : -1));		    
		}
		
		$this->ok($arrVideos);
	}
	
	/**
	 * 
	 * 圈子弹出选择浮层
	 * @param $_GET => array(
	 * 	offset => 起始位置, 默认0
	 *  count => 数量， 默认6
	 * )
	 * 
	 * @return array(
	 * 	html => 页面内容
	 * )
	 */
	public function action_getpopcircles() {
		$this->_needLogin();
		$offset = $this->request->query("offset");
		$count = $this->request->query("count");
		if($offset===NULL) {
			$offset = 0;
		}
		if($count===NULL) {
			$count = 6;
		}
		$arrCircles = $this->objLogicRecommend->getGuessCirclesByUid($this->_uid, $offset, $count);
		
		$objTpl = self::template();
		$objTpl->set_filename("guesslike/popcircles");
	    $objTpl->circle_list = $arrCircles;
		$content = $objTpl->render();
		$this->ok(array("html"=>$content));
	}
	
	private function clusterRoundVideos($arrVideos, $arrVidRec) {
		$arrReturn = array();
		foreach($arrVidRec as $tmpVid=>$row) {
			$key = $row['reason'];
			if(!isset($arrReturn[$key])) {
				$arrReturn[$key] = array(
				);
			}
			if( isset($arrVideos[$tmpVid]) ) {
				$arrReturn[$key][] = $arrVideos[$tmpVid];
			}
		}
		
		return $arrReturn;
	}
	
	private function separateVidsAndCidsFromResult($arrResult) {
		
		$arrReturn = array(
			"vids" => array(),
			"cids" => array()
		);
		if(!$arrResult) {
			return $arrReturn;
		}		
		foreach($arrResult as $intRound=>$row) {
			$arrReturn["vids"] = array_merge($arrReturn["vids"], array_keys($row["vid_list"]));
			$arrReturn["cids"] = array_merge($arrReturn["cids"], array_keys($row["cid_list"]));
		}
		
		return $arrReturn;
	}
	
	private function rebuildGuesslikeResult($arrResult, $arrCircles, $arrVideos) {
		$arrReturn = array();
		if(!$arrResult) {
			return $arrReturn;
		}
		
		foreach($arrResult as $intRound=>$row) {
			$arrReturn[$intRound] = array(
				"video_list" => $this->clusterRoundVideos( $arrVideos, $row["vid_list"] ),
				"circle_list" => $this->appendAppointCircles($arrCircles, $row["cid_list"])				
			);
			
		}
		return $arrReturn;
	}
	
	private function appendAppointCircles($arrCircles, $arrCidRec) {
		$arrReturn = array();
		if(!$arrCidRec) {
			return $arrReturn;
		}
		foreach($arrCidRec as $tmpCid=>$row) {
			if( isset($arrCircles[$tmpCid]) ) {
				$arrReturn[] = $arrCircles[$tmpCid];
			}
		}
		
		return $arrReturn;
	}
	
	private function completeGuesslikeComments( &$arrVideos, $arrCommentIds ) {
		$objModelComment = new Model_Data_Comment();
		$objLogicUser = new Model_Logic_User();
		if(!$arrCommentIds) {
			return ;
		}
		$arrMongoIds = array();
		foreach($arrCommentIds as $strTmpId) {
			$arrMongoIds[] = new MongoId( $strTmpId );
		}
		$arrComments = $objModelComment->find(
			array("_id" => 
				array(
					'$in'=> $arrMongoIds
				)
			)
		);
		if( !$arrComments ) {
			return ;
		}
	
		$arrUserIds = Arr::pluck($arrComments, "user_id");
		$arrUsers = $objLogicUser->getMulti($arrUserIds, false, false);
		$arrComments = $this->_complementInfo($arrComments, array('avatarSize' => 30), $arrUsers);
	if(isset($_GET['test'])) {
			print_r($arrComments);
		}
		#TODO 用视频ID聚合起来
		$arrTmpComments = array();
		foreach($arrComments as $commentRow) {
			if( !isset($arrTmpComments[$commentRow['video_id']]) ) {
				$arrTmpComments[$commentRow['video_id']] = array( $commentRow );
			} else {
				$arrTmpComments[$commentRow['video_id']][] = $commentRow;
			}
		}
		foreach($arrVideos as $k=>$row) {
	    	$tmpVid = $row["_id"];
	    	if (isset($arrTmpComments[$tmpVid])) {
				$row['comments'] = array_slice( $arrTmpComments[$tmpVid], 0, 2);
			} else {
				$row['comments'] = array();
			}
			$arrVideos[$k] = $row;
	    }
	}
	
	private function _complementInfo($arrComment, $arrExtra = NULL, $arrUserInfo = NULL) {
        if (empty($arrComment)) {
            return array();
        }
        if (! is_array($arrUserInfo)) {
            $arrTmp = array();
            foreach ($arrComment as $v) {
                if ($v['user_id'] > 0) {
                    $arrTmp[] = $v['user_id'];
                }
            }
            if (! empty($arrTmp)) {
                $objTmp = new Model_Logic_User();
                $arrUserInfo = (array) $objTmp->getMulti($arrTmp);
            } else {
                $arrUserInfo = array();
            }
        }
        
        if (isset($arrExtra['avatarSize'])) {
            $intAvatarSize = $arrExtra['avatarSize'];
        } else {
            $intAvatarSize = 30;
        }
        foreach ($arrComment as &$v2) {
            $v2['_id'] = (string) $v2['_id'];
            if ($v2['user_id'] > 0) {
                $intUserId = $v2['user_id'];
                if (isset($arrUserInfo[$intUserId])) {
                    $arrTmp = $arrUserInfo[$intUserId];
                    $v2['nick'] = $arrTmp['nick'];
                    $strAvatar = isset($arrTmp['avatar'][$intAvatarSize]) ? $arrTmp['avatar'][$intAvatarSize] : '';
                    $v2['avatar'] = Util::userAvatarUrl($strAvatar, $intAvatarSize);
                } else {
                    $v2['nick'] = $intUserId;
                    $v2['avatar'] = Util::userAvatarUrl('', $intAvatarSize);
                }
            } else {
                if (empty($v2['nick'])) {
                    $v2['nick'] = Model_Logic_Comment::$arrSiteAvatar[$v2['site']]['nick'];
                }
                $v2['avatar'] = Model_Logic_Comment::getSiteCommentAvatar($v2['site'], $intAvatarSize);
            }
            $v2['create_time_str'] = Util::time_from_now($v2['create_time']->sec, true);
        }
        unset($v2);

        return array_values($arrComment);
    }
}