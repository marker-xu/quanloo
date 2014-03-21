<?php defined('SYSPATH') or die('No direct script access.');

class ModelDataVideoTest extends UnitTestCase
{
    private $_model;
    
    function __construct()
    {
        parent::__construct();
        
        $this->_model = new Model_Data_Video();
    }
    
    function setUp()
    {
    }
    
    function tearDown()
    {
    }

    function test_storage_upload_by_filename()
    {
        $fdfs = new FastDFS(FASTDFS_CLUSTER_USER_AVATAR);
        $ret = $fdfs->storage_upload_by_filename(__FILE__);
        Kohana::$log->debug(__FUNCTION__, $ret);
        $this->assertIsA($ret, 'array');
        
        $fdfs = new FastDFS(FASTDFS_CLUSTER_VIDEO_THUMBNAIL);
        $ret = $fdfs->storage_upload_by_filename(__FILE__);
        Kohana::$log->debug(__FUNCTION__, $ret);
        $this->assertIsA($ret, 'array');
    }
}
