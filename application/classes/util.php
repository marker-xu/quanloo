<?php

/**
 * 不好归类的一些助手函数
 * @author wangjiajun
 */
class Util
{
    /**
     * 视频缩略图URL
     * @param string $fdfsPath FastDFS存储路径，比如group1/M00/57/07/CpwYW076qsShLkshAAADL1scZ4o818.php
     * @return string URL
     */
    public static function videoThumbnailUrl($fdfsPath)
    {
        $pos = strpos($fdfsPath, '/');
        $group = substr($fdfsPath, 0, $pos);
        $path = substr($fdfsPath, $pos);
        if ($group == 'group1') {
            /**
             * 取group1/M00/D9/34/CpwYFE92bJb2PUd4AACN1UZ4v0I317.jpg中的第16个字符4的ASCII值，注意与
             * JS版js/video/video_util.js:videoThumbnailUrl保持一致
             */
            $intRnd = (ord($fdfsPath[15]) % DOMAIN_IMAGE_THUMBNAIL_NUM) + 1;
            $strHost = sprintf(DOMAIN_IMAGE_THUMBNAIL_FMT, $intRnd);
            return 'http://'.$strHost.$path;
        } else {
            return 'http://'.DOMAIN_STATIC.'/img/vp120.jpg';
        }
    }
    
    /**
     * 用户头像URL
     * @param string $fdfsPath FastDFS存储路径，比如group1/M00/57/07/CpwYW076qsShLkshAAADL1scZ4o818.php
     * @return string URL
     */
    public static function userAvatarUrl($fdfsPath, $size = 30)
    {
        $pos = strpos($fdfsPath, '/');
        $group = substr($fdfsPath, 0, $pos);
        $path = substr($fdfsPath, $pos);
        if ($group == 'group1') {
            return 'http://'.DOMAIN_IMAGE_USER_AVATAR.$path;
        } else {
            return 'http://'.DOMAIN_STATIC.'/img/head'.$size.'.jpg';
        }
    }
    
    /**
     * Web存储集群文件URL
     * @param string $fdfsPath FastDFS存储路径，比如group1/M00/57/07/CpwYW076qsShLkshAAADL1scZ4o818.php
     * @return string URL
     */
    public static function webStorageClusterFileUrl($fdfsPath)
    {
        $pos = strpos($fdfsPath, '/');
        $group = substr($fdfsPath, 0, $pos);
        $path = substr($fdfsPath, $pos);
        if ($group == 'group1') {
            return 'http://'.DOMAIN_WEB_STORAGE_CLUSTER.$path;
        } else {
            return '';
        }
    }
    
    /**
     * 视频播放页地址
     * @param string $id 视频ID
     * @param string $playlist 播放列表名称，默认为null，显示相关视频
     * @param array $data 相关数据，如果某个key的值为null，则该key不包含在url中
     * array(
     * 	'circle' => ..., 圈子ID
     * 	'user' => ..., 用户ID
     * 	'offset' => ..., 当前页起始位置
     * 	'count' => ..., 当前页条数
     * 	...
     * )
     * @return string
     */
    public static function videoPlayUrl($id, $playlist = null, $data = array())
    {
        $url = 'http://' . DOMAIN_SITE . '/v/' . urldecode($id) . '.html';
        if ($playlist) {
            $data['playlist'] = $playlist;
        }
        foreach ($data as $key => $value) {
            if ($value === null) {
                unset($data[$key]);
            }
        }
        if (! empty($data)) {
            $url .= '?' . http_build_query($data);
        }
        return $url;
    }
    
    /**
     * 圈子页地址
     * @param int $id 圈子ID
     * @param array $arrParam 其它Query参数
     * @param array $circle 圈子信息
     * @return string
     */
    public static function circleUrl($id, $arrParam = null, $circle = null)
    {
        $strTmp = '';
        if ((!isset($circle['official']) || $circle['official'] == 0) 
            && $circle['creator'] > 0) {
            $strTmp .= "/user/{$circle['creator']}";
        }
        $strTmp .= "/circle/$id";
        if (isset($arrParam['tag'])) {
            if ($arrParam['tag'] != '') {
                $strTag = urlencode($arrParam['tag']);
                $strTmp .= "/{$strTag}";
            }
            unset($arrParam['tag']);
        }
        if (! empty($arrParam)) {
            $strTmp .= '?' . http_build_query($arrParam);
        }
            
        return 'http://'.DOMAIN_SITE.$strTmp;
    }
    
