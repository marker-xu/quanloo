<?php

/**
 * 控制器基类，放置一些控制器的公用属性和方法
 * @author wangjiajun
 */
class Controller extends JKit_Controller
{
    const ERROR_NEED_LOGIN = 'sys.permission.need_login';
    
	protected $_uid; // 未登录为NULL，请不要修改成0或其它，有的地方依赖NULL值
	protected $_user;
	
	public function before() {
	    parent::before();
	    
		//用户信息初始化
		$loginUid = (int) $this->request->param('_login_uid');
		$loginTm = (int) $this->request->param('_login_tm');
		$loginSign = (string) $this->request->param('_login_sign');
		if ($loginUid && $loginTm && md5($loginUid.'quanloo'.$loginTm) == $loginSign) {
		    //QA测试时模仿登录的hack
		    $modelDataUser = new Model_Data_User();
		    $this->_user = $modelDataUser->get($loginUid);
		} else {
		    $this->_user = Session::instance()->get('user');
		}
		if($this->_user) {
			$this->_uid = (int) $this->_user['_id'];
		}
		$this->template->set('login_user', $this->_user);
		if(!$this->_uid ) {
			$redirectUrl = "http://".DOMAIN_SITE."/user/login?f=".urlencode(preg_replace('/^\//', '', strip_tags($_SERVER['REQUEST_URI'])));
			$this->template->set('login_iframe_url', Model_Data_Sndauser::buildLoginUrl($redirectUrl, true));	
		}
		$this->template->set('categorys', Model_Data_Circle::$categorys);
		$this->template->set('complete_sdid', Session::instance()->get('sdid'));	
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
			if($this->request->is_ajax()) {
				$this->err(null, '请先登录！', null, null, self::ERROR_NEED_LOGIN);
			} else {
				$this->request->redirect('user/login?f=' . urlencode(preg_replace('/^\//', '', $_SERVER['REQUEST_URI'])));
			}
			exit();
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