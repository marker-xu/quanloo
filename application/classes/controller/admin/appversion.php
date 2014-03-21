<?php

class Controller_Admin_Appversion extends Controller_Admin
{
	private $objModelAppversion;
	
	public function before()
	{
	    parent::before();
	    
	    $this->_checkPrivilege(self::RES_APP_RELEASE, self::PRIV_VIEW);
	    
	    $this->objModelAppversion = new Model_Data_Appversion();
	}

	public function action_index()
	{
	    $query = array();
	    $sort = array("version"=>-1);
	    $arrList = $this->objModelAppversion->find($query, array(), $sort);
	    
	    $this->template->set("app_list", $arrList);
	    
	}
	
	public function action_add() {
	    $this->_checkPrivilege(self::RES_APP_RELEASE, self::PRIV_ADD);
	    
		$arrPost = $this->request->post();
		if( !$arrPost['version'] || !$arrPost['version_name'] || !$arrPost['desc']) {
			$this->response->alertBack("缺少参数");
		}
		$version = floatval($arrPost['version']);
		
		$source = $_FILES['source'];
		$validAvatar = $this->validAvatar($source);
		if( !$validAvatar['ok'] ) {
			$this->response->alertBack($validAvatar['msg']);
		}
		
		#TODO 检查版本是否重复
		$group = $this->objModelAppversion->uploadSource($source['tmp_name'], "apk");
		if(!$group) {
			$this->response->alertBack("文件上传失败");
		}
		$arrPost = array_map('trim', $arrPost);
		$arrForceList = $this->filterVersions( explode(",", $arrPost['force_list']) );
		$strVersionName = trim( $arrPost['version_name'] );
		$arrExtra = array(
			"desc" => $arrPost['desc'],
			"is_force" => isset($arrPost['is_force']) ? intval($arrPost['is_force']):0,
			"size" => $source['size'],
			"force_list" => $arrForceList
		);
		
		$res = $this->objModelAppversion->addAppVersion($version, $strVersionName, 
			$group['group_name']."/".$group['filename'], $arrExtra);
		if($res) {
			$this->response->alertGo("添加成功", "/admin_appversion/index");
		} else {
			$this->objModelAppversion->removeSource($group['group_name'], $group['filename']);
			$this->response->alertBack("添加失败");
		}
	}
	
	public function action_edit() {
	    $this->_checkPrivilege(self::RES_APP_RELEASE, self::PRIV_MODIFY);
	    
		$arrPost = $this->request->post();
		$arrPost = array_map('trim', $arrPost);
		if(!$arrPost['version'] || !$arrPost['version_name'] || !$arrPost['desc']) {
			$this->response->alertBack("缺少参数");
		}
		$version = floatval($arrPost['version']);
		$id = $arrPost["id"];
		$arrInfo = $this->objModelAppversion->getById($id);
		$arrForceList = $this->filterVersions( explode(",", $arrPost['force_list']) );
		$arrParams = array(
			"version_name" => $arrPost['version_name'],
			"version" => $version,
			"desc" => $arrPost['desc'],
			"is_force" => isset($arrPost['is_force']) ? intval($arrPost['is_force']):0,
			"force_list" => $arrForceList
		);
		#TODO 检查版本是否重复
		$isUploadImage = false;
		$source = $_FILES['source'];
		
		if($source['tmp_name']) {
			$validAvatar = $this->validAvatar($source);
			if( !$validAvatar['ok'] ) {
				$this->response->alertBack($validAvatar['msg']);
			}
			$group = $this->objModelAppversion->uploadSource($source['tmp_name'], "apk");
			if(!$group) {
				$this->response->alertBack("文件上传失败");
			}
			$arrParams['source'] = $group['group_name']."/".$group['filename'];
			$arrParams['size'] = $source['size'];
			$isUploadImage = true;
		} 
			
		
		$res = $this->objModelAppversion->modifyById($id, $arrParams);
		if($res) {
			if($isUploadImage) {
				$arrGroup = explode("/", $arrInfo['source'], 2);
				$this->objModelAppversion->removeSource($arrGroup[0], $arrGroup[1]);
			}
			$this->response->alertGo("编辑成功", "/admin_appversion/index");
		} else {
			if($isUploadImage) {
				$this->objModelAppversion->removeSource($group['group_name'], $group['filename']);
			}
			$this->response->alertBack("编辑失败");
		}
		
	}
	
	public function action_remove() {
	    $this->_checkPrivilege(self::RES_APP_RELEASE, self::PRIV_DELETE);
	    
		$id = $this->request->query("id");
		if(!$id) {
			$this->err();
		}
		$arr = $this->objModelAppversion->getById($id);
		if(!$arr) {
			$this->err();
		}
		
		$res = $this->objModelAppversion->removeAppVersion($id);
		JKit::$log->info("appversion remove, id-".$id);
		if($res) {
			$arrGroup = explode("/", $arr['source'], 2);
			$this->objModelAppversion->removeSource($arrGroup[0], $arrGroup[1]);
			
			$this->ok();
		} else {
			$this->err();
		}
		
	}
	
	/**
	 * @param array $avatar Array (
            [name] => Water lilies.jpg
            [type] => image/jpeg
            [tmp_name] => /tmp/phpeFK8jV
            [error] => 0
            [size] => 83794
        )
	 */
	private function validAvatar($avatar) {
		$arrReturn = array(
			'ok' => false,
			'msg' => ''
		);
		if(! $avatar['tmp_name']) {
			$arrReturn['msg'] = "文件不能为空";
			return $arrReturn;
		}
		
		if($avatar['error'] !== UPLOAD_ERR_OK) {
			$arrReturn['msg'] = "文件上传失败";
			return $arrReturn;
		}
		
//		$arrImgAttr = getimagesize($avatar['tmp_name']);
//		$arrValidType = array( IMAGETYPE_GIF => true );
//		if(! is_array($arrImgAttr) || ! isset($arrValidType[$arrImgAttr[2]])) {
//			$arrReturn['msg'] = "文件格式错误";
//			return $arrReturn;
//		}
//		$arrReturn['attr'] = $arrImgAttr;

		$maxSizeLimit = 1024*1024*8;
		if( $avatar['size'] > $maxSizeLimit ) {
			$arrReturn['msg'] = "文件大小不能超过8M";
			return $arrReturn;
		} 
		$arrReturn['ok'] = true;
		return $arrReturn;
	}
	
	private function filterVersions($arrVersions) {
		$arrReturn = array();
		if(!$arrVersions) {
			return $arrReturn;
		}
		foreach($arrVersions as $tagTmp) {
			$tagTmp = round($tagTmp, 1);
			if($tagTmp) {
				$arrReturn[] = $tagTmp;
			}
		}
		return array_unique($arrReturn);
	}
}
