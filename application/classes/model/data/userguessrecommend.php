<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 猜你喜欢推荐, 用户登录下
 * @author xucongbin
 */
class Model_Data_Userguessrecommend {
	
	private $uid;
	
	private $watchedVids;
	
	private $objModelCircle;
	
	private $debugMode = false;
	
	private $debugData = array();
	
	public function __construct($uid) {
		$this->uid = intval( $uid );
		$this->objModelCircle = new Model_Data_Circle();
	}
	
	public function getGuessVids() {
		
		$arrVids = array();
		# 取相关视频推荐
		$arrRecommendVidList = $this->filterVids( $this->batchGetRecommendVids() );
		$intRecommendVidLength = count($arrRecommendVidList);
		#取相关圈子推荐
		$arrCircleRecommendVidList = $this->filterVids( $this->getCircleRecommendVidList() );
		$arrCircleRecommendVidList = array_diff($arrCircleRecommendVidList, $arrRecommendVidList);
		$intCircleRecommendVidLength = count($arrCircleRecommendVidList);
		$arrHomePageRecommendVids = array();
//		echo "recommend by vid length: ( {$intRecommendVidLength}+{$intCircleRecommendVidLength})<br>\n";
//		$intRecommendVidLength = 10;
//		$intCircleRecommendVidLength = 20;
		if( ( $intRecommendVidLength+$intCircleRecommendVidLength)<100 ) {
			$arrHomePageRecommendVids = $this->filterVids( 
				array_keys( Model_Data_Recommend::getHomepageRecommendVideos(0, 199) ) 
			);
			$arrHomePageRecommendVids = array_slice( array_diff($arrHomePageRecommendVids, 
				$arrRecommendVidList, $arrCircleRecommendVidList), 0, 100 );
			rsort($arrHomePageRecommendVids);
			
//			shuffle($arrHomePageRecommendVids);
		}
		$arrVids = $this->sortVids($arrRecommendVidList, $arrCircleRecommendVidList, $arrHomePageRecommendVids);
		return $arrVids;
	}
	
	public function getGuessCircleIds() {
		$arrCiecleIds = array();
		#TODO 个性化圈子ID
		$arrUserCircleIds = $this->filterCids( Model_Data_Recommend::getUserCircles($this->uid, 0, 99) );
		#TODO 流行圈子ID
		$arrPopularCircleIds = $this->getPopularCircleIds();
		$arrPopularCircleIds = $this->filterCids( array_diff($arrPopularCircleIds, $arrUserCircleIds) );
		$arrCiecleIds = $this->sortCids($arrUserCircleIds, $arrPopularCircleIds);
		
		return $arrCiecleIds;
	}
	
