<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 动态相关页面，以及Ajax请求接口
 * @author wangjiajun
 */
class Controller_Feed extends Controller 
{
	public function action_index()
	{
        $this->response->body('Welcome to VideoSearch!');
	}
}
