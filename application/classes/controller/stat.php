<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 统计、排行榜相关页面，以及Ajax请求接口
 * @author wangjiajun
 */
class Controller_Stat extends Controller 
{
	public function action_index()
	{
        $this->response->body('Welcome to VideoSearch!');
	}
}
