<?php
/**
 * 处理与后端Feed系统的交互
 */
class Model_Logic_Feed2 extends Model
{
    const FEED_SOURCE = 1; //web系统在Feed系统中的编号
    const FEED_TYPE_COMMENT_VIDEO = 0x1;//评论视频
    const FEED_TYPE_SHARE_VIDEO = 0x2;//分享视频
    const FEED_TYPE_SHARE_CIRCLE = 0x10;//分享圈子
    const FEED_TYPE_CIRCLE_VIDEO = 0x8;//圈子视频更新
    const FEED_TYPE_MOOD_VIDEO = 0x4;//对视频表示心情
    const FEED_TYPE_FORWARD_FEED = 0x20; //转发动态
    const FEED_TYPE_QUAN_VIDEO = 0x40; //圈一下
    const FEED_TYPE_CREATE_CIRCLE = 0x80; //创建圈子
       
    //虚拟的动态类型，前端展示用，feed系统不真实存在的类型
    const USER_FEED_TYPE_ALL = 0x7f; //用户个人页默认显示的动态类型
    const CIRCLE_FEED_TYPE_ALL = 0xdf; //圈子详情页默认显示的动态类型
    
    const RET_CODE_HAS_MORE = 1;

    const SUBTYPE_ALL = 0;
    const SUBTYPE_FOLLOWING = 1;
    const SUBTYPE_SELF = 2;
    const SUBTYPE_MEMTION_ME = 3;
    const SUBTYPE_CIRCLE_FRIEND = 4;
    const SUBTYPE_CIRCLE_SLEF = 5;    
    public static $arrSubtype = array(
        self::SUBTYPE_FOLLOWING => '关注的人动态',
        self::SUBTYPE_CIRCLE_FRIEND => '圈友动态',
        self::SUBTYPE_CIRCLE_SLEF => '圈子热点',           
        self::SUBTYPE_MEMTION_ME => '@提到我的',
        self::SUBTYPE_SELF => '我的动态',                                      
    );
    
    const FORWARD_FEED_TEXT_MAX_LEN = 140;
    
