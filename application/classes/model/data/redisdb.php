<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Redis数据库
 * @author wangjiajun
 */
class Model_Data_RedisDB extends Model
{    
    /* @var Database_Redis */
    private $_redis;
    private $_db;
    
    /**
     * 构造函数
     * @param string $name
     * @param int $db
     * @return void
     */
    public function __construct($name, $db = 0)
    {
        $this->_redis = Database::instance($name);
        $this->_db = $db;
    }
    
    /**
     * 获取代表一个数据库访问的Redis对象
     * @param int $mode
     * @param bool $readFromMaster
     * @return Redis
	 * @throws Model_Data_Exception
     */
    public function getDb($mode = Database_Redis::MODE_WRITE, $readFromMaster = true)
    {
        try {
            return $this->_redis->getRedisDB($this->_db, $mode, $readFromMaster);
        } catch (Database_Exception $e) {
            throw new Model_Data_Exception($e->getMessage());
        }
    }
}