	/**
	 * 
	 * 获取圈子相关的视频VID列表
	 */
	private function getCircleRecommendVidList() {
		$arrReturn = array();
		$arrTmpCids = Model_Data_Recommend::getUserCircles($this->uid, 0, 9);
		$arrTmpRecommendMembers = array();
		foreach($arrTmpCids as $tmpCid) {
			$initScore = 25;
			$arrTmpRecommendMembers = Model_Data_Recommend::getCircleVideos($tmpCid, 0, 19, true);
			if($arrTmpRecommendMembers) {
				foreach($arrTmpRecommendMembers as $tmpRow) {
					$this->mergeVidsScore($arrReturn, $tmpRow['vid'], $initScore--);
				}
				
			}
		}
		arsort($arrReturn);
		return array_keys( $arrReturn );
	}
	/**
	 * 
	 * 三组数据排序
	 * @param unknown_type $arrRecommendVids
	 * @param unknown_type $arrCircleRecommendVids
	 * @param unknown_type $arrHomePageRecommendVids
	 */
	private function sortVids($arrRecommendVids, $arrCircleRecommendVids, $arrHomePageRecommendVids) {
		$arrReturn = array();
		
		if(!$arrHomePageRecommendVids) {
			# 2:1
			while($arrRecommendVids && $arrCircleRecommendVids) {
				$arrTmp1 = array_slice($arrRecommendVids, 0, 2);
				$arrReturn = array_merge( $arrReturn,  $arrTmp1);
				$arrRecommendVids = array_slice($arrRecommendVids, 2);
				$strTmp = array_shift($arrCircleRecommendVids);
				$arrReturn[] = $strTmp;
				if($this->debugMode) {
					$this->logFrom($arrTmp1, "video", 1);
					$this->logFrom($strTmp, "video", 2);
				}
			}
			if($arrRecommendVids) {
				$arrReturn = array_merge($arrReturn, $arrRecommendVids);
			}
			if($arrCircleRecommendVids) {
				$arrReturn = array_merge($arrReturn, $arrCircleRecommendVids);
			} 
			
			if($this->debugMode) {
				$this->logFrom($arrRecommendVids, "video", 1);
				$this->logFrom($arrCircleRecommendVids, "video", 2);
			}
		} else {
			# 3:2:1
			while( ($arrRecommendVids && $arrCircleRecommendVids)  
			&& ($arrRecommendVids && $arrHomePageRecommendVids) 
			&& ($arrCircleRecommendVids && $arrHomePageRecommendVids) ) {
				$arrTmp1 = array();
				if($arrRecommendVids) {
					$arrTmp1 = array_slice($arrRecommendVids, 0, 3);
					$arrReturn = array_merge( $arrReturn, $arrTmp1 );
					$arrRecommendVids = array_slice($arrRecommendVids, 3);
				}
				$arrTmp2 = array();
				if($arrCircleRecommendVids) {
					$arrTmp2 = array_slice($arrCircleRecommendVids, 0, 2);
					$arrReturn = array_merge( $arrReturn, $arrTmp2 );
					$arrCircleRecommendVids = array_slice($arrCircleRecommendVids, 2);
				}
				$strTmp = array_shift($arrHomePageRecommendVids);
				$arrReturn[] = $strTmp;
				if($this->debugMode) {
					$this->logFrom($arrTmp1, "video", 1);
					$this->logFrom($arrTmp2, "video", 2);
					$this->logFrom($strTmp, "video", 3);
				}
			}
			
			if($arrRecommendVids) {
				$arrReturn = array_merge($arrReturn, $arrRecommendVids);
			}
			if($arrCircleRecommendVids) {
				$arrReturn = array_merge($arrReturn, $arrCircleRecommendVids);
			} 
			
			if($arrHomePageRecommendVids) {
				$arrReturn = array_merge($arrReturn, $arrHomePageRecommendVids);
			}
			
			if($this->debugMode) {
				$this->logFrom($arrRecommendVids, "video", 1);
				$this->logFrom($arrCircleRecommendVids, "video", 2);
				$this->logFrom($arrHomePageRecommendVids, "video", 3);
			}
		}
		
		return array_slice($arrReturn, 0, 100);
	}
	
	/**
	 * 
	 * 过滤已经看过和以后观看的视频ID
	 * @param unknown_type $arrResult
	 */
	private function filterVids($arrResult) {
		if($this->watchedVids===NULL) {
			$objModelUserVideo = new Model_Data_UserVideo($this->uid);
			$arrTmpVids = $objModelUserVideo->getListByType(Model_Data_UserVideo::TYPE_WATCHED, 0, 99, true);
			$this->watchedVids = $arrTmpVids['data'];
		}
		if($this->watchedVids) {
			$arrResult = array_diff($arrResult, $this->watchedVids);
		}
		
		return array_unique( $arrResult );
	}
	