    /**
     * 获取一个用户的动态汇总
     * 
     * @param array $arrParam array(user_id => 用户id, offset => 分页偏移量, count => 每页条数,
     *      lasttime => 选择该时间点之前的feed，0表示不限制, msgtype => 选择哪些类型的feed，不传表示所有,
     *      no_reduce => 不要合并, type => 二级分类, is_mobile => 是不是移动APP的请求)
     * @return array
     */
    public function getUserFeedList($arrParam) {
        $arrRet = array();
        $objLogicUser = new Model_Logic_User();
             
        if (! isset($arrParam['type']) || ! isset(self::$arrSubtype[$arrParam['type']])) {
            $arrParam['type'] = self::SUBTYPE_ALL;
        }

        $arrCircleId = array();        
        if ($arrParam['type'] == self::SUBTYPE_SELF) {//仅获取$arrParam['user_id']的动态
            $arrFollowingId = array($arrParam['user_id']);            
        } elseif ($arrParam['type'] == self::SUBTYPE_FOLLOWING) {
            $arrFollowing = (array) $objLogicUser->followings($arrParam['user_id'], 0, 250);
            $arrFollowingId = array();
            foreach ($arrFollowing as $v) {
                $arrFollowingId[] = $v['following'];
            }           
        } elseif ($arrParam['type'] == self::SUBTYPE_CIRCLE_FRIEND) {
            $arrCircleId = (array) $objLogicUser->getUserCirclesByUid($arrParam['user_id'], true);
            $arrFollowingId = array();
            $arrParam['msgtype'] = self::USER_FEED_TYPE_ALL ^ self::FEED_TYPE_CIRCLE_VIDEO;
        } elseif ($arrParam['type'] == self::SUBTYPE_CIRCLE_SLEF) {
            $arrCircleId = (array) $objLogicUser->getUserCirclesByUid($arrParam['user_id'], true);
            $arrFollowingId = array();
            $arrParam['msgtype'] = self::FEED_TYPE_CIRCLE_VIDEO;           
        } elseif ($arrParam['type'] == self::SUBTYPE_MEMTION_ME) {
            //            
        } else {
            $arrCircleId = (array) $objLogicUser->getUserCirclesByUid($arrParam['user_id'], true);
            $arrFollowing = (array) $objLogicUser->followings($arrParam['user_id'], 0, 250);
            $arrFollowingId = array($arrParam['user_id']);
            foreach ($arrFollowing as $v) {
                $arrFollowingId[] = $v['following'];
            }
        }
        if (! isset($arrParam['msgtype'])) {
            $intMsgType = self::USER_FEED_TYPE_ALL;
        } else {
            $intMsgType = $arrParam['msgtype'];
        }
        if ($arrParam['type'] == self::SUBTYPE_MEMTION_ME) {
            $mixedRes = $this->_getMentionMeFeedList($arrParam);
        } elseif ($arrParam['type'] == self::SUBTYPE_CIRCLE_SLEF) {
            $arrRpcParam = array('balance_key' => $arrParam['user_id'], $arrCircleId, (int) $arrParam['offset'], (int) ($arrParam['count']),
            	$arrParam['lasttime'], JKit::$log->getLogId());
            include_once __DIR__ . '/../thrift/feed/feed_service.php';
            try {
                //get_circle_update_feeds($circleLst, $noffset, $nGetFeedNm, $nLastTime, $rid);
                $mixedRes = RPC::call('feed_query', array('feed_feed_serviceClient', 'get_circle_update_feeds'), $arrRpcParam, 2);
                $mixedRes = get_object_vars($mixedRes);
            } catch (Exception $e) {
                JKit::$log->warn($e->getMessage(), $arrParam, __FILE__, __LINE__);
                return $arrRet;
            }
        } else {
            $arrRpcParam = array('balance_key' => $arrParam['user_id'], $arrCircleId, $arrFollowingId, (int) $arrParam['offset'], (int) ($arrParam['count']),
                $arrParam['lasttime'], $intMsgType, JKit::$log->getLogId());
            include_once __DIR__ . '/../thrift/feed/feed_service.php';
            try {
                //get_feeds($circleLst, $userLst, $nOffset, $nGetFeedNm, $nLastTime, $filterFlag, $rid);
                $mixedRes = RPC::call('feed_query', array('feed_feed_serviceClient', 'get_feeds'), $arrRpcParam, 2);
                $mixedRes = get_object_vars($mixedRes);//print_r($arrRpcParam);print_r($mixedRes);
            } catch (Exception $e) {
                JKit::$log->warn($e->getMessage(), $arrParam, __FILE__, __LINE__);
                return $arrRet;
            }
        }
        if(empty($mixedRes) || $mixedRes['nRetCode'] < 0) {
            return $arrRet;
        }        
        $bolHasMore = false;
        if ($mixedRes['nRetCode'] == self::RET_CODE_HAS_MORE) {
            $bolHasMore = true;
        }  
        $arrMsgId2ContentMap = (array) $mixedRes['feed_content_map'];    
        foreach ($mixedRes['feed_id_lst'] as $intMsgId) {
            if (isset($arrMsgId2ContentMap[$intMsgId])) {
                $objTmp = $arrMsgId2ContentMap[$intMsgId];
                $arrTmp = json_decode($objTmp->content, true);
                $arrTmp['_id'] = $intMsgId;
                $arrTmp['nRepostCount'] = (int) $objTmp->nRepostCount;
                $arrRet[] = $arrTmp;
            }
        }
        unset($mixedRes);
        
        $arrRet = $this->_aggregateUserFeed($arrRet, $arrParam, $arrMsgId2ContentMap);
        $arrRet['has_more'] = $bolHasMore;
        if (isset($arrRet['dict']['circle']) && ! empty($arrCircleId)) {
            //把该用户已经关注的圈子做上标记
            foreach ($arrCircleId as $v) {
                if (isset($arrRet['dict']['circle'][$v])) {
                    $arrRet['dict']['circle'][$v]['is_focus'] = 1;
                }
            }
        }
        
        return $arrRet;
    }

    
    
    /**
     * 获取@我的动态列表
     * @param array $arrParam array(user_id => 用户id, offset => 分页偏移量, count => 每页条数)
     */
    protected function _getMentionMeFeedList($arrParam) {
        $arrRet = array('nRetCode' => 0, 'feed_id_lst' => null, 'feed_content_map' => null);
        $objMsg = new Model_Logic_Msg();
        $arrMsg = $objMsg->recvMsg($arrParam['user_id'], Model_Logic_Msg::T_MENTION, $arrParam['offset'], $arrParam['count']);
        if (empty($arrMsg)) {
            return $arrRet;
        }//print_r($arrMsg);
        $arrMsgId = array();
        foreach ((array) $arrMsg['data'] as $v) {
            $arrMsgContent = json_decode($v['strMessageContent'], true);
            if ($arrMsgContent && $arrMsgContent['msgtype'] == Model_Logic_Msg::MENTION_TYPE_FEED) {
                $arrMsgId[] = $arrMsgContent['msgid'];
            }
        }
        if (empty($arrMsgId)) {
            return $arrRet;
        }
        
        //map<i64,FeedInfo> get_feeds_info(1:list<i64> msgidLst)
        $mixedRes = $this->getFeedById($arrMsgId, $arrParam['user_id']);//print_r($mixedRes);
        if (empty($mixedRes)) {
            return $arrRet;
        }
        
        foreach ($arrMsgId as $k => $v) {
            if (! isset($mixedRes[$v])) {
                //去掉找不到feed的错误Id
                unset($arrMsgId[$k]);
            }
        }
        $arrRet['feed_id_lst'] = $arrMsgId;
        $arrRet['feed_content_map'] = $mixedRes;
        if ($arrMsg['total'] > ($arrParam['offset'] + $arrParam['count'])) {
            $arrRet['nRetCode'] = self::RET_CODE_HAS_MORE;
        }
                
        return $arrRet;        
    }
    
