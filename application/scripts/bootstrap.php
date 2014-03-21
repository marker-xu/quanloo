<?php

/**
 * The directory in which your application specific resources are located.
 * The application directory must contain the bootstrap.php file.
 *
 * @see  http://kohanaframework.org/guide/about.install#application
 */
$application = dirname(__FILE__).'/..';

/**
 * The directory in which your modules are located.
 *
 * @see  http://kohanaframework.org/guide/about.install#modules
 */
$modules = dirname(__FILE__).'/../../../../php5/lib/php-libs/jkit/modules';

/**
 * The directory in which the Kohana resources are located. The system
 * directory must contain the classes/kohana.php file.
 *
 * @see  http://kohanaframework.org/guide/about.install#system
 */
$system = dirname(__FILE__).'/../../../../php5/lib/php-libs/jkit/system';

/**
 * The default extension of resource files. If you change this, all resources
 * must be renamed to use the new extension.
 *
 * @see  http://kohanaframework.org/guide/about.install#ext
 */
define('EXT', '.php');

/**
 * End of standard configuration! Changing any of the code below should only be
 * attempted by those with a working knowledge of Kohana internals.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 */

// Set the full path to the docroot
define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

// Make the application relative to the docroot, for symlink'd index.php
if ( ! is_dir($application) AND is_dir(DOCROOT.$application))
	$application = DOCROOT.$application;

// Make the modules relative to the docroot, for symlink'd index.php
if ( ! is_dir($modules) AND is_dir(DOCROOT.$modules))
	$modules = DOCROOT.$modules;

// Make the system relative to the docroot, for symlink'd index.php
if ( ! is_dir($system) AND is_dir(DOCROOT.$system))
	$system = DOCROOT.$system;

// Define the absolute paths for configured directories
define('APPPATH', realpath($application).DIRECTORY_SEPARATOR);
define('MODPATH', realpath($modules).DIRECTORY_SEPARATOR);
define('SYSPATH', realpath($system).DIRECTORY_SEPARATOR);

// Clean up the configuration vars
unset($application, $modules, $system);

/**
 * Define the start time of the application, used for profiling.
 */
if ( ! defined('KOHANA_START_TIME'))
{
	define('KOHANA_START_TIME', microtime(TRUE));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if ( ! defined('KOHANA_START_MEMORY'))
{
	define('KOHANA_START_MEMORY', memory_get_usage());
}

// Include main config
require_once APPPATH.'config/main.php';

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
	JKit::$environment = constant('JKit::'.strtoupper($_SERVER['KOHANA_ENV']));
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
	'firephp'    => APPPATH.'/modules/firephp',
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

JKit::$template_settings['compile_dir'] = SYSPATH . '/data/_smarty/tpl_c/';
JKit::$template_settings['config_dir'] = APPPATH.'views/conf/';
JKit::$template_settings['cache_dir'] = SYSPATH . '/data/_smarty/cache/';

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

// MongDB访问超时设为不限
MongoCursor::$timeout = -1;

// 内存使用不限
ini_set('memory_limit', -1);
