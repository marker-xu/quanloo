<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Redis驱动
 * @author wangjiajun
 */
class Database_Redis extends Database
{
    const MODE_READ = 1;
    const MODE_WRITE = 2;
    
	public function connect($mode = self::MODE_WRITE, $readFromMaster = true) 
	{
	    if (isset($this->_config['connection']['master']) 
	        && isset($this->_config['connection']['slaves'])) {
	        if ($mode == self::MODE_READ) {
	            $hosts = array();
	            if ($readFromMaster) {
	                $host = $this->_config['connection']['master'];
        		    $weight = isset($host['weight']) ? floor($host['weight']) : 1;
        		    $hosts = array_merge($hosts, array_fill(0, $weight, $host));
	            }
        		foreach ($this->_config['connection']['slaves'] as $host) {
        		    $weight = isset($host['weight']) ? floor($host['weight']) : 1;
        		    $hosts = array_merge($hosts, array_fill(0, $weight, $host));
        		}
        		shuffle($hosts);
	        } else if ($mode == self::MODE_WRITE) {
        		$hosts = array($this->_config['connection']['master']);
	        }
	    } else {
        	$hosts = array($this->_config['connection']);
	    }
		
	    foreach ($hosts as $host) {
    		if (is_array($this->_connection) && isset($this->_connection[$host['hostname']])) {
    			return $this->_connection[$host['hostname']];
    		}
	    }
		
	    $connected = false;
		foreach ($hosts as $host) {
            $host = $host + array(
    			'hostname'        => 'localhost:6379',
    			'timeout'         => 0,
    			'persistent'      => FALSE
    		);
            $connection = new Redis();
            list($ip, $port) = explode(':', $host['hostname']);
            if ($host['persistent']) {
                $ret = $connection->pconnect($ip, $port, $host['timeout'] / 1000);            
            } else {
                $ret = $connection->connect($ip, $port, $host['timeout'] / 1000);
            }
            if (!$ret) {
    		    Kohana::$log->warn("connect to Redis failed: ".$host['hostname']);
    			continue;
            }
            $this->_connection[$host['hostname']] = $connection;
            $connected = true;
            break;
		}
		if (!$connected) {
    		throw new Database_Exception("connect to Redis failed: ".$this->_instance);
		}
        if (isset($this->_config['serializer'])) {
            $connection->setOption(Redis::OPT_SERIALIZER, $this->_config['serializer']);
        }
        if (isset($this->_config['prefix'])) {
            $connection->setOption(Redis::OPT_PREFIX, $this->_config['prefix']);
        }
        
        return $connection;
	}

	public function disconnect()
	{
	    if (is_array($this->_connection)) {
	        foreach ($this->_connection as $connection) {
		        $connection->close();
	        }
	    }
		return parent::disconnect();
	}
	
	/**
	 * 获得Redis对象，类型同Redis扩展里的定义
	 * @param int $db 数据库号，默认为0
	 * @param string $mode 访问模式，读或写，如果是Master-Slave部署，写模式访问Master，读模式访问Slave
	 * @param bool $readFromMaster 读模式下是否从Master读
	 * @return Redis
	 * @throws Database_Exception
	 */
	public function getRedisDB($db = 0, $mode = self::MODE_WRITE, $readFromMaster = true)
	{
	    $connection = $this->connect($mode, $readFromMaster);
	    
	    // 强制select，防止db切换时出现问题
        $connection->select($db);
	    
	    return $connection;
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