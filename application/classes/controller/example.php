<?php 

class Controller_Example extends Controller 
{
	public function action_index()
	{
	    $strContent = "这个不错 @刘江CE@程序员杂志: //@程序员邹欣：动作快@wwwu.com的同学已经发布了基于SDK 的应用 Cartoon Me: http://t.cn/zOd1Gq0";
	    preg_match_all('/@([^[:space:]@:：]+)/u', $strContent, $arrUserNick);
        $this->response->body($strContent . "<br>\n" . print_r($arrUserNick, true));
	}
	
	public function action_sdotest() {
		$objModelSndauser = new Model_Data_Sndauser();
		$welcome = $this->request->query("welcome");
		if($welcome) {
			$ticket = $this->request->query("ticket");
			$authKey = $this->request->query("authKey");
			if($ticket) {
				if($authKey) {
					$gotoUrl = "/user/login".URL::query(array('f'=>$strTargetUrl, 'ticket'=>$ticket), false);
					$strJs = "<script>window.parent.location.href='$gotoUrl';</script>";
					die($strJs);
				}
				$arrSndaInfo = Model_Data_Sndauser::getTicketInfo($ticket);
			}
			die("write here js callback");
		}
		
		$redirectUrl = "http://".DOMAIN_SITE."{$_SERVER['REQUEST_URI']}?welcome=1";
		$arrExtra = array(
			"target" => "iframe"
		);
		$this->template->set("login_test_iframe_url", $objModelSndauser->buildLoginUrl($redirectUrl, true, $arrExtra));
	}
	
	public function action_timeout() {
		$this->ok();
	}
	
	public function action_test(){
		if($this->request->method() !=Request::POST) {
			$this->template->get_template_object()->error_reporting = E_ALL&E_NOTICE;
			$this->template->set("abc", 1);
			print_r(get_class($this->template->get_template_object()));
			return;
		}
		print_r($_POST);
		$this->ok();
	}

    public function action_mass(){
		$this->ok();
	}
	
	public function action_qq() {
		$objModelLogicConnect2 = new Model_Logic_Connect2();
		if( !isset($_GET['code']) ) {
			$strCallBackUrl = "http://dev.quanloo.com/example/qq";
			$url = $objModelLogicConnect2->getQqRedirectUrl($strCallBackUrl);
			$this->request->redirect( $url );
		}
		$strCode = $this->request->param("code");
		echo "code-".$strCode;
		$strCallBackUrl = "http://dev.quanloo.com/example/qq";
		var_dump($objModelLogicConnect2->qqCallback($strCode, $strCallBackUrl));
		return;
	}
	
	public function action_renren() {
		$objModelLogicConnect2 = new Model_Logic_Connect2();
		$strCallBackUrl = "http://dev.quanloo.com/example/renren";
		if( !isset($_GET['code']) ) {
			$url = $objModelLogicConnect2->getRenrenRedirectUrl( $strCallBackUrl );
			$this->request->redirect( $url );
		}
		$strCallBackUrl = $objModelLogicConnect2->getRenrenCallbackUrl( $strCallBackUrl );
		$strCode = $this->request->param("code");
		echo "code-".$strCode;
		
		var_dump($objModelLogicConnect2->renrenCallback($strCode, $strCallBackUrl));
//		echo "qqlogin-url: ".$url."<br>\n";
//		$url = $objModelLogicConnect2->getRenrenRedirectUrl($strCallBackUrl);
//		echo "renrenlogin-url: ".$url."<br>\n";
//		$url = $objModelLogicConnect2->getDoubanRedirectUrl($strCallBackUrl);
//		echo "doubanlogin-url: ".$url."<br>\n";
		return;
	}
	
	public function action_upload() {
		if($this->request->method()=="POST") {
			$status = apc_fetch('upload_'.$_POST['APC_UPLOAD_PROGRESS']);
			print_r($status);
			while(!$status['done']) {
				
  				var_dump($status);
  				echo "<br>\n";
  				
			}
			
			print_r($_FILES);
			print_r($_POST);
			
		}
		$this->template->set("progress_key", "progress_key_".uniqid());
//		$this->create_template();
	}
	
