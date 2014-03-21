<?php 

class Controller_Admin_HotQuery extends Controller_Admin 
{
	public function before() 
	{
	    parent::before();
	    
	    $this->_checkPrivilege(self::RES_HOT_QUERY, self::PRIV_VIEW);
	}
	
	public function action_index() 
	{
		$modelDataQueryStat = new Model_Data_QueryStat();
		
	    if ($this->request->method() == 'POST') {
	        $whitelist = $this->request->param('whitelist', array());
	        $blacklist = $this->request->param('blacklist', array());
	        $tmp = $whitelist;
	        $whitelist = array();
	        foreach ($tmp as $value) {
	            $value = trim($value);
	            if ($value) {
	                $whitelist[] = $value;
	            }
	        }
	        $tmp = $blacklist;
	        $blacklist = array();
	        foreach ($tmp as $value) {
	            $value = trim($value);
	            if ($value) {
	                $blacklist[] = $value;
	            }
	        }
	        $modelDataQueryStat->hotQueryWhitelist($whitelist);
	        $modelDataQueryStat->hotQueryBlacklist($blacklist);
	        $this->response->alertBack("提交成功");
	    } else {
	        $whitelist = $modelDataQueryStat->hotQueryWhitelist();
	        $blacklist = $modelDataQueryStat->hotQueryBlacklist();
    	    $this->template->set('whitelist', $whitelist);
    	    $this->template->set('blacklist', $blacklist);
	    }
	}
}
