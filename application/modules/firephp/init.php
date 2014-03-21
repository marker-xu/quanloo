<?php defined('SYSPATH') or die('No direct script access.');

// Disable in CLI
if (Kohana::$is_cli)
	return;

if (JKit::$environment == JKit::DEVELOPMENT)
{
    require_once Kohana::find_file('vendor/FirePHPCore','FirePHP.class');
    
    $fire_logger = new Fire_Log(array());
    Kohana::$log->attach($fire_logger, Log::STRACE, 0, 'fire_log_writer');
}