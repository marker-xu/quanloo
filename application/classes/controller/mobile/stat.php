<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 * 统计接口
 * @author xucongbin
 *
 */
class Controller_Mobile_Stat extends Mobile {
	
	public function action_watchvideo() {
		$videoId = (string) $this->request->param('vid');
		$circleId = $this->request->param('cid');
	    $userId = $this->_uid;
	    
	    if (strlen($videoId) != 32) {
	        $this->err();
	    }
	    if (! empty($circleId)) {
	        $circleId = (int) $circleId;
    	    if ($circleId <= 0) {
	            $this->err('invalid circle');
    	    }
	    }
		$modelLogicVideo = new Model_Logic_Video();
	    $video = $modelLogicVideo->get($videoId, array( 'status' ), false);
	    if (!$video || $video['status'] != Model_Data_Video::STATUS_VALID) {
	        $this->err();
	    }
	    
		//非(压力测试和spider)的情况下记录
		$strUserAgent = $this->request->headers('user-agent');
		if (! Util::isSpider($strUserAgent)) {
            try {
                $modelLogicVideo->watch($videoId, $userId, $circleId);
            } catch (Model_Exception $e) {
                Kohana::$log->warn('add watch feed failed');
            }	        
	    }
	    $this->ok();
	}
}