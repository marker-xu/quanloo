<?php 

/**
 * 用户视频关系
 * @author xucongbin
 */
class Model_Data_UserVideo extends Model
{
	//看过的
    const TYPE_WATCHED = 'watched';
	//推过的
    const TYPE_LIKED = 'liked';
    //评论过的
    const TYPE_COMMENTED = 'commented';
    //以后观看的
    const TYPE_WATCHLATER = 'watch.later'; //历史遗留的常量，已经在redis里用了，只能保留
    const TYPE_WATCHLATER2 = 'watch_later'; //后加的，使用时需要考虑和TYPE_WATCHLATER兼容
    //分享过的
    const TYPE_SHARED = 'shared';
    //心情过的
    const TYPE_MOODED = 'mooded';
    //圈过的
    const TYPE_CIRCLED = 'circled';
    
    public static $arrType2Name = array(
    	self::TYPE_WATCHLATER => '收藏的视频',
    	self::TYPE_WATCHLATER2 => '收藏的视频',
    	self::TYPE_WATCHED => '看过的视频',
    	self::TYPE_COMMENTED => '评论过的视频',
    	self::TYPE_MOODED => '标过心情的视频',
    	self::TYPE_CIRCLED => '圈过的视频',
    );   
    
	private static $writeDb;
    
    private static $readDb;
    
	public static function getWriteDb() {
		if(!self::$writeDb) {
			$redis = Database::instance('web_redis_master');
        	self::$writeDb = $redis->getRedisDB(1);
		}
		
		return self::$writeDb;
	}
	
	public static function getReadDb() {
		$names = array('web_redis_master', 'web_redis_slave');
	    $name = $names[array_rand($names)];
	    
	    if(!self::$readDb) {
			$redis = Database::instance($name);
        	self::$readDb = $redis->getRedisDB(1);
		}
		
		return self::$readDb;
	}
	
    
    private $uid;
    /**
     * 构造函数
     * @param int $userId 用户ID
     */
	public function __construct($userId)
	{
        $this->uid = $userId;
	}
	
	public function addToByType($type, $value, $score=NULL) {
		$key = $this->getUserVideoKey($type);
    	if($score===NULL) {
    		$score = time();
    	}
    	
		JKit::$log->debug(__FUNCTION__, $key);
		JKit::$log->debug(__FUNCTION__, $score);
		JKit::$log->debug(__FUNCTION__, $value);
    	$result = self::getWriteDb()->zAdd($key, $score, $value);
    	JKit::$log->debug(__FUNCTION__, $result);
    	$this->gc($type);
    	return (bool) $result;
	}
	
