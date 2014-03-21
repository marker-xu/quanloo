<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 视频动态、圈子动态、用户动态等页面逻辑
 * @author wangjiajun
 */
class Model_Logic_Feed extends Model
{    
    /**
     * 新增视频动态
     * @param string $videoId 视频ID
     * @param int $type
     * @param int $userId 发起操作的用户ID
     * @param int $circleId 视频所属圈子ID
     * @param mixed $data
     * @param array $users 评论中的@用户列表
	 * @param array $extra 其它额外参数
     * @return bool
     */
    public function addVideoFeed($videoId, $type, $userId = NULL, $circleId = NULL, 
        $data = NULL, $users = null, $extra = NULL)
    {
        $modelDataVideoFeed = new Model_Data_VideoFeed();
        $doc = array(
            'video_id' => (string) $videoId,
            'type' => (int) $type,
            'create_time' => new MongoDate(),
        );
        if (!is_null($circleId)) {
            $doc['circle_id'] = (int) $circleId;
        }
        if (!is_null($userId)) {
            $doc['user_id'] = (int) $userId;
        }
        if (!is_null($data)) {
            $doc['data'] = $data;
        }
        if ($users !== null) {
            $doc['users'] = $users;
        }
        if (!is_null($extra)) {
            $doc['extra'] = $extra;
        }
        $this->_logToFile('videofeed', $doc);
        if ($type != Model_Data_VideoFeed::TYPE_MOODED || $userId < 1) {
        	//现在只有登录用户的心情视频需要写到mongo表，用于播放页显示哪些用户心情了视频
        	return true;
        }

        return $modelDataVideoFeed->update(array('_id' => "{$type}_{$videoId}_{$userId}"), $doc);
    }
    
    /**
     * 新增圈子动态
     * @param int $circleId 圈子ID
     * @param int $type
     * @param int $userId 发起操作的用户ID
     * @param string $videoId 被操作的圈内视频ID
     * @param mixed $data
     * @param array $users 评论中的@用户列表
	 * @param array $extra 其它额外参数
     * @return bool
     */
    public function addCircleFeed($circleId, $type, $userId = NULL, $videoId = NULL, 
        $data = NULL, $users = null, $extra = NULL)
    {
        $modelDataCircleFeed = new Model_Data_CircleFeed();
        $doc = array(
            'circle_id' => (int) $circleId,
            'type' => (int) $type,
            'create_time' => new MongoDate(),
        );
        if (!is_null($videoId)) {
            $doc['video_id'] = (string) $videoId;
        }
        if (!is_null($userId)) {
            $doc['user_id'] = (int) $userId;
        }
        if (!is_null($data)) {
            $doc['data'] = $data;
        }
        if ($users !== null) {
            $doc['users'] = $users;
        }
        if (!is_null($extra)) {
            $doc['extra'] = $extra;
        }
        return $this->_logToFile('circlefeed', $doc);
    }
    
