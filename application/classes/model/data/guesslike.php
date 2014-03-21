<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 猜你喜欢推荐
 * @author xucongbin
 */
class Model_Data_Guesslike {
	
	private $watchedVids;
	
	private $uid;
	
	private $cacheKey;
	
	private $debugMode = false;
	
	private $sessionId;
	
	private $debugData = array();
	
	private $commentList = array();
	
	public function __construct($uid, $watchedVids=array(), $sessionId=NULL) {
		
		if($sessionId===NULL) {
			$sessionId = Session::instance()->id();
		}
		$this->sessionId = $sessionId;
		$this->uid = $uid;
		$this->initWatchedVids($watchedVids);
		
	}
	
	public function getGuessVids( $intRound=NULL ) {
		$arrVids = array();
		$arrData = $this->getRecommendData( $intRound );
		foreach($arrData as $intTmpRound=>$row) {
			$arrVids = array_merge($arrVids, $row["vid_list"]);
		}
		
		
		return $arrVids;
	}
	
	public function getGuessCircleIds( $intRound=NULL ) {
		$arrCiecleIds = array();
		$arrData = $this->getRecommendData( $intRound );
		foreach($arrData as $intTmpRound=>$row) {
			$arrCiecleIds = array_merge($arrCiecleIds, $row["cid_list"]);
		}
		return $arrCiecleIds;
	}
	
	/**
	 * 
	 * 获取推荐数据
	 */
	public function getRecommendData( $intRound=NULL ) {
		$arrReturn = array(
		);
		
		//存储评论的key
		$commmentListKey = "commment_list";
		$arrParams = array(
			"uid" => $this->uid ? $this->uid : "",
			"cookieid" => $this->sessionId ? $this->sessionId : "",
			"vidlist" => $this->watchedVids ? implode(",", $this->watchedVids) : ""
		);
		if($intRound && $intRound!=3) {
			$arrParams['round'] = $intRound;
		}
		Profiler::startMethodExec();
		$strResult = RPC::call('guesslike_recommend_rec', '/rec', array("post_vars"=>$arrParams));
		Profiler::endMethodExec(__FUNCTION__.' round-'.$intRound);
		if($strResult) {
			$arrResult = json_decode($strResult, true);
			if($arrResult) {
				$tmpVidRec = array();
				$tmpCidRec = array();
				
				foreach($arrResult as $tmpRound=>$arrTmp) {
					$arrRound = array();
					$tmpVidRec['round'] = $tmpRound;
					$tmpCidRec['round'] = $tmpRound;
					$arrReturn[$tmpRound] = array(
						"cid_list" => array(),
						"vid_list" => array()
					);
					if($arrTmp) {
						foreach($arrTmp as $tmpType=>$row) {
							if($tmpType == $commmentListKey) {
								$this->setCommentList($row);
								continue;
							}
							$tmpVidRec['reason'] = $tmpType;
							$tmpCidRec['reason'] = $tmpType;
							if(strstr($tmpType, "video")) {
								foreach($row as $tmpVid) {
									$tmpVidRec["vid"] = $tmpVid;
									$arrRound["vid_list"][$tmpVid] = $tmpVidRec;
								}
							} else {
								foreach($row as $tmpCid) {
									$tmpCid = intval($tmpCid);
									$tmpCidRec["cid"] = $tmpCid;
									$arrRound["cid_list"][$tmpCid] = $tmpCidRec;
								}
							}
						}
					}
					$arrReturn[$tmpRound] = $arrRound;
				}
			}
		}
		return $arrReturn;
	}
	
	public function getCommentList() {
		return $this->commentList;
	}
	
	private function initWatchedVids( $watchedVids=NULL ) {
		if(!$watchedVids && $this->uid) {
			$objModelUserVideo = new Model_Data_UserVideo($this->uid);
			$watchedVids = $objModelUserVideo->getWatched(0, 10, true);
			$watchedVids = array_keys($watchedVids["data"]);
		} else {
			$watchedVids = $watchedVids ? array_reverse($watchedVids): array();
		}
		
		$this->watchedVids = $watchedVids;
	}
	
	private function setCommentList($arrComments) {
		$commentList = $this->commentList;
		if($arrComments) {
			$this->commentList = array_merge($commentList, $arrComments);
		}
	}
}