<?php 
/**
 * 
 * 友情链接表
 * @author xucongbin
 *
 */
class Model_Data_Link extends Model_Data_MongoCollection {
	
	const TYPE_TEXT = 0;
	
	const TYPE_IMAGE = 1;
	
	public function __construct() {
		parent::__construct('web_mongo', 'video_search', 'link');
	}
	
	public function addLink($url, $type=0, $text='', $image='', $arrExtra=array()) {
		$arrParams = array(
			'url' => $url,
			'type' => $type,
			'title' => $text,
			'logo' => $image,
			'create_time' => new MongoDate(),
		);
		$arrParams['update_time'] = $arrParams['create_time'];
		$arrParams = array_merge($arrParams, $arrExtra);
		if( !isset($arrParams['order_no']) ) {
			$arrParams['order_no'] = $this->getMaxOrderNo();
		}
		try {
			$arrResult = $this->getCollection()->insert($arrParams, true);
		} catch (MongoCursorException $e) {
			JKit::$log->debug(__FUNCTION__." failure, code-".$e->getCode().", msg-".$e->getMessage().", param-", $arrParams);
		}
		JKit::$log->debug(__FUNCTION__." result-", $arrResult);
		if($arrResult["ok"]==1) {
			return true;
		}
		
		return false;
		
	}
	
	/**
	 * 
	 * 更新用户信息
	 * @param int $uid
	 * @param array $arrParams
	 * @throws Model_Data_Exception
	 */
	public function modifyLinkById($id, array $arrParams) {
		JKit::$log->debug(__FUNCTION__." id-{$id}, params-", $arrParams);
		if(!isset($arrParams['update_time'])) {
			$arrParams['update_time'] = new MongoDate();	
		}
		$query = array("_id" => new MongoId($id) );
		try {
			$arrResult = $this->getCollection()->update($query, array('$set'=>$arrParams), array("safe"=>true));
		} catch (MongoCursorException $e) {
			JKit::$log->warn("modifyUser failure, code-".$e->getCode().", msg-".$e->getMessage().", uid-{$uid}, param-", $arrParams);
		}
		JKit::$log->debug(__FUNCTION__." result-", $arrResult);
		return isset($arrResult["ok"]) && $arrResult["ok"]==1 ? true : false;
	}
	
	public function removeLinkById($id) {
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
			JKit::$log->debug(__FUNCTION__." failure, code-".$e->getCode().", msg-".$e->getMessage().", param-", $arrParams);
		}
		JKit::$log->debug(__FUNCTION__." result-", $arrResult);
		if($arrResult["ok"]==1) {
			return true;
		}
		
		return false;
	}
	
	public function getLinkById($id) {
		$query = array(
			"_id" => new MongoId($id)
		);
		return $this->findOne($query);
	}
	
	public function getMaxOrderNo() {
		$maxNo = 1;
		$arr = $this->find(array(), array("order_no"), array(
			"order_no" => -1
		), 1);
		if($arr && isset($arr[0])) {
			$maxNo = intval($arr[0]['order_no'])+1;
		}
		
		return $maxNo;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $fileName 图片路径
	 * 
	 * @return boolean|array(
	 * 	'group_name' => 组名,
	 * 	'filename' =>图片存储相对路径
	 * )
	 */
	public function uploadImage($fileName, $ext=NULL) {
		JKit::$log->debug(__FUNCTION__." file-{$fileName}");
		$fdfs = new FastDFS(FASTDFS_CLUSTER_WEB_STORAGE);
		$ret = $fdfs->storage_upload_by_filename($fileName, $ext);
		JKit::$log->debug(__FUNCTION__." errno-".$fdfs->get_last_error_no().", msg-".$fdfs->get_last_error_info().", ret-", $ret);
		return $ret;
	}
	/**
	 * 
	 * 删除头像
	 * @param string $groupName
	 * @param string $fileName
	 * 
	 * @return boolean
	 */
	public function removeImage($groupName, $fileName) {
		JKit::$log->debug(__FUNCTION__." group-{$groupName}, file-{$fileName}");
		$fdfs = new FastDFS(FASTDFS_CLUSTER_WEB_STORAGE);
		$ret = $fdfs->storage_delete_file($groupName, $fileName);
		JKit::$log->debug(__FUNCTION__." errno-".$fdfs->get_last_error_no().", msg-".$fdfs->get_last_error_info().", ret-".$ret);
		return $ret;
	}
}