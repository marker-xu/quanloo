<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 搜索相关页面，以及Ajax请求接口
 * @author wangjiajun
 */
class Controller_Search extends Controller 
{
	public function action_index()
	{
		$from = trim($this->request->param('from'));
	    $query = (string) $this->request->param('q');
	    if ($from != Model_Data_Video::SOURCE_FIREFOX) {//火狐一键应用，搜索词会很长，不能截断
	    	$query = mb_substr(trim($query), 0, 50);
	    }
	    $offset = (int) $this->request->param('offset', 0);
	    if ($offset < 0) {
	        $offset = 0;
	    }
	    $count = (int) $this->request->param('count', 20);
	    if ($count < 1) {
	        $count = 1;
	    }
	    $sort = (string) $this->request->param('sort', 'relevance');
	    if (!in_array($sort, array('relevance', 'time'))) {
	        $sort = 'relevance';
	    }
	    $filters = array();
	    $quality = $this->request->param('quality');
	    if (isset(Model_Data_Video::$qualitys[$quality])) {
	        $filters['quality'] = (int) $quality;
	    }
	    $length = $this->request->param('length');
	    if (!is_null($length) && isset(Model_Data_Video::$durations[$length])) {
	        $filters['length'] = (int) $length;
	    }
	    $category = $this->request->param('category');
	    if (isset(Model_Data_Video::$categorys[$category])) {
	        $filters['category'] = (int) $category;
	    }
	    $domain = $this->request->param('domain');
	    if (isset(Model_Data_Video::$domains[$domain])) {
	        $filters['domain'] = (string) $domain;
	    }
	    $tag = (string) $this->request->param('tag');
	    $tag = mb_substr(trim($tag), 0, 20);
	    if ($tag) {
	        $filters['tag'] = $tag;
	    }	    
	    $debug = (bool) $this->request->param('debug');
	    
	    $modelLogicSearch = new Model_Logic_Search();
	    $arrFilterTmp = $filters;
	    if (isset(Model_Data_Video::$source[$from])) {
	    	$arrFilterTmp['source'] = $from;
	    }
	    $result = $modelLogicSearch->search($query, $offset, $count, $sort, $arrFilterTmp, 
	        $debug);

        // 估计结果总数始终为不带任何过滤条件的搜索结果
        if ($filters) {
    	    $noFilterResult = $modelLogicSearch->search($query, 0, 0, 'relevance', array());
    	    $result['total'] = $noFilterResult['total'];
    	    $result['filters']['tag'] = $noFilterResult['filters']['tag'];
    	    if ($noFilterResult['real_total'] <= $count) {
                $result['filters']['tag'] = array();
    	    }
        } else {
            if ($result['real_total'] <= $count) {
                $result['filters']['tag'] = array();
            }
        }

        // 相关搜索
        $relationQuerys = $modelLogicSearch->relationQuery($query);
	    
	    if ($this->request->is_ajax()) {
	        $this->ok($result);
	    } else {            
    	    if($result['real_total'] == 0 && empty($filters)){                
    	    	$this->template->set_filename('search/noresult');
                $this->template->set('search_result', $result);
                $this->template->set('relation_querys', $relationQuerys);
    	    } else {
                // 搜索结果
                $this->template->set('search_result', $result);
                
                // 相关搜索结果
                $this->template->set('relation_querys', $relationQuerys);
                
                // 圈子搜索结果
                $circleResult = $modelLogicSearch->searchCircle($query, 0, 0);
                $this->template->set('circle_result', $circleResult);
        	    
        	    // 搜索选项
        	    $qualitys = Model_Data_Video::$qualitys;
        	    foreach ($qualitys as $key => &$value) {
        	        if (in_array($key, $result['filters']['quality'])) {
        	            $enable = true;
        	        } else {
        	            $enable = false;
        	        }
        	        $value = array('name' => $value, 'enable' => $enable);
        	    }
        	    unset($value);
        	    $lengths = Model_Data_Video::$durations;
        	    foreach ($lengths as $key => &$value) {
        	        if (in_array($key, $result['filters']['length'])) {
        	            $enable = true;
        	        } else {
        	            $enable = false;
        	        }
        	        $value = array('name' => $value, 'enable' => $enable);
        	    }
        	    unset($value);
        	    $categorys = Model_Data_Video::$categorys;
        	    foreach ($categorys as $key => &$value) {
        	        if (in_array($key, $result['filters']['category'])) {
        	            $enable = true;
        	        } else {
        	            $enable = false;
        	        }
        	        $value = array('name' => $value, 'enable' => $enable);
        	    }
        	    $domains = Model_Data_Video::$domains;
        	    foreach ($domains as $key => &$value) {
        	        if (in_array($key, $result['filters']['domain'])) {
        	            $enable = true;
        	        } else {
        	            $enable = false;
        	        }
        	        $value = array('name' => $value, 'enable' => $enable);
        	    }
        	    $this->template->set('quality', $qualitys);
        	    $this->template->set('length', $lengths);
        	    $this->template->set('category', $categorys);
        	    $this->template->set('domain', $domains);
        	    $this->template->set('tag', $result['filters']['tag']);
        	    
        	    // 与query同名的圈子
        	    $modelDataCircle = new Model_Data_Circle();
        	    $circle = $modelDataCircle->getByTitle($query, true, Model_Logic_Circle::$basicFields);
        	    $this->template->set('circle', $circle);
    	    }
	    }
	}
	
	public function action_circle()
	{
	    $query = (string) $this->request->param('q', '');
	    $query = mb_substr($query, 0, 50);
	    $offset = (int) $this->request->param('offset', 0);
	    $count = (int) $this->request->param('count', 16);
	    
        $modelLogicSearch = new Model_Logic_Search();
        $result = $modelLogicSearch->searchCircle($query, $offset, $count);
	    
	    if ($this->request->is_ajax()) {
	        $this->ok($result);
	    } else {
    	    if($result['total'] == 0){
    	        // 无结果
    	    	$this->template = View::factory('search/noresult');
    	    } else {
    	        // 圈子搜索结果
        	    $this->template->set('search_result', $result);
    	    }
	    }
	}
	
	public function action_hotKeywords()
	{
	    $count = (int) $this->request->param('count', 5);
	    $modelLogicStat = new Model_Logic_Stat();
	    $hotKeywords = $modelLogicStat->mostQueryKeywords($count);
	    $this->ok($hotKeywords);
	}
	
	public function action_relationQuery()
	{
	    $query = (string) $this->request->param('q');
	    $query = mb_substr(trim($query), 0, 50);
	    $modelLogicSearch = new Model_Logic_Search();
        $relationQuery = $modelLogicSearch->relationQuery($query);
	    $this->ok($relationQuery);
	}
}
