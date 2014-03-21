<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MongoDB Database
 * @author wangjiajun
 */
class Model_Data_MongoDB extends Model
{
    protected $_database;
    protected $_dbName;
    protected $_slaveOkay;
    private $_db;
    
    public function __construct($name, $db, $slaveOkay = true)
    {
        $this->_database = Database::instance($name);
        $this->_dbName = $db;
        $this->_slaveOkay = $slaveOkay;
    }
    
	/**
	 * 建立连接
	 * @return void
	 * @throws Model_Data_Exception
	 */
    public function connect()
    {
        try {
            $this->_database->connect();
        } catch (Database_Exception $e) {
            throw new Model_Data_Exception($e->getMessage());
        }
        $this->_db = $this->_database->getMongoDB($this->_dbName);
        $this->_db->setSlaveOkay($this->_slaveOkay);
    }
    
	/**
	 * 重新连接
	 * @return void
	 * @throws Model_Data_Exception
	 */
    public function reconnect()
    {
        try {
            $this->_database->reconnect();
        } catch (Database_Exception $e) {
            throw new Model_Data_Exception($e->getMessage());
        }
        $this->_db = $this->_database->getMongoDB($this->_dbName);
        $this->_db->setSlaveOkay($this->_slaveOkay);
    }
    
    public function getDbName()
    {
        return $this->_dbName;
    }
    
    /**
     * 
     * return MongoDB
     */
    public function getDb()
    {
        if (!$this->_db) {
            $this->connect();
        }
        return $this->_db;
    }
    
	/**
	 * 在数据库上执行命令
	 * @param array $command
	 * @param array $options
	 * @return mixed
	 */
	public function command($command, $options = array())
	{
        if (!$this->_db) {
            $this->connect();
        }
	    return $this->_db->command($command, $options);
	}
    
	/**
	 * 在数据库上执行代码。该操作会对数据加写锁，请谨慎执行。
	 * @param mixed $command
	 * @param array $args
	 * @return mixed
	 */
	public function execute($code, $args = array())
	{
        if (!$this->_db) {
            $this->connect();
        }
	    return $this->_db->execute($code, $args);
	}
	
	public function setSlaveOkay($slaveOkay = true)
	{
        if (!$this->_db) {
            $this->connect();
        }
        $this->_db->setSlaveOkay($slaveOkay);
	}
}