    public static function circleCatUrl($strCatKey = 'all', $strTag = null, $arrParam = null) {
        $strCatKey = urlencode($strCatKey);
        $strTmp = "/category/{$strCatKey}";
        if ($strTag != '') {
            $strTag = urlencode($strTag);
            $strTmp .= "/{$strTag}";
        }
        if (isset($arrParam['offset'])) {
            $arrParam['offset'] = (int) $arrParam['offset'];
            if ($arrParam['offset'] < 1) {
                unset($arrParam['offset']);
            }
        }
        if (! empty($arrParam)) {           
            $strTmp .= '?' . http_build_query($arrParam);
        }
        
        return 'http://'.DOMAIN_SITE.$strTmp;        
    }
    
    /**
     * 个人信息页地址
     * @param int $id
     * @return string
     */
    public static function userUrl($id, $action = null, $arrParam = null)
    {
        $id = (int) $id;
        $strTmp = "/user/$id";
        if ($action) {
            $strTmp .= "/$action";
            if (isset($arrParam['type']) && $arrParam['type'] !== '') {
            	$strTmp .= "/{$arrParam['type']}";
            }
            unset($arrParam['type']);
        }
        if (! empty($arrParam)) {
            $strTmp .= '?' . http_build_query($arrParam);
        }
        return 'http://'.DOMAIN_SITE.$strTmp;
    }

    /**
    * 中文字符串长度截取
    * @param string $str 源字符串
    * @param int $length 字数
    * @param string $trimmarker 被截断部分的替代
    * @param bool $htmlEscape 是否进行HTML转义
    **/
    public static function utf8SubStr($str, $length, $trimmarker = '...', $htmlEscape = true)
    {
        $str = mb_strimwidth($str, 0, $length, $trimmarker, Kohana::$charset);
        if ($htmlEscape) {            
            $str = HTML::chars($str);
        }
        return $str;
    }

    /**
     * 将秒数转换成时间
     */
    public static function sec2time($sec){
        if ($sec < 1) {
            $strRes = '';
        } elseif ($sec > 60) {
            $intMinute = (int) floor($sec / 60);
            $intSecond = $sec % 60;
            $strRes = sprintf('%d:%02d', $intMinute, $intSecond);
        } else {
            $strRes = sprintf('0:%02d', $sec);
        }
        
        return $strRes;
    }
    
    /**
     * 输出圈子是热门/新的CSS
     * @param array $arrCircle 一个圈子的信息
     */
    public static function circleTypeCss($arrCircle) {
        $strCssName = 'type-t0';
        if (Model_Logic_Circle::isCircleHot($arrCircle)) {
            $strCssName = ' type-t2';
        } elseif (Model_Logic_Circle::isCircleNew($arrCircle)) {
            $strCssName = ' type-t1';
        }
    
        return $strCssName;
    }
        
    /**
     * 
     * 获取圈子对应九宫缩略图
     * @param string $fdfsPath
     * 
     * @return string
     */
    public static function circlePreviewPic($fdfsPath) {
        $pos = strpos($fdfsPath, '/');
        $group = substr($fdfsPath, 0, $pos);
        $path = substr($fdfsPath, $pos);
        if ($group == 'group1') {
            return 'http://'.DOMAIN_WEB_STORAGE_CLUSTER.$path;
        } else {
            return 'http://'.DOMAIN_STATIC.'/img/circle_default.gif';
        }
    }
    
