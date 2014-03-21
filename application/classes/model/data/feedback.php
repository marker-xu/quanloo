<?php 
/**
 * 
 * 用户反馈表
 * @author xucongbin
 *
 */
class Model_Data_Feedback extends Model_Data_MongoCollection {
	
	public function __construct() {
		parent::__construct('web_mongo', 'video_search', 'feedback');
	}
	
	public function addFeedback($content, $arrExtra=array()) {
		$arrParams = array(
			'content' => $content,
			'answers' => (object)array(),
			'client_ip' => Request::$client_ip,
			'create_time' => new MongoDate()
		);
		$arrParams = array_merge($arrParams, $arrExtra);
		try {
			$arrResult = $this->getCollection()->insert($arrParams, true);
		} catch (MongoCursorException $e) {
			JKit::$log->debug("addFeedback failure, code-".$e->getCode().", msg-".$e->getMessage().", param-", $arrParams);
		}
		JKit::$log->debug(__FUNCTION__." result-", $arrResult);
		if($arrResult["ok"]==1) {
			return true;
		}
		
		return false;
		
	}
	
	public function removeFeedbackById($id) {
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
			JKit::$log->debug("removeFeedbackById failure, code-".$e->getCode().", msg-".$e->getMessage().", param-", $arrParams);
		}
		JKit::$log->debug(__FUNCTION__." result-", $arrResult);
		if($arrResult["ok"]==1) {
			return true;
		}
		
		return false;
	}
}