<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 首页
 * @author wangjiajun
 */
class Controller_Index extends Controller 
{
	public function action_index()
	{
        $type = (int) $this->request->param("type", 0);
        
	    // 推广圈子
	    $modelLogicCircle = new Model_Logic_Circle();
		$promoteCircles = $modelLogicCircle->promoteCircles(false);
		$promoteCircles = array_slice($promoteCircles, 0, 4);
		shuffle($promoteCircles);
	    $this->template->set('promoteCircles', $promoteCircles);
	    
        $this->template->set('type', $type);

	    // 推荐视频数据产生时间点
	    $modelLogicRecommend = new Model_Logic_Recommend();
	    Profiler::startMethodExec();
	    $recommendTm = (int) $modelLogicRecommend->getHomepageMaxCurrent($type);
		Profiler::endMethodExec(__FUNCTION__.' getHomepageMaxCurrent');
	    $this->template->set('recommend_tm', $recommendTm);
	    
	    if (Util::isSpider()) {
	    	$intVideoNum = 50;
	    } else {
	    	$intVideoNum = 16;
	    }
	    Profiler::startMethodExec();
	    $videos = $modelLogicRecommend->getHomepageRecommendVideos(0, $intVideoNum, $recommendTm, 
	        $type);
	    Profiler::endMethodExec(__FUNCTION__.' getHomepageRecommendVideos');
	    $videos = array_values($videos);
	    $this->template->set('videos', $videos);
	}
	
	/**
	 * @deprecated 此方法已经废弃
	 */
	public function action_personal() {
		$this->_needLogin();
		$this->request->redirect(Util::userUrl($this->_uid), 301);
	}
	
	public function action_hotVideos()
	{
	    $type = (int) $this->request->param('type', 0);
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 10);
	    $tm = $this->request->param('tm');
	    if (!is_null($tm)) {
	        $tm = (int) $tm;
	    }
	    
	    $modelLogicRecommend = new Model_Logic_Recommend();
	    Profiler::startMethodExec();
	    $videos = $modelLogicRecommend->getHomepageRecommendVideos($offset, $count, 
	        $tm, $type);
	    Profiler::endMethodExec(__FUNCTION__.' getHomepageRecommendVideos');
	    $videos = array_values($videos);
	    $this->ok($videos);
	}
	
	public function action_invitecb() {
		$code = trim( $this->request->query("code") );
		$arrCode = explode("\3", base64_decode($code));
		if(!$arrCode) {
			$this->request->redirect("index");
		}
		
		$type = $arrCode[1];
		$uid = intval( $arrCode[0] );
		$objLogicUser = new Model_Logic_User();
		$arrUserInfo = $objLogicUser->get($uid);
		
		if(!$arrUserInfo) {
			$this->request->redirect("index");
		}
		Session::instance()->set("invite", $uid);
		switch ($type) {
			case 'circle':
			    $modelDataCircle = new Model_Data_Circle();
			    $circle = $modelDataCircle->get($arrCode[2]);
				$this->request->redirect(Util::circleUrl($arrCode[2], null, $circle));
				break;
			default:
				$this->request->redirect("index");
		}
		$this->request->redirect("index");
	}
}
