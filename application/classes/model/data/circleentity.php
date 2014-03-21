<?php 

/**
 * 圈子实体关系
 */
class Model_Data_CircleEntity extends Model_Data_RedisDB
{
    // DB号常量
    const DB_DEFAULT = 0; // 默认数据库
    const DB_CIRCLE_ENTITY = 15; // 圈子实体关系
    
    /**
     * 构造函数
     * @param int $db
     * @return void
     */
    public function __construct($db = self::DB_DEFAULT)
    {
        parent::__construct('circle_entity', $db);
    }
	
	/**
	 * 圈内实体
	 * @param int $circleId
	 * @return string|null
	 */
	public function get($circleId)
	{
        try {
            $db = $this->getDb();
        } catch (Model_Data_Exception $e) {
            return null;
        }
        $entityId = $db->get($circleId);
        return $entityId ? $entityId : null;
	}
}