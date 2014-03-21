<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Web Redis数据库
 * @author wangjiajun
 */
class Model_Data_WebRedis extends Model_Data_RedisDB
{
    // DB号常量
    const DB_DEFAULT = 0; // 默认数据库
    const DB_USER_VIDEO = 1; // 用户视频关系数据库
    
    // KEY常量
    const KEY_CIRCLE_PREVIEW_UPDATE = 'circle.preview.update'; // 圈子预览图更新队列
    const KEY_CMS_ACL = 'cms.acl'; // CMS权限规则列表
    
    /**
     * 构造函数
     * @param int $db
     * @return void
     */
    public function __construct($db = self::DB_DEFAULT)
    {
        parent::__construct('web_redis', $db);
    }
    
    /**
     * 发送圈子预览图更新消息
     * @param int $circleId
     * @return bool
     */
    public function sendCirclePreviewUpdateMsg($circleId)
    {
        try {
            $db = $this->getDb();
        } catch (Model_Data_Exception $e) {
            return false;
        }
        return (bool) $db->rPush(self::KEY_CIRCLE_PREVIEW_UPDATE, $circleId);
    }
    
    /**
     * 获取圈子预览图更新消息
     * @param int $offset
     * @param int $count
     * @param bool $remove
     * @return array
     */
    public function receiveCirclePreviewUpdateMsgs($offset = 0, $count = 10, $remove = true)
    {
        try {
            $db = $this->getDb();
        } catch (Model_Data_Exception $e) {
            return false;
        }
        $msgs = $db->lRange(self::KEY_CIRCLE_PREVIEW_UPDATE, $offset, $offset + $count);
        if ($msgs && $remove) {
            $db->lTrim(self::KEY_CIRCLE_PREVIEW_UPDATE, $offset, $offset + $count);
        }
        return $msgs;
    }
    
    /**
     * CMS ACL对象序列化和反序列化
     * @param array $acl
     * @return bool|array
     */
    public function cmsAcl($acl = null)
    {
        try {
            $db = $this->getDb();
        } catch (Model_Data_Exception $e) {
            return is_null($acl) ? array() : false;
        }
        if (is_null($acl)) {
            $value = $db->get(self::KEY_CMS_ACL);
            if ($value === false) {
                return array();
            } else {
                return json_decode($value, true);
            }
        } else {
            $value = json_encode($acl);
            return $db->set(self::KEY_CMS_ACL, $value);
        }
    }
}