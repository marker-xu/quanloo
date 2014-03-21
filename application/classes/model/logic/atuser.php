<?php
/**
 * @用户的相关功能
 */
class Model_Logic_Atuser extends Model {
	/**
	 * 增加一个suggestion用户
	 * @param int $intOwnUid 自己的用户id
	 * @param string $strOwnNick 自己的用户名
	 * @param int $intSugUid 提示出的用户id
	 * @param string $strSugNick 提示出的用户名
	 * @return bool 成功添加返回true，否则false
	 */
	public function addSugUser($intOwnUid, $intSugUid, $strOwnNick = null, $strSugNick = null) {
		if ($intOwnUid < 1 || $intSugUid < 1) {
			return false;
		}
		if ($strOwnNick === null || $strSugNick === null) {
    	    $modelDataUser = new Model_Data_User();
    	    if ($strOwnNick === null) {
    	    	$strOwnNick = $this->_getNick($intOwnUid, $modelDataUser);
    	    	if ($strOwnNick === false) {
    	    		return false;
    	    	}
    	    }
		    if ($strSugNick === null) {
    	    	$strSugNick = $this->_getNick($intSugUid, $modelDataUser);
    	    	if ($strSugNick === false) {
    	    		return false;
    	    	}
    	    }
		}
		
		//http://10.156.24.10:7333/su?id1=1663499986&id2=1802665157&nm1=好汉饶命&nm2=大是大非
		$arrPostParam = array('id1' => (int) $intOwnUid, 'id2' => (int) $intSugUid, 'nm1' => trim($strOwnNick), 'nm2' => trim($strSugNick));
		try {
			$strJson = RPC::call('atuser_sug', '/su', array('post_vars' => $arrPostParam));
			JKit::$log->debug($strJson, null, __FILE__, __LINE__);
		} catch (Exception $e) {
			JKit::$log->warn($e->getMessage(), $arrPostParam, $e->getFile(), $e->getLine());
			return false;
		}
		if (! $strJson || ! ($arrTmp = json_decode($strJson, true)) || ! $arrTmp['res']) {
			return false;
		}
		
		return true;
	}
	
	protected function _getNick($intUid, $modelDataUser = null) {
		if ($modelDataUser === null) {
			$modelDataUser = new Model_Data_User();
		}
		try {
			$user = $modelDataUser->get($intUid, array('nick'));
		} catch (Exception $e) {
			JKit::$log->warn($e->getMessage(), null, $e->getFile(), $e->getLine());
			return false;
		}
		if (is_array($user) && isset($user['nick'])) {
			return $user['nick'];
		}
		
		return false;		
	}
}