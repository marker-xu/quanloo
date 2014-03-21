<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 明星
 * @author wangjiajun
 */
class Controller_Star extends Controller 
{
	public function action_index()
	{
	    
	}
	
	public function action_info()
	{
	    $id = (string) $this->request->param('id');
	    $id = urldecode($id);
	    
	    // 顶部广告位
	    $modelAd = new Model_Logic_Ad();
	    $topAd = $modelAd->getRandAdByPos(Model_Logic_Adconst::AD_POS_ENTITY_TOP_1);
	    $this->template->set('topAd', $topAd);
	    
	    // 明星信息
	    $modelEntity = new Model_Data_Entity();
	    $star = $modelEntity->star($id);
	    $this->template->set('star', $star);
	    $this->template->set('comments', array());
	    
	    // 相关圈子
	    $modelLogicEntity = new Model_Logic_Entity();
	    $relatedCircles = $modelLogicEntity->starRelatedCircles($id, 4, $this->_uid);
	    $this->template->set('relatedCircles', array_slice($relatedCircles, 0, 3));
	    
	    // 相关影片
	    $relatedEntitys = $modelLogicEntity->relatedEntitys($id);
	    $this->template->set('relatedEntitys', array_slice($relatedEntitys, 0, 6));
	    
	    // 相关视频
	    $relatedVideos = $modelLogicEntity->relatedVideos($id);
	    $this->template->set('relatedVideos', $relatedVideos);
	}
}