    /**
     * 到今天的毫秒数/秒数
     */
    public static function time_from_now($time, $bolIsSecond = false) {
        if (!$time) {
            return '未知';
        }
    
        if (! $bolIsSecond) {
            //如果传进来是毫秒，则先处理为秒
            $time = floor($time/1000);
        }
    
        $sec = time() - $time;		//到今天的秒数
    
        if ($sec >= 31104000) {
            $intTmp = floor($sec / 31104000);
            $rstr = "{$intTmp}年前";
        } elseif ($sec >= 2592000) {
            $intTmp = floor($sec / 2592000);
            $rstr = "{$intTmp}个月前";
        } elseif ($sec >= 86400) {
            $intTmp = floor($sec / 86400);
            $rstr = "{$intTmp}天前";
        } elseif ($sec >= 3600) {
            $intTmp = floor($sec / 3600);
            $rstr = "{$intTmp}小时前";
        } elseif ($sec >= 60) {
            $intTmp = floor($sec / 60);
            $rstr = "{$intTmp}分钟前";
        } else {
            $rstr = "此刻";
        }
    
        return $rstr;
    }

    /**
     * 生成符合nginx自动合并css的url
     * 
     * 合并最好是对同一个目录下的文件，然后把公共路径提取出来，这样在css中的相对路径就不会出现错误，
     * 如：Util::concatCss(['bc.css', 'popup_group.css'], $smarty.config.v, 'video/')，即把/css/video
     * 目录下的bc.css和popup_group.css合并
     * 也可以把路径信息放在文件名中：Util::concatCss(['video/bc.css', 'app/popup_group.css'], $smarty.config.v)，
     * 即把/css/video/bc.css和/css/app/popup_group.css合并，此时需要注意css中有url(images/xx.gif)这样的相对路径
     * 会发生错误，必须使用绝对路径url(/css/video/images/xx.gif)
     * 
     * @param string|array $mixedFile 需要合并的文件名，是字符串的话用英文逗号分隔，文件名之间不要有空格
     * @param string $strVersion 版本号
     * @param string $strPrefixDir 文件的公共路径
     * @return string
     */
    public static function concatCss($mixedFile, $strVersion, $strPrefixDir = '') {
        if (JKit::$environment == JKit::DEVELOPMENT) {
            $strTmp = '';
            if (! is_array($mixedFile)) {
                $mixedFile = explode(',', $mixedFile);
            }
            foreach ($mixedFile as $v) {
                $strSrc = 'http://'.DOMAIN_STATIC."/css/{$strPrefixDir}{$v}?v={$strVersion}";
                $strTmp .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$strSrc\">";
            }
            
            return $strTmp;
        }
        if (is_array($mixedFile)) {
            $mixedFile = implode(',', $mixedFile);
        }
        $strSrc = 'http://'.DOMAIN_STATIC."/css/{$strPrefixDir}??{$mixedFile}?v={$strVersion}";
        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"$strSrc\">";
    }

    /**
     * 生成符合nginx自动合并js的url
     *
     * 参考self::concatCss的说明，特别是相对路径的计算
     *
     * @param string|array $mixedFile 需要合并的文件名，是字符串的话用英文逗号分隔，文件名之间不要有空格
     * @param string $strVersion 版本号
     * @param string $strPrefixDir 文件的公共路径
     * @return string
     */
    public static function concatJs($mixedFile, $strVersion, $strPrefixDir = '') {
        if (JKit::$environment == JKit::DEVELOPMENT) {
            $strTmp = '';
            if (! is_array($mixedFile)) {
                $mixedFile = explode(',', $mixedFile);
            }
            foreach ($mixedFile as $v) {
                $strSrc = 'http://'.DOMAIN_STATIC."/js/{$strPrefixDir}{$v}?v={$strVersion}";
                $strTmp .= "<script type=\"text/javascript\" src=\"$strSrc\"></script>";
            }
        
            return $strTmp;
        }        
        if (is_array($mixedFile)) {
           $mixedFile = implode(',', $mixedFile);
        }
        $strSrc = 'http://'.DOMAIN_STATIC."/js/{$strPrefixDir}??{$mixedFile}?v={$strVersion}";
        return "<script type=\"text/javascript\" src=\"$strSrc\"></script>";
    }
    
