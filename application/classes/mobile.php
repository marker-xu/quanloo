<?php 
class Mobile extends JKit_Controller
{
    const ERROR_NEED_LOGIN = 'sys.permission.need_login';
    
	protected $_uid; // 未登录为NULL，请不要修改成0或其它，有的地方依赖NULL值
	protected $_user;
	protected $_token;
	
	public function before() {
		JKit::$security['csrf'] = false;
	    parent::before();
	    
		//用户信息初始化
		$token = trim( $this->request->param('token') );
		if($token) {
			#TODO 初始化用户信息
			$objLogicUser = new Model_Logic_Mobile_User();
			$arrUserInfo = $objLogicUser->getUserByToken($token);
			if($arrUserInfo) {
				$this->_user = $arrUserInfo;
			}
		    $this->_token = $token;
		}
		if($this->_user) {
			$this->_uid = (int) $this->_user['_id'];
		}
		// xhprof
    	if ($this->request->param('xhprof')) {
            if (!extension_loaded('xhprof')) {
                dl('xhprof.so');
            }
            xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
//            xhprof_enable(XHPROF_FLAGS_NO_BUILTINS);
        }
	}
	
	/**
	 * 
	 * 登录判断，跳转,ajax返回，错误
	 */
	protected function _needLogin() {
		if ( !$this->_uid || !$this->_user) {
			//deny not logined user
			$this->err(null, NULL, null, null, self::ERROR_NEED_LOGIN);
		}
	}
	
    public function after()
    {
        // xhprof
        if ($this->request->param('xhprof')) {
            $xhprof_data = xhprof_disable();
            include_once DOCROOT."/xhprof_lib/utils/xhprof_lib.php";
            include_once DOCROOT."/xhprof_lib/utils/xhprof_runs.php";
            $xhprof_runs = new XHProfRuns_Default();
            $xhprofid = $xhprof_runs->save_run($xhprof_data, "xhprof");
            $this->trace('xhprofid', $xhprofid);
        }
        
        parent::after();
    }
}