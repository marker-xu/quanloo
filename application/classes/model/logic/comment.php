<?php
class Model_Logic_Comment extends Model {
    /**
     * 站外评论对应的用户头像，每个网站一个
     * youku, sohu, tudou, pptv, xunlei, joy, sina
     */
    public static $arrSiteAvatar = array(
        'youku.com' => array('avatar' => 'youku', 'nick' => '优酷用户'),
        'sohu.com' => array('avatar' => 'sohu', 'nick' => '搜狐用户'),
        'tudou.com' => array('avatar' => 'tudou', 'nick' => '土豆用户'),
        'pptv.com' => array('avatar' => 'pp', 'nick' => 'PPTV用户'),
        'xunlei.com' => array('avatar' => 'kankan', 'nick' => '迅雷用户'),
        'joy.cn' => array('avatar' => 'joy', 'nick' => '激动网用户'),
        'sina.com.cn' => array('avatar' => 'sina', 'nick' => '新浪用户'),
        '56.com' => array('avatar' => '56', 'nick' => '56网友'),
        'tianya.cn' => array('avatar' => 'tianya', 'nick' => '天涯网友'),
        'mop.com' => array('avatar' => 'maopu', 'nick' => '猫扑网友'),
    );   
    
    protected $_objDataComment;
    
    public function __construct() {
        $this->_objDataComment = new Model_Data_Comment();
    }
    
    /**
     * 添加视频评论
     *
     * @param array $arrParams array(
     *  user_id => 用户ID，站外评论的用户ID为0
     *  video_id => 视频ID
     * 	data => 评论内容
     *  create_time => 创建时间，不填默认当前时间
     *  users => 评论内容中的@用户的列表，id为key，昵称为value
     *  //站外评论的特有字段
     *  nick => 站外评论的昵称
     *  site => 站外评论的来源站点
     * )
     */
    public function add($arrParams) {
        if ($arrParams['user_id']) {
            $arrTmp = array(array('_id' => (int) $arrParams['user_id']));
            $objTmp = new Model_Logic_Stat();
            $objTmp->complementUserStatInfo($arrTmp);
            $strMood = '';
            if (isset($arrTmp[0]['mood_count'])) {
                $intTmp = 0;
                foreach ($arrTmp[0]['mood_count'] as $k => $v) {
                    if ($k == 'total') {
                        continue;
                    }
                    if ($v > $intTmp) {
                        $intTmp = $v;
                        $strMood = $k;
                    }
                }
            }
            $arrParams['mood'] = $strMood;
        }
        if (! isset($arrParams['score'])) {
            $arrParams['score'] = $this->_getCommentScore($arrParams);
        }
        
        return $this->_objDataComment->add($arrParams);
    }
    
    public function remove($id) {
        return $this->_objDataComment->remove($id);
    }
    
    /**
     * 获取每个vid的前N个评论
     *
     * @param array $arrVid vid数组
     * @param int $intCount
     *
     * return array(
     *     vid => array(array(每条评论信息), array(), ...)
     * )
     */
    public function topN(array $arrVid, $intCount = NULL, $arrExtra = NULL) {
        $arrUserId = array();
	    Profiler::startMethodExec();
        $arrRes = $this->_objDataComment->topN($arrVid, $intCount, $arrUserId); 
	    Profiler::endMethodExec(__FUNCTION__.' topN');      
        if (! empty($arrRes)) {
            if (! empty($arrUserId)) {
                $objTmp = new Model_Logic_User();
	            Profiler::startMethodExec();
                $arrUserInfo = (array) $objTmp->getMulti($arrUserId);
	            Profiler::endMethodExec(__FUNCTION__.' getMulti');      
            } else {
                $arrUserInfo = array();
            }

            /**
             * 一次请求中，评论全部是外站评论的视频只能有一个，其他的视频需要把外站评论去掉
             */
            $bolHasOutSiteComment = false;
            foreach ($arrRes as $k => $v) {
            	if (empty($v)) {
            		continue;
            	}
            	foreach ($v as $v2) {
            		if ($v2['user_id'] > 0) {
            			continue 2; //含有站内评论要保留，继续检查下一个视频
            		}
            	}
            	if ($bolHasOutSiteComment) {
            		//已经有全部是站外评论的视频，那么这个视频的评论就全部扔掉
            		unset($arrRes[$k]);
            	} else {
            		$bolHasOutSiteComment = true;
            	}
            }
            
        	Profiler::startMethodExec();
            foreach ($arrRes as $k => $v) {
                if (! empty($v)) {
                    $arrRes[$k] = $this->_complementInfo($v, $arrExtra, $arrUserInfo);
                } 
            }
            Profiler::endMethodExec(__FUNCTION__." _complementInfo");
         }

         return $arrRes;
    }
    
