<?php defined('SYSPATH') or die('No direct script access.');

ini_set('mongo.native_long', 1); //线上系统是64位系统，所以打开对原生INT64的支持
/**
 * MongoDB驱动
 * @author wangjiajun
 */
class Database_Mongo extends Database
{
    private  $_dsn;
    
	/**
	 * 切换服务器，以便在操作失败时重试
	 * @return void
	 * @throws Database_Exception
	 */
	public function connect() 
	{
		if ($this->_connection) {
			return;
		}

		if (is_null($this->_dsn)) {
		    $this->_config['connection'] = $this->_config['connection'] + array(
    			'dsn'        => '',
    			'slaveOkay'  => true
    		);
    		$this->_dsn = $this->_config['connection']['dsn'];
    		if (!is_array($this->_dsn)) {
    		    $this->_dsn = array($this->_dsn);
    		}
    		shuffle($this->_dsn);
		}
		$options = array();
		foreach ($this->_config['connection'] as $key => $value) {
		    if (in_array($key, array('connect', 'timeout', 'replicaSet', 'username', 
		        'password', 'db'))) {
		        $options[$key] = $value;
		    }
		}
		while ($this->_dsn) {
			$server = array_shift($this->_dsn);
    		try {
                $this->_connection = new Mongo($server, $options);
    		} catch(MongoException $e) {
    		    Kohana::$log->warn($e->getMessage());
    			continue;
    		}
    		$this->_connection->setSlaveOkay($this->_config['connection']['slaveOkay']);
    		break;
		}
		if (!$this->_connection) {
    		throw new Database_Exception("connect to MongoDB failed: ".$this->_instance);
		}
	}

	public function disconnect()
	{
		if ($this->_connection) {
			$this->_connection->close();
		}
		return parent::disconnect();
	}
	
	/**
	 * 切换服务器，以便在操作失败时重试
	 * @return void
	 * @throws Database_Exception
	 */
	public function reconnect()
	{
		$this->_connection = null;
	    $this->connect();
	}
	
	/**
	 * 获得Mongo对象，类型同Mongo扩展里的定义
	 * @return Mongo
	 * @throws Database_Exception
	 */
	public function getMongo()
	{
	    $this->_connection || $this->connect();
	    
	    return $this->_connection;
	}
	
	/**
	 * 获得MongoDB对象，类型同Mongo扩展里的定义
	 * @param string $db 库名 
	 * @return MongoDB
	 * @throws Database_Exception
	 */
	public function getMongoDB($db)
	{
	    $mongo = $this->getMongo();
	    return $mongo->selectDB($db);
	}
	
	/**
	 * 获得MongoCollection对象，类型同Mongo扩展里的定义
	 * @param string $db 库名 
	 * @param string $collection 集合名
	 * @return MongoCollection
	 * @throws Database_Exception
	 */
	public function getMongoCollection($db, $collection)
	{
	    $mongo = $this->getMongo();
	    return $mongo->selectCollection($db, $collection);
	}

	public function set_charset($charset) 
	{
		throw new Kohana_Exception('Database method :method is not supported by :class',
			array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function query($type, $sql, $as_object = FALSE, array $params = NULL)
	{
		throw new Kohana_Exception('Database method :method is not supported by :class',
			array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function begin($mode = NULL)
	{
		throw new Kohana_Exception('Database method :method is not supported by :class',
			array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function commit()
	{
		throw new Kohana_Exception('Database method :method is not supported by :class',
			array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function rollback()
	{
		throw new Kohana_Exception('Database method :method is not supported by :class',
			array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function list_tables($like = NULL)
	{
		throw new Kohana_Exception('Database method :method is not supported by :class',
			array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function list_columns($table, $like = NULL, $add_prefix = TRUE)
	{
		throw new Kohana_Exception('Database method :method is not supported by :class',
			array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function escape($value)
	{
		throw new Kohana_Exception('Database method :method is not supported by :class',
			array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}
}