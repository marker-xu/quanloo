<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 
 * 搜索
 * @author wangjiajun
 *
 */
class Controller_Mobile_Search extends Mobile 
{
	public function action_index() 
	{
	    $query = (string) $this->request->param('q');
	    $query = mb_substr(trim($query), 0, 50);
	    $offset = (int) $this->request->param('offset', 0);
	    if ($offset < 0) {
	        $offset = 0;
	    }
	    $count = (int) $this->request->param('count', 5);
	    if ($count < 1) {
	        $count = 1;
	    }
	    $sort = (string) $this->request->param('sort', 'relevance');
	    if (!in_array($sort, array('relevance', 'time'))) {
	        $sort = 'relevance';
	    }
	    
	    $modelLogicSearch = new Model_Logic_Search();
	    $result = $modelLogicSearch->search($query, $offset, $count, $sort);
		foreach ($result['videos'] as &$video) {
		    $video['thumbnail'] = Util::videoThumbnailUrl($video['thumbnail']);
		}
		$objLogicUtil = new Model_Logic_Mobile_Util();
		$objLogicUtil->appendVideoMp4Playurl($result['videos']);
	    $this->ok($result);
	}
	
	public function action_circle() 
	{
	    $query = (string) $this->request->param('q', '');
	    $query = mb_substr($query, 0, 50);
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 5);
	    
        $modelLogicSearch = new Model_Logic_Search();
        $result = $modelLogicSearch->searchCircle($query, $offset, $count);
		$modelLogicRecommend = new Model_Logic_Recommend();
		foreach ($result['circles'] as &$circle) {
		    $circle['tn_path'] = Util::circlePreviewPic(isset($circle['tn_path']) ? $circle['tn_path'] : '');
			try {
		        $videos = $modelLogicRecommend->getCircleVideosByTag($circle['_id'], 
		            $circle['title'], '', 0, 1, Model_Logic_Video::VIDEO_RANK_DEFAULT, 
		            null, array('has_video_stat' => false, 'has_circle_info' => false, 
		            'has_video_comment' => false));
				if (isset($videos['video']) && is_array($videos['video'])) {
					$video = current($videos['video']);
					$circle['video_thumbnail'] = Util::videoThumbnailUrl($video['thumbnail']);
				}
			} catch (Exception $e) {
				continue;
			}
		}
	    
	    $this->ok($result);
	}
}