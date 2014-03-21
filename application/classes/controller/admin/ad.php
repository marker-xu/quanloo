<?php
class Controller_Admin_Ad extends Controller_Admin
{
	public function before()
	{
		parent::before();	  
		$this->_checkPrivilege(self::RES_INSITE_AD, self::PRIV_VIEW);
	}
	
	public function action_index()
	{
		$cur_adpos_id = (int) $this->request->param('cur_adpos_id', 0);
		$intOffset = (int) $this->request->param('offset', 0);
		$intCount = 50;
		$arrAdPosMap = Model_Logic_Adconst::$AD_POS_LIST;
		
		$arrCond = array();
		if ($cur_adpos_id > 0) {
			$arrCond['ad_pos'] = $cur_adpos_id;
		}
		$objDataAdattr = new Model_Data_Adattr();
		$arrData = $objDataAdattr->find($arrCond, array(), array('ad_pos' => 1, 'addtime' => -1), $intCount, $intOffset);
		$intTotal = $objDataAdattr->count($arrCond);
		
		$this->template->set('ad_list', $arrData);
		$this->template->set('pager', array('count' => $intCount, 'offset' => $intOffset, 'total' => $intTotal));		
		$this->template->set('cur_adpos_id', $cur_adpos_id);
		$this->template->set('cur_adpos_nick', isset($arrAdPosMap[$cur_adpos_id]) ? $arrAdPosMap[$cur_adpos_id] : '全部广告位');
		$this->template->set('adpos_map', $arrAdPosMap);		
	}
	
	public function action_add()
	{
		$this->_checkPrivilege(self::RES_INSITE_AD, self::PRIV_ADD);
		 
		$cur_adpos_id = (int) $this->request->param('cur_adpos_id', 0);
		$arrAdPosMap = Model_Logic_Adconst::$AD_POS_LIST;
		if ($this->request->method() == 'POST') {
			$objDataAdattr = new Model_Data_Adattr();
			$strErrMsg = false;
			$arrPost = $this->request->post();
			$arrPost = array_map('trim', $arrPost);
			$cur_adpos_id = (int) $arrPost['ad_pos'];
			$intAdType = (int) $arrPost['ad_type'];
			if (! isset($arrAdPosMap[$cur_adpos_id])) {
				$strErrMsg = "选择的广告位不存在";
			} elseif (! isset(Model_Logic_Adconst::$AD_TYPE_LIST[$intAdType])) {
				$strErrMsg = "选择的广告类型不存在";
			} elseif (empty($arrPost['ad_url'])) {
				$strErrMsg = "广告的跳转链接不能为空";
			} elseif (! Upload::image($_FILES['ad_pic'])) {
				$strErrMsg = "广告图片不能为空";
			}
			
			if (empty($strErrMsg)) {
				$fdfs = new FastDFS(FASTDFS_CLUSTER_WEB_STORAGE);
				$result = $fdfs->storage_upload_by_filename($_FILES['ad_pic']['tmp_name'],
						pathinfo($_FILES['ad_pic']['name'], PATHINFO_EXTENSION));
				if (! $result) {
					$strErrMsg = "广告图片保存失败";
				} else {
					$strImgPath = implode('/', $result);
				}
			}
						
			if (empty($strErrMsg)) {
				if (strncasecmp($arrPost['ad_url'], 'http://', 7)) {
					$arrPost['ad_url'] = "http://{$arrPost['ad_url']}";
				}
				if (! empty($arrPost['ad_starttime'])) {
					$intStartTime = strtotime($arrPost['ad_starttime']);
				} else {
					$intStartTime = time();
				}
				if (! empty($arrPost['ad_endtime'])) {
					$intEndTime = strtotime($arrPost['ad_endtime']);
				} else {
					$intEndTime = $intStartTime + 86400;
				}
				$arrAdInfo = array(
					'ad_pos' => $cur_adpos_id,
					'ad_starttime' => $intStartTime,
					'ad_endtime' => $intEndTime,
					'ad_status' => Model_Logic_Adconst::AD_STATUS_NEW,
					'ad_mat' => array(
						'ad_type' => $intAdType,
						'ad_title' => $arrPost['ad_title'],
						'ad_url' => $arrPost['ad_url'],
						'ad_pic' => $strImgPath,
					),
				);
				$bolRet = $objDataAdattr->add($this->_user, $arrAdInfo);
				if (! $bolRet) {
					$strErrMsg = "广告添加失败";
				}
			}
			if ($strErrMsg) {
				$this->template->set('err_msg', $strErrMsg);
			} else {
				$this->template->set('err_msg', "成功添加");
			}
		}
	
		$this->template->set('cur_adpos_id', $cur_adpos_id);
		$this->template->set('adpos_map', $arrAdPosMap);
	}
	