    protected function _aggregateUserFeed($arrFeed, $arrParam, $arrMsgId2ContentMap) {
        $arrTmp = array();
        $arrDict = array();
        if (isset($arrParam['no_reduce']) && $arrParam['no_reduce']) {
            $bolNoReduce = true;
        } else {
            $bolNoReduce = false;
            $arrCommentVidMap = array(); //所有有评论的video_id
            $arrMoodedVidMap = array();
            foreach ($arrFeed as $v) {
                if ($v['msgtype'] == self::FEED_TYPE_COMMENT_VIDEO) {
                    $arrCommentVidMap[$v['vid']] = true;
                } elseif ($v['msgtype'] == self::FEED_TYPE_MOOD_VIDEO) {
                    $arrMoodedVidMap[$v['vid']] = true;
                }
            }            
        }
        while (! empty($arrFeed)) {
            $v = array_shift($arrFeed);
            if (! $bolNoReduce) {
                if ($v['msgtype'] == self::FEED_TYPE_MOOD_VIDEO || $v['msgtype'] == self::FEED_TYPE_SHARE_VIDEO) {
                    if (isset($arrCommentVidMap[$v['vid']])) {
                        //同一个视频，如果存在评论，则不显示该视频的心情和分享
                        continue;
                    }
                    if ($v['msgtype'] == self::FEED_TYPE_SHARE_VIDEO && isset($arrMoodedVidMap[$v['vid']])) {
                        ////同一个视频，如果存在心情，则不显示该视频的分享
                        continue;
                    }
                }
            }
            if (isset($v['uid']) && $v['uid'] > 0) {
                $arrDict['user'][$v['uid']] = true;
            }
            if (isset($v['cid']) && $v['cid'] > 0) {
                $arrDict['circle'][$v['cid']] = true;
            }
            if (isset($v['vid']) && $v['vid']) {
                $arrDict['video'][$v['vid']] = true;
            }
            if ($v['msgtype'] == self::FEED_TYPE_FORWARD_FEED) {
                //转发动态，需要处理原始feed的数据
                $intRootFid = $v['ref'];
                if (! isset($arrMsgId2ContentMap[$intRootFid])) {
                    //原始feed不存在
                    $v['root_feed'] = null;
                } else {
                    $objTmp = $arrMsgId2ContentMap[$intRootFid];
                    $arrRootFeedContent = json_decode($objTmp->content, true);
                    $arrRootFeedContent['nRepostCount'] = (int) $objTmp->nRepostCount;
                    $arrRootFeedContent['_id'] = $intRootFid;
                    $v['root_feed'] = $arrRootFeedContent;
                    if (isset($arrRootFeedContent['uid']) && $arrRootFeedContent['uid'] > 0) {
                        $arrDict['user'][$arrRootFeedContent['uid']] = true;
                    }
                    if (isset($arrRootFeedContent['cid']) && $arrRootFeedContent['cid'] > 0) {
                        $arrDict['circle'][$arrRootFeedContent['cid']] = true;
                    }
                    if (isset($arrRootFeedContent['vid']) && $arrRootFeedContent['vid']) {
                        $arrDict['video'][$arrRootFeedContent['vid']] = true;
                    }
                    if (isset($v['orig_feed_data']) && ! empty($v['orig_feed_data'])) {
                        $v['orig_feed_data'] = json_decode($v['orig_feed_data'], true);
                        if (isset($v['orig_feed_data']['vid'])) {
                            if (! is_array($v['orig_feed_data']['vid'])) {
                                $arrRootFeedVid = array($v['orig_feed_data']['vid']);
                            } else {
                                $arrRootFeedVid = $v['orig_feed_data']['vid'];
                                $v['root_feed']['vid'] = $arrRootFeedVid;
                            }
                            foreach ($arrRootFeedVid as $strRootFeedVidTmp) {
                                $arrDict['video'][$strRootFeedVidTmp] = true;
                            }
                        }
                        if (isset($v['orig_feed_data']['cid'])) {
                            if (! is_array($v['orig_feed_data']['cid'])) {
                                $arrRootFeedCid = array($v['orig_feed_data']['cid']);
                            } else {
                                $arrRootFeedCid = $v['orig_feed_data']['cid'];
                                $v['root_feed']['cid'] = $arrRootFeedCid;
                            }
                            foreach ($arrRootFeedCid as $strRootFeedCidTmp) {
                                $arrDict['circle'][$strRootFeedCidTmp] = true;
                            }
                        }
                    }                    
                }
            }
            if ($bolNoReduce) {
                $arrTmp[] = $v;
                continue;
            }
            /*归并策略start*/                      
            if ($v['msgtype'] == self::FEED_TYPE_SHARE_CIRCLE) {                
                foreach ($arrFeed as $k2 => $v2) {
                    if ($v2['msgtype'] == self::FEED_TYPE_SHARE_CIRCLE && $v2['cid'] == $v['cid']) {
                        //同一个圈子的分享只留最新的一条
                        unset($arrFeed[$k2]);
                        continue;
                    }
                    if ($v2['msgtype'] == self::FEED_TYPE_SHARE_CIRCLE && $v['uid'] == $v2['uid']) {
                        //如果一个人分享了多个圈子，需要两两合并在一块
                        $v['cid'] = array($v['cid'], $v2['cid']);
                        $arrDict['circle'][$v2['cid']] = true;
                        unset($arrFeed[$k2]);
                        break;
                    }
                }
            }
            
            if ($v['msgtype'] == self::FEED_TYPE_MOOD_VIDEO) {
                foreach ($arrFeed as $k2 => $v2) {                   
                    if ($v2['msgtype'] == self::FEED_TYPE_MOOD_VIDEO && $v2['vid'] == $v['vid']) {                        
                        //同一个视频的心情只留最新的一条
                        unset($arrFeed[$k2]);
                        continue;
                    }
                }                
            }
            
            if ($v['msgtype'] == self::FEED_TYPE_SHARE_VIDEO) {
                foreach ($arrFeed as $k2 => $v2) {
                    if ($v2['msgtype'] == self::FEED_TYPE_SHARE_VIDEO && $v2['vid'] == $v['vid']) {
                        //同一个视频的分享只留最新的一条
                        unset($arrFeed[$k2]);
                        continue;
                    }
                }            
            }

            if ($v['msgtype'] == self::FEED_TYPE_CIRCLE_VIDEO) {                
                if (isset($arrParam['type']) && $arrParam['type'] == self::SUBTYPE_CIRCLE_SLEF) {
                	//如果是圈子热点，则同一个圈子新增的同一批次视频，每3个合并在一起
	                $intCountTmp = 1;
	                foreach ($arrFeed as $k2 => $v2) {
	                    if ($v2['msgtype'] == self::FEED_TYPE_CIRCLE_VIDEO && $v['cid'] == $v2['cid'] && $v['time'] == $v2['time']) {
	                        if (is_array($v['vid'])) {
	                            $v['vid'][] = $v2['vid'];
	                        } else {
	                            $v['vid'] = array($v['vid'], $v2['vid']);
	                        }
	                        
	                        $arrDict['video'][$v2['vid']] = true;
	                        unset($arrFeed[$k2]);
	                        if ($intCountTmp >= 2) {
	                            break;
	                        } else {
	                            $intCountTmp++;
	                        }
	                    }
	                }
                } else {
                	//同一批次视频，每3个合并在一起，不同圈子也可以合并在一起
                	$intCountTmp = 1;
                	foreach ($arrFeed as $k2 => $v2) {
                		if ($v2['msgtype'] == self::FEED_TYPE_CIRCLE_VIDEO) {
                			if (is_array($v['vid'])) {
                				$v['vid'][] = $v2['vid'];
                				$v['cid'][] = $v2['cid'];
                			} else {
                				$v['vid'] = array($v['vid'], $v2['vid']);
                				$v['cid'] = array($v['cid'], $v2['cid']);
                			}
                			 
                			$arrDict['video'][$v2['vid']] = true;
                			$arrDict['circle'][$v2['cid']] = true;
                			unset($arrFeed[$k2]);
                			if ($intCountTmp >= 2) {
                				break;
                			} else {
                				$intCountTmp++;
                			}
                		}
                	}                	
                }
            }
            $arrTmp[] = $v;
            /*归并策略end*/
        }
        if (isset($arrDict['user']) && ! empty($arrDict['user'])) {
            $arrUid = array_keys($arrDict['user']);
            $objTmp = new Model_Data_User();
            $arrDict['user'] = $objTmp->getMulti($arrUid, Model_Logic_User::$basicFields);
        }

        if (isset($arrDict['circle']) && ! empty($arrDict['circle'])) {
            $arrCircleId = array_keys($arrDict['circle']);
            $objTmp = new Model_Data_Circle();
            $arrDict['circle'] = $objTmp->getMulti($arrCircleId, Model_Logic_Circle::$basicFields);
        }
        
        if (isset($arrDict['video']) && ! empty($arrDict['video'])) {
            $arrVid = array_keys($arrDict['video']);
            $objTmp = new Model_Logic_Video();
            if (isset($arrParam['is_mobile']) && $arrParam['is_mobile']) {
                $arrVideoField = Model_Logic_Video::$basicFieldsForMobile;
            } else {
                $arrVideoField = Model_Logic_Video::$basicFields;
            }
            $arrDict['video'] = $objTmp->getMulti($arrVid, false, $arrVideoField, true);
        }
     
        return array('data' => $arrTmp, 'dict' => $arrDict);       
    }
/*     
    protected function _feedTestData() {
        $arrData = array(
            json_encode(array('msgtype' => self::FEED_TYPE_CIRCLE_VIDEO, 'cid' => 10073, 'time' => 1336616566, 'uid' => 0, 'vid' => '724de8dfc48916a8d6d106da857b1797')),    
            json_encode(array('msgtype' => self::FEED_TYPE_CIRCLE_VIDEO, 'cid' => 10073, 'time' => 1336616566, 'uid' => 0, 'vid' => '241b4a43c8a037772cf3e31e0849f0c2')),
            json_encode(array('msgtype' => self::FEED_TYPE_CIRCLE_VIDEO, 'cid' => 10073, 'time' => 1336616566, 'uid' => 0, 'vid' => '92b25c840b6b59cacf51438b51f1b9b8')),
            json_encode(array('msgtype' => self::FEED_TYPE_CIRCLE_VIDEO, 'cid' => 10073, 'time' => 1336656566, 'uid' => 0, 'vid' => '6e1780d3fd248290f5c3bdd95d9ff780')),
            json_encode(array('msgtype' => self::FEED_TYPE_CIRCLE_VIDEO, 'cid' => 10073, 'time' => 1336616566, 'uid' => 0, 'vid' => '6e1780d3fd248290f5c3bdd95d9ff780')),                
            json_encode(array('msgtype' => self::FEED_TYPE_COMMENT_VIDEO, 'cid' => 10073, 'time' => 1336616566, 'uid' => 1798015323, 'vid' => '724de8dfc48916a8d6d106da857b1797', 'data' => '这真是一个绝无仅有的好视频')),
            json_encode(array('msgtype' => self::FEED_TYPE_COMMENT_VIDEO, 'cid' => 10627, 'time' => 1336636566, 'uid' => 1546961348, 'vid' => '241b4a43c8a037772cf3e31e0849f0c2', 'data' => '这想得到的噩噩噩噩')),                
            json_encode(array('msgtype' => self::FEED_TYPE_SHARE_VIDEO, 'cid' => 10073, 'time' => 1336616566, 'uid' => 1546961348, 'vid' => '241b4a43c8a037772cf3e31e0849f0c2')),
            json_encode(array('msgtype' => self::FEED_TYPE_SHARE_VIDEO, 'cid' => 0, 'time' => 1336616566, 'uid' => 1546961348, 'vid' => '92b25c840b6b59cacf51438b51f1b9b8')),
            json_encode(array('msgtype' => self::FEED_TYPE_SHARE_VIDEO, 'cid' => 10627, 'time' => 1336616566, 'uid' => 1546961348, 'vid' => '6e1780d3fd248290f5c3bdd95d9ff780')),                
            json_encode(array('msgtype' => self::FEED_TYPE_SHARE_CIRCLE, 'cid' => 10073, 'time' => 1336616566, 'uid' => 1546961348)),
            json_encode(array('msgtype' => self::FEED_TYPE_SHARE_CIRCLE, 'cid' => 10025, 'time' => 1336616566, 'uid' => 1546961348)),
            json_encode(array('msgtype' => self::FEED_TYPE_SHARE_CIRCLE, 'cid' => 10627, 'time' => 1336616566, 'uid' => 1546961348)),                
            json_encode(array('msgtype' => self::FEED_TYPE_MOOD_VIDEO, 'cid' => 10073, 'time' => 1336616566, 'uid' => 1546961348, 'vid' => '724de8dfc48916a8d6d106da857b1797', 'data' => 'dx')),
            json_encode(array('msgtype' => self::FEED_TYPE_MOOD_VIDEO, 'cid' => 10071, 'time' => 1336616566, 'uid' => 1798015323, 'vid' => '241b4a43c8a037772cf3e31e0849f0c2', 'data' => 'fn')),
            json_encode(array('msgtype' => self::FEED_TYPE_MOOD_VIDEO, 'cid' => 10073, 'time' => 1336616566, 'uid' => 1798015323, 'vid' => '92b25c840b6b59cacf51438b51f1b9b8', 'data' => 'wg')),
            json_encode(array('msgtype' => self::FEED_TYPE_MOOD_VIDEO, 'cid' => 10066, 'time' => 1336656566, 'uid' => 1182841008, 'vid' => '6e1780d3fd248290f5c3bdd95d9ff780', 'data' => 'jn')),
                                
                
                );
        
        return array('nRetCode' => 0, 'feedlists' => $arrData);
    } */