    /**
     * 圈子动态，按时间段、类型做聚合
     * @param int $circleId 圈子ID
     * @param int $beginTime 时间段开始，距离当前时间的秒数
     * @param int $endTime 时间段结束，距离当前时间的秒数，NULL表示不限
     * @param int $count 返回条数
     * @return array
     * Array (
		[0] => Array ( // 观看、分享、评论、心情视频动态
            [video_id] => 0b2baecddc8a6115162c423b212f8fc7 // 视频ID
            [type] => 6 // 动态类型
            [users] => Array ( // 发起动态的用户列表
                [0] => Array (
                    [user_id] => 794123477 // 用户ID
                    [data] => dx // 具体心情，只有心情类型的动态有该项
                    [user] => Array ( // 用户信息，具体字段参考用户表，如果用户未查到则为NULL
                        ...
                    )
                )
                [1] => Array ( // 为空表示游客
                )
                ...
            )
            [video] => Array ( // 视频信息，具体字段参考视频表，如果视频未查到则为NULL
                ...
            )
        )
        [1] => Array ( // 关注、分享、邀请圈子动态
            [circle_id] => 10046 // 圈子ID
            [type] => 4 // 动态类型
            [users] => Array ( // 发起动态的用户列表
                [0] => Array (
                        [user_id] => 1754575752 // 用户ID
                        [user] => Array ( // 用户信息，具体字段参考用户表
                            ...
                        )
                    )
                ...
            )
            [circle] => Array ( // 圈子信息，具体字段参考圈子表，如果圈子未查到则为NULL
                ...
            )
		)
		...
       )
     */
    public function circleFeeds($circleId, $beginTime = 0, $endTime = NULL, $count = 20)
    {
	    Profiler::startMethodExec();
        $modelDataCircleFeed = new Model_Data_CircleFeed();
        try {
            $coll = $modelDataCircleFeed->getCollection();
        } catch (Model_Data_Exception $e) {
            Kohana::$log->error($e);
            return array();
        }
        $query = array(
        	'circle_id' => (int) $circleId,
            'type' => array('$ne' => Model_Data_CircleFeed::TYPE_VIDEO_WATCHED)
        );
        if ($beginTime > 0) {
            $query['create_time'] = array('$lte' => new MongoDate(time() - $beginTime));
        }
        if (!is_null($endTime) && $endTime > 0) {
            $query['create_time'] = array('$gt' => new MongoDate(time() - $endTime));
        }
        $cursor = $coll->find($query)->sort(array('create_time' => -1));
        $feeds = array();
        $i = 0;
        $j = -1;
        $userIds = array();
        $videoIds = array();
        $circleIds = array();
        foreach ($cursor as $doc) {
            if (in_array($doc['type'], array(Model_Data_CircleFeed::TYPE_VIDEO_WATCHED, 
                Model_Data_CircleFeed::TYPE_VIDEO_SHARED, Model_Data_CircleFeed::TYPE_VIDEO_MOODED, 
                Model_Data_CircleFeed::TYPE_VIDEO_COMMENTED))) {
                $key = $doc['video_id'].'_'.$doc['type'];
                if (!isset($feeds[$key])) {
                    $feeds[$key] = array(
                        'video_id' => $doc['video_id'],
                        'type' => $doc['type'],
                        'users' => array()
                    );
                    $videoIds[] = $doc['video_id'];
                }
                if (count($feeds[$key]['users']) >= 10) {
                    continue;
                }
                if (!isset($doc['user_id'])) {
                    $feeds[$key]['users'][$j--] = array();
                } else {
                    if (!isset($feeds[$key]['users'][$doc['user_id']])) {
                        $feeds[$key]['users'][$doc['user_id']] = array(
                            'user_id' => $doc['user_id']
                        );
                        if (isset($doc['data'])) {
                            $feeds[$key]['users'][$doc['user_id']]['data'] = $doc['data'];
                        }
                        $userIds[] = $doc['user_id'];
                    }
                }
            } else if (in_array($doc['type'], array(Model_Data_CircleFeed::TYPE_SUBSCRIBED, 
                Model_Data_CircleFeed::TYPE_SHARED, Model_Data_CircleFeed::TYPE_INVITED))) {
                $key = $doc['circle_id'].'_'.$doc['type'];
                if (!isset($feeds[$key])) {
                    $feeds[$key] = array(
                        'circle_id' => $doc['circle_id'],
                        'type' => $doc['type'],
                        'users' => array()
                    );
                    $circleIds[] = $doc['circle_id'];
                }
                if (count($feeds[$key]['users']) >= 10) {
                    continue;
                }
                if (!isset($doc['user_id'])) {
                    $feeds[$key]['users'][$j--] = array();
                } else {
                    if (!isset($feeds[$key]['users'][$doc['user_id']])) {
                        $feeds[$key]['users'][$doc['user_id']] = array(
                            'user_id' => $doc['user_id']
                        );
                        $userIds[] = $doc['user_id'];
                    }
                }
            }
            if (count($feeds) >= $count) {
                break;
            }
            // 总动态数限制
            if (++$i >= $count * 100) {
                break;
            }
        }
        $userIds = array_unique($userIds);
        $videoIds = array_unique($videoIds);
        $circleIds = array_unique($circleIds);
		Profiler::endMethodExec(__FUNCTION__.' aggregate feeds');
        
	    Profiler::startMethodExec();
        $modelDataUser = new Model_Data_User();
        $users = $modelDataUser->getMulti($userIds, Model_Logic_User::$basicFields);
        $modelLogicVideo = new Model_Logic_Video();
        $videos = $modelLogicVideo->getMulti($videoIds, false, Model_Logic_Video::$basicFields, 
            false);
        $modelDataCircle = new Model_Data_Circle();
        $circles = $modelDataCircle->getMulti($circleIds, Model_Logic_Circle::$basicFields);
        foreach ($feeds as &$feed) {
            if (isset($feed['video_id'])) {
                $feed['video'] = isset($videos[$feed['video_id']]) ? $videos[$feed['video_id']] : null;
            }
            if (isset($feed['circle_id'])) {
                $feed['circle'] = isset($circles[$feed['circle_id']]) ? $circles[$feed['circle_id']] : null;
            }
            foreach ($feed['users'] as &$user) {
                if (isset($user['user_id']) && isset($users[$user['user_id']])) {
                    $user['user'] = isset($users[$user['user_id']]) ? $users[$user['user_id']] : null;
                }
            }
            unset($user);
            $feed['users'] = array_values($feed['users']);
        }
        unset($feed);
		$feeds = array_values($feeds);
		Profiler::endMethodExec(__FUNCTION__.' complement info');
        
        return $feeds;
    }
    