    /**
     * 输出带@用户链接的文本，文本已经按照html转义好，调用者不要再做html转义
     * @param string $strTxt
     * @param array $arrUser
     * @param array $arrUserLinkParam
     * @param int $intMaxLen 返回文本最长长度
     * @return string
     */
    public static function formatUserLinkText($strTxt, $arrUser = null, $arrUserLinkParam = null, $intMaxLen = 0) {
        if ($intMaxLen > 0) {
            $strTxt = self::utf8SubStr($strTxt, $intMaxLen, true, false);
        }
        $strTxt = HTML::chars($strTxt); //做HTML转义
        if (empty($arrUser)) {
            return $strTxt;
        }
        $strUserLinkParam = '';
        if (! empty($arrUserLinkParam)) {
            $strUserLinkParam = ' data-lks="' . HTML::chars(json_encode($arrUserLinkParam)) . '"';
        }
        
        $arrSortedUser = array();
        foreach ($arrUser as $intUid => $strNick) {
        	$arrSortedUser[$intUid] = mb_strlen($strNick, 'utf-8');
        }
        arsort($arrSortedUser, SORT_NUMERIC);
        
        $arrReplace = array();
        foreach ($arrSortedUser as $intUid => $nouse) {
        	$strNick = HTML::chars($arrUser[$intUid]);
            $strUrl = self::userUrl($intUid);
            $strLinkTxtKey = "@{$strNick}";
            $strLinkTxtVal = "_@_{$strNick}"; //_@_保证后面不会再次替换同一个用户名
            $arrReplace[$strLinkTxtKey] = "<a href=\"$strUrl\"{$strUserLinkParam} target=\"_blank\" class=\"ava_popup_\" data-id=\"{$intUid}\">$strLinkTxtVal</a>";            
        }
        if (! empty($arrReplace)) {
            $strTxt = str_replace(array_keys($arrReplace), array_values($arrReplace), $strTxt);
            $strTxt = str_replace('>_@_', '>@', $strTxt);
        }
        
        return $strTxt;
    }        

    public static function getMood($arrMoodCount, $strDefault = 'xh') {
        $strMood = $strDefault;
        if (is_array($arrMoodCount)) {
            $intTmp = 0;
            foreach ($arrMoodCount as $k => $v) {
                if ($k == 'total') {
                    continue;
                }
                if ($v > $intTmp) {
                    $intTmp = $v;
                    $strMood = $k;
                }
            }
        }
        return $strMood;
    }
    
    /**
     * 获取缓存结果
     * @param mixed $mixedId 非字符的key，会自动用serialize转换为字符串
     * @param mixed $mixedDefault 默认返回值
     * @return NULL|mixed 如果不允许缓存，返回null，否则返回缓存结果，若缓存为空，返回传入的默认值
     */
    public static function getCache($mixedId, $mixedDefault = null) {
        if (! defined('SITE_CACHE_ENABLE') || ! SITE_CACHE_ENABLE) {
            return null;
        }
        if (! is_string($mixedId)) {
            $mixedId = serialize($mixedId);
        }
        $cache = Cache::instance('web');
        $strRet = $cache->get($mixedId, null);
        if ($strRet === null) {
            return $mixedDefault;
        } else {
            return unserialize($strRet);
        }
    }
    
