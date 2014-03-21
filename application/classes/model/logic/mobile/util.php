<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 加一些通用的处理函数
 * @author xucongbin
 */
class Model_Logic_Mobile_Util {
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function appendVideoMp4Playurl( &$arrVideos ) {
		if( !$arrVideos ) {
			return;
		}
		foreach($arrVideos as $k=>$row) {
			$arrVideos[$k]['mp4_playurl_map'] = $this->getVideoMp4Playurl( $row );
		}
	}
	
	/**
	 * 
	 * 获取mp4视频播放地址
	 * @param array $video
	 * 
	 * @return array
	 */
	public function getVideoMp4Playurl( $video ) {
		$arrReturnMp4PlayUrl = array(
			"src" => "",
			"is_callback" => false
		);
		$strPlayUrl = $video['play_url'];
		$intSharpCharPos = strpos($strPlayUrl, '#');
		if ($intSharpCharPos !== false) {
			$strPlayUrl = substr($strPlayUrl, 0, $intSharpCharPos);
		}
		switch ($video['domain']) {
			case 'ku6.com':
				/**
				 * [play_url] => http://v.ku6.com/show/wTgx6hAQ0TIPLaWs.html
				 * 				 http://my.ku6.com/watch?loc=datajinrijiaodian&v=7WyQ1RPcJNEKdY5ctn0Zsg..
				 * http://v.ku6.com/fetchwebm/wTgx6hAQ0TIPLaWs.m3u8
				 */
				if (strpos($strPlayUrl, '/show/') !== false) {
					$strVid = basename($video['play_url'], '.html');
				} else {
					$strQueryStr = parse_url($strPlayUrl, PHP_URL_QUERY);					
					$arrTmp = null;
					if ($strQueryStr) {
						parse_str($strQueryStr, $arrTmp);
						$strVid = $arrTmp['v'];
					}
				}
				if (strlen($strVid) > 0) {
					
					$arrReturnMp4PlayUrl['src'] = "http://v.ku6.com/fetchwebm/{$strVid}.m3u8";
				}				
				break;
			case 'youku.com':
				/**
				 * [play_url] => http://v.youku.com/v_show/id_XNDExNDA2Mjg0.html
				 * http://v.youku.com/player/getRealM3U8/vid/XNDA5Nzg5NzU2/video.m3u8
				 */
				$strVid = basename($strPlayUrl, '.html');
				if (strncmp($strVid, 'id_', 3) == 0) {
					$strVid = substr($strVid, 3);
				}
				if (strlen($strVid) > 0) {
					$arrReturnMp4PlayUrl['src'] = "http://v.youku.com/player/getRealM3U8/vid/{$strVid}/video.m3u8";
				}
				break;
			case 'tudou.com':
				/**
				 * [play_url] => http://www.tudou.com/programs/view/jzwIjJb8F2M/ 
				 */
				$strVid = basename(trim($strPlayUrl, '/'));
				if (strlen($strVid) > 0) {
					$arrReturnMp4PlayUrl['src'] = $strVid;
					$arrReturnMp4PlayUrl['is_callback'] = true;
				}
				break;
			case 'ifeng.com':
			
				break;
			case '56.com':
				/**
				 * [play_url] => http://www.56.com/u89/v_NjkwNTkyMjI.html
				 */
				$strVid = basename($strPlayUrl, '.html');
				if (strncmp($strVid, 'v_', 2) == 0) {
					$strVid = substr($strVid, 2);
				}
				if (strlen($strVid) > 0) {
					$strVid = base64_decode($strVid);
					if ($strVid) {
						$arrReturnMp4PlayUrl['src'] = "http://vxml.56.com/m3u8/{$strVid}/";
					}
				}			
				break;
			case 'sohu.com':
				/**
				 * [player_url] => http://share.vrs.sohu.com/686050/v.swf
				 */
				$strRegex = '#/(\d+)/v\\.swf#';
				$intNum = preg_match($strRegex, $video['player_url'], $arrTmp);
				if ($intNum > 0) {
					$strVid = $arrTmp[1];
					if (strlen($strVid) > 0) {
						$arrReturnMp4PlayUrl['src'] = "http://hot.vrs.sohu.com/ipad{$strVid}.m3u8";
					}
				}				
				break;
			case 'sina.com.cn':
				//http://v.iask.com/v_play_ipad.php?vid={iosid}
				if (isset($video['iosid']) && $video['iosid']) {
					$arrReturnMp4PlayUrl['src'] = "http://v.iask.com/v_play_ipad.php?vid={$video['iosid']}";					
				}
				break;
			case 'qq.com':
				
				break;
			case '6.cn':
				
				break;
			case 'pptv.com':
				//http://api.v.pptv.com/api/ipad/play.js?rid={iosid}&cb=pptv_player_rand&r=0.1412951403938234
				if (isset($video['iosid']) && $video['iosid']) {
					$arrReturnMp4PlayUrl['src'] = $video['iosid'];
					$arrReturnMp4PlayUrl['is_callback'] = true;
				}				
				break;
			case 'joy.cn':
				
				break;
		}
		
		return $arrReturnMp4PlayUrl['src'] ? $arrReturnMp4PlayUrl : null;
	}
}