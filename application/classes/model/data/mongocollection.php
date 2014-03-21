<?php defined('SYSPATH') or die('No direct script access.');
/**
 * MongoDB Collection
 * @author wangjiajun
 */
class Model_Data_MongoCollection extends Model_Data_MongoDB
{
    protected $_collectionName;
    protected $_cursorTimeout;
    private $_collection;
    
    public function __construct($name, $db, $collection, $slaveOkay = true, 
        $cursorTimeout = 5000)
    {
        parent::__construct($name, $db, $slaveOkay);
        
        $this->_collectionName = $collection;
        $this->_cursorTimeout = $cursorTimeout;
    }
    
	/**
	 * 连接
	 * @return void
	 * @throws Model_Data_Exception
	 */
    public function connect()
    {
        parent::connect();
        
        $this->_collection = $this->getDb()->selectCollection($this->_collectionName);
        $this->_collection->setSlaveOkay($this->_slaveOkay);
    }
    
	/**
	 * 重新连接
	 * @return void
	 * @throws Model_Data_Exception
	 */
    public function reconnect()
    {
        parent::reconnect();
        
        $this->_collection = $this->getDb()->selectCollection($this->_collectionName);
        $this->_collection->setSlaveOkay($this->_slaveOkay);
    }
    
    public function getCollectionName()
    {
        return $this->_collectionName;
    }
    
	/**
	 * 获取MongoCollection对象
	 * @return MongoCollection
	 * @throws Model_Data_Exception
	 */
    public function getCollection()
    {
        if (!$this->_collection) {
            $this->connect();
        }
        return $this->_collection;
    }
	
	/**
	 * 设置是否从从库读
	 * @return MongoCollection
	 * @throws Model_Data_Exception
	 */
	public function setSlaveOkay($slaveOkay = true)
	{
        if (!$this->_collection) {
            $this->connect();
        }
        $this->_collection->setSlaveOkay($slaveOkay);
	}
	
	public function setCursorTimeout($cursorTimeout)
	{
        $this->_cursorTimeout = $cursorTimeout;
	}
    
	/**
	 * 查询单个文档
	 * @param array $query
	 * @param array $fields
	 * @return array|null
	 */
	public function findOne($query = array(), $fields = array())
	{
        if (!$this->_collection) {
		    try {
                $this->connect();
		    } catch (Model_Data_Exception $e) {
    		    Kohana::$log->error($e);
		        return null;
		    }
        }
	    while (true) {
	        Profiler::startMethodExec();
    	    try {
        	    $doc = $this->_collection->find($query, $fields)->limit(-1)
        	        ->timeout($this->_cursorTimeout)->getNext();
    	    } catch (MongoException $e) {
    		    Kohana::$log->warn($e);
    		    try {
	                $this->reconnect();
    		    } catch (Model_Data_Exception $e) {
    		        Kohana::$log->error($e);
    		        return null;
    		    }
    		    continue;
    	    }
	        Profiler::endMethodExec(__FUNCTION__.' find '.$this->_dbName.'.'.$this->_collectionName.' '.http_build_query($query));
    	    break;
	    }
	    return $doc;
	}
	
	/**
	 * 查询多个文档
	 * @param array $query
	 * @param array $fields
	 * @param array $sort
	 * @param int $limit
	 * @param int $skip
	 * @return array
	 */
	public function find($query = array(), $fields = array(), $sort = NULL, $limit = NULL, 
	    $skip = NULL)
	{
        if (!$this->_collection) {
		    try {
                $this->connect();
		    } catch (Model_Data_Exception $e) {
    		    Kohana::$log->error($e);
		        return array();
		    }
        }
	    while (true) {
	        Profiler::startMethodExec();
    	    try {
        	    $cursor = $this->_collection->find($query, $fields)->timeout($this->_cursorTimeout);
        	    if (!is_null($sort)) {
        	        $cursor->sort($sort);
        	    }
        	    if ($limit > 0) {
        	        $cursor->limit($limit);
        	    }
        	    if ($skip > 0) {
        	        $cursor->skip($skip);
        	    }
        	    $docs = iterator_to_array($cursor);
    	    } catch (MongoException $e) {
    		    Kohana::$log->warn($e);
    		    try {
	                $this->reconnect();
    		    } catch (Model_Data_Exception $e) {
    		        Kohana::$log->error($e);
    		        return array();
    		    }
    		    continue;
    	    }
	        Profiler::endMethodExec(__FUNCTION__.' find '.$this->_dbName.'.'.$this->_collectionName.' '.http_build_query($query));
    	    break;
	    }
	    return $docs;
	}
	
	/**
	 * 查询符合条件的文档个数
	 * @param array $query
	 * @return int
	 */
	public function count($query = array())
	{
        if (!$this->_collection) {
		    try {
                $this->connect();
		    } catch (Model_Data_Exception $e) {
    		    Kohana::$log->error($e);
		        return 0;
		    }
        }
	    while (true) {
	        Profiler::startMethodExec();
    	    try {
	            $count = $this->_collection->count($query);
    	    } catch (MongoException $e) {
    		    Kohana::$log->warn($e);
    		    try {
	                $this->reconnect();
    		    } catch (Model_Data_Exception $e) {
    		        Kohana::$log->error($e);
    		        return 0;
    		    }
    		    continue;
    	    }
	        Profiler::endMethodExec(__FUNCTION__.' count '.$this->_dbName.'.'.$this->_collectionName.' '.http_build_query($query));
    	    break;
	    }
	    return $count;
	}
	