	protected function _isInList($type, $value) {
		$key = $this->getUserVideoKey($type);
		$result = self::getReadDb()->zRank($key, $value);//echo $key; echo "==$value==";var_dump($result);
		if (is_int($result)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function deleteFromByType($type, $value) {
		$key = $this->getUserVideoKey($type);
    	
		JKit::$log->debug(__FUNCTION__, $key);
		JKit::$log->debug(__FUNCTION__, $value);
    	$result = self::getWriteDb()->zRem($key, $value);
    	JKit::$log->debug(__FUNCTION__, $result);
    	$this->gc($type);
    	return (bool) $result;
	}
	
	public function zScore( $type, $member ) {
		$key = $this->getUserVideoKey($type);
		$result = self::getWriteDb()->zScore($key, $member);
		
		return $result;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $type
	 * @param int $start
	 * @param int $stop
	 * @param boolean $reverse
	 * @param boolean $withscores
	 * @param array $arrBetween array(
	 * 	'start' => 起始的scroe,
	 *  'end' => 结束的的scroe
	 * )
	 * 
	 * @return array
	 */
	public function getListByType($type, $start=0, $stop=-1, $reverse=TRUE, $withscores=FALSE, $arrBetween=NULL)
    {
        JKit::$log->debug(__FUNCTION__." type-{$type}, uid-{$this->uid}, start-{$start}, stop-{$stop}, ".
        "reverse-".$reverse);
    	$arrReturn = array(
    		"count" => 0,
    		"data" => array()
    	);
    	$key = $this->getUserVideoKey($type);
    	if($arrBetween) {
    		if(!isset($arrBetween['end'])) {
    			$arrBetween['end'] = $_SERVER['REQUEST_TIME'];
    		}
	    	$method = "zRangeByScore";
	    	
	    	$extra = array(
	    		'withscores' => $withscores
	    	);
	    	$arrReturn['count'] = self::getReadDb()->zCount($key, $arrBetween['start'],$arrBetween['end']);
	    	$extra['limit'] = array( intval( $start) , ($stop==-1?$arrReturn['count']:intval($stop-$start-1)) );
    		if ($reverse) {
	    		$method = "zRevRangeByScore";
	    		$tmp = $arrBetween['start'];
	    		$arrBetween['start'] = $arrBetween['end'];
	    		$arrBetween['end'] = $tmp;
	    	}
    		if($arrReturn['count']) {
	    		$arrReturn['data'] = self::getReadDb()->$method($key, $arrBetween['start'], $arrBetween['end'], 
	    		$extra);	
	    	}
    	} else {
    		$method = "zRange";
	    	if ($reverse) {
	    		$method = "zRevRange";
	    	}
	    	$arrReturn['count'] = self::getReadDb()->zSize($key);
	    	if($arrReturn['count']) {
	    		$arrReturn['data'] = self::getReadDb()->$method($key, $start, $stop, $withscores);	
	    	} 
    	}
    	
    	JKit::$log->debug(__FUNCTION__." results-", $arrReturn);
    	return $arrReturn;
    }
	
	private function getUserVideoKey($type) {
		return "user.video:".$type.":".$this->uid;
	}
	
	/**
	 * 
	 * 添加到已经观看
	 * @param string $videoId
	 * 
	 * @return boolean
	 */
	public function addToWatched($videoId)
	{
	    return $this->addToByType(self::TYPE_WATCHED, $videoId);
	}
	/**
	 * 
	 * 查询已经观看视频ID
	 * @param int $start
	 * @param int $stop
	 * @param boolean $reverse 是否按照时间倒序
	 * 
	 * @return array(
	 * 	count => 记录条数,
	 * 	data => array(
	 * 		vid1,
	 * 		...
	 * 	)
	 * )
	 */
	public function getWatched($start = 0, $stop = -1, $reverse=TRUE)
	{
	    return $this->getListByType(self::TYPE_WATCHED, $start, $stop, $reverse, true);
	}
	
	
	public function addToLiked($videoId)
	{
	    return $this->addToByType(self::TYPE_LIKED, $videoId);
	}
	/**
	 * 
	 * 查询推过的视频
	 * @param int $start
	 * @param int $stop
	 * @param boolean $reverse 是否按照时间倒序
	 * 
	 * @return array(
	 * 	count => 记录条数,
	 * 	data => array(
	 * 		vid1,
	 * 		...
	 * 	)
	 * )
	 */
	public function getLiked($start = 0, $stop = -1, $reverse=TRUE)
	{
	    return $this->getListByType(self::TYPE_LIKED, $start, $stop, $reverse, true);
	}
	
	public function addToCommented($videoId, $content, $users = null)
	{
		$jsonValue = json_encode(
			array(
			"video_id"=>$videoId, 
			"content"=>$content,
			"time" => time(),
			'users' => $users,
			)
		);
	    return $this->addToByType(self::TYPE_COMMENTED, $jsonValue);
	}
	/**
	 * 
	 * 查询评论过的视频
	 * @param int $start
	 * @param int $stop
	 * @param boolean $reverse 是否按照时间倒序
	 * 
	 * @return array(
	 * 	count => 记录条数,
	 * 	data => array(
	 * 		array(
	 * 			vid		=>	视频ID,
	 * 			content => 	内容,
	 * 			time 	=> 	时间戳
	 * 		)
	 * 		...
	 * 	)
	 * )
	 */
	public function getCommented($start = 0, $stop = -1, $reverse=TRUE)
	{
	    $arrResult = $this->getListByType(self::TYPE_COMMENTED, $start, $stop, $reverse);
	    if($arrResult['data']) {
	    	foreach($arrResult['data'] as $k=>$value) {
	    		$arrResult['data'][$k] = json_decode($value, true);
	    	}
	    }
	    
	    return $arrResult;
	}
	
	public function addToWatchlater($videoId)
	{
	    return $this->addToByType(self::TYPE_WATCHLATER, $videoId);
	}
	
	public function deleteFromWatchlater($videoId)
	{
	    return $this->deleteFromByType(self::TYPE_WATCHLATER, $videoId);
	}
	
	public function isInWatchlater($videoId)
	{
		return $this->_isInList(self::TYPE_WATCHLATER, $videoId);
	}	
	
	public function addToShared($videoId)
	{
	    return $this->addToByType(self::TYPE_SHARED, $videoId);
	}
	
	public function getShared($start = 0, $stop = -1, $reverse = TRUE)
	{
	    return $this->getListByType(self::TYPE_SHARED, $start, $stop, $reverse, true);
	}
	
	public function addToMooded($videoId)
	{
	    return $this->addToByType(self::TYPE_MOODED, $videoId);
	}
	
	public function getMooded($start = 0, $stop = -1, $reverse = TRUE)
	{
	    return $this->getListByType(self::TYPE_MOODED, $start, $stop, $reverse, true);
	}
	
	/**
	 * 
	 * 查询以后观看的视频
	 * @param int $start
	 * @param int $stop
	 * @param boolean $reverse 是否按照时间倒序
	 * 
	 * @return array(
	 * 	count => 记录条数,
	 * 	data => array(
	 * 		vid1,
	 * 		...
	 * 	)
	 * )
	 */
	public function getWatchlater($start = 0, $stop = -1, $reverse=TRUE, $arrBetween=NULL)
	{
	    return $this->getListByType(self::TYPE_WATCHLATER, $start, $stop, $reverse, true, $arrBetween);
	}
	/**
	 * 
	 * 垃圾回收
	 * @param string $type
	 * @param int $size
	 * 
	 * @return boolean
	 */
	private function gc($type, $size=NULL) {
		#TODO 执行的算法, 正常情况下二十分之一的概率
		$rnd = rand(0, 600);
		if($rnd<=400 || $rnd>406) {
			return false;
		}
		JKit::$log->debug(__FUNCTION__." run, uid-".$this->uid);
		
		$key = $this->getUserVideoKey($type);
		if($size===NULL) {
			
			$size = self::getWriteDb()->zSize($key);
			
		}
		if( $size>1000 ) {
			self::getWriteDb()->zRemRangeByRank($key, 0, $size-1000);
		} 
		return true;
	}
}