    /**
     * 保存缓存结果
     * @param mixed $mixedId 非字符的key，会自动用serialize转换为字符串
     * @param mixed $mixedValue 支持各种类型的变量，底层自动用serialize转换
     * @param int $intLifetime 缓存时间，单位为秒，默认使用常量值SITE_CACHE_DEFAULT_LIFETIME
     */
    public static function setCache($mixedId, $mixedValue, $intLifetime = null) {
        if (! defined('SITE_CACHE_ENABLE') || ! SITE_CACHE_ENABLE) {
            return;
        }
        if (! is_string($mixedId)) {
            $mixedId = serialize($mixedId);
        }
        if (! is_string($mixedValue)) {
            $mixedValue = serialize($mixedValue);
        }
        if ($intLifetime === null) {
            $intLifetime = SITE_CACHE_DEFAULT_LIFETIME;
        }
        $cache = Cache::instance('web');
        $cache->set($mixedId, $mixedValue, $intLifetime);
    }
	/**
     *  
     * 检测是不是爬虫访问
     * @param mixed $userAgent
     * @param mixed $remoteIp
     */
    public static function isSpider( $userAgent=NULL, $remoteIp=NULL ) {
        if (isset($_GET['_spider']) && $_GET['_spider']) {
            return true;
        }
    	if($userAgent===NULL) {
    		$userAgent = $_SERVER['HTTP_USER_AGENT'];
    	}
    	if($remoteIp===NULL) {
    		$remoteIp = Request::$client_ip;
    	}
    	$arrFilterIps = Jkit::$config->load('filter.spiderip');
	    $reg = "/(spider)|(crawler)|(googlebot)|(^mlbot)|(^mediapartners)|(^curl)".
	    	"|(LoadRunner)|(google\sweb\spreview)/i";
	    return preg_match($reg, $userAgent) || isset($arrFilterIps[$remoteIp]);
    }
    /**
     * 
     * 发送短信报警
     * @param string $message 报警信息
     * @param array $recivers 报警接收者，不传选用配置
     * @param boolean $isForce 无论环境是否强制发送
     */
    public static function sendSmsMonitor($message, $recivers=NULL, $isForce=false) {
    	if (!$isForce && JKit::$environment != JKit::PRODUCTION) {
    		JKit::$log->debug(__FUNCTION__." env-".JKit::$environment." not send");
    		return true;
    	}
    	$monitorConfig = Kohana::$config->load('monitor');
    	if($recivers===NULL) {
    		$recivers = $monitorConfig['sms_receivers'];
    	}
    	$oneSuccess = false;
    				foreach ($recivers as $receiver) {
                    	$cmd = SMS_SCRIPT_PATH.' '.escapeshellarg($receiver).' '
                            .escapeshellarg('[ERROR] '.substr(str_replace("'", '', $message), 0, 140));
                        JKit::$log->debug(__FUNCTION__, $cmd);
                        $output = shell_exec($cmd);
                        JKit::$log->debug(__FUNCTION__, $output);
                        $output = json_decode($output, true);
                        if (!$output || $output['return_code'] != 0) {
                            JKit::$log->error("send sms failed, $cmd");
                        } else {
                        	$oneSuccess = true;
                        }
                    }
       return $oneSuccess;
    }
    
    /**
     * 全站通用分页条，适用于offset+count形式的分页参数，分页链接依据当前query自动生成
     * @param int $total 总记录数
     * @param int $count 每页记录数
     * @param int $bothEndPageNumber 当前页左右显示的分页个数
     * @return string
     */
    public static function pager($total, $count, $bothEndPageNumber = 5)
    {
        $url = parse_url($_SERVER['REQUEST_URI']);
        $query = array();
        parse_str($url['query'], $query);
        if (!isset($query['offset'])) {
            $query['offset'] = 0;
        }
        $totalPage = ceil($total / $count);
        if ($totalPage <= 1) {
            return '';
        }
        $currentPage = floor($query['offset'] / $count) + 1;
        
        $pager = '<div class="pager">';
        if ($currentPage == 1) {
            $pager .= '<span class="pager-pre">&lt;上一页</span>';
        } else {
            $query['offset'] = ($currentPage - 2) * $count;
            $pager .= '<a class="pager-pre" href="'.$url['path'].'?'.http_build_query($query).'">&lt;上一页</a>';
        }
        $begin = $currentPage - $bothEndPageNumber;
        $end = $currentPage + $bothEndPageNumber;
        if ($begin < 1 && $end > $totalPage) {
            $begin = 1;
            $end = $totalPage;
        } else if ($begin < 1) {
            $begin = 1;
            $end = $begin + 2 * $bothEndPageNumber;
            $end = $end > $totalPage ? $totalPage : $end;
        } else if ($end > $totalPage) {
            $end = $totalPage;
            $begin = $totalPage - 2 * $bothEndPageNumber;
            $begin = $begin < 1 ? 1 : $begin;
        }
        for ($p = $begin; $p <= $end; $p++) {
            if ($p == $currentPage) {
                $pager .= '<em>'.$p.'</em>';
            } else {
                $query['offset'] = ($p - 1) * $count;
                $pager .= '<a href="'.$url['path'].'?'.http_build_query($query).'">'.$p.'</a>';
            }
        }
        if ($currentPage == $totalPage) {
            $pager .= '<span class="pager-next">下一页&gt;</span>';
        } else {
            $query['offset'] = $currentPage * $count;
            $pager .= '<a class="pager-next" href="'.$url['path'].'?'.http_build_query($query).'">下一页&gt;</a>';
        }
        $pager .= '</div>';
        return $pager;
    }
    
