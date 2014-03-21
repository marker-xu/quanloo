<?php
class Model_Data_UserFeedStore extends Model_Data_MongoCollection
{
    public function __construct()
    {
        parent::__construct('stat', 'video_search', 'user_feed_store');
    }

    public function add($intUid, $intFeedType, $intFeedId) {
        if ($intUid < 1 || $intFeedId < 1) {
            return false;
        }
        $arrParams = array('_id' => "{$intFeedId}_{$intUid}", 'user_id' => (int) $intUid, 'feed_type' => (int) $intFeedType,
            'feed_id' => $intFeedId, 'create_time' => time(),
        );
        try {
            $ret = $this->getCollection()->insert($arrParams);
        } catch (MongoException $e) {
            JKit::$log->warn("addFeedStore failure, code-".$e->getCode().", msg-".$e->getMessage().", id-".$intFeedId);
            return false;
        }
        $ret = is_array($ret) ? $ret['ok'] : $ret;
        
        return true;
    }

    public function remove($intUid, $intFeedId) {
        try {
            $ret = $this->getCollection()->remove(array('_id' => "{$intFeedId}_{$intUid}"));
        } catch (MongoException $e) {
            JKit::$log->warn("removeFeed failure, code-".$e->getCode().", msg-".$e->getMessage().", id-".$intFeedId);
            return false;
        }
        $ret = is_array($ret) ? $ret['ok'] : $ret;
        
        return $ret;
    }
}