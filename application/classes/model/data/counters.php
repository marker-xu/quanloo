<?php 

/**
 * 计数器，实现类似MySQL的自增长ID
 * @author wangjiajun
 */
class Model_Data_Counters extends Model_Data_MongoCollection
{    
	public function __construct()
	{
        parent::__construct('web_mongo', 'video_search', 'counters');
	}
	
	/**
	 * 返回自增长ID
	 * @param string $namespace 命名空间
	 * @param int $init 初始值
	 * @param int $step 递增值
	 * @return int
	 */
	public function id($namespace, $init = 1, $step = 1)
	{
	    $result = $this->command(array(
            'findAndModify' => $this->_collectionName,
            'query'         => array('_id' => $namespace),
            'update'        => array('$inc' => array('id' => $step)),
            'new'           => true,
        ));
        if (isset($result['value']['id'])) {
            return $result['value']['id'];
        }
        $this->insert(array(
            '_id' => $namespace,
            'id'  => $init,
        ));
        return $init;
	}
}