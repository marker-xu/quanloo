<?php 

/**
 * 用户
 * @author xucongbin
 */
class Model_Data_User extends Model_Data_MongoCollection
{
    const MEDAL_SUBSCRIBE_CIRCLE = 1; // 关注圈子 
    const MEDAL_INVITE_FRIEND = 2; // 邀请好友
    const MEDAL_CREATE_CIRCLE = 3; //  成功创建圈子
    
	public function __construct()
	{
        parent::__construct('web_mongo', 'video_search', 'user');
	}

	/**
	 * 查询单个用户的信息
	 * @param string $id
	 * @param array $fields
	 * @return array|null
	 */
	public function get($id, $fields = array())
	{
	    return $this->findOne(array('_id' => intval($id)), $fields);
	}
	
	/**
	 * 查询多个用户的信息
	 * @param array $ids
	 * @param array $fields
	 * @param bool $keepOrder 是否保持传入参数中ID的顺序
	 * @return array
	 */
	public function getMulti($ids, $fields = array(), $keepOrder = false) 
	{
	    if (!$ids) {
	        return array();
	    }
	    $users = $this->find(array('_id' => array('$in' => $ids)), $fields);
	    if ($keepOrder) {
	        $tmp = array();
	        foreach ($ids as $id) {
	            if (isset($users[$id])) {
	                $tmp[$id] = $users[$id];
	            }
	        }
	        $users = $tmp;
	    }
	    return $users;
	}
	