	/**
	 * 
	 * 获取相关视频ID
	 */
	private function batchGetRecommendVids() {
		$arrReturn = array();
		
		$objModelUserVideo = new Model_Data_UserVideo($this->uid);
		#看过的视频 取10个
		$arrTmpVids = $objModelUserVideo->getListByType(Model_Data_UserVideo::TYPE_WATCHED, 0, 9, true);
		if($arrTmpVids['data']) {
			$arrTmpVids = $arrTmpVids['data'];
			foreach($arrTmpVids as $tmpVid) {
				$arrTmpRecommendVids = Model_Data_Recommend::getRecommendVideos($tmpVid);
				$this->mergeRelateVidsScore($arrReturn, array_slice($arrTmpRecommendVids, 0, 10), 1);
			}
		}
		#推过的，没有了
		#评论
		$arrTmpVids = $objModelUserVideo->getCommented(0, 2, true);
		if($arrTmpVids['data']) {
			$arrTmpVids = Arr::pluck($arrTmpVids['data'], "vid");
			foreach($arrTmpVids as $tmpVid) {
				$arrTmpRecommendVids = Model_Data_Recommend::getRecommendVideos($tmpVid);
				$this->mergeRelateVidsScore($arrReturn, array_slice($arrTmpRecommendVids, 0, 10), 1);
			}
		}
		#分享
		$arrTmpVids = $objModelUserVideo->getListByType(Model_Data_UserVideo::TYPE_SHARED, 0, 2, true);
		if($arrTmpVids['data']) {
			$arrTmpVids = $arrTmpVids['data'];
			foreach($arrTmpVids as $tmpVid) {
				$arrTmpRecommendVids = Model_Data_Recommend::getRecommendVideos($tmpVid);
				$this->mergeRelateVidsScore($arrReturn, array_slice($arrTmpRecommendVids, 0, 10), 1);
			}
		}
		#心情
		$arrTmpVids = $objModelUserVideo->getListByType(Model_Data_UserVideo::TYPE_MOODED, 0, 2, true);
		if($arrTmpVids['data']) {
			$arrTmpVids = $arrTmpVids['data'];
			foreach($arrTmpVids as $tmpVid) {
				$arrTmpRecommendVids = Model_Data_Recommend::getRecommendVideos($tmpVid);
				$this->mergeRelateVidsScore($arrReturn, array_slice($arrTmpRecommendVids, 0, 10), 1);
			}
		}
		
		arsort($arrReturn);
		return array_keys( $arrReturn );
	}
	
	private function mergeRelateVidsScore(&$arrVids, $arrNewVids, $score=1) {
		if(!$arrNewVids) {
			return;
		}
		$initScore = 25;
		foreach($arrNewVids as $tmpVid) {
			$this->mergeVidsScore($arrVids, $tmpVid, $initScore*$score);
			$initScore--;
		}
	}
	/**
	 * 
	 * 合并视频ID，并且打分
	 * @param unknown_type $arrVids
	 * @param unknown_type $arrNewVids
	 * @param unknown_type $score
	 */
	private function mergeVidsScore(&$arrVids, $arrNewVids, $score=1) {
		if(!$arrNewVids) {
			return;
		}
		if( !is_array($arrNewVids) ) {
			$tmpVid = $arrNewVids;
			if( isset($arrVids[$tmpVid]) ) {
				$arrVids[$tmpVid]+=$score;
			} else {
				$arrVids[$tmpVid] =$score;
			}
			return;
		}
		$arrRecommendVidList = $arrVids;
		foreach($arrNewVids as $tmpVid) {
			if( isset($arrRecommendVidList[$tmpVid]) ) {
				$arrRecommendVidList[$tmpVid]+=$score;
			} else {
				$arrRecommendVidList[$tmpVid] =$score;
			}
		}
		$arrVids = $arrRecommendVidList;
	}
	/**
	 * 
	 * 获取流行圈子
	 */
	private function getPopularCircleIds() {
		$objModelCircleStatRecent = new Model_Data_CircleStatRecent();
		$arrCids = $objModelCircleStatRecent->find(array(), array("_id"), 
			array("popularity"=>-1, "watched_count"=>-1), 100);
		return $arrCids ? array_keys($arrCids) : array();
	}
	/**
	 * 
	 * 合并圈子ID，并且打分
	 * @param unknown_type $arrCids
	 * @param unknown_type $arrNewCids
	 * @param unknown_type $score
	 */
	private function mergeCidsScore(&$arrCids, $arrNewCids, $score=1) {
		if(!$arrNewCids) {
			return;
		}
		
		$arrRecommendCidList = $arrCids;
		foreach($arrNewCids as $tmpCid) {
			if( isset($arrRecommendCidList[$tmpCid]) ) {
				$arrRecommendCidList[$tmpCid]+=$score;
			} else {
				$arrRecommendCidList[$tmpCid] =$score;
			}
		}
		$arrCids = $arrRecommendCidList;
	}
	/**
	 * 
	 * 圈子ID排序
	 * @param unknown_type $arrUserCircleIds
	 * @param unknown_type $arrPopularCircleIds
	 */
	private function sortCids($arrUserCircleIds, $arrPopularCircleIds) {
		$arrReturn = array();
		# 3:1
		while($arrUserCircleIds && $arrPopularCircleIds) {
			$arrTmp1 = array_slice($arrUserCircleIds, 0, 3);
			$arrReturn = array_merge( $arrReturn, $arrTmp1 );
			$arrUserCircleIds = array_slice($arrUserCircleIds, 3);
			$strTmp = array_shift($arrPopularCircleIds);
			$arrReturn[] = $strTmp;
			
			if($this->debugMode) {
				$this->logFrom($arrTmp1, "circle", 1);
				$this->logFrom($strTmp, "circle", 2);
			}
		}
		if($arrUserCircleIds) {
			$arrReturn = array_merge($arrReturn, $arrUserCircleIds);
		}
		if($arrPopularCircleIds) {
			$arrReturn = array_merge($arrReturn, $arrPopularCircleIds);
		} 
		if($this->debugMode) {
			$this->logFrom($arrUserCircleIds, "circle", 1);
			$this->logFrom($arrPopularCircleIds, "circle", 2);
		}
		return array_slice( $arrReturn, 0, 40);
	}
	
