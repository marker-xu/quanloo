<?php 

/**
 * 圈子视频关系
 * @deprecated 该mongo表已经废弃
 */
class Model_Data_CircleVideo extends Model_Data_MongoCollection
{
	public function __construct()
	{
        parent::__construct('circle_video', 'video_search', 'circle_video', false);
	}
	
	/**
	 * 圈内视频数
	 * @param int $circleId
	 * @return int
	 */
	public function circleVideoCount($circleId)
	{
	    return $this->count(array('circle_id' => $circleId));
	}
    
    /**
     * 随机选择一批圈内视频
	 * @param int $circleId
     * @param int $count
     * @param array $fields
     * @return array
     */
    public function random($circleId, $count, $fields = array())
    {
        $min = 0;
        $max = 1024;
        $docs = array();
        while ($min < $max) {
            $docs = $this->find(array('circle_id' => $circleId), $fields, NULL, $count, 
                rand($min, $max));
            if (count($docs) == $count) {
                break;
            }
            $max = floor($max / 2);
        }
        return $docs;
    }	
}