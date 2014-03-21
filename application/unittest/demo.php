<?php defined('SYSPATH') or die('No direct script access.');

class DemoTest extends UnitTestCase
{
    function __construct()
    {
        parent::__construct();
    }
    
    function setUp() {}
    
    function tearDown() {}

    function test_test()
    {
        $str = '剧情简介：山姆·维特维奇（希亚·拉博夫 饰）成功阻止了“霸天虎”和“汽车人”两派变形金刚的对决，拯救了全世界的两年后，他虽然被视为英雄人物，但是他还是一个青少...';
        print_r(mb_strwidth($str));
        print_r(mb_strimwidth($str, 0, 4, '', "UTF-8"));
        print_r(mb_internal_encoding());
    }
}