    /**
     * 获取一个圈子的动态汇总
     *
     * @param array $arrParam array(circle_id => 圈子id, user_id => 用户id, offset => 分页偏移量,
     *      count => 每页条数, lasttime => 选择该时间点之前的feed，0表示不限制,
     *      msgtype => 选择哪些类型的feed，不传表示所有,)
     * @return array
     */
    public function getCircleFeedList($arrParam) {
    	$arrRet = array();
    	$objLogicUser = new Model_Logic_User();
    	
    	$arrCircleId = array((int) $arrParam['circle_id']);
    	$arrFollowingId = array();
    	if (! isset($arrParam['msgtype'])) {
    		$intMsgType = self::CIRCLE_FEED_TYPE_ALL;
    	} else {
    		$intMsgType = $arrParam['msgtype'];
    	}
    	
    	$arrRpcParam = array('balance_key' => $arrParam['circle_id'], $arrCircleId, $arrFollowingId, (int) $arrParam['offset'], (int) ($arrParam['count']),
    			$arrParam['lasttime'], $intMsgType, JKit::$log->getLogId());
    	include_once __DIR__ . '/../thrift/feed/feed_service.php';
    	try {
    		//get_feeds($circleLst, $userLst, $nOffset, $nGetFeedNm, $nLastTime, $filterFlag, $rid);
    		$mixedRes = RPC::call('feed_query', array('feed_feed_serviceClient', 'get_feeds'), $arrRpcParam, 2);
    		$mixedRes = get_object_vars($mixedRes);//print_r($arrRpcParam);print_r($mixedRes);
    	} catch (Exception $e) {
    		JKit::$log->warn($e->getMessage(), $arrParam, __FILE__, __LINE__);
    		return $arrRet;
    	}
		if(empty($mixedRes) || $mixedRes['nRetCode'] < 0) {
			return $arrRet;
		}
    	$bolHasMore = false;
    	if ($mixedRes['nRetCode'] == self::RET_CODE_HAS_MORE) {
    		$bolHasMore = true;
		}
    	$arrMsgId2ContentMap = (array) $mixedRes['feed_content_map'];
    	foreach ($mixedRes['feed_id_lst'] as $intMsgId) {
    		if (isset($arrMsgId2ContentMap[$intMsgId])) {
				$objTmp = $arrMsgId2ContentMap[$intMsgId];
				$arrTmp = json_decode($objTmp->content, true);
				$arrTmp['_id'] = $intMsgId;
				$arrTmp['nRepostCount'] = (int) $objTmp->nRepostCount;
				$arrRet[] = $arrTmp;
			}
    	}
    	unset($mixedRes);
    	
    	$arrRet = $this->_aggregateUserFeed($arrRet, $arrParam, $arrMsgId2ContentMap);
    	$arrRet['has_more'] = $bolHasMore;
    	if (isset($arrRet['dict']['circle']) && isset($arrParam['user_id']) && $arrParam['user_id'] > 0) {
	    	//把该用户已经关注的圈子做上标记
    		$arrFocusCircleId = (array) $objLogicUser->getUserCirclesByUid($arrParam['user_id'], true);
	    	foreach ($arrFocusCircleId as $v) {
		    	if (isset($arrRet['dict']['circle'][$v])) {
		    		$arrRet['dict']['circle'][$v]['is_focus'] = 1;
		    	}
	    	}
    	}
    	
    	return $arrRet;    	
    }
    