	public function action_writehottags() {
		$key = "RECOMMEND_USER_TAGS";
		$objRedis = Model_Data_Recommend::getRedisDb(14);
		$arrOrgTags = array(
			"NBA",	"mtv",	"体育",	"动漫",	"动画片", "原创", "可爱", "娱乐",	"宝宝",
			"宠物",	"小品",	"广告",	"影视",	"恶搞",	"搞笑",	"搞笑视频",	
			"教学",	"教程",	"教育",	"新闻",	"旅游",	"日剧",	"日韩动漫",	
			"时尚",	"明星",	"有声小说",	"歌曲",	"汽车",	"泰剧",	"游戏",	"演唱会",	
			"爆笑",	"爱情",	"电视剧",	"相声",	"篮球",	"综艺",	"美女",	"自拍",	
			"舞蹈",	"英语",	"资讯",	"足球",	"韩剧",	"音乐",	"预告片","高清","魔术"
		);
		$arrTags = $objRedis->zRange($key, 0, -1);
		if(!$arrTags) {
			$objRedis = $objRedis->multi();
			$i = 0;
			foreach($arrOrgTags as $tag) {
				$i++;
				$objRedis = $objRedis->zAdd($key, $i, $tag);
			}
			$res = $objRedis->exec();
			echo count($res);
			var_dump($res);
		} else {
			print_r($arrTags);
		}
		exit;
	}
	
	public function action_getuploadprogress() {
		if(isset($_GET['pk'])) {
			$status = apc_fetch('upload_'.$_GET['pk']);
//  			echo $status['current']/$status['total']*100;
  			var_dump($status);
		}
		$this->ok();
	}
	
	public function action_video() {
		$obj = new Model_Data_Video();
		print_r($obj->get('9cc5ed82f8b18bd74135f33e0953f484'));
	}
	
	public function action_session() {
//		Session::instance()->set('example', array("baowei", "no problem", "xiaohui"));
		print_r(Session::instance()->get('example'));
	}
	
	public function action_user() {
		$objModel = new Model_Data_User();
		$account = "123@1.com";
		$arrParams = array(
			'password' => md5(111111),
			'avatar' => '',
		);
		
		var_dump( $objModel->addUser($account, $arrParams) );
//		echo ( $objModel->getUniqueValue("user") );
	}
	
	public function action_valid() {
		$arrRules = array(
			'@email' => array(
//                    'reqmsg' => '邮箱',
                   	'datatype' => 'email',
                   	'errmsg' => '邮箱格式错误'
            ),
            '@phone' => array(
                    'reqmsg' => '手机',
                   	'datatype' => 'reg',
            		'reg-pattern' => "/(^([0-9]{2,4}\-)?[1-9][0-9]{6,7}$)|(^(13|15|18|14)\d{9}$)/i",
                   	'errmsg' => '手机号码格式错误'
            ),
		);
		
		$arrPost = array(
			'email' => 'kk.com',
			'phone' => '15821950828',
			'nick' => '',
			'password' => 'k@k.com',
			'pass_again' => 'k@k.comd',
		);
		$objValidation = new Validation($arrPost, $arrRules);
		if (! $this->valid($objValidation)) {
		    return;
		}
		
	}
	
	public function action_recommend() {
		
		$type = $this->request->post("type");
		$cid = $this->request->post("cid");
		$offset = $this->request->post("offset");
		$count = $this->request->post("count");
		if($type===NULL) {
			$type = 0;
		}
		if($offset===NULL) {
			$offset = 0;
		}
		if($count===NULL) {
			$count = 10;
		}
		$obj = new Model_Logic_Recommend();
		$default = array('type' => $type, "offset"=>$offset, "count"=>$count, "cid" => $cid);
		if($type) {
			
			$objCircle = new Model_Logic_Circle();
			$default['circle'] = $objCircle->get(intval( $cid) );
			if($default['circle']) {
				$result = $obj->getCircleVideos($cid, $offset, $count);
			} else {
				$result = array();
			}
		} else {
			$maxCurrent = $obj->getHomepageMaxCurrent();
			$this->template->set('home_page_max_num', $maxCurrent);
			$result = $obj->getHomepageRecommendVideos($offset, $count, $maxCurrent);
			if(isset($_GET['test'])) {
				print_r($result);
			}
		}
		$this->completeVideoInfo($result);
		$this->template->set('result', $result);
		$this->template->set('default', $default);
	}
	
