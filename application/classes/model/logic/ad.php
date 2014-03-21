<?php
class Model_Logic_Ad extends Model {
	/**
	 * 随机获取指定广告位的广告
	 * @param int $intAdPos 广告位编号
	 * @param int $intCount 返回广告个数
	 * @return array 返回广告信息数组，具体字段参考Model_Data_Adattr::add()的说明
	 */
	public function getRandAdByPos($intAdPos, $intCount = 1) {
		$objDataAdattr = new Model_Data_Adattr();
		$objCurTime = new MongoInt64(time());
		$arrCond = array(
			'ad_status' => Model_Logic_Adconst::AD_STATUS_VALID,
			'ad_pos' => (int) $intAdPos,
 			'ad_starttime' => array('$lt' => $objCurTime),
			'ad_endtime' => array('$gt' => $objCurTime),
		);
		
		$arrData = null;
		try {
			$arrData = $objDataAdattr->find($arrCond, array(), null, 100);
		} catch (Exception $e) {
			JKit::$log->warn($e->getMessage(), func_get_args(), $e->getFile(), $e->getLine());
		}
		if (empty($arrData)) {
			JKit::$log->info('No Ad found', func_get_args() + $arrCond);
			return array();
		}
		
		if ($intCount < 2) {
			$strKey = array_rand($arrData);
			$arrData = $arrData[$strKey];
		} else {
			$arrSelectedKey = array_rand($arrData, $intCount);
			$arrTmp = array();
			foreach ($arrSelectedKey as $v) {
				$arrTmp[] = $arrData[$v];
			}
			$arrData = $arrTmp;
		}

		return $arrData;
	}
}