<?php 

class Controller_Admin_PromoteCircle extends Controller_Admin 
{
	public function before() 
	{
	    parent::before();
	    
	    $this->_checkPrivilege(self::RES_PROMOTE_CIRCLE, self::PRIV_VIEW);
	}
	
	public function action_index() 
	{
	    $modelLogicCircle = new Model_Logic_Circle();
	    $promoteCircles = $modelLogicCircle->promoteCircles();
	    $this->template->set('promote_circles', $promoteCircles);
	}
	
	public function action_add()
	{
	    $this->_checkPrivilege(self::RES_PROMOTE_CIRCLE, self::PRIV_ADD);
	    
	    if ($this->request->method() == 'POST') {
	        $id = (int) $this->request->param('id');
	        $keepThumbnail = (bool) $this->request->param('keep_thumbnail');
	        
    	    $modelLogicCircle = new Model_Logic_Circle();
    	    $promoteCircles = $modelLogicCircle->promoteCircles();
    	    foreach ($promoteCircles as $circle) {
    	        if ($circle['_id'] == $id) {
    	            $this->response->alertBack('圈子已添加过');
    	            return;
    	        }
    	    }
	        
	        $modelLogicCircle = new Model_Logic_Circle();
	        $circle = $modelLogicCircle->get($id);
	        if (!$circle) {
	            $this->response->alertBack('圈子不存在');
	            return;
	        }
	        
    		$fdfs = new FastDFS(FASTDFS_CLUSTER_WEB_STORAGE);
    		
	        if (!Upload::not_empty($_FILES['thumbnail'])) {
	            $this->response->alertBack('请上传圈子九宫图');
	            return;
	        }
	        if (!Upload::image($_FILES['thumbnail'])) {
	            $this->response->alertBack('上传九宫图必须是图片');
	            return;
	        }
	        if ($_FILES['thumbnail']['size'] >= 30 * 1024) {
	            $this->response->alertBack('上传九宫图大小不能超过30K');
	            return;
	        }
    		$result = $fdfs->storage_upload_by_filename($_FILES['thumbnail']['tmp_name'], 
        		pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
    		if (!$result) {
	            $this->response->alertBack('上传九宫图到存储集群失败');
	            return;
    		}
    		$circle['tn_path'] = implode('/', $result);
    		$circle['tn_status'] = 2;
    		
	        if (!Upload::not_empty($_FILES['recommend_image'])) {
	            $this->response->alertBack('请上传圈子推广图');
	            return;
	        }
	        if (!Upload::image($_FILES['recommend_image'])) {
	            $this->response->alertBack('上传推广图必须是图片');
	            return;
	        }
	        if ($_FILES['recommend_image']['size'] >= 30 * 1024) {
	            $this->response->alertBack('上传推广图大小不能超过30K');
	            return;
	        }
    		$result = $fdfs->storage_upload_by_filename($_FILES['recommend_image']['tmp_name'], 
        		pathinfo($_FILES['recommend_image']['name'], PATHINFO_EXTENSION));
    		if (!$result) {
	            $this->response->alertBack('上传推广图到存储集群失败');
	            return;
    		}
    		$circle['recommend_image'] = implode('/', $result);
    		
    		$circle['keep_thumbnail'] = $keepThumbnail;
    		
	        $modelDataCircle = new Model_Data_Circle();
	        try {
	            $modelDataCircle->update(array(
	            	'_id' => (int) $circle['_id']
	            ), array(
	                'tn_path' => $circle['tn_path'],
	                'tn_status' => $circle['tn_status']
	            ));
	        } catch (Exception $e) {
	            Kohana::$log->debug($e);
	            $this->response->alertBack('更新圈子信息失败');
	            return;
	        }
    	    
    	    array_unshift($promoteCircles, $circle);
    	    $result = $modelLogicCircle->savePromoteCircles($promoteCircles);
	        
    	    if ($result) {
	            $this->response->alertBack('添加成功');
    	    } else {
	            $this->response->alertBack('添加失败');
    	    }
	    } else {
	        $id = (int) $this->request->param('id');
	        
	        $this->template->set('id', $id);
	    }
	}
	
	public function action_mod()
	{
	    $this->_checkPrivilege(self::RES_PROMOTE_CIRCLE, self::PRIV_MODIFY);
	    
	    $id = (int) $this->request->param('id');
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    $promoteCircles = $modelLogicCircle->promoteCircles();
	    $circle = NULL;
	    foreach ($promoteCircles as $value) {
	        if ($value['_id'] == $id) {
	            $circle = $value;
	            break;
	        }
	    }
	    if (!$circle) {
            $this->response->alertBack('推广圈子未找到');
            return;
	    }
	    
	    if ($this->request->method() == 'POST') {
	        $circle['keep_thumbnail'] = (bool) $this->request->param('keep_thumbnail');
	        
            $fdfs = new FastDFS(FASTDFS_CLUSTER_WEB_STORAGE);
            
	        if (Upload::not_empty($_FILES['thumbnail']) 
	            && Upload::image($_FILES['thumbnail'])) {
    	        if ($_FILES['thumbnail']['size'] >= 30 * 1024) {
    	            $this->response->alertBack('上传九宫图大小不能超过30K');
    	            return;
    	        }
        		$result = $fdfs->storage_upload_by_filename($_FILES['thumbnail']['tmp_name'], 
        		    pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
        		if (!$result) {
    	            $this->response->alertBack('上传九宫图到存储集群失败');
    	            return;
        		}
        		$pos = strpos($circle['tn_path'], '/');
        		$group = substr($circle['tn_path'], 0, $pos);
        		$path = substr($circle['tn_path'], $pos + 1);
        		if (!$fdfs->storage_delete_file($group, $path)) {
    		        Kohana::$log->warn("storage_delete_file failed: $group, $path");
    		    }
        		$circle['tn_path'] = implode('/', $result);
    		
    	        $modelDataCircle = new Model_Data_Circle();
    	        try {
    	            $modelDataCircle->update(array(
    	            	'_id' => (int) $circle['_id']
    	            ), array(
    	                'tn_path' => $circle['tn_path']
    	            ));
    	        } catch (Exception $e) {
    	            Kohana::$log->debug($e);
    	            $this->response->alertBack('更新圈子信息失败');
    	            return;
    	        }
	        }
	        
	        if (Upload::not_empty($_FILES['recommend_image']) 
	            && Upload::image($_FILES['recommend_image'])) {
    	        if ($_FILES['recommend_image']['size'] >= 30 * 1024) {
    	            $this->response->alertBack('上传推广图大小不能超过30K');
    	            return;
    	        }
        		$result = $fdfs->storage_upload_by_filename($_FILES['recommend_image']['tmp_name'], 
        		    pathinfo($_FILES['recommend_image']['name'], PATHINFO_EXTENSION));
        		if (!$result) {
    	            $this->response->alertBack('上传推广图到存储集群失败');
    	            return;
        		}
        		$pos = strpos($circle['recommend_image'], '/');
        		$group = substr($circle['recommend_image'], 0, $pos);
        		$path = substr($circle['recommend_image'], $pos + 1);
        		if (!$fdfs->storage_delete_file($group, $path)) {
    		        Kohana::$log->warn("storage_delete_file failed: $group, $path");
    		    }
        		$circle['recommend_image'] = implode('/', $result);
	        }
    	    
    	    foreach ($promoteCircles as &$value) {
    	        if ($value['_id'] == $circle['_id']) {
    	            $value = $circle;
    	            break;
    	        }
    	    }
    	    $result = $modelLogicCircle->savePromoteCircles($promoteCircles);
	        
    	    if ($result) {
	            $this->response->alertBack('修改成功');
    	    } else {
	            $this->response->alertBack('修改失败');
    	    }
	    } else {    	    
	        $this->template->set('circle', $circle);
	    }
	}
	
	public function action_move()
	{
	    $this->_checkPrivilege(self::RES_PROMOTE_CIRCLE, self::PRIV_MODIFY);
	    
	    $id = (int) $this->request->param('id');
	    $amount = (int) $this->request->param('amount');
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    $promoteCircles = $modelLogicCircle->promoteCircles();
	    $found = false;
	    foreach ($promoteCircles as $index => $value) {
	        if ($value['_id'] == $id) {
	            $found = true;
	            break;
	        }
	    }
	    if (!$found) {
            $this->response->json2(-1, '推广圈子未找到');
            return;
	    }
	    $oldIndex = $index;
	    $newIndex = $index + $amount;
	    if ($newIndex < 0) {
	        $newIndex = 0;
	    }
	    if ($newIndex > count($promoteCircles)) {
	        $newIndex = count($promoteCircles);
	    }
	    if ($oldIndex == $newIndex) {
            $this->response->json2();
	    }
	    $circle = $promoteCircles[$oldIndex];
	    unset($promoteCircles[$oldIndex]);
	    $promoteCircles = array_merge(array_slice($promoteCircles, 0, $newIndex), 
	        array($circle), array_slice($promoteCircles, $newIndex));
	    $promoteCircles = array_values($promoteCircles);
	    $result = $modelLogicCircle->savePromoteCircles($promoteCircles);
        
	    if ($result) {
            $this->response->json2();
	    } else {
            $this->response->json2(-1, '移动失败');
	    }
	}
	
	public function action_delete()
	{
	    $this->_checkPrivilege(self::RES_PROMOTE_CIRCLE, self::PRIV_DELETE);
	    
	    $id = (int) $this->request->param('id');
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    $promoteCircles = $modelLogicCircle->promoteCircles();
	    $found = false;
	    foreach ($promoteCircles as $index => $promoteCircle) {
	        if ($promoteCircle['_id'] == $id) {
	            $found = true;
	            break;
	        }
	    }
	    if (!$found) {
            $this->response->json2(-1, '推广圈子未找到');
            return;
	    }
    		
        $modelDataCircle = new Model_Data_Circle();
        try {
            $modelDataCircle->update(array(
            	'_id' => (int) $promoteCircle['_id']
            ), array(
                'tn_status' => ($promoteCircle['keep_thumbnail'] ? 2 : 1)
            ));
        } catch (Exception $e) {
            Kohana::$log->debug($e);
            $this->response->alertBack('更新圈子信息失败');
            return;
        }
	    
	    unset($promoteCircles[$index]);
	    $promoteCircles = array_values($promoteCircles);
	    $result = $modelLogicCircle->savePromoteCircles($promoteCircles);
        
	    if ($result) {
            $this->response->json2();
	    } else {
            $this->response->json2(-1, '删除失败');
	    }
	}
	
	public function action_publish()
	{	    
	    $this->_checkPrivilege(self::RES_PROMOTE_CIRCLE, self::PRIV_MODIFY);
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    $promoteCircles = $modelLogicCircle->promoteCircles();
	    $result = $modelLogicCircle->savePromoteCircles($promoteCircles, FALSE);
        
	    if ($result) {
            $this->response->json2();
	    } else {
            $this->response->json2(-1, '发布失败');
	    }
	}
}
