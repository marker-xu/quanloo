<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 推荐相关页面，以及Ajax请求接口
 * @author xucongbin
 */
class Controller_Recommend extends Controller 
{
	
	private $objLogicRecommend;
	
	public function before() {
		parent::before();
		$this->objLogicRecommend = new Model_Logic_Recommend();
	}
	
	public function action_index()
	{
        $this->response->body('Welcome to VideoSearch!');
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param array $_GET = array(
	 * 	cid => 圈子ID, -1表示全部, 默认-1
	 * 	offset => 起始位置, 默认0
	 * 	count => 数量， 默认12
	 * )
	 * 
	 * @return array(
	 * 	data => array(
	 * 		视频基本信息,
	 * 		...
	 * 	),
	 *  count => 视频总数
	 * )
	 */
	public function action_mycirclevideos() {
		$this->_needLogin();
		$arrReturn = array();
		$intCircleId =  $this->request->query("cid");
		$offset = $this->request->query("offset");
		$count = $this->request->query("count");
		if( $intCircleId===NULL ) {
			$intCircleId = -1;
		} 
		if($offset===NULL) {
			$offset = 0;
		}
		if($count===NULL) {
			$count = 12;
		}
		$intCircleId = intval($intCircleId);
		
		if($intCircleId!=-1) {
		    $arrRet = $this->objLogicRecommend->getCircleVideosByTag($intCircleId, null, '', $offset, $count);		     
			$arrReturn['data'] = $arrRet['video'];
			$this->unsetCircleField($arrReturn['data']);
			$arrReturn['total'] = $arrRet['total'];
		} else {
			$arrReturn = $this->objLogicRecommend->getAllCircleVideos($this->_uid, $offset, $count);
		}
		
		$this->ok($arrReturn);
	}
	/**
	 * 
	 * 推荐用户选取的tag换一换
	 * 
	 * @param array $_GET = array(
	 * 	offset => 默认为0，
	 *  count => 默认为10
 	 * )
	 */
	public function action_recommendusertags() {
		$offset = $this->request->query("offset");
		$count = $this->request->query("count");
		if($offset===NULL) {
			$offset = 0;
		}
		if($count===NULL) {
			$count = 10;
		}
		$arrTags = $this->objLogicRecommend->getRecommendUserTags($offset, $count);
		$this->ok($arrTags);
	}
	
	private function unsetCircleField( &$arrData ) {
		if(!$arrData) {
			return;
		}
		
		foreach($arrData as $k=>$row) {
			$arrData[$k]['circle'] = array();
		}
	}
}
