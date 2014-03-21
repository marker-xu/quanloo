<?php defined('SYSPATH') or die('No direct script access.');
// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH.'classes/kohana/core'.EXT;

if (is_file(APPPATH.'classes/kohana'.EXT)) {
	// Application extends the core
	require APPPATH.'classes/kohana'.EXT;
} else {
	// Load empty core extension
	require SYSPATH.'classes/kohana'.EXT;
}

if (is_file(APPPATH.'class/jkit'.EXT)) {
	// Application extends the jkit
	require APPPATH.'classes/jkit'.EXT;	
} else {
	// Load jkit
    require MODPATH.'jkit/classes/jkit'.EXT;
}

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Asia/Shanghai');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('zh-cn');

/**
 * 设置cookie加密令牌
 */
Cookie::$salt = 'f5d2a73f311f7dec173560b88d7d1a9d';

/**
 * Set JKit::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV'])) {
	JKit::$environment = constant('Kohana::'.strtoupper($_SERVER['KOHANA_ENV']));
}

// 注册模块
JKit::register_modules(array(
	'jkit'		=>  MODPATH.'jkit',          // the JKit framework
//	'tests'		=>	MODPATH.'jkit/tests',    // the tests for JKit
//	'auth'       => MODPATH.'auth',          // Basic authentication
	'cache'      => MODPATH.'cache',         // Caching with multiple backends
	'codebench'  => MODPATH.'codebench',     // Benchmarking tool
	'database'   => MODPATH.'database',      // Database access
	'image'      => MODPATH.'image',         // Image manipulation
//	'orm'        => MODPATH.'orm',           // Object Relationship Mapping
	'unittest'   => MODPATH.'unittest',      // Unit testing
//	'userguide'  => MODPATH.'userguide',     // User guide and API documentation
	'email'  	 => MODPATH.'email',     	 // email
	'firephp'    => APPPATH.'/modules/firephp',
	'pagination' => APPPATH.'/modules/pagination',
	'opensdk'    => APPPATH.'/modules/opensdk',
));

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
JKit::init(array(
	'base_url'   => NULL,
	'index_file' => false,
	'charset'    => 'utf-8',
	'cache_dir'  => APPPATH.'../../../data/cache',
	'errors'     => false,
	'caching'    => JKit::$environment == JKit::DEVELOPMENT ? false : true,
	'profile'    => JKit::$environment == JKit::DEVELOPMENT ? true : false,
));

// 初始化各个模块
JKit::init_modules();

JKit::$template_settings['compile_dir'] = APPPATH.'../../../data/_smarty/tpl_c/';
JKit::$template_settings['config_dir'] = APPPATH.'views/conf/';
JKit::$template_settings['cache_dir'] = APPPATH.'../../../data/_smarty/cache/';

JKit::$security['csrf'] = false;
JKit::$security['xss']  = false;
JKit::$security['non-ajax access']  = true;

// 错误处理
if (JKit::$environment == JKit::DEVELOPMENT) {
    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 0);
}
set_exception_handler(array('ExceptionHandler', 'handle'));

// 日志设置
if (JKit::$environment == JKit::DEVELOPMENT) {
    $level = Log::DEBUG;
} else {
    $level = Log::INFO;
}
Kohana::$log->attach(new Log_File(APPPATH.'../../../data/log/www'), $level);

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
Route::set('player_page', 'v/<id>.html')
    ->defaults(array(
        'controller' => 'video',
        'action'     => 'index',
        'route_name' => 'player_page',
));
Route::set('circle_cat', 'category/(<cat>(/<tag>))', array('tag' => '.*'))
    ->defaults(array(
        'controller' => 'circle',
        'action'     => 'browse',
        'cat'        => 'all',
        'route_name' => 'circle_cat',
));
Route::set('circle_detail', 'circle/<id>(/<tag>)', array('id' => '\d+', 'tag' => '.*'))
    ->defaults(array(
        'controller' => 'circle',
        'action'     => 'index',
        'route_name' => 'circle_detail',
));
Route::set('circle_detail_user', 'user/<creator>/circle/<id>(/<tag>)', array('id' => '\d{5,}', 'tag' => '.*'))
    ->defaults(array(
        'controller' => 'circle',
        'action'     => 'index',
        'route_name' => 'circle_detail_user',
));
Route::set('user', 'user/(<id>(/<action>(/<type>)))', array('id' => '\d+', 'type' => '[\d\w-_]*'))
    ->defaults(array(
        'controller' => 'user',
        'action'     => 'index',
        'route_name' => 'user_page',
));
Route::set('movie_info', 'movie/info/<id>', array('id' => '\w+'))
    ->defaults(array(
        'controller' => 'movie',
        'action'     => 'info',
        'route_name' => 'movie_info',
));
Route::set('tv_info', 'tv/info/<id>', array('id' => '\w+'))
    ->defaults(array(
        'controller' => 'tv',
        'action'     => 'info',
        'route_name' => 'tv_info',
));
Route::set('star_info', 'star/info/<id>')
    ->defaults(array(
        'controller' => 'star',
        'action'     => 'info',
        'route_name' => 'star_info',
));
Route::set('default', '(<controller>(/<action>))')
	->defaults(array(
		'controller' => 'index',
		'action'     => 'index',
));
Route::set('mobile', '<directory>(/<controller>(/<action>))',
	    array(
	        'directory' => '(mobile|apis)'
	    )
	)->defaults(array(
        'controller' => 'welcome',
        'action'     => 'index',
));