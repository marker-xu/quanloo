<?php
/**
 * 操作频率控制
 */
class Model_Logic_Acts extends Model {
	const REDIS_DB = 3;
	const ERR_STATUS = 'sys.acts';
	const CMD_VIDEO_MOOD = 1; //给视频标心情
	const CMD_VIDEO_TAG = 2; //给视频的tag墙顶tag
	
	/**
	 * 该操作在当前自然天中是否可以执行
	 * 
	 * @param int $intCmd 命令类型
	 * @param mixed $mixedKey 用于标识操作的key
	 * @param int $intThreshold 上限阈值
	 * @return bool 允许返回true，否则false
	 */
	public function day($intCmd, $mixedKey, $intThreshold = 1) {
		$strKey = $this->_makeKey($intCmd, $mixedKey);
		$bolRet = true;
		try {
			$bolRet = $this->_allow($strKey, $intThreshold);
		} catch (Exception $e) {
			//do nothing
		}
		
		return $bolRet;
	}
	
	public function dayUpdate($intCmd, $mixedKey, $intVal = 1) {
		$strKey = $this->_makeKey($intCmd, $mixedKey);
		$intExpireTime = strtotime(date("Y-m-d 23:59:59"));
		$bolRet = false;
		try {
			$objRedis = $this->_getRedis($strKey);
			if (! $objRedis) {
				return $bolRet;
			}
			$objRedis->incrBy($strKey, $intVal);
			$objRedis->expireAt($strKey, $intExpireTime);			
		} catch (Exception $e) {
			//do nothing
		}
		
		return true;
	}
	
	protected function _allow($strKey, $intThreshold) {
		$objRedis = $this->_getRedis($strKey);
		if (! $objRedis) {
			throw new Exception('server connect error');
		}
		$intRet = $objRedis->get($strKey);
		if ($intRet && $intRet >= $intThreshold) {
			return false;
		}
		
		return true;
	}
	
	protected function _makeKey($intCmd, $mixedKey) {
		if (! is_string($mixedKey)) {
			$mixedKey = serialize($mixedKey);
		}
		$strKey = "acts:{$intCmd}:{$mixedKey}";

		return $strKey;
	}
	
	protected function _getRedis($strKey) {
		$cache = Cache::instance('web');
		return $cache->getRedisDB(self::REDIS_DB, $strKey);
	}
}