    /**
     * 获取一个用户的新动态数目
     *
     * @param array $arrParam array(user_id => 用户id, lasttime => 选择该时间点之后的feed，0表示不限制, msgtype => 选择哪些类型的feed，不传表示所有)
     * @return int
     */
    public function getNewUserFeedNum($arrParam) {
        //new_feeds_nm($circleLst, $userLst, $nTime, $filterFlag, $rid)
        include_once __DIR__ . '/../thrift/feed/feed_service.php';
        $objLogicUser = new Model_Logic_User();
        $arrCircleId = (array) $objLogicUser->getUserCirclesByUid($arrParam['user_id'], true);
        $arrFollowing = (array) $objLogicUser->followings($arrParam['user_id'], 0, 250);
        $arrFollowingId = array();
        foreach ($arrFollowing as $v) {
            $arrFollowingId[] = $v['following'];
        }
        if (! isset($arrParam['msgtype'])) {
            $intMsgType = self::USER_FEED_TYPE_ALL;
        } else {
            $intMsgType = $arrParam['msgtype'];
        }        
        $arrRpcParam = array('balance_key' => $arrParam['user_id'], $arrCircleId, $arrFollowingId, $arrParam['lasttime'], $intMsgType, JKit::$log->getLogId());
        try {
            $mixedRes = RPC::call('feed_query', array('feed_feed_serviceClient', 'new_feeds_nm'), $arrRpcParam);
        } catch (Exception $e) {
            JKit::$log->warn($e->getMessage(), $arrParam, __FILE__, __LINE__);
            return 0;
        }
        
        return (int) $mixedRes;
    } 