    /**
     * 某个圈子的新动态
     * @param int $circleId
     * @param int $tm
     * @param int $count 最多返回多少条
     * @return array
     */
    public function circleNewFeeds($circleId, $tm, $count = 10)
    {
        $modelDataCircleFeed = new Model_Data_CircleFeed();
        $feeds = $modelDataCircleFeed->find(array(
        	'circle_id' => $circleId,
            'create_time' => array('$gt' => new MongoDate($tm))
        ), array(), array('create_time' => -1), $count);
            
        $this->_complementInfo($feeds);
        
        return $feeds;
    }
    
    /**
     * 某个圈子的最新动态
     * @param int $circleId
     * @param int $count
     * @return array
     */
    public function circleNewestFeeds($circleId, $count = 10)
    {
        $modelDataCircleFeed = new Model_Data_CircleFeed();
        $coll = $modelDataCircleFeed->getCollection();
        $cursor = $coll->find(array(
        	'circle_id' => $circleId
        ))->sort(array('create_time' => -1));
        $feeds = array();
        $userCounts = array();
        $videoCounts = array();
        $userVideoTypeCounts = array();
        $userCircleTypeCounts = array();
        $i = 0;
        foreach ($cursor as $doc) {
            if (isset($doc['user_id'])) {
                if (!isset($userCounts[$doc['user_id']])) {
                    $userCounts[$doc['user_id']] = 0;
                }
                if ($userCounts[$doc['user_id']] >= 3) {
                    continue;
                }
            }
            if (isset($doc['video_id'])) {
                if (!isset($videoCounts[$doc['video_id']])) {
                    $videoCounts[$doc['video_id']] = 0;
                }
                if ($videoCounts[$doc['video_id']] >= 3) {
                    continue;
                }
            }
            if (isset($doc['user_id']) && isset($doc['video_id'])) {
                $key = $doc['user_id'].'_'.$doc['video_id'].'_'.$doc['type'];
                if (!isset($userVideoTypeCounts[$key])) {
                    $userVideoTypeCounts[$key] = 0;
                }
                if ($userVideoTypeCounts[$key] >= 1) {
                    continue;
                }
            }
            if (isset($doc['user_id']) && isset($doc['circle_id'])) {
                $key = $doc['user_id'].'_'.$doc['circle_id'].'_'.$doc['type'];
                if (!isset($userCircleTypeCounts[$key])) {
                    $userCircleTypeCounts[$key] = 0;
                }
                if ($userCircleTypeCounts[$key] >= 1) {
                    continue;
                }
            }
            $feeds[] = $doc;
            if (isset($doc['user_id'])) {
                $userCounts[$doc['user_id']]++;
            }
            if (isset($doc['video_id'])) {
                $videoCounts[$doc['video_id']]++;
            }
            if (isset($doc['user_id']) && isset($doc['video_id'])) {
                $key = $doc['user_id'].'_'.$doc['video_id'].'_'.$doc['type'];
                $userVideoTypeCounts[$key]++;
            }
            if (isset($doc['user_id']) && isset($doc['circle_id'])) {
                $key = $doc['user_id'].'_'.$doc['circle_id'].'_'.$doc['type'];
                $userCircleTypeCounts[$key]++;
            }
            if (count($feeds) >= $count) {
                break;
            }
            // 防止过多循环
            if (++$i >= $count * 10) {
                break;
            }
        }
        
	    Profiler::startMethodExec();
        $this->_complementInfo($feeds);
		Profiler::endMethodExec(__FUNCTION__.' _complementInfo');
        
        return $feeds;
    }
    
