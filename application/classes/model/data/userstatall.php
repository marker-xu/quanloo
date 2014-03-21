<?php 

/**
 * 用户统计（最近一段时间）
 * @author xucongbin
 */
class Model_Data_UserStatAll extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('stat', 'video_search', 'user_stat_all', 
            true, 500);
	}
	/**
	 * 
	 * 查询用户统计信息
	 * @param unknown_type $uid
	 */
	public function getByUid($uid) {
		return $this->findOne(array("_id" => intval($uid)));
	}
	
	/**
	 * 
	 * 添加记录
	 * @param int $uid
	 * @param array $arrParams
	 * 
	 * @return boolean
	 */
	public function addStat($uid, $arrParams) {
		$arrParams['_id'] = intval($uid);
		$arrParams = array_merge($this->initParams(), $arrParams);
		try {
			$ret = $this->getCollection()->insert($arrParams);
		} catch (MongoException $e) {
			JKit::$log->warn("addUser failure, code-".$e->getCode().", msg-".$e->getMessage().", param-", $arrParams);
		}
		return $ret;
	}
	
	/**
	 * 
	 * 更新统计
	 * @param int $uid
	 * @param array $arrParams
	 * 
	 * @return boolean
	 */
	public function modifyStatByUid($uid, $arrParams) {
		if ( !$this->getByUid($uid) ) {
			throw new Model_Data_Exception("stat user({$uid}) not exists", NULL, -3001);
		}
		
		$query = array("_id" => intval($uid) );
		try {
			$ret = $this->getCollection()->update($query, array('$inc'=>$arrParams));
		} catch (MongoCursorException $e) {
			JKit::$log->warn("modifyUser failure, code-".$e->getCode().", msg-".$e->getMessage().", uid-{$uid}, param-", $arrParams);
		}
		return $ret;
	}
	
	
	private function initParams() {
		return array(
			'subscribed_circle_count' => 0,
			'like_count' => 0,
			'watch_count' => 0,
			'comment_count' => 0,
			'activity' => 0,
		);
	}
}