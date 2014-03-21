<?php
class Model_Logic_Adconst extends Model {
	const AD_POS_PLAYER_RIGHT_1 = 1;
	const AD_POS_CIRCLE_TOP_1 = 2;
	const AD_POS_ENTITY_TOP_1 = 3;
	
	public static $AD_POS_LIST = array(
		self::AD_POS_PLAYER_RIGHT_1 => "播放页右侧第一个广告位",
		self::AD_POS_CIRCLE_TOP_1 => "圈子详情页头部横条广告",
		self::AD_POS_ENTITY_TOP_1 => "长视频页头部横条广告",
	);

	/**
	 * 该类型物料的ad_mat字段格式为：{ad_type: 1, ad_title: 广告标题, ad_url: 广告跳转url, ad_pic: 广告图片地址}
	 */
	const AD_TYPE_1_IMG = 1;
	
	public static $AD_TYPE_LIST = array(
		self::AD_TYPE_1_IMG => "单个图片+标题",
	);
	
	const AD_STATUS_NEW = 1;
	const AD_STATUS_VALID = 2;
	const AD_STATUS_REFUSE = 3;
	const AD_STATUS_PAUSE = 4;
	
	public static $AD_STATUS_LIST = array(
		self::AD_STATUS_NEW => '新建',
		self::AD_STATUS_VALID => '生效',
		self::AD_STATUS_REFUSE => '审核不通过',
		self::AD_STATUS_PAUSE => '暂停',
	);
}