    /**
     * 获取一个视频的评论
     *
     * @param string $strVid
     * @param int $intCount
     * @param int $intOffset
     * @return array(total => 评论总数, count => 当前返回的评论数, has_more => 是否还有下一页, data => array(array(_id, user_id, video_id, data, mood, create_time, nick, avatar, site), ...))
     */
    public function get($strVid, $intCount = NULL, $intOffset = NULL, $arrExtra = NULL) {
        $arrComment = $this->_objDataComment->get($strVid, $intCount, $intOffset);
        if (! empty($arrComment['data'])) {
            $arrComment['data'] = $this->_complementInfo($arrComment['data'], $arrExtra);
            $arrComment['count'] = count($arrComment['data']);
            if ($arrComment['total'] > ($intCount + $intOffset)) {
            	$arrComment['has_more'] = true;
            } else {
            	$arrComment['has_more'] = false;
            }
        }
        
        return $arrComment;
    }
    
    protected function _complementInfo($arrComment, $arrExtra = NULL, $arrUserInfo = NULL) {
        if (empty($arrComment)) {
            return array();
        }
        
        if (! is_array($arrUserInfo)) {
            $arrTmp = array();
            foreach ($arrComment as $v) {
                if ($v['user_id'] > 0) {
                    $arrTmp[] = $v['user_id'];
                }
            }
            if (! empty($arrTmp)) {
                $objTmp = new Model_Logic_User();
                $arrUserInfo = (array) $objTmp->getMulti($arrTmp);
            } else {
                $arrUserInfo = array();
            }
        }
        
        if (isset($arrExtra['avatarSize'])) {
            $intAvatarSize = $arrExtra['avatarSize'];
        } else {
            $intAvatarSize = 30;
        }
        foreach ($arrComment as &$v2) {
            $v2['_id'] = (string) $v2['_id'];
            if ($v2['user_id'] > 0) {
                $intUserId = $v2['user_id'];
                if (isset($arrUserInfo[$intUserId])) {
                    $arrTmp = $arrUserInfo[$intUserId];
                    $v2['nick'] = $arrTmp['nick'];
                    $strAvatar = isset($arrTmp['avatar'][$intAvatarSize]) ? $arrTmp['avatar'][$intAvatarSize] : '';
                    $v2['avatar'] = Util::userAvatarUrl($strAvatar, $intAvatarSize);
                } else {
                    $v2['nick'] = $intUserId;
                    $v2['avatar'] = Util::userAvatarUrl('', $intAvatarSize);
                }
            } else {
                if (empty($v2['nick'])) {
                    $v2['nick'] = self::$arrSiteAvatar[$v2['site']]['nick'];
                }
                $v2['avatar'] = self::getSiteCommentAvatar($v2['site'], $intAvatarSize);
            }
            $v2['create_time_str'] = Util::time_from_now($v2['create_time']->sec, true);
        }
        unset($v2);

        return array_values($arrComment);
    }
    
    protected function _getCommentScore($arrParams) {
        require_once Kohana::find_file('vendor', 'thrift/Thrift');
        include_once __DIR__ . '/../thrift/comment/comment_score.php';
        $intScore = 0;
        $objTmp = new cmt_CommentInfo(array('comment' => $arrParams['data'], 'title' => '', 'tags' => array()));
        $arrRpcParam = array($objTmp);
        try {
            $intScore = (int) RPC::call('comment_score', array('cmt_comment_scoreClient', 'score'), $arrRpcParam);
            JKit::$log->debug(__CLASS__ . '::' . __FUNCTION__, $arrRpcParam, __FILE__, __LINE__);
        } catch (Exception $e) {
            JKit::$log->warn($e->getMessage(), $arrParam, $e->getFile(), $e->getLine());
        }
     
        return $intScore;
    }

    public static function getSiteCommentAvatar($strSite, $intSize) {
    	if($intSize>48) {
    		$intSize = 48;
    	}
        if (isset(self::$arrSiteAvatar[$strSite])) {
            $strTmp = self::$arrSiteAvatar[$strSite]['avatar'];
            return 'http://' . DOMAIN_STATIC . "/img/c/{$strTmp}_{$intSize}.png";
        } else {
            return Util::userAvatarUrl('', $intSize);
        }
    }
}
