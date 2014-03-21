<?php 
/**
 * 
 * APP版本列表 
 * @author xucongbin
 *
 */
class Model_Data_Widgetadmin extends Model_Data_MongoCollection {
	
	public function __construct() {
		parent::__construct('web_mongo', 'video_search', 'widget_admin');
	}
	
	public function getById($id) {
		$query = array(
			"_id" => new MongoId($id)
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
	 * @param $type
	 * @param $name
	 * @param $arrData
	 * @param $arrExtra
	 */
	public function addWidget($uid, $type=1, $name, $arrData, $arrExtra=array()) {
		$arrParams = array(
			'name' => $name,
			'user_id' => (int)$uid,
			'type' => $type,
			'data' => $arrData,
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
			return $arrParams["_id"]->{'$id'};
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
	public function modifyById($id, array $arrParams) {
		JKit::$log->debug(__FUNCTION__." id-{$id}, params-", $arrParams);
		if ( !$this->getById($id) ) {
			throw new Model_Data_Exception("widget({$id}) not exists", -3001, NULL);
		}
		if(!isset($arrParams['update_time'])) {
			$arrParams['update_time'] = new MongoDate();	
		}
		
		$query = array("_id" => new MongoId($id) );
		try {
			$arrResult = $this->getCollection()->update($query, array('$set'=>$arrParams), array("safe"=>true));
		} catch (MongoCursorException $e) {
			JKit::$log->warn(__FUNCTION__." failure, code-".$e->getCode().", msg-".$e->getMessage().
			", id-{$id}, param-", $arrParams);
			return false;
		}
		JKit::$log->debug(__FUNCTION__." result-", $arrResult);
		return isset($arrResult["ok"]) && $arrResult["ok"]==1 ? true : false;
	}
	
	public function removeWidget($id) {
		$query = array(
			"_id" => new MongoId($id)
		);
		$options = array(
			"justOne" => true,
			"safe" => true
		);
		try {
			$arrResult = $this->getCollection()->remove($query, $options);
		} catch (MongoCursorException $e) {
			JKit::$log->debug(__FUNCTION__."failure, code-".$e->getCode().", msg-".$e->getMessage().
			", param-", $arrParams);
			return false;
		}
		JKit::$log->debug(__FUNCTION__." result-", $arrResult);
		if($arrResult["ok"]==1) {
			return true;
		}
		
		return false;
	}
	
	
}