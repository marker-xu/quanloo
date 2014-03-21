<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 猜你喜欢推荐
 * @author xucongbin
 */
class Model_Data_Guessrecommend {
	
	private $watchedVids;
	
	private $watchlaterVids;
	
	private $debugMode = false;
	
	private $sessionId;
	
	private $debugData = array();
	
	private $objModelCircle;
	
	public function __construct($watchedVids, $watchlaterVids, $sessionId=NULL) {
		$this->watchedVids = $watchedVids ? array_reverse($watchedVids): array();
		$this->watchlaterVids = $watchlaterVids ? array_reverse( $watchlaterVids ): array();
		$this->objModelCircle = new Model_Data_Circle();
		if($sessionId===NULL) {
			$sessionId = Session::instance()->id();
		}
		$this->sessionId = $sessionId;
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
		$arrCookieCircleIds =  $this->filterCids( $this->getCookieCircleIds() );
		
		#TODO 流行圈子ID
		$arrPopularCircleIds = $this->getPopularCircleIds();
		$arrPopularCircleIds = $this->filterCids( array_diff($arrPopularCircleIds, $arrCookieCircleIds) );
		$arrCiecleIds = $this->sortCids($arrCookieCircleIds, $arrPopularCircleIds);
		
		return $arrCiecleIds;
	}
	
	/**
	 * 
	 * 获取圈子相关的视频VID列表
	 */
	private function getCircleRecommendVidList() {
		$arrReturn = array();
		$arrCookieCircleIds = Model_Data_Recommend::getSessionCircles($this->sessionId, 0, 9);
		$arrTmpRecommendMembers = array();
		foreach($arrCookieCircleIds as $tmpCid) {
			$initScore = 25;
			$arrTmpRecommendMembers = Model_Data_Recommend::getCircleVideos($tmpCid, 0, 19, true);
			if($arrTmpRecommendMembers) {
				foreach($arrTmpRecommendMembers as $tmpRow) {
					$this->mergeVidsScore($arrReturn, $tmpRow['vid'],$initScore--);
				}
				
			}
		}
		
		arsort($arrReturn);
		$arrReturn = array_keys( $arrReturn );
		if(!$arrReturn) {
			$arrReturn = $this->getNullSessionCircleAlternative();
		}
		return $arrReturn;
	}
	
	private function getNullSessionCircleAlternative() {
		$arrReturn = array();
		
		$arrWatchedCids = array();
		$arrWatchlaterCids = array();
		$arrTmpVidList = array();
		
		$arrTmpWatchedCids = $this->getCirclesByVid($this->watchedVids);
		$arrTmpWatchlaterCids = $this->getCirclesByVid($this->watchlaterVids);
		
		foreach($arrTmpWatchedCids as $row) {
			$arrWatchedCids = array_merge($arrWatchedCids, $row);
		}
		$arrWatchedCids = array_unique($arrWatchedCids);
		foreach($arrTmpWatchlaterCids as $row) {
			$arrWatchlaterCids = array_merge($arrWatchlaterCids, $row);
		}
		$arrWatchlaterCids = array_unique($arrWatchlaterCids);
		$arrRelateCids = array();
		$arrRelateCids = $this->getRelateCircleIds($arrWatchedCids, $arrWatchlaterCids);
		
		# 从看过的和以后观看取两个圈子
		$arrTmpWatchCids = $this->merge2Arr($arrWatchedCids, $arrWatchlaterCids, 8);
		$arrTmpRecommendMembers = array();
		foreach($arrTmpWatchCids as $tmpCid) {
			$arrTmpRecommendMembers = Model_Data_Recommend::getCircleVideos($tmpCid, 0, 99, true);
			if($arrTmpRecommendMembers) {
				$arrTmpVidList[$tmpCid] = Arr::pluck($arrTmpRecommendMembers, "vid");
			}
		}
		# 相关的
		foreach($arrRelateCids as $tmpCid) {
			$arrTmpRecommendMembers = Model_Data_Recommend::getCircleVideos($tmpCid, 0, 49, true);
			if($arrTmpRecommendMembers) {
				$arrTmpVidList[$tmpCid] = Arr::pluck($arrTmpRecommendMembers, "vid");
			}
		}
		if($arrTmpVidList) {
			$tmpLength = 0;
			$maxLimit = ceil( 100/(count($arrTmpVidList)) )+1;
			for($i=0; $i<$maxLimit; $i++) {
				foreach($arrTmpVidList as $tmpCid => $row) {
					if(isset($row[$i])) {
						$arrReturn[] = $row[$i];
						if($this->debugMode) {
							if(in_array($tmpCid, $arrRelateCids)) {
								$this->logFrom($row[$i], "relate_circle", 4);
							}
						}
					}
				}
				$tmpLength = count($arrReturn);
				if($tmpLength>100) {
					break;
				}
			}
			if($tmpLength>100) {
				$arrReturn = array_slice($arrReturn, 0, 100);
			}
		}
		return $arrReturn;
	}
	
