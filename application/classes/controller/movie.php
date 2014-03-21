<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 电影
 * @author wangjiajun
 */
class Controller_Movie extends Controller 
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
	    $hotMovies = $modelEntity->hotLongVideos('movie');
	    $this->template->set('hotMovies', $hotMovies);
	    
	    // 热门明星
	    $hotStars = $modelEntity->hotStars('movie');
	    $this->template->set('hotStars', $hotStars);
	    
	    // 推荐圈子
	    $modelLogicEntity = new Model_Logic_Entity();
	    $hotCircles = $modelLogicEntity->hotCircles('movie', 8, $this->_uid);
	    $this->template->set('hotCircles', $hotCircles);
	    
	    // 检索结果
	    $movies = $modelEntity->longVideos('movie', $sort, $count, $offset, array(
	        'genre' => $genre,
	        'region' => $region,
	        'released_date' => $releasedDate,
	        'cast' => $cast,
	    ));
	    $this->template->set('movies', $movies);
	}
	
	public function action_info()
	{
	    $id = (string) $this->request->param('id');
	    
	    // 顶部广告位
	    $modelAd = new Model_Logic_Ad();
	    $topAd = $modelAd->getRandAdByPos(Model_Logic_Adconst::AD_POS_CIRCLE_TOP_1);
	    $this->template->set('topAd', $topAd);
	    
	    // 电影信息
	    $modelEntity = new Model_Data_Entity();
	    $movie = $modelEntity->get($id);
	    $this->template->set('movie', $movie);
	    $this->template->set('comments', $movie['comments']);
	    
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
