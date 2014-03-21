<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 * 示例
 * @author xucongbin
 *
 */
class Controller_Mobile_Upgrade extends Mobile {
	
	public function action_index() {
		$this->ok("hello world");
	}
	
	public function action_get() {
		$arrReturn = array();
		$version = floatval( $this->request->param("app_version", 0.1) );
		$cancleVersion = floatval( $this->request->param("cancle_version", 0) );
		$objModelAppVersion = new Model_Data_Appversion();
		$query = array(
			"version" => array('$gt'=>$version)
		);
		if($cancleVersion) {
//			$query["version"]['$ne'] = $cancleVersion;
		}
		$sort = array("version"=>-1);
		$arrResult = $objModelAppVersion->find($query, array(), $sort, 1);
		if($arrResult) {
			$arrReturn = array_pop($arrResult);
			$arrReturn['source'] = Util::webStorageClusterFileUrl($arrReturn['source']);
			$arrForceList = $arrReturn['force_list'];
			unset($arrReturn['force_list']);
			if(!$arrReturn['is_force'] && $arrForceList) {
				foreach($arrForceList as $forceVersion) {
					if($version==$forceVersion) {
						$arrReturn['is_force'] = 1;
						break;
					}
				}
			}	
			
		}
		
		$this->ok($arrReturn);
	}
}