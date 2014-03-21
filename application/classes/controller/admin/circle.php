<?php

class Controller_Admin_Circle extends Controller_Admin
{
	public function before()
	{
	    parent::before();
	    
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_VIEW);
	}

	public function action_index()
	{
	    $page = (int) $this->request->param('page', 1);
	    $category = (int) $this->request->param('category', -1);
	    $certified = (int) $this->request->param('certified', -1);
	    $official = (int) $this->request->param('official', -1);
	    $status = (int) $this->request->param('status', -1);
	    $filterTag = (int) $this->request->param('filter_tag', -1);
	    $orderby = (string) $this->request->param('orderby', '_id');
	    $direction = (int) $this->request->param('direction', -1);
	    $count = 30;
		$keyword = trim($this->request->param('keyword', ''));

	    $modelDataCircle = new Model_Data_Circle();
	    $query = array();
	    if ($category >= 0) {
	        $query['category'] = $category;
	    }
	    if ($certified >= 0) {
	        if ($certified == 0) {
	            $query['certified'] = array('$in' => array(null, $certified));
	        } else {
	            $query['certified'] = $certified;
	        }
	    }
	    if ($official >= 0) {
	        if ($official == 0) {
	            $query['official'] = array('$in' => array(null, $official));
	        } else {
	            $query['official'] = $official;
	        }
	    }
	    if ($status >= 0) {
	        $query['status'] = $status;
	    }
		if ($keyword) {
			$query['title'] = new MongoRegex("/$keyword/i");
		}
	    $circles = $modelDataCircle->find($query, array(), array($orderby => $direction),
	        $count, ($page - 1) * $count);
	    foreach ($circles as &$circle) {
	        if (!isset($circle['certified'])) {
	            $circle['certified'] = 0;
	        }
	        if (!isset($circle['official'])) {
	            $circle['official'] = 0;
	        }
	    }
	    $this->template->set('circles', $circles);

	    $total = $modelDataCircle->count($query);
	    $pagination = Pagination::factory(array(
	    	'total_items' => $total,
	    	'items_per_page' => $count
	    ));
	    $this->template->set('pagination', $pagination);
	}

	public function action_mod()
	{
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_MODIFY);
	    
	    $id = (int) $this->request->param('id');

	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->findOne(array('_id' => $id));
	    if (!$circle) {
            $this->response->alertBack('圈子不存在');
            return;
	    }
        if (!isset($circle['certified'])) {
            $circle['certified'] = 0;
        }
        if (!isset($circle['official'])) {
            $circle['official'] = 0;
        }

	    if ($this->request->method() == 'POST') {
	        $doc = array();
	        $doc['title'] = trim($this->request->param('title'));
	        $doc['category'] = $this->request->param('category', array());
	        foreach ($doc['category'] as &$category) {
	            $category = (int) $category;
	        }
	        $doc['tag'] = explode(',', trim($this->request->param('tag')));
	        $doc['certified'] = (int) $this->request->param('certified');
	        $doc['official'] = (int) $this->request->param('official');
	        $doc['status'] = (int) $this->request->param('status');
	        
    		$fdfs = new FastDFS(FASTDFS_CLUSTER_WEB_STORAGE);
    		
	        if (Upload::not_empty($_FILES['logo'])) {
    	        if (!Upload::image($_FILES['logo'])) {
    	            $this->response->alertBack('Logo必须是图片');
    	            return;
    	        }
    	        if ($_FILES['logo']['size'] >= 10 * 1024) {
    	            $this->response->alertBack('Logo大小不能超过10K');
    	            return;
    	        }
        		$result = $fdfs->storage_upload_by_filename($_FILES['logo']['tmp_name'], 
            		pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        		if (!$result) {
    	            $this->response->alertBack('存储Logo失败');
    	            return;
        		}
    		    $doc['logo'] = implode('/', $result);
	        }
    		
	        $modelLogicCircle = new Model_Logic_Circle();
    	    try {
    	        $modelLogicCircle->modify($id, $doc);
    	    } catch (Exception $e) {
    	        Kohana::$log->error($e->getMessage());
	            $this->response->alertBack('修改失败');
	            return;
    	    }
	        $this->response->alertBack('修改成功');
	    } else {
	        $this->template->set('circle', $circle);
	    }
	}

	public function action_filterTag()
	{
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_MODIFY);
	    
	    $id = (int) $this->request->param('id');

	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->findOne(array('_id' => $id));
	    if (!$circle) {
            $this->response->alertBack('圈子不存在');
            return;
	    }
	    if (!isset($circle['filter_tag'])) {
	        $circle['filter_tag'] = array();
	    }
	    if (!isset($circle['filter_tag_candidate'])) {
	        $circle['filter_tag_candidate'] = array();
	    }

	    if ($this->request->method() == 'POST') {
	        $doc = array();
	        $doc['title'] = trim($this->request->param('title'));
	        $doc['category'] = $this->request->param('category', array());
	        foreach ($doc['category'] as &$category) {
	            $category = (int) $category;
	        }
	        $doc['tag'] = explode(',', trim($this->request->param('tag')));
	        $doc['certified'] = (int) $this->request->param('certified');
	        $doc['official'] = (int) $this->request->param('official');
	        $doc['status'] = (int) $this->request->param('status');
	        $modelLogicCircle = new Model_Logic_Circle();
    	    try {
    	        $modelLogicCircle->modify($id, $doc);
    	    } catch (Exception $e) {
    	        Kohana::$log->error($e->getMessage());
	            $this->response->alertBack('修改失败');
	            return;
    	    }
	        $this->response->alertBack('修改成功');
	    } else {
	        $this->template->set('circle', $circle);
	    }
	}
	
	public function action_addDimension()
	{
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_ADD);
	    
	    $id = (int) $this->request->param('id');
	    $name = trim($this->request->param('name'));
	    if (!$name) {
	        $this->response->json2(-1, '维度名称为空');
            return;
	    }
	    
	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->findOne(array('_id' => $id));
	    if (!$circle) {
            $this->response->json2(-1, '圈子不存在');
            return;
	    }
	    if (!isset($circle['filter_tag']) || !is_array($circle['filter_tag'])) {
	        $circle['filter_tag'] = array();
	    }
	    
	    if (in_array($name, Arr::pluck($circle['filter_tag'], 'name'))) {
            $this->response->json2(-1, '维度已存在');
            return;
	    }
	    
	    $dimension = array('name' => $name, 'tag' => array());
	    if (count($circle['filter_tag']) == 0) {
	        $dimension['default'] = true;
	    }
	    $circle['filter_tag'][] = $dimension;
	    try {
    	    $modelDataCircle->update(array('_id' => $id), array(
    	    	'filter_tag' => $circle['filter_tag']
    	    ), array('safe' => true));
	    } catch (Exception $e) {
    	    Kohana::$log->error($e->getMessage());
            $this->response->json2(-1, '操作失败');
            return;
	    }
	    
	    $this->response->json2();
	}
	
	public function action_setDefaultDimension()
	{
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_MODIFY);
	    
	    $id = (int) $this->request->param('id');
	    $name = trim($this->request->param('name'));
	    if (!$name) {
	        $this->response->json2(-1, '维度名称为空');
            return;
	    }
	    
	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->findOne(array('_id' => $id));
	    if (!$circle) {
            $this->response->json2(-1, '圈子不存在');
            return;
	    }
	    if (!isset($circle['filter_tag'])) {
	        $circle['filter_tag'] = array();
	    }
	    
	    array_walk($circle['filter_tag'], function (&$value, $index) use ($name) {
	        if ($value['name'] == $name) {
	            $value['default'] = true;
	        } else {
	            $value['default'] = false;
	        }
	    });
	    try {
    	    $modelDataCircle->update(array('_id' => $id), array(
    	    	'filter_tag' => $circle['filter_tag']
    	    ), array('safe' => true));
	    } catch (Exception $e) {
    	    Kohana::$log->error($e->getMessage());
            $this->response->json2(-1, '操作失败');
            return;
	    }
	    
	    $this->response->json2();
	}
	
	public function action_deleteDimension()
	{
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_DELETE);
	    
	    $id = (int) $this->request->param('id');
	    $name = trim($this->request->param('name'));
	    if (!$name) {
	        $this->response->json2(-1, '维度名称为空');
            return;
	    }
	    
	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->findOne(array('_id' => $id));
	    if (!$circle) {
            $this->response->json2(-1, '圈子不存在');
            return;
	    }
	    if (!isset($circle['filter_tag'])) {
	        $circle['filter_tag'] = array();
	    }
	    
	    $circle['filter_tag'] = array_filter($circle['filter_tag'], function ($value) use ($name) {
	        return $value['name'] != $name;
	    });
	    try {
    	    $modelDataCircle->update(array('_id' => $id), array(
    	    	'filter_tag' => $circle['filter_tag']
    	    ), array('safe' => true));
	    } catch (Exception $e) {
    	    Kohana::$log->error($e->getMessage());
            $this->response->json2(-1, '操作失败');
            return;
	    }
	    
	    $this->response->json2();
	}
	
	public function action_addTag()
	{
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_ADD);
	    
	    $id = (int) $this->request->param('id');
	    $tags = $this->request->param('tags', array());
	    if (!$tags) {
	        $this->response->json2(-1, 'Tag为空');
            return;
	    }
	    $name = trim($this->request->param('name'));
	    if (!$name) {
	        $this->response->json2(-1, '维度名称为空');
            return;
	    }
	    
	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->findOne(array('_id' => $id));
	    if (!$circle) {
            $this->response->json2(-1, '圈子不存在');
            return;
	    }
	    if (!isset($circle['filter_tag'])) {
	        $circle['filter_tag'] = array();
	    }
	    
	    array_walk($circle['filter_tag'], function (&$value, $index) use ($tags, $name) {
	        if ($value['name'] == $name) {
	            $value['tag'] = array_values(array_unique(array_merge($value['tag'], $tags)));
	        }
	    });
	    try {
    	    $modelDataCircle->update(array('_id' => $id), array(
    	    	'filter_tag' => $circle['filter_tag']
    	    ), array('safe' => true));
	    } catch (Exception $e) {
    	    Kohana::$log->error($e->getMessage());
            $this->response->json2(-1, '操作失败');
            return;
	    }
	    
	    $this->response->json2();
	}
	
	public function action_deleteTag()
	{
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_DELETE);
	    
	    $id = (int) $this->request->param('id');
	    $dimensions = $this->request->param('dimensions', array());
	    if (!$dimensions) {
	        $this->response->json2(-1, '维度为空');
            return;
	    }
	    $tags = $this->request->param('tags', array());
	    if (!$tags) {
	        $this->response->json2(-1, 'Tag为空');
            return;
	    }
	    if (count($dimensions) != count($tags)) {
	        $this->response->json2(-1, '非法请求');
            return;
	    }
	    
	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->findOne(array('_id' => $id));
	    if (!$circle) {
            $this->response->json2(-1, '圈子不存在');
            return;
	    }
	    if (!isset($circle['filter_tag'])) {
	        $circle['filter_tag'] = array();
	    }
	    
	    $tag = current($tags);
	    foreach ($dimensions as $dimension) {
	        $tag = explode(',', $tag);
    	    array_walk($circle['filter_tag'], function (&$value, $index) use ($dimension, $tag) {
    	        if ($value['name'] == $dimension) {
    	            $value['tag'] = array_values(array_diff($value['tag'], $tag));
    	        }
    	    });
	        $tag = next($tags);
	    }
	    try {
    	    $modelDataCircle->update(array('_id' => $id), array(
    	    	'filter_tag' => $circle['filter_tag']
    	    ), array('safe' => true));
	    } catch (Exception $e) {
    	    Kohana::$log->error($e->getMessage());
            $this->response->json2(-1, '操作失败');
            return;
	    }
	    
	    $this->response->json2();
	}
	
	public function action_moveTag()
	{
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_MODIFY);
	    
	    $id = (int) $this->request->param('id');
	    $dimension = trim($this->request->param('dimension'));
	    $tag = trim($this->request->param('tag'));
	    $amount = (int) $this->request->param('amount');
	    
	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->findOne(array('_id' => $id));
	    if (!$circle) {
            $this->response->json2(-1, '圈子不存在');
            return;
	    }
	    if (!isset($circle['filter_tag'])) {
	        $circle['filter_tag'] = array();
	    }
	    
	    foreach ($circle['filter_tag'] as &$value) {
	        if ($value['name'] == $dimension) {
        	    $oldIndex = -1;
                foreach ($value['tag'] as $index => $t) {
                    if ($t == $tag) {
                        $oldIndex = $index;
                        break;
                    }
                }
                $newIndex = $oldIndex + $amount;
                $newIndex = $newIndex < 0 ? 0 : $newIndex;
                $newIndex = $newIndex > count($value['tag']) ? count($value['tag']) : $newIndex;
                array_splice($value['tag'], $newIndex, 0, array_splice($value['tag'], $oldIndex, 1));
                break;
	        }
	    }
	    
	    try {
    	    $modelDataCircle->update(array('_id' => $id), array(
    	    	'filter_tag' => $circle['filter_tag']
    	    ), array('safe' => true));
	    } catch (Exception $e) {
    	    Kohana::$log->error($e->getMessage());
            $this->response->json2(-1, '操作失败');
            return;
	    }
	    
	    $this->response->json2();
	}
	
	public function action_moveDimension()
	{
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_MODIFY);
	    
	    $id = (int) $this->request->param('id');
	    $name = trim($this->request->param('name'));
	    if (!$name) {
	        $this->response->json2(-1, '维度名称为空');
            return;
	    }
	    $amount = (int) $this->request->param('amount');
	    
	    $modelDataCircle = new Model_Data_Circle();
	    $circle = $modelDataCircle->findOne(array('_id' => $id));
	    if (!$circle) {
            $this->response->json2(-1, '圈子不存在');
            return;
	    }
	    if (!isset($circle['filter_tag'])) {
	        $circle['filter_tag'] = array();
	    }
	
	    $oldIndex = -1;
        foreach ($circle['filter_tag'] as $index => $dimension) {
            if ($dimension['name'] == $name) {
                $oldIndex = $index;
                break;
            }
        }
        if ($oldIndex < 0) {
            $this->response->json2(-1, '维度不存在');
            return;
        }
        $newIndex = $oldIndex + $amount;
        $newIndex = $newIndex < 0 ? 0 : $newIndex;
        $newIndex = $newIndex > count($circle['filter_tag']) ? count($circle['filter_tag']) : $newIndex;
        array_splice($circle['filter_tag'], $newIndex, 0, array_splice($circle['filter_tag'], $oldIndex, 1));
	    
	    try {
    	    $modelDataCircle->update(array('_id' => $id), array(
    	    	'filter_tag' => $circle['filter_tag']
    	    ), array('safe' => true));
	    } catch (Exception $e) {
    	    Kohana::$log->error($e->getMessage());
            $this->response->json2(-1, '操作失败');
            return;
	    }
	    
	    $this->response->json2();
	}

	public function action_changeStatus()
	{
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_MODIFY);
	    
	    $id = (int) $this->request->param('id');
	    $status = (int) $this->request->param('status');
	    if (!isset(Model_Data_Circle::$statuses[$status])) {
            $this->response->json2(-1, '状态错误');
            return;
	    }

	    $modelLogicCircle = new Model_Logic_Circle();
	    try {
	        $modelLogicCircle->modify($id, array('status' => $status));
	    } catch (Exception $e) {
    	    Kohana::$log->error($e->getMessage());
            $this->response->json2(-1, '操作失败');
            return;
	    }
        $this->response->json2();
	}

	public function action_videos()
	{
	    $circleId = (int) $this->request->param('id');
	    $offset = (int) $this->request->param('offset', 0);
	    $count = 50;
	
        $modelLogicCircle = new Model_Logic_Circle();
        $circle = $modelLogicCircle->get($circleId);
        if (!$circle) {
			throw new HTTP_Exception_404();
        }
        if ($circle['status'] != Model_Data_Circle::STATUS_PUBLIC) {
        	$this->response->alertBack("圈子已删除。");
        	return;
		}
        // 圈子信息
        $this->template->set('circle', $circle);
        
        $modelLogicRecommend = new Model_Logic_Recommend();
	    $arrOplist = $this->getCircleVideoOPList($circleId);
	    $arrRet = $modelLogicRecommend->getCircleVideosByTag($circleId, $circle['title'], '', $offset, $count);
        $intTotal = $arrRet['total'];
        if ($intTotal > 0) {
            $arrVideo = $arrRet['video'];
        } else {
            $arrVideo = array();
        }
        $arrVideo = $this->mergeVideos($arrVideo, $arrOplist);
	    $this->template->set('offset', $offset);
	    $this->template->set('vcount', $intTotal);
	    $this->template->set('c_videos', $arrVideo);
	}

	/**
	 * 查看被删除的视频列表
	 */
	public function action_delVideos()
	{	    
		$circleId = (int) $this->request->param('id');
		$offset = (int) $this->request->param('offset', 0);
		$count = 50;
	
		$modelLogicCircle = new Model_Logic_Circle();
		$circle = $modelLogicCircle->get($circleId);
		if (!$circle) {
			throw new HTTP_Exception_404();
		}
		if ($circle['status'] != Model_Data_Circle::STATUS_PUBLIC) {
			$this->response->alertBack("圈子已删除。");
			return;
		}
		// 圈子信息
		$this->template->set('circle', $circle);
	
		$modelLogicRecommend = new Model_Logic_Recommend();
		$arrOplist = $this->getCircleVideoOPList($circleId, $count, Model_Data_CircleAdmin::OPTYPE_DEL);
		$arrVideo = array();
		foreach ($arrOplist as $v) {
			$arrTmp = $v['video'];
			$arrTmp['cms_id'] = (string) $v['_id'];
			$arrVideo[] = $arrTmp;
		}
		$this->template->set('offset', $offset);
		$this->template->set('c_videos', $arrVideo);
	}
	
	public function action_preview()
	{
		$circleId = (int) $this->request->param('id');
		$modelLogicRecommend = new Model_Logic_Recommend();
		$arrRet = $modelLogicRecommend->getCircleVideosByTag($circleId, null, '', 0, 200);
        $intTotal = $arrRet['total'];
        if ($intTotal > 0) {
        	$arrVideo = $arrRet['video'];
        } else {
                $arrVideo = array();
        }
        
        $dc = new Model_Data_MongoCollection("cms","video_search","circle_cms_tmp");
        $query = array("cid"=>$circleId);
        $circleOp = $dc->find($query,array(),array("ctime"=>1));
        
        $this->template->set('cops', $circleOp);
        foreach ($circleOp as $val)
        {
        	
        }
	}
	public function action_searchVideo()
	{
		$circleId = (int) $this->request->param('id');
		$modelLogicCircle = new Model_Logic_Circle();
		$circle = $modelLogicCircle->get($circleId);
		if (!$circle) {
			throw new HTTP_Exception_404();
			return;
		}
		if ($circle['status'] != Model_Data_Circle::STATUS_PUBLIC) {
			throw new HTTP_Exception_404();
			return;
		}

				
		$query = (string) $this->request->param('q');
		$query = mb_substr(trim($query), 0, 50);
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
		$result = $modelLogicSearch->search($query, $offset, $count, $sort, $filters,
				$debug);
		
		// 推荐理由
		$reasons = Model_Data_Recommend::videoRecommendReason(Arr::pluck($result['videos'], '_id'));
		foreach ($result['videos'] as &$video) {
			if (isset($reasons[$video['_id']])) {
				$video['recommend_reason'] = $reasons[$video['_id']];
			}
		}
		
		$modelLogicStat = new Model_Logic_Stat();
		$modelLogicStat->complementVideoStatInfo($result['videos']);
		
		// 估计结果总数始终为不带任何过滤条件的搜索结果
		if ($filters) {
			$noFilterResult = $modelLogicSearch->search($query, 0, 0, 'relevance', array());
			$result['total'] = $noFilterResult['total'];
		}
		 
		if ($this->request->is_ajax()) {
			$this->ok($result);
		} else {
			// 圈子信息
			$this->template->set('circle', $circle);
			
			// 搜索结果
			$this->template->set('search_result', $result);			 
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
		}
	}
	
	public function action_videoOpt()
	{		
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_MODIFY);
	    
		$circleId = (int) $this->request->param('cid');
		$videoId = trim($this->request->param('vid', ''));
		$userId = (int) $this->_uid;
		$pos = (int) $this->request->param('pos');
		$type = $this->request->param('type');
				
		$modelDataCircleAdmin = new Model_Data_CircleAdmin();
		$ret = false;
		if ($type == 'cancel') {
			$id = trim($this->request->param('id'));
			if ($id == '') {
				$this->err();
			}
			$ret = $modelDataCircleAdmin->remove($id);
		} elseif ($type == 'add') {
			if ($circleId < 1 || $videoId == '') {
				$this->err();
			}
			if ($pos < 0) $pos = 0;
			$arrParams = array(
					'cid'=> $circleId,
					'vid'=> $videoId,
					'uid'=> $userId,
					'position' =>$pos,
					'optype' => Model_Data_CircleAdmin::OPTYPE_ADD,
					'ctime' => new MongoDate());
			$ret = $modelDataCircleAdmin->addOpt($arrParams);			
		} elseif ($type == 'remove') {
			if ($circleId < 1 || $videoId == '') {
				$this->err();
			}
			$arrParams = array(
					'cid'=> $circleId,
					'vid'=> $videoId,
					'uid'=> $userId,
					'position' => 0,
					'optype' => Model_Data_CircleAdmin::OPTYPE_DEL,
					'ctime' => new MongoDate());
			$ret = $modelDataCircleAdmin->addOpt($arrParams);			
		}
		
		if ($ret && $ret['ok']) {
			$this->ok();
		} else {
			$this->err();
		}
	}
	
	public function action_miniQuery()
	{
		$query = $this->request->param('wd','');
		$count = 100;
		$offset = (int) ($this->request->param('page', 0)  - 1) * $count;
		if ($offset < 0) {
			$offset = 0;
		}
		$sort = 'relevance';
		$filters = array();
		$debug = false;
		$modelLogicSearch = new Model_Logic_Search();
		$result = $modelLogicSearch->search($query, $offset, $count, $sort, $filters, $debug);
		foreach ($result["videos"] as $key=>$video)
		{
			$result["videos"][$key]["thumbnail"] = Util::videoThumbnailUrl($video["thumbnail"]);
		}
		$this->ok($result);
	}

	public function action_categoryTag()
	{
	    $id = (int) $this->request->param('id', 0);
	    
        $categorys = Model_Data_Circle::categorys();
        $tags = array();
        foreach ($categorys as &$category) {
            $category['candidate_tags'] = Model_Data_Circle::categoryCandidateTags($category['id']);
            if ($category['id'] == $id) {
                $current = $category;
            }
            $tags = array_merge($tags, $category['tags']);
        }
        $tags = array_unique($tags);
        
	    $modelLogicCircle = new Model_Logic_Circle();
	    $circlesCountOfTag = array();
	    foreach ($tags as $tag) {
	        $circles = $modelLogicCircle->groupByCategory($id, Model_Logic_Circle::RANK_NEW, 
	            0, 0, 0, $tag);
	        $circlesCountOfTag[$tag] = $circles['total'];
	    }
        if (!$current) {
            $this->response->alertBack('圈子分类不存在');
            return;
        }
        $this->template->set('categorys', $categorys);
        $this->template->set('circlesCountOfTag', $circlesCountOfTag);
        $this->template->set('current', $current);
	}
	
	public function action_addCategoryTag()
	{
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_ADD);
	    
	    $id = (int) $this->request->param('id');
	    $tags = $this->request->param('tags', array());
	    if (!$tags) {
	        $this->response->json2(-1, 'Tag为空');
            return;
	    }
	    
        $categorys = Model_Data_Circle::categorys();
	    array_walk($categorys, function (&$value, $index) use ($id, $tags) {
	        if ($value['id'] == $id) {
	            $value['tags'] = array_values(array_unique(array_merge($value['tags'], $tags)));
	        }
	    });
	    if (!Model_Data_Circle::categorys($categorys)) {
            $this->response->json2(-1, '操作失败');
            return;
	    }	    
	    $this->response->json2();
	}
	
	public function action_deleteCategoryTag()
	{
	    $this->_checkPrivilege(self::RES_CIRCLE, self::PRIV_DELETE);
	    
	    $id = (int) $this->request->param('id');
	    $tags = $this->request->param('tags', array());
	    if (!$tags) {
	        $this->response->json2(-1, 'Tag为空');
            return;
	    }
	    
        $categorys = Model_Data_Circle::categorys();
	    array_walk($categorys, function (&$value, $index) use ($id, $tags) {
	        if ($value['id'] == $id) {
	            $value['tags'] = array_values(array_diff($value['tags'], $tags));
	        }
	    });
	    if (!Model_Data_Circle::categorys($categorys)) {
            $this->response->json2(-1, '操作失败');
            return;
	    }	    
	    $this->response->json2();
	}

	public function action_hot()
	{
	    $category = (int) $this->request->param('category', -1);
	    $status = (int) $this->request->param('status', -1);
	    $filterTag = (int) $this->request->param('filter_tag', -1);
	    $orderby = (string) $this->request->param('orderby', 'popularity');
//	    $direction = (int) $this->request->param('direction', -1);

	    $modelDataCircle = new Model_Data_Circle();
	    $modelDataCircleStatAll = new Model_Data_CircleStatAll();
	    $query = array();
	    if ($category >= 0) {
	        $query['category'] = $category;
	    }
	    if ($status >= 0) {
	        $query['status'] = $status;
	    }
	    $cursor = $modelDataCircleStatAll->getCollection()->find($query, array())
	        ->sort(array($orderby => -1));
	    $circles = array();
	    foreach ($cursor as $doc) {
	        $circle = $modelDataCircle->get($doc['_id']);
	        if (!$circle) {
	            continue;
	        }
            $tagsCount = 0;
            if (isset($circle['filter_tag'])) {
                foreach ($circle['filter_tag'] as $degree) {
                    $tagsCount += count($degree['tag']);            
                }
            }
	        if (($filterTag == 1 && $tagsCount == 0) || ($filterTag == 0 && $tagsCount > 0)) {
	            continue;	            
	        }
	        $circles[] = array_merge(array(
	            'popularity' => 0,
	            'user_count' => 0,
	        ), $doc, $circle);
	        if (count($circles) >= 100) {
	            break;
	        }
	    }
	    $this->template->set('circles', $circles);
	}
	
	private function getCircleVideoOPList($intCircleId, $count=100, $optype = null) {
		$arrReturn = array();
		$modelDataCircleAdmin = new Model_Data_CircleAdmin();
		$objModelUser = new Model_Data_User();
		$objLogicVideo = new Model_Logic_Video();
		$query = array(
			"cid" => intval($intCircleId)
		);
		if ($optype) {
			$query['optype'] = $optype;
		}
		$sort = array("ctime"=>-1);
		$arrTmp = $modelDataCircleAdmin->find($query, array(), $sort, $count);
		if(!$arrTmp) {
			return $arrReturn;
		}
		$arrTmpVids = array_unique( Arr::pluck($arrTmp, "vid") );
		$arrTmpUids = array_unique( Arr::pluck($arrTmp, "uid"));
		$arrVideos = $objLogicVideo->getMulti($arrTmpVids, false, null, false);
		$arrUsers = $objModelUser->getMulti($arrTmpUids, array("nick"));
		foreach($arrTmp as $row) {
			$row["user_name"] = isset($arrUsers[$row['uid']]) ? $arrUsers[$row['uid']]['nick']:"管理员";
			$row["video"] = isset($arrVideos[$row['vid']]) ? $arrVideos[$row['vid']]:array();
			$arrReturn[] = $row;
		}
		
		return $arrReturn;
	}
	
	/**
	 * 把cms指定的视频插入到圈子视频列表
	 * @param array $arrOrg 目前自然排序获得的视频列表
	 * @param array $arrOplist CMS指定的视频，按添加时间倒序排
	 * @return array
	 */
	private function mergeVideos($arrOrg, $arrOplist) {
		$arrReturn = array();
		
		$arrCmsVid = array();
		foreach ($arrOplist as $v) {
			$arrCmsVid[$v['vid']] = true;
		}
		
		//去掉CMS中指定的视频
		$arrTmp = array();
		foreach($arrOrg as $row) {
			if (isset($arrCmsVid[$row['_id']])) {
				continue;
			}
 			$arrTmp[] = $row; 
		}
		$arrReturn = $arrTmp;
		
		$arrOplistTmp = array_reverse($arrOplist); //按照操作时间升序排列	
		//print_r($arrOplistTmp);
		foreach ($arrOplistTmp as $v) {
			//对自然排序的视频重放CMS操作，获取圈子视频的预览结果
			if ($v['optype'] == Model_Data_CircleAdmin::OPTYPE_DEL) {
				continue;
			} elseif ($v['optype'] != Model_Data_CircleAdmin::OPTYPE_ADD) {
				continue;
			}
			
			$arrTmp = $v['video'];
			$arrTmp['is_cms'] = true;
			$arrTmp['cms_id'] = (string) $v['_id'];
			$arrTmp['position'] = $v['position'];			
			if ($v['position'] < 2) {//echo $v['vid'] . "==在开头<br>\n";
				//position为0或者1都是放在开头即可
				array_unshift($arrReturn, $arrTmp);
				continue;
			} elseif (isset($arrReturn[$v['position'] - 1])) {
				//插入指定位置，原来位置的视频顺延
				//echo $v['vid'] . "==在{$v['position']}顺延<br>\n";print_r($arrReturn);
				array_splice($arrReturn, $v['position'] - 1, 0, array($arrTmp));
			} else {
				//如果实际视频总共只有30个，但是先后指定了50为V1，45为V2，那么需要保证第29个是V2，第30个是V1
				$bolProcessed = false;
				foreach ($arrReturn as $k2 => $v2) {
					if (! isset($v2['is_cms'])) {
						continue;
					}
					if ($v2['position'] < $v['position']) {
						continue;
					}
					//echo $v['vid'] . "==在{$v2['_id']}后<br>\n";
					array_splice($arrReturn, $k2, 0, array($arrTmp));
					$bolProcessed = true;
					break;
				}
				if (! $bolProcessed) {
					//echo $v['vid'] . "==在后<br>\n";
					$arrReturn[] = $arrTmp;
				}
			}			
		}
		
		return $arrReturn;
	}
}