    /**
     * 新增用户动态
     * @param int $userId 用户ID
     * @param int $type
     * @param string $videoId 被操作的视频ID
     * @param int $circleId 被操作的圈子ID或被操作的视频所属圈子ID
     * @param mixed $data
     * @param array $users 评论中的@用户列表
	 * @param array $extra 其它额外参数
     * @return bool
     */
    public function addUserFeed($userId, $type, $videoId = NULL, $circleId = NULL, 
        $data = NULL, $users = null, $extra = NULL)
    {
        $modelDataUserFeed = new Model_Data_UserFeed();
        $doc = array(
            'user_id' => (int) $userId,
            'type' => (int) $type,
            'create_time' => new MongoDate(),
        );
        if (!is_null($circleId)) {
            $doc['circle_id'] = (int) $circleId;
        }
        if (!is_null($videoId)) {
            $doc['video_id'] = (string) $videoId;
        }
        if (!is_null($data)) {
            $doc['data'] = $data;
        }
        if ($users !== null) {
            $doc['users'] = $users;
        }
        if (!is_null($extra)) {
            $doc['extra'] = $extra;
        }
        return $this->_logToFile('userfeed', $doc);
    }
    
    /**
     * 用户动态是否已产生过
     * @param int $userId
     * @param int $type
     * @param string $videoId
     * @param int $circleId
     * @param mixed $data
     * @return bool
     */
/*     public function isUserFeed($userId, $type, $videoId = NULL, $circleId = NULL, 
        $data = NULL)
    {
        $modelDataUserFeed = new Model_Data_UserFeed();
        $query = array(
            'user_id' => (int) $userId,
            'type' => (int) $type
        );
        if (!is_null($videoId)) {
            $query['video_id'] = $videoId;
        }
        if (!is_null($circleId)) {
            $query['circle_id'] = (int) $circleId;
        }
        if (!is_null($data)) {
            $query['data'] = $data;
        }
        return (bool) $modelDataUserFeed->findOne($query);
    } */
    
    /**
     * 某个用户的动态
     * @param int $userId
     * @param int $offset
     * @param int $count
     * @return array
     */
    public function userFeeds($userId, $offset = 0, $count = 10)
    {
        $modelDataUserFeed = new Model_Data_UserFeed();
        $feeds = $modelDataUserFeed->find(array('user_id' => $userId), array(), 
            array('create_time' => -1), $count, $offset);
            
        $this->_complementInfo($feeds);
        
        return $feeds;
    }
    
    private function _complementInfo(&$feeds)
    {
        $modelDataUser = new Model_Data_User();
        $userIds = Arr::pluck($feeds, 'user_id');
        $userIds = array_filter($userIds, function ($value) {
            return $value > 0;
        });
        if ($userIds) {
	        Profiler::startMethodExec();
            $users = $modelDataUser->getMulti($userIds, Model_Logic_User::$basicFields);
		    Profiler::endMethodExec(__FUNCTION__.' user getMulti');
        } else {
            $users = array();
        }
        $modelDataCircle = new Model_Data_Circle();
        $circleIds = Arr::pluck($feeds, 'circle_id');
        $circleIds = array_filter($circleIds, function ($value) {
            return $value > 0;
        });
        if ($circleIds) {
	        Profiler::startMethodExec();
            $circles = $modelDataCircle->getMulti($circleIds, Model_Logic_Circle::$basicFields);
		    Profiler::endMethodExec(__FUNCTION__.' circle getMulti');
        } else {
            $circles = array();
        }
        $modelDataVideo = new Model_Data_Video();
        $videoIds = Arr::pluck($feeds, 'video_id');
        $videoIds = array_filter($videoIds, function ($value) {
            return strlen($value) == 32;
        });
        if ($videoIds) {
	        Profiler::startMethodExec();
            $videos = $modelDataVideo->getMulti($videoIds, Model_Logic_Video::$basicFields);
		    Profiler::endMethodExec(__FUNCTION__.' video getMulti');
        } else {
            $videos = array();
        }
        
        foreach ($feeds as &$feed) {
            if (isset($feed['user_id']) && isset($users[$feed['user_id']])) {
                $feed['user'] = $users[$feed['user_id']];
            }
            if (isset($feed['circle_id']) && isset($circles[$feed['circle_id']])) {
                $feed['circle'] = $circles[$feed['circle_id']];
            }
            if (isset($feed['video_id']) && isset($videos[$feed['video_id']])) {
                $feed['video'] = $videos[$feed['video_id']];
            }
        }
        unset($feed);
    }
    
    private function _logToFile($type, $doc)
    {
        $dir = APPPATH.'../../../data/log/stat/'.date('Ymd').'/'.$type.'/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, TRUE);
        }
        $filename = $type.'-'.date('YmdH').'-'.$_SERVER["SERVER_ADDR"];
        $doc['create_time'] = date('Y-m-d H:i:s');
        $data = json_encode($doc)."\n";
        return file_put_contents($dir.$filename, $data, FILE_APPEND | LOCK_EX);
    }
}