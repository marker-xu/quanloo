<?php

class Controller_Admin_Link extends Controller_Admin
{
	private $objModelLink;
	public function before()
	{
	    parent::before();
	    
	    $this->objModelLink = new Model_Data_Link();
	    
	    $this->_checkPrivilege(self::RES_FRIEND_LINK, self::PRIV_VIEW);
	}

	public function action_index()
	{
	    $arrText = array( );
	    $arrImage = array( );
	    $query = array();
	    $sort = array("sort_no"=>1);
	    $arrList = $this->objModelLink->find($query, array(), $sort);
	    
		if($arrList) {
			foreach($arrList as $row) {
				if($row['type']==Model_Data_Link::TYPE_IMAGE) {
					$arrImage[] = $row;
				}  else {
					$arrText[] = $row;
				}
			}
		}
	    $this->template->set("text_list", $arrText);
	    $this->template->set("image_list", $arrImage);
	    
	}
	
	public function action_image() {
	    $this->_checkPrivilege(self::RES_FRIEND_LINK, self::PRIV_ADD);
	    
		$arrPost = $this->request->post();
		if( !JKit_Valid::url($arrPost['url']) ) {
			$this->response->alertBack("URL格式错误");
//			$this->err(NULL, array('url'=>"格式错误"), NULL, NULL, "usr.submit.valid");
		}
		$avatar = $_FILES['image'];
		$validAvatar = $this->validAvatar($avatar);
		if( !$validAvatar['ok'] ) {
			$this->response->alertBack($validAvatar['msg']);
//			$this->err(NULL, array('image'=>$validAvatar['msg']), NULL, NULL, "usr.submit.valid");
		}
		$group = $this->objModelLink->uploadImage($avatar['tmp_name'], "png");
		if(!$group) {
			$this->response->alertBack("图片上传失败");
//			$this->err(NULL, array('image'=>"图片上传失败"), NULL, NULL, "usr.submit.valid");
		}
		$arrPost = array_map('trim', $arrPost);
		$url = $arrPost['url'];
		$res = $this->objModelLink->addLink($url, Model_Data_Link::TYPE_IMAGE, '', 
		$group['group_name']."/".$group['filename']);
		if($res) {
			$this->response->alertGo("添加成功", "/admin_link/index");
		} else {
			$this->objModelLink->removeImage($group['group_name'], $group['filename']);
			$this->response->alertBack("添加失败");
		}
	}
	
	public function action_text() {
	    $this->_checkPrivilege(self::RES_FRIEND_LINK, self::PRIV_ADD);
	    
		$arrRules = array(
			'@title' => array(
                    'datatype' => 'text',
                    'reqmsg' => '文字',
					'minlength' => 2,
            		'maxlength' => 10
            ),
		);
		$arrPost = $this->request->post();
		$objValidation = new Validation($arrPost, $arrRules);
		 
		if (! $this->valid($objValidation)) {
		    return;
		}
		if( !JKit_Valid::url($arrPost['url']) ) {
			$this->err(NULL, array('url'=>"格式错误"), NULL, NULL, "usr.submit.valid");
		}
		$arrPost = array_map('trim', $arrPost);
		$title = $arrPost['title'];
		$url = $arrPost['url'];
		$res = $this->objModelLink->addLink($url, Model_Data_Link::TYPE_TEXT, $title);
		if($res) {
			$this->ok();
		} else {
			$this->err();
		}
		
	}
	
	public function action_edit() {
	    $this->_checkPrivilege(self::RES_FRIEND_LINK, self::PRIV_MODIFY);
	    
		$arrPost = $this->request->post();
	
		if( !JKit_Valid::url($arrPost['url']) ) {
			$this->response->alertBack("URL格式错误");
		}
		$arrPost = array_map('trim', $arrPost);
		$id = $arrPost["id"];
		$arrInfo = $this->objModelLink->getLinkById($id);
		$arrParams = array(
			"url" => $arrPost['url'],
		);
		$isImage = $arrPost['link_type'];
		$isUploadImage = false;
		if($isImage) {
			$avatar = $_FILES['image'];
			if($avatar['tmp_name']) {
				$validAvatar = $this->validAvatar($avatar);
				if( !$validAvatar['ok'] ) {
					$this->response->alertBack($validAvatar['msg']);
				}
				$group = $this->objModelLink->uploadImage($avatar['tmp_name'], "png");
				if(!$group) {
					$this->response->alertBack("图片上传失败");
				}
				$arrParams['logo'] = $group['group_name']."/".$group['filename'];
				$isUploadImage = true;
			} 
			
		} else {
			$arrParams['title'] = $arrPost['title'];
		}
		
		$res = $this->objModelLink->modifyLinkById($id, $arrParams);
		if($res) {
			if($isUploadImage) {
				$arrGroup = explode("/", $arrInfo['logo'], 2);
				$this->objModelLink->removeImage($arrGroup[0], $arrGroup[1]);
			}
			$this->response->alertGo("编辑成功", "/admin_link/index");
		} else {
			if($isUploadImage) {
				$this->objModelLink->removeImage($group['group_name'], $group['filename']);
			}
			$this->response->alertBack("编辑失败");
		}
		
	}
	
	public function action_remove() {
	    $this->_checkPrivilege(self::RES_FRIEND_LINK, self::PRIV_DELETE);
	    
		$id = $this->request->query("id");
		if(!$id) {
			$this->err();
		}
		$arr = $this->objModelLink->getLinkById($id);
		if(!$arr) {
			$this->err();
		}
		
		$res = $this->objModelLink->removeLinkById($id);
		JKit::$log->info("link remove, id-".$id);
		if($res) {
			if($arr['type']==Model_Data_Link::TYPE_IMAGE) {
				$arrGroup = explode("/", $arr['logo'], 2);
				$this->objModelLink->removeImage($arrGroup[0], $arrGroup[1]);
			}
			
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
			$arrReturn['msg'] = "图片不能为空";
			return $arrReturn;
		}
		
		if($avatar['error'] !== UPLOAD_ERR_OK) {
			$arrReturn['msg'] = "图片上传失败";
			return $arrReturn;
		}
		
		$arrImgAttr = getimagesize($avatar['tmp_name']);
		$arrValidType = array(IMAGETYPE_GIF => true, IMAGETYPE_PNG => true,
		    IMAGETYPE_JPEG => true);
		if(! is_array($arrImgAttr) || ! isset($arrValidType[$arrImgAttr[2]])) {
			$arrReturn['msg'] = "图片格式错误";
			return $arrReturn;
		}
		$arrReturn['attr'] = $arrImgAttr;

		$maxSizeLimit = 5242880;
		if($avatar['size'] > $maxSizeLimit) {
			$arrReturn['msg'] = "图片大小不能超过5M";
			return $arrReturn;
		} 
		$arrReturn['ok'] = true;
		return $arrReturn;
	}
}
