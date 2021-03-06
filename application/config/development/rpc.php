<?php defined('SYSPATH') or die('No direct script access.');

return array
(
	'search' => array(
		'type' => RPC_TYPE_THRIFT,
		'option' => array(
			'balance' => 'Rpc_Balance_RoundRobin',
			'transport' => 'TFramedTransport',		
			'protocol' => NULL, //传入NULL会使用系统默认的TBinaryProtocolAccelerated
			'ctimeout' => 1000,
			'wtimeout' => 2000,
			'rtimeout' => 7000,
		),
		'server' => array(
			array('host' => '10.156.24.55', 'port' => 9990),
		),		
	),
	'search_circle' => array(
		'type' => RPC_TYPE_HTTP,
		'option' => array(
			'balance' => 'Rpc_Balance_RoundRobin',
			'ctimeout' => 1000,
			'wtimeout' => 2000,
			'rtimeout' => 3000,
		),
		'server' => array(
			array('host' => '10.156.24.55', 'port' => 8091),
		),		
	),
	'relation_query' => array(
		'type' => RPC_TYPE_THRIFT,
		'option' => array(
			'balance' => 'Rpc_Balance_RoundRobin',
			'transport' => 'TBufferedTransport',		
			'protocol' => NULL,	
			'ctimeout' => 1000,
			'wtimeout' => 2000,
			'rtimeout' => 3000,
		),
		'server' => array(
			array('host' => '10.156.24.54', 'port' => 9246),
		),		
	),
	'suggestion' => array(
		'type' => RPC_TYPE_HTTP,
		'option' => array(
			'balance' => 'Rpc_Balance_RoundRobin',
			'ctimeout' => 1000,
			'wtimeout' => 2000,
			'rtimeout' => 3000,
		),
		'server' => array(
			array('host' => 'sug.sii.sdo.com', 'port' => 6333),
		),		
	),
	'video_thumbnail' => array(
		'type' => RPC_TYPE_HTTP,
		'option' => array(
			'balance' => 'Rpc_Balance_RoundRobin',
			'ctimeout' => 1000,
			'wtimeout' => 2000,
			'rtimeout' => 3000,
		),
		'server' => array(
			array('host' => 'tnpath.sii.sdo.com', 'port' => 7788),
		),		
	),
	'stat_log' => array(
		'type' => RPC_TYPE_HTTP,
		'option' => array(
			'balance' => 'Rpc_Balance_RoundRobin',
			'ctimeout' => 1000,
			'wtimeout' => 2000,
			'rtimeout' => 3000,
		),
		'server' => array(
			array('host' => 'log.sii.sdo.com', 'port' => 8081),
		),		
	),
    'circle_video' => array(
        'type' => RPC_TYPE_THRIFT,
        'option' => array(
            'transport' => 'TFramedTransport',
            'protocol' => NULL,
            'ctimeout' => 1000,
            'wtimeout' => 2000,
            'rtimeout' => 3000,
        ),
        'server' => array(
            array('host' => '10.156.24.55', 'port' => 8765),
        ),
    ),
	'related_circles' => array(
		'type' => RPC_TYPE_THRIFT,
		'option' => array(
			'balance' => 'Rpc_Balance_RoundRobin',
			'transport' => 'TBufferedTransport',		
			'protocol' => NULL,	
			'ctimeout' => 1000,
			'wtimeout' => 2000,
			'rtimeout' => 3000,
		),
		'server' => array(
			array('host' => '10.156.24.35', 'port' => 33304),
		),		
	),
    'feed_submit' => array(
        'type' => RPC_TYPE_THRIFT,
        'option' => array(
                'transport' => 'TFramedTransport',
                'protocol' => NULL,
                'ctimeout' => 1000,
                'wtimeout' => 2000,
                'rtimeout' => 3000,
        ),
        'server' => array(
                array('host' => '10.156.24.55', 'port' => 8484),
        ),
    ),
    'feed_query' => array(
        'type' => RPC_TYPE_THRIFT,
        'option' => array(
                'transport' => 'TFramedTransport',
                'protocol' => NULL,
                'ctimeout' => 1000,
                'wtimeout' => 2000,
                'rtimeout' => 3000,
        ),
        'server' => array(
                array('host' => '10.156.24.55', 'port' => 7667),
        ),
    ),
    'message' => array(
            'type' => RPC_TYPE_HTTP,
            'option' => array(
                    'balance' => 'Rpc_Balance_RoundRobin',
                    'ctimeout' => 1000,
                    'wtimeout' => 1000,
                    'rtimeout' => 1000,
            ),
            'server' => array(
                    array('host' => '10.156.24.97', 'port' => 8080),
            ),
    ), 
    'comment_score' => array(
        'type' => RPC_TYPE_THRIFT,
        'option' => array(
            'transport' => 'TFramedTransport',
        ),
        'server' => array(
            array('host' => '10.156.24.23', 'port' => 9090),
        ),
    ),
	'related_video_realtime_reco' => array( //相关视频的实时推荐服务
		'type' => RPC_TYPE_HTTP,
		'option' => array(
			'balance' => 'Rpc_Balance_RoundRobin',
			'ctimeout' => 1000,
			'wtimeout' => 1000,
			'rtimeout' => 1000,
		),
		'server' => array(
			array('host' => '10.156.24.34', 'port' => 9875),
			array('host' => '10.156.24.34', 'port' => 9876),
			array('host' => '10.156.24.35', 'port' => 9875),
			array('host' => '10.156.24.35', 'port' => 9876),
		),
	), 
	'atuser_sug' => array( //@用户的suggestion
			'type' => RPC_TYPE_HTTP,
			'option' => array(
					'balance' => 'Rpc_Balance_RoundRobin',
					'ctimeout' => 200,
					'wtimeout' => 500,
					'rtimeout' => 200,
			),
			'server' => array(
					array('host' => '10.156.24.10', 'port' => 7333),
			),
	),
	'register_recommend_circles' => array( //用户注册页的TAG推荐圈子服务
		'type' => RPC_TYPE_HTTP,
		'option' => array(
			'balance' => 'Rpc_Balance_RoundRobin',
			'ctimeout' => 1000,
			'wtimeout' => 1000,
			'rtimeout' => 1000,
		),
		'server' => array(
			array('host' => '10.156.24.35', 'port' => 9844),
		),
	), 
	'guesslike_recommend_rec' => array( //发现页推荐服务
		'type' => RPC_TYPE_HTTP,
		'option' => array(
			'balance' => 'Rpc_Balance_RoundRobin',
			'ctimeout' => 2000,
			'wtimeout' => 1000,
			'rtimeout' => 2000,
		),
		'server' => array(
			array('host' => '10.156.24.35', 'port' => 7966),
			array('host' => '10.156.24.36', 'port' => 7966),
		),
	),
	// 实体库
    'entity' => array(
            'type' => RPC_TYPE_HTTP,
            'option' => array(
                    'balance' => 'Rpc_Balance_RoundRobin',
                    'ctimeout' => 1000,
                    'wtimeout' => 1000,
                    'rtimeout' => 3000,
            ),
            'server' => array(
                    array('host' => '10.156.24.55', 'port' => 7791),
            ),
    ), 
	// 长视频检索
    'long_video' => array(
            'type' => RPC_TYPE_HTTP,
            'option' => array(
                    'balance' => 'Rpc_Balance_RoundRobin',
                    'ctimeout' => 1000,
                    'wtimeout' => 1000,
                    'rtimeout' => 3000,
            ),
            'server' => array(
                    array('host' => '10.156.24.36', 'port' => 8333),
            ),
    ), 
	// 明星
    'star' => array(
            'type' => RPC_TYPE_HTTP,
            'option' => array(
                    'balance' => 'Rpc_Balance_RoundRobin',
                    'ctimeout' => 1000,
                    'wtimeout' => 1000,
                    'rtimeout' => 3000,
            ),
            'server' => array(
                    array('host' => '10.156.24.55', 'port' => 7792),
            ),
    ), 
    // 长视频的相关推荐
    'entity_related' => array(
            'type' => RPC_TYPE_TTSERVER,
            'option' => array(
                    'balance' => 'Rpc_Balance_RoundRobin',
                    'ctimeout' => 1000,
                    'wtimeout' => 1000,
                    'rtimeout' => 1000,
            ),
            'server' => array(
                    array('host' => '10.156.24.36', 'port' => 2098),
            ),
    ),
    // 长视频的圈子推荐的thrift服务
    'entity_circle' => array(
            'type' => RPC_TYPE_THRIFT,
            'option' => array(
                    'balance' => 'Rpc_Balance_RoundRobin',
    				'transport' => 'TBufferedTransport',
                    'ctimeout' => 1000,
                    'wtimeout' => 1000,
                    'rtimeout' => 1000,
            ),
            'server' => array(
                    array('host' => '10.156.24.36', 'port' => 9094),
            ),
    ),
);