	public function action_mod()
	{
		$this->_checkPrivilege(self::RES_INSITE_AD, self::PRIV_MODIFY);
		$id = trim($this->request->param('id'));
		if (empty($id)) {
			$this->err(null, '广告不存在');
			return;
		}
		
		$objDataAdattr = new Model_Data_Adattr();
		$arrAdPosMap = Model_Logic_Adconst::$AD_POS_LIST;
		$strErrMsg = false;
		if ($this->request->method() == Request::POST) {
			$arrPost = $this->request->param();
			$arrPost = array_map('trim', $arrPost);
			$cur_adpos_id = (int) $arrPost['ad_pos'];
			if (! isset($arrAdPosMap[$cur_adpos_id])) {
				$strErrMsg = "选择的广告位不存在";
			} elseif (empty($arrPost['ad_url'])) {
				$strErrMsg = "广告的跳转链接不能为空";
			}
			
			if (empty($strErrMsg)) {
				if (strncasecmp($arrPost['ad_url'], 'http://', 7)) {
					$arrPost['ad_url'] = "http://{$arrPost['ad_url']}";
				}
				if (! empty($arrPost['ad_starttime'])) {
					$intStartTime = strtotime($arrPost['ad_starttime']);
				} else {
					$intStartTime = time();
				}
				if (! empty($arrPost['ad_endtime'])) {
					$intEndTime = strtotime($arrPost['ad_endtime']);
				} else {
					$intEndTime = $intStartTime + 86400;
				}
				$arrAdInfo = $objDataAdattr->findOne(array('_id' => new  MongoId($id)));
				unset($arrAdInfo['_id']);
				$arrAdInfo = array_merge($arrAdInfo, array(
						'ad_pos' => $cur_adpos_id,
						'ad_starttime' => $intStartTime,
						'ad_endtime' => $intEndTime,
						'ad_status' => Model_Logic_Adconst::AD_STATUS_NEW,
				));
				$arrAdInfo['ad_mat'] = array_merge($arrAdInfo['ad_mat'], array(
						'ad_title' => $arrPost['ad_title'],
						'ad_url' => $arrPost['ad_url'],
				));
				$mixedRet = $objDataAdattr->update(array('_id' => new  MongoId($id)), $arrAdInfo, array('upsert' => false));
				if (! isset($mixedRet['ok']) || $mixedRet['ok'] != 1) {
					$strErrMsg = "广告修改失败";
				}
			}
			if ($strErrMsg) {
				$this->err(null, $strErrMsg);
			} else {
				$this->ok();
			}
		} else {
			$arrCurAdInfo = $objDataAdattr->findOne(array('_id' => new  MongoId($id)));
			$this->template->set('cur_ad', $arrCurAdInfo);
			$this->template->set('adpos_map', $arrAdPosMap);
		}				
	}
	
	public function action_modStatus()
	{
		$this->_checkPrivilege(self::RES_INSITE_AD, self::PRIV_MODIFY);
		$id = trim($this->request->param('id'));
		if (empty($id)) {
			$this->err(null, '广告不存在');
			return;
		}
		$intStatus = (int) $this->request->param('ad_status');
		if (! isset(Model_Logic_Adconst::$AD_STATUS_LIST[$intStatus])) {
			$this->err(null, '广告状态错误');
			return;
		}

		$objDataAdattr = new Model_Data_Adattr();
		$mixedRet = $objDataAdattr->update(array('_id' => new  MongoId($id)), array('ad_status' => $intStatus), array('upsert' => false));
		if (! isset($mixedRet['ok']) || $mixedRet['ok'] != 1) {
			$this->err(null, '修改失败');
		} else {
			$this->ok();
		}
	}
	
	public function action_delete()
	{
		$this->_checkPrivilege(self::RES_INSITE_AD, self::PRIV_DELETE);
		 
		$id = trim($this->request->param('id'));
		$bolRet = false;
		if ($id) {
			$objDataAdattr = new Model_Data_Adattr();
			$arrAdInfo = $objDataAdattr->findOne(array('_id' => new  MongoId($id)));
			if (empty($arrAdInfo)) {
				$this->ok(); //找不到广告信息当作已删除，直接返回true
				return;
			}
			if (! empty($arrAdInfo['ad_mat']['ad_pic'])) {
				$arrTmp = explode("/", $arrAdInfo['ad_mat']['ad_pic'], 2);
				$fdfs = new FastDFS(FASTDFS_CLUSTER_WEB_STORAGE);
				$ret = $fdfs->storage_delete_file($arrTmp[0], $arrTmp[1]);
				if (! $ret) {
					$this->err(null, '删除图片失败');
					return;
				}
			}
			$bolRet = $objDataAdattr->remove($id);
		}
		if ($bolRet) {
			$this->ok();
		} else {
			$this->err(null, '删除失败');
		}
	}
}