    /**
     * 获取一个圈子的新动态数目
     *
     * @param array $arrParam array(circle_id => 圈子id, lasttime => 选择该时间点之后的feed，0表示不限制, msgtype => 选择哪些类型的feed，不传表示所有)
     * @return int
     */
    public function getNewCircleFeedNum($arrParam) {
    	//new_feeds_nm($circleLst, $userLst, $nTime, $filterFlag, $rid)
    	include_once __DIR__ . '/../thrift/feed/feed_service.php';
    	$arrCircleId = array((int) $arrParam['circle_id']);
    	if (! isset($arrParam['msgtype'])) {
    		$intMsgType = self::CIRCLE_FEED_TYPE_ALL;
    	} else {
    		$intMsgType = $arrParam['msgtype'];
    	}
    	$arrRpcParam = array('balance_key' => $arrParam['circle_id'], $arrCircleId, array(), $arrParam['lasttime'], $intMsgType, JKit::$log->getLogId());
    	try {
    		$mixedRes = RPC::call('feed_query', array('feed_feed_serviceClient', 'new_feeds_nm'), $arrRpcParam);
    	} catch (Exception $e) {
    		JKit::$log->warn($e->getMessage(), $arrParam, __FILE__, __LINE__);
    		$mixedRes = 0;
    	}
    
    	return (int) $mixedRes;
    }    
    
