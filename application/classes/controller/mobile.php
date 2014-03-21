<?php 

class Controller_Mobile extends Jkit_Controller 
{
	public function before() {
		JKit::$security['csrf'] = false;
		parent::before();
	}
	
	public function action_index() {
		$directory = "mobile";
		$method = trim( $this->request->param("api", "") );
		
        $arrParam = explode(".", $method);
        $controller = $arrParam[0] ? $arrParam[0]:"welcome";
        $action = isset($arrParam[1]) ? $arrParam[1]:"index";
        $uri = "{$directory}/{$controller}/{$action}";
        $forward = Request::process_uri($uri);
        if(!isset($forward['params']['directory'])) {
        	$this->err(NULL, NULL, NULL, NULL, 'sys.api.not_exist');
        }
        $this->request->forward($uri);
	}
}