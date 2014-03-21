<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 * 首页
 * @author wangjiajun
 *
 */
class Controller_Mobile_Index extends Mobile 
{
	public function action_recommendCircles() 
	{
	    $count = (int) $this->request->param('count', 4);
	    
	    $modelLogicCircle = new Model_Logic_Circle();
		$promoteCircles = $modelLogicCircle->promoteCircles(false);
		$promoteCircles = array_slice($promoteCircles, 0, $count);
		foreach ($promoteCircles as &$circle) {
		    $circle['tn_path'] = Util::circlePreviewPic($circle['tn_path']);
		    $circle['recommend_image'] = Util::circlePreviewPic($circle['recommend_image']);
		}
	    $this->ok($promoteCircles);
	}
	
	public function action_recommendVideos() 
	{
	    $type = (int) $this->request->param('type', 0);
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 5);
	    $tm = $this->request->param('tm');
	    if (!is_null($tm)) {
	        $tm = (int) $tm;
	    }
	    
	    $modelLogicRecommend = new Model_Logic_Recommend();
	    Profiler::startMethodExec();
	    $videos = $modelLogicRecommend->getHomepageRecommendVideos($offset, $count, 
	        $tm, $type, Model_Logic_Video::$basicFieldsForMobile);
	    Profiler::endMethodExec(__FUNCTION__.' getHomepageRecommendVideos');
	    if (is_null($tm)) {
	        $tm = $modelLogicRecommend->getHomepageMaxCurrent(Model_Data_Recommend::HOMEPAGE_REC);
	    }
	    $videos = array_values($videos);
		foreach ($videos as &$video) {
		    $video['thumbnail'] = Util::videoThumbnailUrl($video['thumbnail']);
		}
		$objLogicUtil = new Model_Logic_Mobile_Util();
		$objLogicUtil->appendVideoMp4Playurl($videos);
	    $this->ok(array('videos' => $videos, 'tm' => $tm));
	}
}