	/**
	 * 根据昵称查询多个用户的信息
	 * @param array $arrNick
	 * @param array $fields
	 * @param bool $keepOrder 是否保持传入参数中ID的顺序
	 * @return array
	 */
	public function getMultiByNick($arrNick, $fields = array())
	{
	    if (empty($arrNick)) {
	        return array();
	    }
	    $users = $this->find(array('nick' => array('$in' => $arrNick)), $fields);

	    return $users;
	}
		
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $account
	 * @param unknown_type $arrParams
	 * 
	 * @reutrn int|boolean uid
	 */
	public function addUser($email, array $arrParams) {
		JKit::$log->debug(__FUNCTION__." email-{$email}, params-", $arrParams);
		if(!isset($arrParams['_id'])) {
			$arrParams['_id'] = $this->getUniqueValue("user");
		} 
		$arrParams['email'] = strtolower($email);
		if( !isset($arrParams['nick']) ) {
			$arrParams['nick'] = $arrParams['email'];
		}
		
		if( !isset($arrParams['create_time']) ) {
			$arrParams['create_time'] = new MongoDate();
		}
		$arrParams['update_time'] = $arrParams['create_time'];
		$arrParams['last_login_time'] = $arrParams['create_time'];
		if( !isset($arrParams['last_login_ip']) ) {
			$arrParams['last_login_ip'] = Request::$client_ip;
		}
		if( !isset($arrParams['is_email_verified']) ) {
			$arrParams['is_email_verified'] = 0;
		}
		if( !isset($arrParams['medal']) ) {
			$arrParams['medal'] = array();
		}
		if( !isset($arrParams['tags']) ) {
			$arrParams['tags'] = array();
		}
		if( !isset($arrParams['accept_subscribe_email']) ) {
			$arrParams['accept_subscribe_email'] = 1;
		}
		
		try {
			$arrResult = $this->getCollection()->insert($arrParams, true);
		} catch (MongoCursorException $e) {
//			echo "addUser failure, code-".$e->getCode().", msg-".$e->getMessage()."<br>\n";
			JKit::$log->warn("addUser failure, code-".$e->getCode().", msg-".$e->getMessage().", param-", $arrParams);
			
			return false;
		}
		JKit::$log->debug(__FUNCTION__." result-", $arrResult);
		if($arrResult["ok"]==1) {
			return $arrParams['_id'];
		}
		
		return false;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param string $email
	 * @param int $excludeId
	 */
	public function getByEmail($email, $excludeId=NULL) {
		$query = array(
			'email' => strtolower($email)
		);
		if ($excludeId!==NULL) {
			$query['_id'] = array('$ne'=>intval($excludeId));
		}
		return $this->findOne($query);
	}
	/**
	 * 
	 * Enter description here ...
	 * @param string $nick
	 * @param int $excludeId
	 */
	public function getByNick($nick, $excludeId=NULL) {
		$query = array(
			'nick' => $nick
		);
		if ($excludeId!==NULL) {
			$query['_id'] = array('$ne'=>intval($excludeId));
		}
		return $this->findOne($query);
	}
	/**
	 * 
	 * 更新用户信息
	 * @param int $uid
	 * @param array $arrParams
	 * @throws Model_Data_Exception
	 */
	public function modifyById($uid, array $arrParams) {
		JKit::$log->debug(__FUNCTION__." uid-{$uid}, params-", $arrParams);
		if ( !$this->get($uid) ) {
			throw new Model_Data_Exception("user({$uid}) not exists", -3001, NULL);
		}
		if(!isset($arrParams['update_time'])) {
			$arrParams['update_time'] = new MongoDate();	
		}
		
		$query = array("_id" => intval($uid) );
		try {
			$arrResult = $this->getCollection()->update($query, array('$set'=>$arrParams), array("safe"=>true));
		} catch (MongoCursorException $e) {
			JKit::$log->warn("modifyUser failure, code-".$e->getCode().", msg-".$e->getMessage().", uid-{$uid}, param-", $arrParams);
		}
		JKit::$log->debug(__FUNCTION__." result-", $arrResult);
		return isset($arrResult["ok"]) && $arrResult["ok"]==1 ? true : false;
	}
	
	private function getUniqueCode($collectionName, $step=1) {
		$strCode = 'db.unique_coll.findAndModify({query:{name:"'.$collectionName.
		'"}, update:{$inc:{max:'.intval($step).'}}, new:true, upsert:1}).max';
		
		return $strCode;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $collectionName
	 * @param int $step
	 * 
	 * @return int;
	 */
	public function getUniqueValue($collectionName, $step=1) {
		$strCode = $this->getUniqueCode($collectionName, $step);
		$arrReturn =  Database::instance("web_mongo")->getMongoDB('video_search')->execute($strCode);
		
		return $arrReturn['retval'];
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
	public static function uploadAvatar($fileName, $ext=NULL) {
		JKit::$log->debug(__FUNCTION__." file-{$fileName}");
		$fdfs = new FastDFS(FASTDFS_CLUSTER_USER_AVATAR);
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
	public static function removeAvatar($groupName, $fileName) {
		JKit::$log->debug(__FUNCTION__." group-{$groupName}, file-{$fileName}");
		$fdfs = new FastDFS(FASTDFS_CLUSTER_USER_AVATAR);
		$ret = $fdfs->storage_delete_file($groupName, $fileName);
		JKit::$log->debug(__FUNCTION__." errno-".$fdfs->get_last_error_no().", msg-".$fdfs->get_last_error_info().", ret-".$ret);
		return $ret;
	}
    
	/**
	 * 颁发勋章
	 * @param int $userId
	 * @param string|array $medal 可以一次颁发多个，勋章定义见Model_Data_User里的勋章常量
	 * @return bool
	 */
    public function awardMedal($userId, $medal)
    {
        return $this->addToSet(array('_id' => $userId), 'medal', $medal);
    }
    
	/**
	 * 取消勋章
	 * @param int $userId
	 * @param string|array $medal 可以一次取消多个
	 * @return bool
	 */
    public function unawardMedal($userId, $medal)
    {
        return $this->removeFromSet(array('_id' => $userId), 'medal', $medal);
    }
    
	/**
	 * 用户是否拥有某个勋章
	 * @param int $userId
	 * @param string $medal
	 * @return bool
	 */
    public function isAwardedMedal($userId, $medal)
    {
        return $this->count(array('_id' => $userId, 'medal' => $medal)) > 0;
    }
    
	/**
	 * 用户拥有的所有勋章
	 * @param int $userId
	 * @return array
	 */
    public function medals($userId)
    {
        $docs = $this->find(array('_id' => $userId), array('medal'));
        if ($docs) {
            $doc = current($docs);
            return isset($doc['medal']) ? $doc['medal'] : array();
        } else {
            return array();
        }
    }
    
}