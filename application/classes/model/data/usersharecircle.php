<?php 

/**
 * 用户圈子分享关系
 * @author xucongbin
 */
class Model_Data_Usersharecircle extends Model
{
    
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
	
	public function add($intCircleId, $score=NULL) {
		JKit::$log->debug(__FUNCTION__." cid-{$intCircleId}, score-{$score}");
		$key = $this->getKey();
    	if($score===NULL) {
    		$score = time();
    	}
    	
    	$result = self::getWriteDb()->zAdd($key, $score, intval($intCircleId));
    	
    	JKit::$log->debug(__FUNCTION__." result-", $result);
    	$this->gc();
    	return $result!==false;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param int $start
	 * @param int $stop
	 * @param boolean $reverse
	 * @param boolean $withscores
	 * 
	 * @return array
	 */
	public function getList($start=0, $stop=-1, $reverse=TRUE, $withscores=FALSE)
    {
        JKit::$log->debug(__FUNCTION__." uid-{$this->uid}, start-{$start}, stop-{$stop}, ".
        "reverse-".$reverse);
    	$key = $this->getKey();
    	
    	$method = "zRange";
    	if ($reverse) {
    		$method = "zRevRange";
    	}
    	$arrReturn = self::getReadDb()->$method($key, $start, $stop, $withscores);	
    	
    	JKit::$log->debug(__FUNCTION__." results-", $arrReturn);
    	return $arrReturn;
    }
    
    public function getCount() {
    	$key = $this->getKey();
    	
    	$total = self::getReadDb()->zSize($key);
    	
    	JKit::$log->debug(__FUNCTION__." total-", $total);
    	return $arrReturn;
    }
    
    
	private function getKey() {
		return "user.share.circle:".$this->uid;
	}
	
	/**
	 * 
	 * 垃圾回收
	 * @param string $type
	 * @param int $size
	 * 
	 * @return boolean
	 */
	private function gc( $size=NULL ) {
		#TODO 执行的算法, 正常情况下二十分之一的概率
		$rnd = rand(0, 600);
		if($rnd<=400 || $rnd>406) {
			return false;
		}
		JKit::$log->debug(__FUNCTION__." run, uid-".$this->uid);
		
		$key = $this->getKey();
		if($size===NULL) {
			
			$size = self::getWriteDb()->zSize($key);
			
		}
		if( $size>1000 ) {
			self::getWriteDb()->zRemRangeByRank($key, 0, $size-1000);
		} 
		return true;
	}
}