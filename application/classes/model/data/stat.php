<?php
/**
 * 统计系统
 * @author 王家军
 */
class Model_Data_Stat extends Model
{

	/**
	 * 发送统计日志
	 * @param array $data
	 * @return bool
	 */
	public function log($data)
	{
	    $userAgent = $_SERVER['HTTP_USER_AGENT'];
	    if(Util::isSpider($userAgent)) {
	    	return true;
	    }
		$data = array_merge(array(
	        'site_id' => 'videosearch',
	        'page_id' => '',
	        'cookie_id' => Session::instance()->id(),
	        'user_id' => -1,
	        'ip' => Request::$client_ip,
	        'agent' => $userAgent,
	        'url' => urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']),
	        'out_url' => '',
	        'referrer' => isset($_SERVER['HTTP_REFERER']) ? urlencode($_SERVER['HTTP_REFERER']) : '',
	        'bucket' => 1,
	        'video_id' => '',
	    ), $data);
        Kohana::$log->debug(__FUNCTION__, $data);
		Profiler::startMethodExec();
        $result = RPC::call('stat_log', '/index.php?data='.urlencode(json_encode($data)));
	    Profiler::endMethodExec(__FUNCTION__.' stat_log');
        Kohana::$log->debug(__FUNCTION__, $result);
        return $result;
	}
}