	public function action_recommendbeta() {
		
		$type = $this->request->post("type");
		$cid = $this->request->post("cid");
		$offset = $this->request->post("offset");
		$count = $this->request->post("count");
		if($type===NULL) {
			$type = 0;
		}
		if($offset===NULL) {
			$offset = 0;
		}
		if($count===NULL) {
			$count = 10;
		}
		$obj = new Model_Logic_Recommend();
		$default = array('type' => $type, "offset"=>$offset, "count"=>$count, "cid" => $cid);
		if($type) {
			
			$objCircle = new Model_Logic_Circle();
			$default['circle'] = $objCircle->get(intval( $cid) );
			if($default['circle']) {
				$result = $this->getCircleVideos($cid, $offset, $count);
			} else {
				$result = array();
			}
		} else {
			$maxCurrent = $this->getHomepageMaxCurrent();
			$this->template->set('home_page_max_num', $maxCurrent);
			$result = $this->getHomepageRecommendVideos($offset, $count, $maxCurrent);
		}
		$this->completeVideoInfo($result);
		$this->template->set('result', $result);
		$this->template->set('default', $default);
	}
	
	public function action_recommendvideo() {
		$default = array('vid' => "");
		if($this->request->method()=="POST") {
			$vid = trim( $this->request->post("vid") );
			$result = "";
			if( $vid ) {
				$default['vid'] = $vid;
				$obj = new Model_Data_Recommend();
				$result = $obj->getRecommendVideos($vid);
				
				$objLogicVideo = new Model_Data_Video();
				$default['video'] = $objLogicVideo->get($vid, array("_id", "title", "tag", "desc", "play_url"));
				if(!$result) {
					Model_Data_Recommend::writeWaitingVidTB($vid);
					$objModelCircleVideo = new Model_Data_CircleVideo();
					$arrTmpCircles = $objModelCircleVideo->find(array("video_id"=>$vid), array("circle_id"));
					//没圈子，就返回上升最快的10个视频
					if($arrTmpCircles) {
						$arrCircles = Arr::pluck($arrTmpCircles, "circle_id");
						$strKey = Model_Data_Recommend::getCircleCatKey($arrCircles[0]);
						$arrVids = array_slice( Model_Data_Recommend::getList($strKey), 0 ,15 );
					} else {
						$arrVids = Model_Data_Recommend::getHomepageRecommendVideos(0, 14);
					}
					
					$this->template->set('alter_result', $arrVids);
				} else {
					$result = array_slice($result, 0, 20);
					$result = $objLogicVideo->getMulti($result, array(), true);
				}
				
			}
			$this->template->set('result', $result);
		}
		$this->template->set('default', $default);
	}
	
	public function action_load() {
		$obj = new Model_Data_VideoFeed();
	}
	
	public function action_guesslike() {
		$uid = $this->_uid;
		$watchedVids = null;
		if(!$uid) {
			$watchedVids = isset( $_COOKIE['watched_list'] ) ? json_decode($_COOKIE['watched_list'], true) : array();
			if($watchedVids) {
				ksort($watchedVids);
			}
		}
		
		$intRound = 4;
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
		$arrVideos = $objLogicVideo->getMulti( $arrVids, true, null, false );
		
		$objLogicVideo->complementCircleVideoRel( $arrVideos );
//		if(isset($_GET['test'])) {
		$this->completeGuesslikeComments($arrVideos, $arrCommentIds);
//		}
		$arrCircles = $objLogicCircle->getMulti($arrCids, true);
		
		$objLogicUser->complementUserCircleRel($arrCircles, $uid);
		$arrData = $this->rebuildGuesslikeResult($arrResult, $arrCircles, $arrVideos);
		$this->template->set("guess_result", $arrData);
		$this->template->set("recommend_type_map", $arrRecommendTypeMap);
		$this->template->set_filename("example/guesslike");
		return;
		
	}
	
