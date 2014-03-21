<?php 

class Controller_Admin extends Controller 
{
    const RES_CMS = 'cms';
    const RES_PROMOTE_CIRCLE = 'promote_circle';
    const RES_CANDIDATE_CIRCLE = 'candidate_circle';
    const RES_CIRCLE = 'circle';
    const RES_HOT_QUERY = 'hot_query';
    const RES_USER_FEEDBACK = 'user_feedback';
    const RES_FRIEND_LINK = 'friend_link';
    const RES_SENSITIVE_WORDS = 'sensitive_words';
    const RES_APP_RELEASE = 'app_release';
    const RES_INSITE_AD = 'insite_ad';
    
    const PRIV_VIEW = 'view';
    const PRIV_ADD = 'add';
    const PRIV_MODIFY = 'modify';
    const PRIV_DELETE = 'delete';
    
    // 模块列表
    public static $resources = array(
        self::RES_CMS => 'CMS',
        self::RES_PROMOTE_CIRCLE => '推广圈子管理',
        self::RES_CANDIDATE_CIRCLE => '候选圈子管理',
        self::RES_CIRCLE => '圈子管理',
        self::RES_HOT_QUERY => '热搜词管理',
        self::RES_USER_FEEDBACK => '用户反馈',
        self::RES_FRIEND_LINK => '友情链接管理',
        self::RES_SENSITIVE_WORDS => '敏感词管理',
        self::RES_APP_RELEASE => '移动APP发布',
    	self::RES_INSITE_AD => '站内广告管理',
    );
    
    // 操作列表
    public static $privileges = array(
        self::PRIV_VIEW => '查看',
        self::PRIV_ADD => '新增',
        self::PRIV_MODIFY => '修改',
        self::PRIV_DELETE => '删除',
    );
    
    protected $_acl;
    
	public function before() 
	{
	    parent::before();
	    
	    $this->_needLogin();
	    
	    $modelDataWebRedis = new Model_Data_WebRedis();
	    $this->_acl = $modelDataWebRedis->cmsAcl();
	    
	    $this->_checkPrivilege(self::RES_CMS, self::PRIV_VIEW);
	}
	
	/**
	 * 检查当前登录用户是否具有指定模块的指定操作
	 * @param string|array $resources 模块列表
	 * @param string|array $privileges 操作列表
	 * @return bool
	 */
	protected function _checkPrivilege($resources, $privileges)
	{
	    if (Kohana::$environment == Kohana::DEVELOPMENT) {
	        return true;
	    }
	    if ($this->_isSuperAdmin($this->_uid) 
	        || $this->_isAllowed($this->_uid, $resources, $privileges)) {
	        return true;
	    } else {
    	    if (!is_array($resources)) {
    	        $resources = array($resources);
    	    }
    	    if (!is_array($privileges)) {
    	        $privileges = array($privileges);
    	    }
    	    $resNames = array();
    	    foreach ($resources as $resource) {
    	        $resNames[] = self::$resources[$resource];
    	    }
    	    $privNames = array();
    	    foreach ($privileges as $privilege) {
    	        $privNames[] = self::$privileges[$privilege];
    	    }
	        $msg = "你不具备 [".implode(' ', $resNames)."] 模块的 [".implode(' ', $privNames)."] 操作权限。";
	        if ($this->request->is_ajax()) {
	            $this->err(null, $msg);
	        } else {
	            $this->response->alertBack($msg);
	        }
	        return false;
	    }
	}
	
	protected function _isAllowed($roles, $resources, $privileges)
	{
	    if (!is_array($roles)) {
	        $roles = array($roles);
	    }
	    if (!is_array($resources)) {
	        $resources = array($resources);
	    }
	    if (!is_array($privileges)) {
	        $privileges = array($privileges);
	    }
	    foreach ($roles as $role) {
	        if (!isset($this->_acl[$role])) {
	            return false;
	        }
	        foreach ($resources as $resource) {
    	        if (!isset($this->_acl[$role][$resource])) {
    	            return false;
    	        }
    	        foreach ($privileges as $privilege) {
        	        if (!isset($this->_acl[$role][$resource][$privilege]) 
        	            || !$this->_acl[$role][$resource][$privilege]) {
        	            return false;
        	        }
    	        }
	        }
	    }
	    return true;
	}
	
	protected function _isSuperAdmin($userId)
	{
	    return in_array($userId, Kohana::$config->load('admin')->administrators);
	}
}