	private function getRelateCircleIds($arrWatchedCids, $arrWatchelaterCids) {
		$arrReturn = array();
		$arrMergeCids = array_merge($arrWatchedCids, $arrWatchelaterCids);
		if(!$arrMergeCids) {
			return $arrReturn;
		}
		$objModelCircleStatRecent = new Model_Data_CircleStatRecent();
		$objModelCircle = new Model_Data_Circle();
		$arrTmpCircles = $objModelCircle->getMulti($this->merge2Arr($arrWatchedCids, $arrWatchelaterCids), array("category"), true);
		if(!$arrTmpCircles) {
			return $arrReturn;
		}
		$arrCategorys = array();
		$arrTmpAppend = array();
		foreach($arrTmpCircles as $row) {
			if($row["category"]) {
				$arrCategorys[] = $row["category"][0];
				$arrTmpAppend = array_merge($arrTmpAppend, $row["category"]);
			}
		}
		$arrCategorys = array_slice( array_unique( array_merge($arrCategorys, $arrTmpAppend) ), 0, 2);
		if(!$arrCategorys) {
			return $arrReturn;
		}
		$sort = array(
			"popularity" => -1,
//			"create_time" => -1
		);
		$limit = count($arrMergeCids)+4;
		$arrCids = array();
		$arrTmpAppend = array();
		$arrTmpCids = array();
		foreach($arrCategorys as $category) {
			$query = array(
				"category" => $category
			);
			$arrTmpCircles = $objModelCircleStatRecent->find($query, array("_id"), $sort, $limit);
			if($arrTmpCircles) {
				$arrTmpCids = array_diff( array_keys($arrTmpCircles), $arrMergeCids);
				$arrCids = array_merge($arrCids, array_slice($arrTmpCids, 0, 2));
				$arrTmpAppend = array_merge($arrTmpAppend, array_slice($arrTmpCids, 2));
			}
		}
		$arrReturn = array_slice( array_unique( array_merge($arrCids, $arrTmpAppend) ), 0, 4);
		return $arrReturn;
	}
	/**
	 * 
	 * 查询视频对应的圈子列表
	 * @param array $vids
	 * @param boolean $keepOrder
	 */
	private function getCirclesByVid($vids, $keepOrder=false) {
		$arrReturn = array();
		if(!$vids) {
			return $arrReturn;
		}
		
		$arrCid = Model_Data_Recommend::getCidByVid($vids);
		if(empty($arrCid )) {
			return $arrReturn;
		}
		$arrRelTmp = array();
		foreach($arrCid as $strVid => $intCid) {
			$arrRelTmp[$strVid] = array($intCid);
		}
		
		if($keepOrder) {
			foreach($vids as $tmpVid) {
				if(isset($arrRelTmp[$tmpVid])) {
					$arrReturn[$tmpVid] = $arrRelTmp[$tmpVid];
				}
			}
		} else {
			$arrReturn = $arrRelTmp;
		}
		return $arrReturn;
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
		if($this->watchedVids) {
			$arrResult = array_diff($arrResult, $this->watchedVids);
		}
		
		if($this->watchlaterVids) {
			$arrResult = array_diff($arrResult, $this->watchlaterVids);
		}
		
		return array_unique( $arrResult );
	}
	
	/**
	 * 
	 * 获取相关视频ID
	 */
	private function batchGetRecommendVids() {
		$arrReturn = array();
		
		$arrTmpVids = $this->watchedVids;
		$i=1;
		foreach($arrTmpVids as $tmpVid) {
			$arrTmpRecommendVids = Model_Data_Recommend::getRecommendVideos($tmpVid);
			$this->mergeVidWeightScore($arrReturn, array_slice($arrTmpRecommendVids, 0, 10), $i, 1);
			$i++;
		}
		$arrTmpVids = $this->watchlaterVids;
		$i=1;
		foreach($arrTmpVids as $tmpVid) {
			$arrTmpRecommendVids = Model_Data_Recommend::getRecommendVideos($tmpVid);
			$this->mergeVidWeightScore($arrReturn, array_slice($arrTmpRecommendVids, 0, 10), $i, 2);
			$i++;
		}
		arsort($arrReturn);
		return array_keys( $arrReturn );
	}
	
