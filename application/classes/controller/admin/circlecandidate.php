<?php 

class Controller_Admin_CircleCandidate extends Controller_Admin 
{
	public function before() 
	{
	    parent::before();
	    
	    $this->_checkPrivilege(self::RES_CANDIDATE_CIRCLE, self::PRIV_VIEW);
	}
	
	public function action_index() 
	{
	    $page = (int) $this->request->param('page', 1);
	    $category = (int) $this->request->param('category', -1);
	    $source = (int) $this->request->param('source', -1);
	    $status = (int) $this->request->param('status', -1);
	    $orderby = (string) $this->request->param('orderby', 'create_time');
	    $direction = (int) $this->request->param('direction', -1);
	    $count = 30;
		$keyword = trim($this->request->param('keyword', ''));
	    
	    $modelDataCircleCandidate = new Model_Data_CircleCandidate();
	    $query = array();
	    if ($category >= 0) {
	        $query['category'] = $category;
	    }
	    if ($source >= 0) {
	        $query['source'] = $source;
	    }
	    if ($status >= 0) {
	        $query['status'] = $status;
	    }
		if ($keyword) {
			$query['title'] = new MongoRegex("/$keyword/i");
		}
	    $circles = $modelDataCircleCandidate->find($query, array(), array($orderby => $direction), 
	        $count, ($page - 1) * $count);
	    $this->template->set('circles', $circles);
	    
	    $total = $modelDataCircleCandidate->count($query);
	    $pagination = Pagination::factory(array(
	    	'total_items' => $total, 
	    	'items_per_page' => $count
	    ));
	    $this->template->set('pagination', $pagination);
	}
	
	public function action_add()
	{
	    $this->_checkPrivilege(self::RES_CANDIDATE_CIRCLE, self::PRIV_ADD);
	    
	    if ($this->request->method() == 'POST') {
	        $doc = array();
    	    $title = (string) $this->request->param('title');
    	    $title = mb_substr(trim($title), 0, 50);
    	    if (strlen($title) == 0) {
                $this->response->alertBack('圈子名称不能为空');
                return;
    	    }
	        $doc['category'] = $this->request->param('category', array());
	        foreach ($doc['category'] as &$category) {
	            $category = (int) $category;
	        }
	        $doc['tag'] = explode(',', trim($this->request->param('tag')));
    	    $status = (int) $this->request->param('status');
	        
    	    $modelDataCircleCandidate = new Model_Data_CircleCandidate();
    	    if ($modelDataCircleCandidate->getByTitle($title)) {
	            $this->response->alertBack('圈子已存在');
    	        return;
    	    }
	    
	        $modelLogicCircle = new Model_Logic_Circle();
    	    try {
    	        $id = $modelLogicCircle->createCandidateCircle($title, Model_Data_CircleCandidate::SOURCE_EDITOR_ADD, 
    	            $this->_uid, Model_Data_CircleCandidate::STATUS_PENDING, $doc);
    	        $this->_changeStatus($id, $status);
    	    } catch (Exception $e) {
    	        Kohana::$log->error($e);
	            $this->response->alertBack($e->getMessage());
	            return;
    	    }
	        $this->response->alertBack('创建成功');
	    } else {
	        
	    }
	}
	
	public function action_mod()
	{
	    $this->_checkPrivilege(self::RES_CANDIDATE_CIRCLE, self::PRIV_MODIFY);
	    
	    $id = (string) $this->request->param('id');
	    
	    $modelDataCircleCandidate = new Model_Data_CircleCandidate();
	    $circle = $modelDataCircleCandidate->findOne(array('_id' => new MongoId($id)));
	    if (!$circle) {
            $this->response->alertBack('候选圈子不存在');
            return;
	    }
	    
	    if ($this->request->method() == 'POST') {
	        $doc = array();
	        $doc['title'] = trim($this->request->param('title'));
	        $doc['category'] = $this->request->param('category', array());
	        foreach ($doc['category'] as &$category) {
	            $category = (int) $category;
	        }
	        $doc['tag'] = explode(',', trim($this->request->param('tag')));
	        
    	    try {
    	        $modelDataCircleCandidate->modify($id, $doc);
    	    } catch (Model_Data_Exception $e) {
	            $this->response->alertBack($e->getMessage());
	            return;
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
	
	public function action_del()
	{
	    $this->_checkPrivilege(self::RES_CANDIDATE_CIRCLE, self::PRIV_DELETE);
	    
	    $id = (string) $this->request->param('id');
	
	    $modelDataCircleCandidate = new Model_Data_CircleCandidate();
	    try {
	        $modelDataCircleCandidate->delete(array('_id' => new MongoId($id)), 
	            array('safe' => true));
	    } catch (Exception $e) {
	        Kohana::$log->error($e->getMessage());
            $this->response->json2(-1, '删除失败');
            return;
	    }      
        $this->response->json2();
	}
	
	public function action_batchop()
	{
	    $op = (string) $this->request->param('op');
	    $ids = (string) $this->request->param('ids');
	    $ids = array_filter(explode(',', trim($ids)));
	    if (!$ids) {
	        $this->response->json2(-1, 'ids为空');
	        return;
	    }
	    
    	try {
    	    if ($op == 'audit') {
	            $this->_checkPrivilege(self::RES_CANDIDATE_CIRCLE, self::PRIV_MODIFY);
	            
    	        $status = (int) $this->request->param('status');
    	        foreach ($ids as $id) {
    	            $this->_changeStatus($id, $status);
    	        }
    	    } else if ($op == 'delete') {
	            $this->_checkPrivilege(self::RES_CANDIDATE_CIRCLE, self::PRIV_DELETE);
	            
    	        $modelDataCircleCandidate = new Model_Data_CircleCandidate(); 
    	        $modelDataCircleCandidate->delete(array('_id' => array('$in' => array_map(function ($value) {
    	            return new MongoId($value);
    	        }, $ids))));
    	    }
	    } catch (Exception $e) {
	        Kohana::$log->error($e);
            $this->response->json2(-1, $e->getMessage());
            return;
	    }
	    
	    $this->response->json2();
	}
	
	public function action_blacklist()
	{
	    
	}
	
	public function _changeStatus($id, $status)
	{
    	$modelDataCircleCandidate = new Model_Data_CircleCandidate();
    	$modelDataCircleCandidate->setSlaveOkay(false);
        $candidateCircle = $modelDataCircleCandidate->findOne(array(
        	'_id' => new MongoId($id),
        ));
        if (!$candidateCircle) {
            return true;
        }
        $modelLogicCircle = new Model_Logic_Circle();
        if ($status == Model_Data_CircleCandidate::STATUS_APPROVED) {
            $modelLogicCircle->create($candidateCircle['title'], $candidateCircle['category'], 
                1846037590, $candidateCircle['tag'], Model_Data_Circle::STATUS_UNINITIALIZED, array(
                    'official' => 1
                ));
        }
        $modelDataCircleCandidate->update(array(
        	'_id' => new MongoId($id),
        ), array('status' => $status));
        return true;
	}
}
