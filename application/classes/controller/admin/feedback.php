<?php

class Controller_Admin_Feedback extends Controller_Admin
{
	private $objModelFeedback;
	public function before()
	{
	    parent::before();
	    
	    $this->objModelFeedback = new Model_Data_Feedback();
	    
	    $this->_checkPrivilege(self::RES_USER_FEEDBACK, self::PRIV_VIEW);
	}

	public function action_index()
	{
	    $page = (int) $this->request->param('page', 1);
	    
	    $count = 15;
		$offset = ($page-1)*$count;
	    
	    $query = array();
	    $sort = array("create_time"=>-1);
	    $arrList = $this->objModelFeedback->find($query, array(), $sort,
	        $count, $offset);
		
	    $total = $this->objModelFeedback->count($query);
	    
	    $this->template->set("list", $arrList);
	    $this->template->set("total", $total);
	    $arrUids = Arr::pluck($arrList, "user_id");
		$arrUserList = array();
		if($arrUids) {
			$objModelUser = new Model_Data_User();
			$arrUserList = $objModelUser->getMulti( array_unique( $arrUids ), array("_id", "nick") );
		}
		$this->template->set("user_list", $arrUserList);
	    # 用户点击数
	    $redis = Database::instance('web_redis_master');
    	$objModelReids = $redis->getRedisDB(4);
    	$strKey = "FEEDBACK_CLICK_COUNT";
	    $this->template->set("feedback_click_count", $objModelReids->get($strKey));
	    $pagination = Pagination::factory(array(
	    	'total_items' => $total,
	    	'items_per_page' => $count
	    ));
	    $this->template->set('pagination', $pagination);
	}

	public function action_view()
	{
		return;
	}
	
	public function action_clear() {
	    $this->_checkPrivilege(self::RES_USER_FEEDBACK, self::PRIV_DELETE);
	    
		$query = array();
		$options = array("safe"=>true);
		$res = $this->objModelFeedback->delete($query, $options);
		$redis = Database::instance('web_redis_master');
    	$objModelReids = $redis->getRedisDB(4);
    	$strKey = "FEEDBACK_CLICK_COUNT";
	    $objModelReids->delete($strKey);
	    JKit::$log->info("feedback clear, uid-".$this->_uid);
		$this->ok();
	}
	
	public function action_remove() {
	    $this->_checkPrivilege(self::RES_USER_FEEDBACK, self::PRIV_DELETE);
	    
		$id = $this->request->query("id");
		if(!$id) {
			$this->err();
		}
		$res = $this->objModelFeedback->removeFeedbackById($id);
		JKit::$log->info("feedback remove, uid-".$this->_uid);
		if($res) {
			$this->ok();
		} else {
			$this->err();
		}
		
	}
}