	private function mergeVidWeightScore(&$arrVids, $arrNewVids, $timeWeight, $score=1) {
		if(!$arrNewVids) {
			return;
		}
		$weight = 0;
		$length = count($arrNewVids);
		$i=1;
		$arrRecommendVidList = $arrVids;
		foreach($arrNewVids as $tmpVid) {
			$tmpScore = round( ($score/($timeWeight*$i)), 2 ) ;
			if( isset($arrRecommendVidList[$tmpVid]) ) {
				$arrRecommendVidList[$tmpVid]+=$tmpScore;
			} else {
				$arrRecommendVidList[$tmpVid] =$tmpScore;
			}
			$i++;
		}
		$arrVids = $arrRecommendVidList;
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
	 * 获取相关圈子ID
	 */
	private function getCookieCircleIds() {
		$arrReturn = array();
		$arrCookieCircleIds = Model_Data_Recommend::getSessionCircles($this->sessionId);
		if($arrCookieCircleIds) {
			return $arrCookieCircleIds;
		}
		$arrTmpCids = $this->getCirclesByVid($this->watchedVids);
		foreach($arrTmpCids as $row) {
			if($row) {
				$this->mergeCidsScore($arrReturn, $row, 1);
			}
		}
		$arrTmpCids = $this->getCirclesByVid($this->watchlaterVids);
		foreach($arrTmpCids as $row) {
			if($row) {
				$this->mergeCidsScore($arrReturn, $row, 2);
			}
		}
		arsort($arrReturn);
		return array_keys($arrReturn);
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
	 * @param unknown_type $arrCookieCircleIds
	 * @param unknown_type $arrPopularCircleIds
	 */
	private function sortCids($arrCookieCircleIds, $arrPopularCircleIds) {
		$arrReturn = array();
		# 3:1
		while($arrCookieCircleIds && $arrPopularCircleIds) {
			$arrTmp1 = array_slice($arrCookieCircleIds, 0, 3);
			$arrReturn = array_merge( $arrReturn, $arrTmp1 );
			$arrCookieCircleIds = array_slice($arrCookieCircleIds, 3);
			$strTmp = array_shift($arrPopularCircleIds);
			$arrReturn[] = $strTmp;
			
			if($this->debugMode) {
				$this->logFrom($arrTmp1, "circle", 1);
				$this->logFrom($strTmp, "circle", 2);
			}
		}
		if($arrCookieCircleIds) {
			$arrReturn = array_merge($arrReturn, $arrCookieCircleIds);
		}
		if($arrPopularCircleIds) {
			$arrReturn = array_merge($arrReturn, $arrPopularCircleIds);
		} 
		if($this->debugMode) {
			$this->logFrom($arrCookieCircleIds, "circle", 1);
			$this->logFrom($arrPopularCircleIds, "circle", 2);
		}
		return array_slice( $arrReturn, 0, 40);
	}
	
	private function filterCids( $arrCircleIds ) {
		$arrReturn = array();
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
	
	private function merge2Arr($arr1, $arr2, $intLength=2) {
		$arrReturn = array();
		$i = 0;
		$maxLimit = ceil($intLength/2);
		while( $arr1 && $arr2 && $i<$maxLimit ) {
			$arrReturn[] = array_shift($arr1);
			$arrReturn[] = array_shift($arr2);
		}
		if($arr1) {
			$arrReturn = array_merge($arrReturn, $arr1);
		}
		if($arr2) {
			$arrReturn = array_merge($arrReturn, $arr2);
		}
		$arrReturn = array_slice( array_unique($arrReturn), 0, $intLength );
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
		$arrKeys = array_keys( $this->debugData[$type] );
		return array_combine( array_slice($arrKeys, $offset, $count), 
			array_slice( $this->debugData[$type], $offset, $count) );
	}
	
	public function setDebugMode($bolMode) {
		$this->debugMode = $bolMode;
	}
}