    protected function _addFeed($arrParam) {
        //submit_msg($source, $msgtype, $msgbody, $requestid);
        include_once __DIR__ . '/../thrift/feed/FifoService.php';
        if (! isset($arrParam['time'])) {
            $arrParam['time'] = time();
        }
        $arrRpcParam = array('balance_key' => $arrParam['uid'], self::FEED_SOURCE, $arrParam['msgtype'], json_encode($arrParam), JKit::$log->getLogId());
        try {
            $mixedRes = RPC::call('feed_submit', array('sub_svr_FifoServiceClient', 'submit_msg'), $arrRpcParam, 2);
            JKit::$log->debug(__CLASS__ . '::' . __FUNCTION__ . '(add feed success)', $arrParam, __FILE__, __LINE__);
        } catch (Exception $e) {
            JKit::$log->warn($e->getMessage(), $arrParam, $e->getFile(), $e->getLine());
            return false;
        }
        if(empty($mixedRes) || $mixedRes < 0) {
            JKit::$log->warn('add feed fail', $arrParam, __FILE__, __LINE__);
            return false;
        }
        
        return $mixedRes;        
    }
    
    public function getFeedById($mixedId, $intBalanceKey = null) {
        $bolSingle = false;
        if (! is_array($mixedId)) {
            $bolSingle = true;
            $mixedId = array($mixedId);
        }
        //map<i64,FeedInfo> get_feeds_info(1:list<i64> msgidLst)
        include_once __DIR__ . '/../thrift/feed/feed_service.php';
        $arrRpcParam = array($mixedId);
        if ($intBalanceKey !== null) {
            $arrRpcParam['balance_key'] = $intBalanceKey;
        }
        try {
            $mixedRes = RPC::call('feed_query', array('feed_feed_serviceClient', 'get_feeds_info'), $arrRpcParam);
        } catch (Exception $e) {
            JKit::$log->warn($e->getMessage(), $mixedId, __FILE__, __LINE__);
            return false;
        }
        if (! is_array($mixedRes) || empty($mixedRes)) {
            return false;
        }
        if ($bolSingle) {
            return array_shift($mixedRes);
        } else {
            return $mixedRes;
        }        
    }

    /**
     * 添加视频评论动态
     * @param int $intUid
     * @param int $intVid
     * @param int $intCircleId
     * @param string $strContent
     */
    public function addFeedCommentVideo($arrSender, $strVid, $intCircleId, $strContent, $arrUserNick = null) {
        $intUid = $arrSender['_id'];
        if ($intUid < 1 || empty($strVid)) {
            return false;
        }
        $arrParam = array(
            'msgtype' => self::FEED_TYPE_COMMENT_VIDEO,
            'uid' => (int) $intUid,
            'cid' => (int) $intCircleId,
            'vid' => $strVid,
            'data' => $strContent,
        );
        if (! empty($arrUserNick)) {
            $arrParam['users'] = $arrUserNick;
        }
        
        $intMsgId = $this->_addFeed($arrParam);
        if ($intMsgId > 0) {
            //发送@消息提示
            unset($arrUserNick[$intUid]); //不允许@自己       
            if (! empty($arrUserNick)) {
                $arrMsgContent = array('msgtype' => Model_Logic_Msg::MENTION_TYPE_FEED, 'msgid' => $intMsgId);
                $objMsg = new Model_Logic_Msg();
                $objMsg->sendMsg($arrSender['_id'], $arrSender['nick'], $arrUserNick, Model_Logic_Msg::T_MENTION, $arrMsgContent);
            }
        }
        
        return $intMsgId;
    }
   
    public function addFeedShareVideo($intUid, $strVid, $intCircleId) {
        if ($intUid < 1 || empty($strVid)) {
            return false;
        }
        $arrParam = array(
            'msgtype' => self::FEED_TYPE_SHARE_VIDEO,
            'uid' => (int) $intUid,
            'cid' => (int) $intCircleId,
            'vid' => $strVid,
        );
        return $this->_addFeed($arrParam);
    }
    
    public function addFeedShareCircle($intUid, $intCircleId) {
        if ($intUid < 1 || $intCircleId < 1) {
            return false;
        }
        $arrParam = array(
            'msgtype' => self::FEED_TYPE_SHARE_CIRCLE,
            'uid' => (int) $intUid,
            'cid' => (int) $intCircleId,
        );
        return $this->_addFeed($arrParam);        
    }
    
