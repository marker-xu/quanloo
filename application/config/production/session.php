<?php defined('SYSPATH') or die('No direct script access.');

ini_set('session.gc_maxlifetime', 86400 * 30);
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://10.156.24.38:6380?weight=1&timeout=1,tcp://10.156.24.39:6380?weight=1&timeout=1');

return array(
	'native' => array(
        'name' => 'PHPSESSID',
        'lifetime' => 86400 * 30,
    ),
);