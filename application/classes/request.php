<?php defined('SYSPATH') or die('No direct script access.');

class Request extends JKit_Request
{    
	/**
	 * 让流程跳转到指定action
	 *
	 * @param string 要跳转到的uri
	 * @param string 新增query参数，原有参数会保留
	 * @param int    跳转状态码 20x
	 * @uses  Profiler::stop_all
	 */
	public function forward($uri, $params = array(), $code = 200){
		
		$forward = Request::process_uri($uri);
	
		$this->route($forward['route']);
		$this->directory($forward['params']['directory']);
		$this->action($forward['params']['action']);
		$this->controller($forward['params']['controller']);

		unset($forward['params']['controller'], $forward['params']['action'], $forward['params']['directory']);
		$this->_params = $forward['params'] + $params;

		Profiler::stop_all(); //结束所有的Profiler

		echo $this->execute()->status($code)
			      ->send_headers()
			      ->body();
		exit;
	}
}