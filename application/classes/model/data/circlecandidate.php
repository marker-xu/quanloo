<?php 

/**
 * 候选圈子
 * @author wangjiajun
 */
class Model_Data_CircleCandidate extends Model_Data_MongoCollection
{
    const SOURCE_DATA_MINING = 1;
    const SOURCE_EDITOR_ADD = 2;
    const SOURCE_USER_SUBMIT = 3;
    
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REFUSED = 2; 
    const STATUS_PROCESSED = 3; 
    
    public static $sources = array(
        self::SOURCE_DATA_MINING => '数据挖掘',
        self::SOURCE_EDITOR_ADD => '编辑添加',
        self::SOURCE_USER_SUBMIT => '用户提交'
    );
    
    public static $statuses = array(
        self::STATUS_PENDING => '待审核',
        self::STATUS_APPROVED => '已通过',
        self::STATUS_REFUSED => '已拒绝',
        self::STATUS_PROCESSED => '已处理'
    );
    
	public function __construct()
	{
        parent::__construct('circle', 'video_search', 'circle_candidate');
	}
    
    /**
     * 根据名称查找圈子
     * @param string $title
     * @param bool $caseSensitive
     * @return array
     */
    public function getByTitle($title, $caseSensitive = true)
    {
        if ($caseSensitive) {
            $circle = $this->findOne(array('title' => $title));
        } else {
            $circle = $this->findOne(array('title' => new MongoRegex("/^$title$/i")));
        }
        return $circle;
    }
	
	/**
	 * 新建
	 * @param string $title
	 * @param int $source
	 * @param int $creator
	 * @param int $status
	 * @param array $doc
	 * @return MongoId
	 */
	public function create($title, $source, $creator = 0, $status = self::STATUS_PENDING, 
	    $doc = array())
	{
	    $doc = array_merge(array(
	        'title' => $title,
	        'source' => (int) $source,
	        'creator' => (int) $creator,
	        'status' => $status,
	        'category' => array(),
	        'tag' => array(),
	        'submitted_count' => 1,
	        'create_time' => new MongoDate()
	    ), $doc);
	    $_id = new MongoId();
	    $this->insert($doc, array(), $_id);
	    return $_id;
	}
	
	/**
	 * 修改
	 * @param string $id
	 * @param array $doc
	 * @return bool
	 */
	public function modify($id, $doc)
	{
	    if (isset($doc['title'])) {
	        $doc2 = $this->getByTitle($doc['title']);
	        if ($doc2 && $doc2['_id'] != $id) {
	            throw new Model_Data_Exception("圈子名称重复。");
	        }
	    }
	    if (isset($doc['tag'])) {
            $doc['tag'] = array_values(array_unique(array_filter($doc['tag'])));
	    }
	    return $this->update(array('_id' => new MongoId($id)), $doc, array('safe' => true));
	}
}
