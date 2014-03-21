<?php
/**
 * 视频Tag墙
 */
class Model_Data_XComment extends Model_Data_MongoCollection
{
	public static $arrDefaultXComment = array('真带劲!!' => 1, '视频有点糊啊' => 1, '坑爹的看不了啊' => 1);
	public function __construct() {
		parent::__construct('web_mongo', 'video_search', 'video_xcomment');
	}
}