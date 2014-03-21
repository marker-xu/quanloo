<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 缓存配置
	Redis示例：
	'redis' => array(
		'driver'                 => 'redis', // 缓存类型
		'servers'           	 => array( // 服务器列表
			array(
				'host'             => '10.156.24.38', // IP
				'port'             => 6379, // PORT
				'persistent'       => FALSE, // 是否采用持久连接，可选，默认为FALSE
				'weight'           => 1, // 权重，可选，默认为1
				'timeout'          => 1000, // 连接超时时间，单位ms，可选
                'status'           => TRUE, // 是否可用，可选，默认为TRUE
			),
		),
		'serializer'             => Redis::SERIALIZER_NONE, // 是否序列化，可选，默认不序列化
		'prefix'                 => '', // 统一的Key前缀，可选，默认为空
	),
 */

return array
(
	'web' => array(
		'driver'                 => 'redis',
		'servers'           	 => array(
			array(
				'host'             => '10.156.24.38',
				'port'             => 6380,
				'timeout'          => 1000,
			),
			array(
				'host'             => '10.156.24.39',
				'port'             => 6380,
				'timeout'          => 1000,
			),
		),
	),
	'recommend' => array(
		'driver'                 => 'redis',
		'servers'           	 => array(
			array(
				'host'             => '10.156.24.35',
				'port'             => 6379,
				'timeout'          => 1000,
			),
		),
	),
	
	'recommend_video' => array(
		'driver'                 => 'redis',
		'servers'           	 => array(
			array(
				'host'             => '10.156.24.36',
				'port'             => 6399,
				'timeout'          => 1000,
			),
		),
	),
	
	'recommend_kt' => array(
		'driver'             => 'memcache',
		'default_expire'     => 3600,
		'compression'        => FALSE,              // Use Zlib compression (can cause issues with integers)
		'servers'            => array(
			array(
				'host'             => '10.156.24.36',  //vs-14
				'port'             => 2098,        // Memcache port number
				'persistent'       => FALSE,        // Persistent connection
				'weight'           => 1,
				'timeout'          => 1,
				'retry_interval'   => 15,
				'status'           => TRUE,
			),
		),
		'instant_death'      => TRUE,
	),
	// 视频推荐理由
	'recommend_reason' => array(
		'driver'                 => 'redis',
		'servers'           	 => array(
			array(
				'host'             => '10.156.24.35',
				'port'             => 6379,
				'timeout'          => 1000,
			),
		),
	),
	//大家都在看推荐视频
	'recommend_cur_watched' => array(
		'driver'                 => 'redis',
		'servers'           	 => array(
			array(
				'host'             => '10.156.24.35',
				'port'             => 6379,
				'timeout'          => 500,
			),
			array(
				'host'             => '10.156.24.36',
				'port'             => 6399,
				'timeout'          => 500,
			),
		),
	),
	//根据vid查找对应的圈子id
	'recommend_vid2cid' => array(
		'driver'                 => 'redis',
		'servers'           	 => array(
			array(
				'host'             => '10.156.24.35',
				'port'             => 6379,
				'timeout'          => 500,
			),
		),
	),
);