    /**
     * 实体剧集分页
     * @param array $episodes
     * @param int $bothEndNumber
     * @return string
     */
    public static function episodesPager($episodes, $bothEndNumber = 5, $finished = 'yes')
    {
        $html = array();
        $total = count($episodes);
        foreach ($episodes as $index => $episode) {
            if ($index < $bothEndNumber || $index >= ($total - $bothEndNumber)) {
                if ($index == ($total - 1) && $finished != 'yes') {
                    $new = '<em class="y-ico y-ico-new"></em>';
                } else {
                    $new = '';
                }
                if ($episode['play_url']) {
                    $html[$index] = '<a href="'.$episode['play_url'].'" target="_blank">'.$episode['order'].$new.'</a>';
                } else {
                    $html[$index] = '<span>'.$episode['order'].$new.'</span>';
                }
            } elseif ($index == $bothEndNumber) {
                if ($total == (2 * $bothEndNumber + 1)) {
                    if ($episode['play_url']) {
                        $html[$index] = '<a href="'.$episode['play_url'].'" target="_blank">'.$episode['order'].$new.'</a>';
                    } else {
                        $html[$index] = '<span>'.$episode['order'].$new.'</span>';
                    }
                } elseif ($total > (2 * $bothEndNumber + 1)) {
                    $html[$index] = '<span>...</span>';
                }
            }
        }
        return '<div class="y-warehouse-list-a">'.implode('', $html).'</div>';
    }
    
    /**
     * 将评分转换成星级（五星）
     * @param string|float $score 评分，0～10之间
     * @param int $starWidth 单个星星宽度占总宽度的百分比
     * @return int 要显示的宽度百分比
     */
    public static function scoreToStar($score, $starWidth = 16)
    {
        if (!is_float($score)) {
            $score = floatval($score);
        }
        if ($score < 0) {
            $score = 0;
        } else if ($score > 10) {
            $score = 10;
        }
        $score = floor($score) / 2;
        return $score * $starWidth + floor($score) * ((100 - $starWidth * 5) / 4);
    }
    
    /**
     * 实体信息页地址，实体包括电影、电视剧等
     * @param string $id
     * @return string
     */
    public static function entityInfoUrl($entity)
    {
        if ($entity['entity_type'] == 'movie') {
            return 'http://'.DOMAIN_SITE.'/movie/info/'.urlencode($entity['entity_id']);
        } elseif ($entity['entity_type'] == 'tv') {
            return 'http://'.DOMAIN_SITE.'/tv/info/'.urlencode($entity['entity_id']);
        } elseif (isset($entity['play_url'])) {
            return $entity['play_url'];
        } elseif (isset($entity['episode']) && count($entity['episode']) > 0) {
            return $entity['episode'][0]['play_url'];
        } else {
            return '';
        }
    }
    
    /**
     * 明星信息页地址
     * @param array $star
     * @return string
     */
    public static function starInfoUrl($star)
    {
        return 'http://'.DOMAIN_SITE.'/star/info/'.urlencode($star['name']);
    }
}