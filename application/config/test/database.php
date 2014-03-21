<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * 数据库配置
 * MongoDB示例：
 * 'mongo' => array( // 配置项名称
		'type'       => 'mongo', // 数据库类型
		'connection' => array( // 连接参数
    	    'dsn'        => 'mongodb://10.156.24.76:27017,10.156.24.98:27017', // 服务器地址
			'connect'    => TRUE, // 是否立即连接，可选，默认为TRUE
			'timeout'    => 1000, // 连接超时时间，单位ms，可选
			'replicaSet' => 'video', // 集群名称，可选
			'username'   => '', // 用户名，可选
			'password'   => '', // 密码，可选
    	    'db'         => 'video', // 认证针对的数据库，可选
    	    'slaveOkay'  => true, // slave是否可读，默认为true
	    )
	)
	Redis示例：
	'redis' => array( // 配置项名称
		'type'                  => 'redis', // 数据库类型
		'connection' => array( // 连接参数
    	    'hostname'   => '10.156.24.38:6379', // 服务器地址
			'timeout'    => 1000, // 连接超时时间，单位ms，可选
    	    'persistent' => FALSE, // 是否使用持久化连接，可选，默认为FALSE
	    )
		'serializer'            => Redis::SERIALIZER_NONE, // 是否序列化，可选，默认为不序列化
		'prefix'                => '', // 统一的Key前缀，可选，默认为空
	),
 */
return array
(
	// 视频MongoDB集群
	'video' => array(
		'type'       => 'mongo',
		'connection' => array(
    	    'dsn'        => array(
    	    	'mongodb://10.156.24.76:30000', 
    	    	'mongodb://10.156.24.116:30000', 
    	    	'mongodb://10.156.24.109:30000', 
    	    	'mongodb://10.156.24.111:30000', 
            ),
			'timeout'    => 1000,
	    )
	),
	// 统计MongoDB集群
	'stat' => array(
		'type'       => 'mongo',
		'connection' => array(
    	    'dsn'        => array(
    	    	'mongodb://10.156.24.99:30011', 
    	    	'mongodb://10.156.24.110:30012'
            ),
			'timeout'    => 1000,
	    )
	),
	// 视频全量统计MongoDB集群
	'video_stat_all' => array(
		'type'       => 'mongo',
		'connection' => array(
    	    'dsn'        => array(
    	    	'mongodb://10.156.24.99:30051', 
    	    	'mongodb://10.156.24.110:30052'
            ),
			'timeout'    => 1000,
	    )
	),
	// 圈子MongoDB集群
	'circle' => array(
		'type'       => 'mongo',
		'connection' => array(
    	    'dsn'        => array(
    	    	'mongodb://10.156.24.101:30041', 
    	    	'mongodb://10.156.24.102:30042'
            ),
			'timeout'    => 1000,
	    )
	),
	// 圈子视频关系MongoDB集群
	'circle_video' => array(
		'type'       => 'mongo',
		'connection' => array(
    	    'dsn'        => array(
    	    	'mongodb://10.156.24.101:30021', 
    	    	'mongodb://10.156.24.102:30022'
            ),
			'timeout'    => 1000,
	    )
	),
	// 前端MongoDB集群
	'web_mongo' => array(
		'type'       => 'mongo',
		'connection' => array(
    	    'dsn'        => array(
    	    	'mongodb://10.156.24.99:30020', 
    	    	'mongodb://10.156.24.110:30020'
            ),
			'timeout'    => 1000,
	    )
	),
	// 前端Redis数据库（Master）
	'web_redis_master' => array(
		'type'       => 'redis',
		'connection' => array(
    	    'hostname'   => '10.156.24.38:6379',
			'timeout'    => 1000,
	    )
	),
	// 前端Redis数据库（Slave）
	'web_redis_slave' => array(
		'type'       => 'redis',
		'connection' => array(
    	    'hostname'   => '10.156.24.39:6379',
			'timeout'    => 1000,
	    )
	),
	// 前端Redis数据库
	'web_redis' => array(
		'type'       => 'redis',
		'connection' => array(
    	    'master' => array(
        	    'hostname'   => '10.156.24.38:6379',
    			'timeout'    => 1000,
    	    ),
    	    'slaves' => array(
    	        array(
            	    'hostname'  => '10.156.24.39:6379',
        			'timeout'   => 1000,
        			'weight'    => 1, // 读取时访问权重，默认为1
        	    )
        	)
	    )
	),
	// 圈内实体
	'circle_entity' => array(
		'type'       => 'redis',
		'connection' => array(
    	    'hostname'   => '10.156.24.35:6379',
			'timeout'    => 1000,
	    )
	),
	// 实体库待处理队列存储的redis配置
	'entity_pending_list' => array(
		'type'       => 'redis',
		'connection' => array(
    	    'hostname'   => '10.156.24.36:6399',
			'timeout'    => 1000,
	    )
	),
);