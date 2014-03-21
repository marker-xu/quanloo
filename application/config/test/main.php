<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 主配置文件，会被框架自动包含，其它要包含的配置文件可以在这里include
 */

// FastDFS集群编号
define('FASTDFS_CLUSTER_USER_AVATAR', 0); // 用户头像集群
define('FASTDFS_CLUSTER_WEB_STORAGE', 0); // WEB存储集群
define('FASTDFS_CLUSTER_VIDEO_THUMBNAIL', 1); // 视频缩略图集群

// 域名常量
define('DOMAIN_SITE', 'test.quanloo.com:8181'); // 站点域名
define('DOMAIN_STATIC', 'test.static.quanloo.sii.sdo.com:9111'); // 静态资源域名
define('DOMAIN_IMAGE_VIDEO_THUMBNAIL', 'img01.quanloostatic.com'); // 视频缩略图域名
define('DOMAIN_IMAGE_THUMBNAIL_FMT', 'img%02d.quanloostatic.com'); // 视频缩略图域名模版
define('DOMAIN_IMAGE_THUMBNAIL_NUM', 3); // 视频缩略图域名个数
define('DOMAIN_IMAGE_USER_AVATAR', 'img11.quanloostatic.com'); // 用户头像域名
define('DOMAIN_WEB_STORAGE_CLUSTER', 'img11.quanloostatic.com'); // WEB存储集群域名
define('DOMAIN_API', 'test.quanloo.com:8181'); // API子域名
define('DOMAIN_API_STATIC', 'test.static.quanloo.sii.sdo.com:9111'); // API静态资源域名

define('SITE_CACHE_ENABLE', FALSE); //是否开启网站cache
define('SITE_CACHE_DEFAULT_LIFETIME', 300); //网站默认cache 300秒