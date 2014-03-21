<?php 
/**
 * 
 * APP版本列表 
 * @author xucongbin
 *
 */
class Model_Data_Appversion extends Model_Data_MongoCollection {
	
	public function __construct() {
		parent::__construct('web_mongo', 'video_search', 'app_version');
	}
	
	public function getInfoByVersion($version, $excludeId=null) {
		$query = array(
			"version" => floatval($version)
		);
		if( $excludeId!==NULL ) {
			$query["_id"] = array('$not'=>new MongoId($excludeId));
		}
		return $this->findOne($query);
	}
	
	public function getById($id) {
		$query = array(
			"_id" => new MongoId($id)
		);
		return $this->findOne($query);
	}
	
	
	/**
	 * 
	 * Enter description here ...
	 * @param float $version 版本号
	 * @param string $versionName 版本名称
	 * @param string $source 资源地址
	 * @param array $arrExtra array(
	 * 	is_force => 是否强制,
	 * 	desc => 描述
	 * )
	 * 
	 * @return boolean
	 */
	public function addAppVersion($version, $versionName, $source, $arrExtra=array()) {
		$arrParams = array(
			'version' => (float)$version,
			'version_name' => $versionName,
			'source' => $source,
			'desc' => '',
			'is_force' => 0,
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
	 * 更新 版本信息
	 * @param string $id
	 * @param array $arrParams
	 * @throws Model_Data_Exception
	 */
	public function modifyById($id, array $arrParams) {
		JKit::$log->debug(__FUNCTION__." id-{$id}, params-", $arrParams);
		if ( !$this->getById($id) ) {
			throw new Model_Data_Exception("appversion({$id}) not exists", -3001, NULL);
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
	
	public function removeAppVersion($id) {
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
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $fileName 资源路径
	 * 
	 * @return boolean|array(
	 * 	'group_name' => 组名,
	 * 	'filename' =>资源存储相对路径
	 * )
	 */
	public function uploadSource($fileName, $ext=NULL) {
		JKit::$log->debug(__FUNCTION__." file-{$fileName}");
		$fdfs = new FastDFS(FASTDFS_CLUSTER_WEB_STORAGE);
		$ret = $fdfs->storage_upload_by_filename($fileName, $ext);
		JKit::$log->debug(__FUNCTION__." errno-".$fdfs->get_last_error_no().", msg-".$fdfs->get_last_error_info().
		", ret-", $ret);
		return $ret;
	}
	/**
	 * 
	 * 删除资源
	 * @param string $groupName
	 * @param string $fileName
	 * 
	 * @return boolean
	 */
	public function removeSource($groupName, $fileName) {
		JKit::$log->debug(__FUNCTION__." group-{$groupName}, file-{$fileName}");
		$fdfs = new FastDFS(FASTDFS_CLUSTER_WEB_STORAGE);
		$ret = $fdfs->storage_delete_file($groupName, $fileName);
		JKit::$log->debug(__FUNCTION__." errno-".$fdfs->get_last_error_no().", msg-".$fdfs->get_last_error_info().
		", ret-".$ret);
		return $ret;
	}
}