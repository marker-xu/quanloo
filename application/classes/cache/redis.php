<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 使用Redis来缓存数据
 * @author wangjiajun
 */
class Cache_Redis extends Cache implements Cache_Arithmetic 
{
	/**
	 * Redis resource
	 *
	 * @var Redis
	 */
	protected $_redis;
	
	protected $_connections;

	/**
	 * Constructs the redis Kohana_Cache object
	 *
	 * @param   array     configuration
	 * @throws  Cache_Exception
	 */
	protected function __construct(array $config)
	{
		// Check for the redis extention
		if ( ! extension_loaded('redis'))
		{
			throw new Cache_Exception('Redis PHP extention not loaded');
		}

		parent::__construct($config);
	}
	
	private function _connect($key = NULL)
	{
		// Load servers from configuration
		$servers = Arr::get($this->_config, 'servers', NULL);
		if (!$servers) {
			// Throw an exception if no server found
			throw new Cache_Exception('No Redis servers defined in configuration');
		}

		// Choose One Server
		$flatServers = array();
		foreach ($servers as $server) {
		    $server = array_merge(array(
				'host'             => 'localhost',
				'port'             => 6379,
				'persistent'       => FALSE,
				'weight'           => 1,
				'timeout'          => 0,
				'status'           => TRUE,
		    ), $server);
		    if ($server['status']) {
		        $flatServers = array_merge($flatServers, array_fill(0, $server['weight'], $server));
		    }
		}
		$servers = $flatServers;
	    if (!is_null($key)) {
		    $index = hexdec(substr(md5($key), -1, 8)) % count($servers);
	    } else {
	        $index = array_rand($servers);
	    }
		$server = $servers[$index];
		$hash = md5($server['host'].':'.$server['port']);
		
		if (isset($this->_connections[$hash])) {
		    $this->_redis = $this->_connections[$hash];
		    return;
		}
		
		$this->_redis = new Redis();
		if ($server['persistent']) {
		    $ret = $this->_redis->pconnect($server['host'], $server['port'], $server['timeout']);
		} else {
		    $ret = $this->_redis->connect($server['host'], $server['port'], $server['timeout']);
		}
		if (!$ret) {
			throw new Cache_Exception('Redis could not connect to host \':host\' using port \':port\'', 
			    array(':host' => $server['host'], ':port' => $server['port']));
		}
        if (isset($this->_config['serializer'])) {
            $this->_redis->setOption(Redis::OPT_SERIALIZER, $this->_config['serializer']);
        }
        if (isset($this->_config['prefix'])) {
            $this->_redis->setOption(Redis::OPT_PREFIX, $this->_config['prefix']);
        }
        $this->_connections[$hash] = $this->_redis;
	}

	/**
	 * Retrieve a cached value entry by id.
	 * 
	 *     // Retrieve cache entry from redis group
	 *     $data = Cache::instance('redis')->get('foo');
	 * 
	 *     // Retrieve cache entry from redis group and return 'bar' if miss
	 *     $data = Cache::instance('redis')->get('foo', 'bar');
	 *
	 * @param   string   id of cache to entry
	 * @param   string   default value to return if cache miss
	 * @return  mixed
	 * @throws  Cache_Exception
	 */
	public function get($id, $default = NULL)
	{
	    $this->_connect($id);
	    
		$value = $this->_redis->get($this->_sanitize_id($id));
		return ($value === FALSE ? $default : $value);
	}

	/**
	 * Set a value to cache with id and lifetime
	 * @param   string   id of cache entry
	 * @param   mixed    data to set to cache
	 * @param   integer  lifetime in seconds, 0 for no expire
	 * @return  boolean
	 */
	public function set($id, $data, $lifetime = 0)
	{
	    $this->_connect($id);
	    
		if ($lifetime > 0) {
		    return $this->_redis->setex($this->_sanitize_id($id), $lifetime, $data);
		} else {
		    return $this->_redis->set($this->_sanitize_id($id), $data);
		}
	}

	/**
	 * Delete a cache entry based on id
	 * 
	 *     // Delete the 'foo' cache entry immediately
	 *     Cache::instance('redis')->delete('foo');
	 * 
	 * @param   string   id of entry to delete
	 * @return  boolean
	 */
	public function delete($id)
	{
	    $this->_connect($id);
	    
		// Delete the id
		return $this->_redis->del($this->_sanitize_id($id));
	}

	/**
	 * Delete all cache entries.
	 * 
	 * Beware of using this method when
	 * using shared memory cache systems, as it will wipe every
	 * entry within the system for all clients.
	 * 
	 *     // Delete all cache entries in the default group
	 *     Cache::instance('redis')->delete_all();
	 *
	 * @return  boolean
	 */
	public function delete_all()
	{
		$servers = Arr::get($this->_config, 'servers', NULL);
		foreach ($servers as $server) {
		    $server = array_merge(array(
				'host'             => 'localhost',
				'port'             => 6379,
				'persistent'       => FALSE,
				'weight'           => 1,
				'timeout'          => 0,
				'status'           => TRUE,
		    ), $server);
    		$redis = new Redis();
    		$redis->connect($server['host'], $server['port'], $server['timeout']);
    		$redis->flushDB();
		}
		return true;
	}

	/**
	 * Increments a given value by the step value supplied.
	 * Useful for shared counters and other persistent integer based
	 * tracking.
	 *
	 * @param   string    id of cache entry to increment
	 * @param   int       step value to increment by
	 * @return  integer
	 * @return  boolean
	 */
	public function increment($id, $step = 1)
	{
	    $this->_connect($id);
	    
		return $this->_redis->incrBy($id, $step);
	}

	/**
	 * Decrements a given value by the step value supplied.
	 * Useful for shared counters and other persistent integer based
	 * tracking.
	 *
	 * @param   string    id of cache entry to decrement
	 * @param   int       step value to decrement by
	 * @return  integer
	 * @return  boolean
	 */
	public function decrement($id, $step = 1)
	{
	    $this->_connect($id);
	    
		return $this->_redis->decrBy($id, $step);
	}
	
	/**
	 * 获得Redis对象，类型同Redis扩展里的定义
	 * @param int $db 数据库号，默认为0
	 * @param int $id 缓存Key，可选，用于选择具体哪个cache
	 * @return Redis
	 */
	public function getRedisDB($db = 0, $id = NULL)
	{
	    $this->_connect($id);
	    
	    // 强制select，防止db切换时出现问题
        $this->_redis->select($db);
	    
	    return $this->_redis;
	}
}