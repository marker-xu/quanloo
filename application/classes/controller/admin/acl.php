<?php 

class Controller_Admin_Acl extends Controller_Admin 
{
    
	public function before() 
	{
	    parent::before();
	    
	    if (!$this->_isSuperAdmin($this->_uid)) {
	        $msg = '你不是超级管理员。';
	        if ($this->request->is_ajax()) {
	            $this->err(null, $msg);
	        } else {
	            $this->response->alertBack($msg);
	        }
	    }
	}
	
	public function action_index() 
	{
	    $modelLogicUser = new Model_Logic_User();
	    $users = $modelLogicUser->getMulti(array_keys($this->_acl), false, false);
	    $this->template->set('acl', $this->_acl);
	    $this->template->set('users', $users);
	}
	
	public function action_addAdmin()
	{
	    if ($this->request->method() == 'POST') {
    	    $id = (int) $this->request->param('id');
    	    if ($id <= 0) {
	            $this->response->alertBack('用户ID非法。');
	            return;
    	    }
    	    if (isset($this->_acl[$id])) {
	            $this->response->alertBack('已是管理员。');
	            return;
    	    }
	        $modelLogicUser = new Model_Logic_User();
	        if (!$modelLogicUser->get($id)) {
	            $this->response->alertBack('用户不存在。');
	            return;
	        }
    	    $posts = $this->request->post();
    	    
    	    $this->_acl[$id] = $this->_parsePrivilege($posts);
	    
	        $modelDataWebRedis = new Model_Data_WebRedis();
	        if (!$modelDataWebRedis->cmsAcl($this->_acl)) {
	            $this->response->alertBack('增加失败');
	            return;
	        }
	        $this->response->alertBack('增加成功');
	        return;
	    }
	}
	
	public function action_modifyAdmin()
	{
	    $id = (int) $this->request->param('id');
	    if ($id <= 0) {
            $this->response->alertBack('用户ID非法。');
            return;
	    }
	    if ($this->request->method() == 'POST') {
    	    $posts = $this->request->post();
    	    
    	    $this->_acl[$id] = $this->_parsePrivilege($posts);
	    
	        $modelDataWebRedis = new Model_Data_WebRedis();
	        if (!$modelDataWebRedis->cmsAcl($this->_acl)) {
	            $this->response->alertBack('修改失败');
	            return;
	        }
	        $this->response->alertBack('修改成功');
	        return;
	    }
	    $this->template->set('id', $id);
	    $this->template->set('resources', $this->_acl[$id]);
	}
	
	public function action_deleteAdmin()
	{
	    $id = (int) $this->request->param('id');
	    if ($id <= 0) {
            $this->response->json2(-1, '用户ID非法。');
            return;
	    }
	    
        unset($this->_acl[$id]);
    
        $modelDataWebRedis = new Model_Data_WebRedis();
        if (!$modelDataWebRedis->cmsAcl($this->_acl)) {
            $this->response->json2(-1, '删除失败。');
            return;
        }
        $this->response->json2();
	}
	
	public function _parsePrivilege($posts)
	{
	    // 最基本的CMS查看权限
	    $resources = array(
	        self::RES_CMS => array(self::PRIV_VIEW => true)
	    );
	    array_walk($posts, function ($value, $key) use (&$resources) {
	        if (preg_match('/^(\w+)_privileges$/', $key, $matches)) {
	            $resource = $matches[1];
        	    if (!isset($resources[$resource])) {
        	        $resources[$resource] = array();
        	    }
        	    $privileges = $value;
        	    foreach ($privileges as $privilege) {
            	    $resources[$resource][$privilege] = true;
        	    }
	        }
	    });
	    return $resources;
	}
}
