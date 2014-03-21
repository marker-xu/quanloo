<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 电视剧
 * @author wangjiajun
 */
class Controller_Tv extends Controller 
{
	public function action_index()
	{
	    $sort = (string) $this->request->param('sort', 'new');
	    $genre = (string) $this->request->param('genre', '');
	    $region = (string) $this->request->param('region', '');
	    $releasedDate = (string) $this->request->param('released_date', '');
	    $cast = (string) $this->request->param('cast', '');
	    $count = (int) $this->request->param('count', 6);
	    $offset = (int) $this->request->param('offset', 0);
	    // Query参数
	    $this->template->set('sort', $sort);
	    $this->template->set('genre', $genre);
	    $this->template->set('region', $region);
	    $this->template->set('released_date', $releasedDate);
	    $this->template->set('cast', $cast);
	    $this->template->set('count', $count);
	    $this->template->set('offset', $offset);
	    
	    // 顶部广告位
	    $modelAd = new Model_Logic_Ad();
	    $topAd = $modelAd->getRandAdByPos(Model_Logic_Adconst::AD_POS_ENTITY_TOP_1);
	    $this->template->set('topAd', $topAd);
	    
	    // 当前热映
	    $modelEntity = new Model_Data_Entity();
	    $hotTvs = $modelEntity->hotLongVideos('tv');
	    $this->template->set('hotTvs', $hotTvs);
	    
	    // 热门明星
	    $hotStars = $modelEntity->hotStars('tv');
	    $this->template->set('hotStars', $hotStars);
	    
	    // 推荐圈子
	    $modelLogicEntity = new Model_Logic_Entity();
	    $hotCircles = $modelLogicEntity->hotCircles('tv', 8, $this->_uid);
	    $this->template->set('hotCircles', $hotCircles);
	    
	    // 检索结果
	    $tvs = $modelEntity->longVideos('tv', $sort, $count, $offset, array(
	        'genre' => $genre,
	        'region' => $region,
	        'released_date' => $releasedDate,
	        'cast' => $cast,
	    ));
	    $this->template->set('tvs', $tvs);
	}
	
	public function action_info()
	{
	    $id = (string) $this->request->param('id');
	    
	    // 顶部广告位
	    $modelAd = new Model_Logic_Ad();
	    $topAd = $modelAd->getRandAdByPos(Model_Logic_Adconst::AD_POS_CIRCLE_TOP_1);
	    $this->template->set('topAd', $topAd);
	    
	    // 电视剧信息
	    $modelEntity = new Model_Data_Entity();
	    $tv = $modelEntity->get($id);
	    $this->template->set('tv', $tv);
	    $this->template->set('comments', $tv['comments']);
	    
	    // 相关圈子
	    $modelLogicEntity = new Model_Logic_Entity();
	    $relatedCircles = $modelLogicEntity->relatedCircles($id, $this->_uid);
	    $this->template->set('relatedCircles', array_slice($relatedCircles, 0, 3));
	    
	    // 相关影片
	    $relatedEntitys = $modelLogicEntity->relatedEntitys($id);
	    $this->template->set('relatedEntitys', array_slice($relatedEntitys, 0, 6));
	    
	    // 相关视频
	    $relatedVideos = $modelLogicEntity->relatedVideos($id);
	    $this->template->set('relatedVideos', $relatedVideos);
	}
}
