<?php 

class Controller_Admin_TopicCircle extends Controller_Admin 
{
	public function before() 
	{
	    parent::before();
	}
	
	public function action_index() 
	{
	    $modelLogicCircle = new Model_Logic_Circle();
	    $topicCircles = $modelLogicCircle->topicCircles();
	    $this->template->set('topic_circles', $topicCircles);
	}
	
	public function action_add()
	{
	    if ($this->request->method() == 'POST') {
	        $id = (int) $this->request->param('id');
	        
    	    $modelLogicCircle = new Model_Logic_Circle();
    	    $topicCircles = $modelLogicCircle->topicCircles();
    	    foreach ($topicCircles as $circle) {
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
	        
	        Kohana::$log->debug(__FUNCTION__, $_FILES);
	        if (!Upload::not_empty($_FILES['big_picture'])) {
	            $this->response->alertBack('请选择要上传的大图');
	            return;
	        }
	        if (!Upload::image($_FILES['big_picture'])) {
	            $this->response->alertBack('上传的大图必须是图片');
	            return;
	        }
	        if (!Upload::not_empty($_FILES['small_picture'])) {
	            $this->response->alertBack('请选择要上传的小图');
	            return;
	        }
	        if (!Upload::image($_FILES['small_picture'])) {
	            $this->response->alertBack('上传的小图必须是图片');
	            return;
	        }
	        
    		$fdfs = new FastDFS(FASTDFS_CLUSTER_WEB_STORAGE);
    		$result = $fdfs->storage_upload_by_filename($_FILES['big_picture']['tmp_name'], 
        		pathinfo($_FILES['big_picture']['name'], PATHINFO_EXTENSION));
    		if (!$result) {
	            $this->response->alertBack('上传大图到存储集群失败');
	            return;
    		}
    		$circle['big_picture'] = Util::webStorageClusterFileUrl(implode('/', $result));
    		$result = $fdfs->storage_upload_by_filename($_FILES['small_picture']['tmp_name'], 
        		pathinfo($_FILES['big_picture']['name'], PATHINFO_EXTENSION));
    		if (!$result) {
	            $this->response->alertBack('上传小图到存储集群失败');
	            return;
    		}
    		$circle['small_picture'] = Util::webStorageClusterFileUrl(implode('/', $result));
    		
    	    $modelLogicRecommend = new Model_Logic_Recommend();
    	    $circle['videos'] = array();
    	    
    	    array_unshift($topicCircles, $circle);
    	    $result = $modelLogicCircle->saveTopicCircles($topicCircles);
	        
    	    if ($result) {
	            $this->response->alertBack('添加成功');
    	    } else {
	            $this->response->alertBack('保存到数据库失败');
    	    }
	    } else {
	        $id = (int) $this->request->param('id');
	        
	        $this->template->set('id', $id);
	    }
	}
	
	public function action_mod()
	{
	    $id = (int) $this->request->param('id');
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    $topicCircles = $modelLogicCircle->topicCircles();
	    $circle = NULL;
	    foreach ($topicCircles as $value) {
	        if ($value['_id'] == $id) {
	            $circle = $value;
	            break;
	        }
	    }
	    if (!$circle) {
            $this->response->alertBack('主题圈未找到');
            return;
	    }
	    
	    if ($this->request->method() == 'POST') {
	        Kohana::$log->debug(__FUNCTION__, $_FILES);
            $fdfs = new FastDFS(FASTDFS_CLUSTER_WEB_STORAGE);
	        if (Upload::not_empty($_FILES['big_picture']) 
	            && Upload::image($_FILES['big_picture'])) {
        		$result = $fdfs->storage_upload_by_filename($_FILES['big_picture']['tmp_name'], 
        		    pathinfo($_FILES['big_picture']['name'], PATHINFO_EXTENSION));
        		if (!$result) {
    	            $this->response->alertBack('上传大图到存储集群失败');
    	            return;
        		}
        		$circle['big_picture'] = Util::webStorageClusterFileUrl(implode('/', $result));
	        }
	        if (Upload::not_empty($_FILES['small_picture']) 
	            && Upload::image($_FILES['small_picture'])) {
        		$result = $fdfs->storage_upload_by_filename($_FILES['small_picture']['tmp_name'], 
        		    pathinfo($_FILES['big_picture']['name'], PATHINFO_EXTENSION));
        		if (!$result) {
    	            $this->response->alertBack('上传小图到存储集群失败');
    	            return;
        		}
        		$circle['small_picture'] = Util::webStorageClusterFileUrl(implode('/', $result));
	        }
	        
	        $videos = $this->request->param('videos');
	        $videoIds = array();
	        foreach ($videos as $id) {
	            if (strlen($id) == 32) {
	                $videoIds[] = $id;
	            } else if (preg_match('/^http:\/\/.*id=([0-9a-f]{32})/', $id, $matches)) {
	                $videoIds[] = $matches[1];
	            }
	        }
    	    $modelLogicVideo = new Model_Logic_Video();
    	    $circle['videos'] = array_values($modelLogicVideo->getMulti($videoIds, true));
    	    
    	    foreach ($topicCircles as &$value) {
    	        if ($value['_id'] == $circle['_id']) {
    	            $value = $circle;
    	            break;
    	        }
    	    }
    	    $result = $modelLogicCircle->saveTopicCircles($topicCircles);
	        
    	    if ($result) {
	            $this->response->alertBack('修改成功');
    	    } else {
	            $this->response->alertBack('保存到数据库失败');
    	    }
	    } else {	        
    	    $modelLogicRecommend = new Model_Logic_Recommend();
    	    $recommendVideos = $modelLogicRecommend->getCircleVideos($id, 0, 100);
    	    
	        $this->template->set('circle', $circle);
	        $this->template->set('recommendVideos', $recommendVideos);
	    }
	}
	
	public function action_move()
	{
	    $id = (int) $this->request->param('id');
	    $amount = (int) $this->request->param('amount');
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    $topicCircles = $modelLogicCircle->topicCircles();
	    $found = false;
	    foreach ($topicCircles as $index => $value) {
	        if ($value['_id'] == $id) {
	            $found = true;
	            break;
	        }
	    }
	    if (!$found) {
            $this->response->json2(-1, '主题圈未找到');
            return;
	    }
	    $oldIndex = $index;
	    $newIndex = $index + $amount;
	    if ($newIndex < 0) {
	        $newIndex = 0;
	    }
	    if ($newIndex > count($topicCircles)) {
	        $newIndex = count($topicCircles);
	    }
	    if ($oldIndex == $newIndex) {
            $this->response->json2();
	    }
	    $circle = $topicCircles[$oldIndex];
	    unset($topicCircles[$oldIndex]);
	    $topicCircles = array_merge(array_slice($topicCircles, 0, $newIndex), 
	        array($circle), array_slice($topicCircles, $newIndex));
	    $topicCircles = array_values($topicCircles);
	    $result = $modelLogicCircle->saveTopicCircles($topicCircles);
        
	    if ($result) {
            $this->response->json2();
	    } else {
            $this->response->json2(-1, '保存到数据库失败');
	    }
	}
	
	public function action_delete()
	{
	    $id = (int) $this->request->param('id');
	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    $topicCircles = $modelLogicCircle->topicCircles();
	    $found = false;
	    foreach ($topicCircles as $index => $value) {
	        if ($value['_id'] == $id) {
	            $found = true;
	            break;
	        }
	    }
	    if (!$found) {
            $this->response->json2(-1, '主题圈未找到');
            return;
	    }
	    unset($topicCircles[$index]);
	    $topicCircles = array_values($topicCircles);
	    $result = $modelLogicCircle->saveTopicCircles($topicCircles);
        
	    if ($result) {
            $this->response->json2();
	    } else {
            $this->response->json2(-1, '保存到数据库失败');
	    }
	}
	
	public function action_publish()
	{	    
	    $modelLogicCircle = new Model_Logic_Circle();
	    $topicCircles = $modelLogicCircle->topicCircles();
	    $topicCircles = array_slice($topicCircles, 0, 5);
	    foreach ($topicCircles as &$circle) {
	        $circle['videos'] = array_slice($circle['videos'], 0, 5);
	    }
	    $result = $modelLogicCircle->saveTopicCircles($topicCircles, FALSE);
        
	    if ($result) {
            $this->response->json2();
	    } else {
            $this->response->json2(-1, '保存到数据库失败');
	    }
	}
}