    public function addFeedMoodVideo($intUid, $strVid, $intCircleId, $strMood) {
        if ($intUid < 1 || empty($strVid)) {
            return false;
        }
        $arrParam = array(
            'msgtype' => self::FEED_TYPE_MOOD_VIDEO,
            'uid' => (int) $intUid,
            'cid' => (int) $intCircleId,
            'vid' => $strVid,
            'data' => $strMood
        );
        return $this->_addFeed($arrParam);
    }
    
    public function addFeedForward($arrSender, $intOrigRefId, $intRepostRefId, $strContent, $arrExtra = null) {
        $intUid = $arrSender['_id'];
        if ($intUid < 1 || $intOrigRefId < 1 || $intRepostRefId < 1) {
            return false;
        }       
        $objDataUser = new Model_Data_User();
        $arrUserNick = self::findUserFromText($strContent, $objDataUser);
        
        $arrParam = array(
                'msgtype' => self::FEED_TYPE_FORWARD_FEED,
                'uid' => (int) $intUid,
                'ref' => (string) $intOrigRefId, //最原始的feed id
                'repost_ref' => (string) $intRepostRefId, //直接的feed id
                'data' => $strContent,
        );
        if ($arrExtra && ! empty($arrExtra['orig_feed_data'])) {
            /*最原始的feed有可能是PHP聚合之后显示的，这时候必须有额外的数据，才能够完成该feed的显示
             *如圈子视频更新，页面上显示的是一行3个视频，但是底层是一个视频一条feed，这时需要把这一行
             *3个视频的vid记录下来，下次才能重现，很trick的做法。
             */
            $arrParam['orig_feed_data'] = $arrExtra['orig_feed_data'];
        }
        if (! empty($arrUserNick)) {
            $arrParam['users'] = $arrUserNick;
        }
               
        $intMsgId = $this->_addFeed($arrParam);
        if ($intMsgId > 0) {
            //发送@消息提示
            unset($arrUserNick[$intUid]); //不允许@自己
            //加上原始feed的作者
            $objRootFeed = $this->getFeedById($intOrigRefId);
            if ($objRootFeed && ($arrRootFeedInfo = json_decode($objRootFeed->content, true))) {
                if (isset($arrRootFeedInfo['uid']) && $arrRootFeedInfo['uid'] > 0) {
                    $arrRootFeedOwner = $objDataUser->get($arrRootFeedInfo['uid'], array('_id', 'nick'));
                    if ($arrRootFeedOwner && isset($arrRootFeedOwner['_id'])) {
                        $arrUserNick[$arrRootFeedOwner['_id']] = $arrRootFeedOwner['nick'];
                    }
                }
            }
            
            if (! empty($arrUserNick)) {
                $arrMsgContent = array('msgtype' => Model_Logic_Msg::MENTION_TYPE_FEED, 'msgid' => $intMsgId);
                $objMsg = new Model_Logic_Msg();
                $objMsg->sendMsg($arrSender['_id'], $arrSender['nick'], $arrUserNick, Model_Logic_Msg::T_MENTION, $arrMsgContent);
            }
            
            $objDataUserFeedStore = new Model_Data_UserFeedStore();
            $objDataUserFeedStore->add($intUid, self::FEED_TYPE_FORWARD_FEED, $intMsgId);
        }
        
        return $intMsgId;
    }
    
    public function addFeedQuanVideo($intUid, $strVid, $intCircleId) {
    	if ($intUid < 1 || empty($strVid) || $intCircleId < 1) {
    		return false;
    	}
    	$arrParam = array(
    			'msgtype' => self::FEED_TYPE_QUAN_VIDEO,
    			'uid' => (int) $intUid,
    			'cid' => (int) $intCircleId,
    			'vid' => $strVid,
    	);
    	return $this->_addFeed($arrParam);
    }

    /**
     * 返回文本串中的@用户
     * 
     * @param string $strTxt
     * @return array(用户id => 昵称)
     */
    public static function findUserFromText($strTxt, $objDataUser = null) {
        if ($objDataUser === null) {
            $objDataUser = new Model_Data_User();
        }
        $arrUserNick = null;
        preg_match_all('/@([^[:space:]\\/@:：]+)/u', $strTxt, $arrUserNick); //用UTF-8模式支持中文冒号
        if ($arrUserNick && isset($arrUserNick[1]) && ! empty($arrUserNick[1])) {
            $arrUserNick = $arrUserNick[1];
            $arrTmp = $objDataUser->getMultiByNick($arrUserNick, array('_id', 'nick'));
            $arrUserNick = null;
            if (! empty($arrTmp)) {
                foreach ($arrTmp as $v) {
                    $arrUserNick[$v['_id']] = $v['nick'];
                }
            }
        } else {
            $arrUserNick = null;
        }  

        return $arrUserNick;
    }
}