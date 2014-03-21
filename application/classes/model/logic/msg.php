<?php
/**
 * 处理与后端消息系统的交互
 */
class Model_Logic_Msg extends Model
{
    const T_FOLLOW = 1;
    const T_SYSTEM = 2;    
    const T_MENTION = 3;
    const T_COMMENT = 4;
    const T_PEER = 5;
    
    const MENTION_TYPE_FEED = 1;
  
    public function resetNewFansCounter($intUid) {
        $arrPostParam = array('intUid' => $intUid);
        try {
            $strJson = RPC::call('message', '/clearfollowcount', array('method' => 'post', 'post_vars' => $arrPostParam), 2);
            JKit::$log->debug($strJson, null, __FILE__, __LINE__);
        } catch (Exception $e) {
            JKit::$log->warn($e->getMessage(), $arrPostParam, $e->getFile(), $e->getLine());
            return false;
        }
        if (! $strJson || ! ($arrTmp = json_decode($strJson, true)) || $arrTmp['retcode']) {
            return false;
        } else {
            return true;
        }
        
        return $arrTmp['retbody']['fail'] < 1;        
    }
    
    /**
     * 获取一个用户的消息计数信息
     * 
     * @param int $intUid
     * @param int $strKey 如果为null，返回数组，否则为具体项的值，支持的key有：
     *     intFollowUnread	INT	关注消息未读数，没有消息总数
     *     intPeerAll	INT	个人消息总数
     *     intPeerUnread	INT	个人消息未读数
     *     intMentionAll	INT	提到我的总数
     *     intMentionUnread	INT	提到我的未读数
     *     intSystemAll	INT	系统消息总数
     *     intSystemUnread	INT	系统消息未读数
     *     intCommentAll	INT	被评论消息总数
     *     intCommentUnread	INT	被评论消息未读数
     * @return array|int 失败返回false
     */
    public function getMsgCountInfo($intUid, $strKey = null) {
        try {
            $strJson = RPC::call('message', '/messagecount', array('post_vars' => array('intUid' => $intUid)), 2);
            JKit::$log->debug($strJson, $intUid, __FILE__, __LINE__);
        } catch (Exception $e) {
            JKit::$log->warn($e->getMessage(), $intUid, $e->getFile(), $e->getLine());
            return false;
        }
        if (! $strJson || ! ($arrTmp = json_decode($strJson, true)) || $arrTmp['retcode']) {
            return false;
        }
        $arrTmp = (array) $arrTmp['retbody'];
        if ($strKey === null) {
            return $arrTmp;
        } else {
            return (int) $arrTmp[$strKey];
        }       
    }  
    
    public function sendFollowMsg($intSender, $strSenderNick, $intReceiver, $strReceiverNick = null) {
        if ($strReceiverNick === null) {
    	    $modelDataUser = new Model_Data_User();
    	    try {
    	        $user = $modelDataUser->get($intReceiver, array('nick'));
    	    } catch (Exception $e) {
    	        JKit::$log->warn($e->getMessage(), null, $e->getFile(), $e->getLine());
    	        return false;
    	    }
    	    $strReceiverNick = @$user['nick'];
        }
        $arrPostParam = array('strReceivers' => json_encode(array($intReceiver => $strReceiverNick)),
            'intTypeId' => self::T_FOLLOW, 'intSendUid' => $intSender, 'strSendUname' => $strSenderNick, 'strMessageContent' => '{}');
        $arrTmp = $this->_sendMsgHelper($arrPostParam);
        
        return $arrTmp['retbody']['fail'] < 1;       
    }
    
    public function sendMsg($intSender, $strSenderNick, array $arrReceiver, $intMsgType, $arrMsgContent) {
        $arrPostParam = array('strReceivers' => json_encode($arrReceiver),
            'intTypeId' => $intMsgType, 'intSendUid' => $intSender, 'strSendUname' => $strSenderNick, 'strMessageContent' => json_encode($arrMsgContent));
        $arrTmp = $this->_sendMsgHelper($arrPostParam);
        
        return $arrTmp['retbody']['fail'] < 1;       
    }
    
    public function recvMsg($intUid, $intMsgType, $intOffset, $intCount) {
        $intPageNo = (int) ($intOffset / $intCount);
        $arrPostParam = array('intUid' => $intUid, 'intTypeId' => $intMsgType, 'intPageNo' => $intPageNo, 'intListNum' => $intCount);
        try {
            $strJson = RPC::call('message', '/recvbox', array('post_vars' => $arrPostParam), 2);
            JKit::$log->debug($strJson, $intUid, __FILE__, __LINE__);
        } catch (Exception $e) {
            JKit::$log->warn($e->getMessage(), $intUid, $e->getFile(), $e->getLine());
            return false;
        }
        if (! $strJson || ! ($arrTmp = json_decode($strJson, true)) || $arrTmp['retcode']) {
            return false;
        }
        $arrTmp = array('total' => (int) @$arrTmp['retbody']['all_num'], 'data' => @$arrTmp['retbody']['message_list']);
        return $arrTmp;      
    }   
    
    protected function _sendMsgHelper($arrPostParam) {
        try {
            $strJson = RPC::call('message', '/sendmessage', array('method' => 'post', 'post_vars' => $arrPostParam), 2);
            JKit::$log->debug($strJson, $arrPostParam, __FILE__, __LINE__);
        } catch (Exception $e) {
            JKit::$log->warn($e->getMessage(), $arrPostParam, $e->getFile(), $e->getLine());
            return false;
        }
        if (! $strJson || ! ($arrTmp = json_decode($strJson, true))) {
            return false;
        }

        return $arrTmp;
    }
}