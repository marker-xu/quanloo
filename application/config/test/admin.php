<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'administrators' => array(
        794123477, // 王家军 
        1798015323, // 吴丽军
        1514986670, // Alex
        850271823, // 肖潇
        1577902728, // 陈媛先
        1185015640, // Shirly
        1827876013, // 石婧
        1546615369, // 林土城
    ),

    'adminwordlist' => array(
        'path' => realpath(APPPATH . '../../../data') . '/wordlist', //词表所在的目录，必须是绝对路径
        'server' => array('10.156.24.38', '10.156.24.39'), //需要同步的web机器
        'server_user' => 'worker',
    ),
    'rsync_passwd' => 'workervideosearch',
);