	/**
	 * Group查询
	 * @param array|MongoCode $keys
	 * @param array $initial
	 * @param MongoCode $reduce
	 * @param array $options
	 * @return array
	 */
	public function group($keys, $initial, $reduce, $options = array())
	{
        if (!$this->_collection) {
		    try {
                $this->connect();
		    } catch (Model_Data_Exception $e) {
    		    Kohana::$log->error($e);
		        return array();
		    }
        }
	    while (true) {
	        Profiler::startMethodExec();
    	    try {
	            $result = $this->_collection->group($keys, $initial, $reduce, $options);
    	    } catch (MongoException $e) {
    		    Kohana::$log->warn($e);
    		    try {
	                $this->reconnect();
    		    } catch (Model_Data_Exception $e) {
    		        Kohana::$log->error($e);
    		        return array();
    		    }
    		    continue;
    	    }
	        Profiler::endMethodExec(__FUNCTION__.' group '.$this->_dbName.'.'.$this->_collectionName.' '.http_build_query($keys));
    	    break;
	    }
	    return $result;
	}
	
	/**
	 * 插入单个文档
	 * @param array $doc
	 * @param array $options
	 * @param MongoId $_id
	 * @return bool|array
	 * @throws Model_Data_Exception
	 */
	public function insert($doc, $options = array(), &$_id = null)
	{
        if (!$this->_collection) {
            $this->connect();
        }
	    $options = array_merge(array(
	        'safe' => TRUE,
	        'fsync' => FALSE
	    ), $options);
	    $result = $this->_collection->insert($doc, $options);
	    if (!is_null($_id)) {
	        $_id = $doc['_id'];
	    }
	    return $result;
	}
	
	/**
	 * 更新文档，默认更新所有命中文档的指定字段
	 * @param array $query
	 * @param array $doc
	 * @param array $options 更新选项，默认为upsert，更新所有命中文档
	 * @param bool $replace 是否替换原文档，默认为否
	 * @return bool|array
	 * @throws Model_Data_Exception
	 */
	public function update($query, $doc, $options = array(), $replace = FALSE)
	{
        if (!$this->_collection) {
            $this->connect();
        }
	    $options = array_merge(array(
	        'upsert' => TRUE,
	        'multiple' => TRUE,
	        'safe' => TRUE,
	        'fsync' => FALSE
	    ), $options);
	    if ($replace) {
	        return $this->_collection->update($query, $doc, $options);
	    } else {
            return $this->_collection->update($query, array('$set' => $doc), $options);
	    }
	}
	
	/**
	 * 加减数值
	 * @param array $query
	 * @param string $field
	 * @param mixed $value
	 * @param array $options 更新选项，默认为upsert，更新所有命中文档
	 * @return bool|array
	 * @throws Model_Data_Exception
	 */
	public function inc($query, $field, $value = 1, $options = array())
	{
        if (!$this->_collection) {
            $this->connect();
        }
	    $options = array_merge(array(
	        'upsert' => TRUE,
	        'multiple' => TRUE,
	        'safe' => TRUE,
	        'fsync' => FALSE
	    ), $options);
        return $this->_collection->update($query, array('$inc' => array($field => $value)), 
            $options);
	}
	
	/**
	 * 往类型为array的field里添加一个或多个元素，前提是元素不在数组里，如果field不存在，会自动新增
	 * @param array $query
	 * @param string $field
	 * @param mixed $value 一个或多个
	 * @param array $options 更新选项，默认为upsert，更新所有命中文档
	 * @return bool
	 * @throws Model_Data_Exception
	 */
	public function addToSet($query, $field, $value, $options = array())
	{
        if (!$this->_collection) {
            $this->connect();
        }
	    $options = array_merge(array(
	        'upsert' => TRUE,
	        'multiple' => TRUE,
	        'safe' => TRUE,
	        'fsync' => FALSE
	    ), $options);
	    if (is_array($value)) {
            return $this->_collection->update($query, array('$addToSet' => array(
                $field => array('$each' => $value))), $options);
	    } else {
            return $this->_collection->update($query, array('$addToSet' => array(
                $field => $value)), $options);
	    }
	}
	
	/**
	 * 从类型为array的field里移除一个或多个元素
	 * @param array $query
	 * @param string $field
	 * @param mixed $value 一个或多个
	 * @param array $options 更新选项，默认为upsert，更新所有命中文档
	 * @return bool
	 * @throws Model_Data_Exception
	 */
	public function removeFromSet($query, $field, $value, $options = array())
	{
        if (!$this->_collection) {
            $this->connect();
        }
	    $options = array_merge(array(
	        'upsert' => TRUE,
	        'multiple' => TRUE,
	        'safe' => TRUE,
	        'fsync' => FALSE
	    ), $options);
	    if (is_array($value)) {
            return $this->_collection->update($query, array('$pullAll' => array(
                $field => $value)), $options);
	    } else {
            return $this->_collection->update($query, array('$pull' => array(
                $field => $value)), $options);
	    }
	}
	
	/**
	 * 删除文档，默认删除符合条件的所有文档
	 * @param array $query
	 * @param array $options
	 * @return bool|array
	 * @throws Model_Data_Exception
	 */
	public function delete($query, $options = array())
	{
        if (!$this->_collection) {
            $this->connect();
        }
	    $options = array_merge(array(
	        'justOne' => FALSE,
	        'safe' => TRUE,
	        'fsync' => FALSE
	    ), $options);
	    return $this->_collection->remove($query, $options);
	}
}