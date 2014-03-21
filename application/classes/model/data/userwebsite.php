<?php 
/**
 * 
 * 使用widget的网站
 * @author xucongbin
 *
 */
class Model_Data_Userwebsite extends Model_Data_MongoCollection {
	
	private static $domainTypes = array(
		 101 => "音乐影视", 
		 102 => "休闲娱乐",
		 103 => "游戏",
		 104 => "网络服务应用",
		 105 => "博客",
		 106 => "网址导航",
		 107 => "软件与硬件",
		 108 => "数码及手机",
		 109 => "教学及考试",
		 110 => "医疗保健",
		 111 => "女性时尚",
		 112 => "社交网络",
		 113 => "在线社区",
		 114 => "生活服务",
		 115 => "房产家居",
		 116 => "汽车",
		 117 => "交通旅游",
		 118 => "体育运动",
		 119 => "金融投资",
		 120 => "垂直行业",
		 121 => "新闻媒体",
		 122 => "人文艺术",
		 123 => "小说",
		 124 => "人才招聘",
		 125 => "网络购物",
		 126 => "其他"
	);
	
	public function __construct() {
		parent::__construct('web_mongo', 'video_search', 'user_website');
	}
	
	public static function getDomainTypes() {
		return self::$domainTypes;
	}
	
	public function getWebsiteById( $id ) {
		$query = array(
			"_id" => new MongoId($id)
		);
		$arrRow = $this->findOne($query);
		if($arrRow) {
			$arrRow["_id"] = $arrRow["_id"]->{'$id'};
		}
		
		return $arrRow;
	}
	
	public function getWebsiteByUserId( $intUid ) {
		$query = array(
			"user_id" => (int)$intUid
		);
		$arrRow = $this->findOne($query);
		if($arrRow) {
			$arrRow["_id"] = $arrRow["_id"]->{'$id'};
		}
		
		return $arrRow;
	}
	
	
	/**
	 * 
	 * 添加widget应用
	 * @param $uid
	 * @param $strDomain 网站域名
	 * @param $name
	 * @param $arrExtra array(
	 *  type => 网站类型,
	 * 	desc => 网站描述,
	 * 	icp => 网站备案
	 * 	
	 * )
	 */
	public function addWebsite($uid, $strDomain, $name, $arrExtra=array()) {
		$arrParams = array(
			'name' => $name,
			'user_id' => (int)$uid,
			'domain' => $strDomain,
			'type' => array(126),
			'desc' => '',
			'icp' => '',
			'create_time' => new MongoDate()
		);
		$arrParams['update_time'] = $arrParams['create_time'];
		$arrParams = array_merge($arrParams, $arrExtra);
		try {
			$arrResult = $this->getCollection()->insert($arrParams, true);
		} catch (MongoCursorException $e) {
			JKit::$log->warn(__FUNCTION__."failure, code-".$e->getCode().", msg-".$e->getMessage().
			", param-", $arrParams);
			return false;
		}
		JKit::$log->debug(__FUNCTION__." result-", $arrResult);
		if($arrResult["ok"]==1) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * 
	 * 更新widget信息
	 * @param string $id
	 * @param array $arrParams
	 * @throws Model_Data_Exception
	 */
	public function modifyByUid($uid, array $arrParams) {
		JKit::$log->debug(__FUNCTION__." uid-{$uid}, params-", $arrParams);
		if ( !$this->getWebsiteByUserId($uid) ) {
			throw new Model_Data_Exception("user({$uid}) not exists", -3001, NULL);
		}
		if(!isset($arrParams['update_time'])) {
			$arrParams['update_time'] = new MongoDate();	
		}
		
		$query = array("user_id" => intval($uid) );
		try {
			$arrResult = $this->getCollection()->update($query, array('$set'=>$arrParams), array("safe"=>true));
		} catch (MongoCursorException $e) {
			JKit::$log->warn(__FUNCTION__." failure, code-".$e->getCode().", msg-".$e->getMessage().
			", uid-{$uid}, param-", $arrParams);
			return false;
		}
		JKit::$log->debug(__FUNCTION__." result-", $arrResult);
		return isset($arrResult["ok"]) && $arrResult["ok"]==1 ? true : false;
	}
	
	public function removeWebsite($uid) {
		$query = array(
			"user_id" => intval($uid) 
		);
		$options = array(
			"justOne" => true,
			"safe" => true
		);
		try {
			$arrResult = $this->getCollection()->remove($query, $options);
		} catch (MongoCursorException $e) {
			JKit::$log->debug(__FUNCTION__."failure, code-".$e->getCode().", msg-".$e->getMessage().
			", uid-". $uid);
			return false;
		}
		JKit::$log->debug(__FUNCTION__." result-", $arrResult);
		if($arrResult["ok"]==1) {
			return true;
		}
		
		return false;
	}
	
	
}