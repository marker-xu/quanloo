<?php
/**
 * 后台管理用的词表管理
 */
class Model_Data_Adattr extends Model_Data_MongoCollection
{
	public function __construct() {
		parent::__construct('web_mongo', 'video_search', 'adattr');
	}

	/**
	 * 添加广告
	 * 
	 * adattr表结构为(_id, ad_pos, ad_mat:{ad_type, 每个ad_type的字段可能不一样，只保证有ad_type字段，其余字段请参考Model_Logic_Adconst
	 * 中AD_TYPE_*系列常量的说明}, ad_starttime, ad_endtime, ad_status, adder_nick, adder_id, addtime)
	 * @param array $arrAdder 添加者的信息
	 * @param array $arrAdInfo 广告信息
	 * @return bool
	 */
	public function add($arrAdder, $arrAdInfo) {
		$arrAdInfo['adder_nick'] = $arrAdder['nick'];
		$arrAdInfo['adder_id'] = $arrAdder['_id'];
		$arrAdInfo['addtime'] = time();

		$mixedRet = null;
		try {
			$mixedRet = $this->insert($arrAdInfo);
		} catch (Exception $e) {
			JKit::$log->warn($e->getMessage(), null, $e->getFile(), $e->getLine());
		}
		if (! isset($mixedRet['ok']) || $mixedRet['ok'] != 1) {
			return false;
		}		

		return true;
	}

	public function remove($id) {
		$condition = array(
			"_id" => new MongoId($id),
		);
		
		$mixedRet = null;
		try {
			$mixedRet = $this->delete($condition);
		} catch (Exception $e) {
			JKit::$log->warn("remove word fail, code-".$e->getCode().", msg-".$e->getMessage().", id-".$id);
		}
		
		if (! isset($mixedRet['ok']) || $mixedRet['ok'] != 1) {
			return false;
		}		

		return true;
	}
}