	private function filterCids( $arrCircleIds ) {
		$arrReturn = array();
		if(!$arrCircleIds) {
			return $arrReturn;
		}
		$arrUserFocusCircleIds = $this->getUserFocusCircleIds();
		$arrCircleIds = array_diff($arrCircleIds, $arrUserFocusCircleIds);
		#TODO 过滤未经审核的圈子,无视频的圈子
		if($arrCircleIds) {
			$query = array(
				"_id"=>array('$in'=>$arrCircleIds),
			);
			$arrTmpCircleIds = $this->objModelCircle->find($query, array("certified", "tn_path", "status"));
			if( $arrTmpCircleIds ) {
				foreach($arrTmpCircleIds as $row) {
					if(!isset($row['tn_path']) || !$row['tn_path']) {
						continue;
					}
//					#TODO certified
//					if(!$row['certified']) {
//						$arrNotCircleIds[] = $row["_id"];
//					}
					if( $row['status']==1 ) {
						$arrReturn[] = $row["_id"];
					}
				}
			}
		}
		
		return $arrReturn;
	}
	
	private function getUserFocusCircleIds() {
		$arrReturn = array();
		$objModelCircleUser = new Model_Data_CircleUser();
		$query = array("user_id"=>$this->uid);
		$arrUserCircleIds = $objModelCircleUser->find($query, array( "circle_id"));
		if( $arrUserCircleIds ) {
			$arrReturn = Arr::pluck($arrUserCircleIds, "circle_id");
		}
		
		return $arrReturn;
	}
	
	private function logFrom($mixedInput, $dataType='video', $fromType) {
		if (!$mixedInput) {
			return;
		}
		
		$arrData = isset( $this->debugData[$dataType]) ? $this->debugData[$dataType] : array();
		if( is_array($mixedInput) ) {
			foreach($mixedInput as $value) {
				$arrData[$value] = $fromType;
			}
		} else {
			$arrData[$mixedInput] = $fromType;
		}
		$this->debugData[$dataType] = $arrData;
	}
	
	public function getDebugData($type, $offset=0, $count=10) {
		if(!isset($this->debugData[$type])) {
			return array();
		}
		$arrKeys = array_keys( $this->debugData[$type] );
		return array_combine( array_slice($arrKeys, $offset, $count), 
			array_slice( $this->debugData[$type], $offset, $count) );
	}
	
	public function setDebugMode($bolMode) {
		$this->debugMode = $bolMode;
	}
}