	public function action_image() {
		$sourceImage = "/tmp/12345.png";
		$destImage = "/tmp/dest.jpg";
		$objImage = Image::factory($sourceImage);
		$objImage->save($destImage, 85);
	}
	
	
	public function action_email() {
		$emailList = "gggfreak@126.com,lang_gong@163.com,onlytesttest@163.com,wulijun01234@163.com";
		$content = "baowei";
		$arrEmailList = explode(",", $emailList);
		$objEmail = Email::factory("test", $content);
		$arrEmailConfig = Jkit::config("email.options");
		$objEmail->from($arrEmailConfig['from']);
		$objEmail->to("gggfreak@126.com");
		$arrErrors = array();
//		var_dump( $objEmail );
//		var_dump( $objEmail->send($arrErrors) );
		$objEmail->to("lang_gong@163.com");
		var_dump( $objEmail->send($arrErrors) );
		var_dump($arrErrors);
		return;
	}
	
	
	private function completeVideoInfo(&$arrVideos) {
		if (!$arrVideos) {
			return;
		}
		$objLogicVideo = new Model_Logic_Video();
		$arrVids = Arr::pluck($arrVideos, "_id");
		$arrResult = $objLogicVideo->getMulti($arrVids, true, array('type', 'category', 'title', 'tag', 'quality', 
    	'length', 'thumbnail', 'mid_thumbnail', 'big_thumbnail', 'play_url', 'player_url', 
    	'embed_code', 'domain', 'upload_time'));
		if($arrResult) {
			foreach($arrVideos as $k=>$row) {
				if(isset($arrResult[$row["_id"]])) {
					$arrVideos[$k] = array_merge($row, $arrResult[$row["_id"]]);
				}
			}
		}
	}
	
	private function getCircleVideos($intCircleId, $offset=0, $count=10) {
		$arrReturn = array();
		$objRecommendRedis = Model_Data_Recommend::getRedisDb(3);
		$strKey = Model_Data_Recommend::getCircleCatKey($intCircleId);
		$arrMembers = $objRecommendRedis->zRevRange($strKey, intval($offset), intval(($offset+$count-1)), true);
		$arrMembers  = self::parseSortedMembers( $arrMembers, true );
		if( !$arrMembers ) {
		    return $arrReturn;
		}
		
		return $arrMembers;
	}
	
	private function getHomepageRecommendVideos($offset=0, $count=10, $maxCurrent=NULL, $arrField=NULL) {
		$arrReturn = array();
		$objRecommendRedis = Model_Data_Recommend::getRedisDb(3);
		$start = intval( $offset );
		$end = intval($offset+$count-1);
		$extra = array(
	    		'withscores' => true
	    	);
    	$extra['limit'] = array(intval($start), $end==-1 ? 1000 : intval( $end-$start+1 ));
		$arrMembers = $objRecommendRedis->zRevRangeByScore("homepage_rec", $maxCurrent, 0, 
			$extra);
		$arrMembers =  self::parseSortedMembers( $arrMembers, true );
		if( !$arrMembers ) {
			return $arrReturn;
		}
		return $arrMembers;
	}
	/**
	 * 
	 * 当前页面的最大序号
	 * 
	 * @return int
	 */
	private function getHomepageMaxCurrent() {
		return Model_Data_Recommend::getRedisDb(3)->get('homepage_cur_num');
	}
	
	private function buildSortedVideos($arrMembers) {
		$objLogicVideo = new Model_Logic_Video();
		$arrResult = $objLogicVideo->getMulti($arrInput, true, $arrField);
	}
	
	private static function parseSortedMembers($arrMembers, $includeScore=true) {
		$arrReturn = array();
		if(!$arrMembers) {
			return $arrReturn;
		}
		$arrTmp = array();
		if($includeScore) {
			foreach($arrMembers as $value=>$score) {
				$arrTmp = explode("\3", $value);
				if(!isset($arrReturn[$arrTmp[0]])) {
					$arrReturn[$arrTmp[0]] = array(
						'vid' => $arrTmp[0], 
						'_id' => $arrTmp[0],
						'rec_type' => $arrTmp[1], 
						'timestamp' => isset($arrTmp[2]) ? $arrTmp[2]:0,
						'sort_num' => $score
					);
				}
			}
		} else {
			foreach($arrMembers as $value) {
				$arrTmp = explode("\3", $value);
				if(!isset($arrReturn[$arrTmp[0]])) {
					$arrReturn[$arrTmp[0]] = array(
						'vid' => $arrTmp[0], 
						'_id' => $arrTmp[0],
						'rec_type' => $arrTmp[1], 
						'timestamp' => $arrTmp[2],
					);
				}
			}
		}
		
		
		return $arrReturn;
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