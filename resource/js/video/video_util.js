/**
* 页面需要用到的Util，对应php classes/util.php
**/

(function(){
    var getUtilConst                 = QW.Config.get('utilConst'),
        DOMAIN_IMAGE_VIDEO_THUMBNAIL = getUtilConst.domain_image_video_thumbnail,
        DOMAIN_IMAGE_THUMBNAIL_ARR   = getUtilConst.domain_image_thumbnail_arr,
        DOMAIN_STATIC                = getUtilConst.domain_static,
        DOMAIN_IMAGE_USER_AVATAR     = getUtilConst.domain_image_user_avatar,
        DOMAIN_SITE                  = getUtilConst.domain_site,
        DOMAIN_WEB_STORAGE_CLUSTER   = getUtilConst.domain_web_storage_cluster;
    var encode4Http = QW.StringH.encode4Http,
        encode4Html = QW.StringH.encode4Html;

    var PageUtil = {
        /**
         * 视频缩略图URL
         * @param string fdfsPath FastDFS存储路径，比如group1/M00/57/07/CpwYW076qsShLkshAAADL1scZ4o818.php
         * @return string URL
         */
        videoThumbnailUrl : function(fdfsPath){
            var fdfsPath = fdfsPath || '',
                pos = fdfsPath.indexOf('/'),
                group = fdfsPath.substr(0, pos),
                path = escape(fdfsPath.substr(pos) + '');

            if(group == 'group1'){
            	//取group1/M00/D9/34/CpwYFE92bJb2PUd4AACN1UZ4v0I317.jpg中的第16个字符4的ASCII值，注意与PHP版videoThumbnailUrl保持一致
            	var i = fdfsPath.charCodeAt(15) % DOMAIN_IMAGE_THUMBNAIL_ARR.length;
                return 'http://' + DOMAIN_IMAGE_THUMBNAIL_ARR[i] + path;
            } else {
                return 'http://' + DOMAIN_STATIC + '/img/vp120.jpg';
            }
        },

        /**
         * 用户头像URL
         * @param string fdfsPath FastDFS存储路径，比如group1/M00/57/07/CpwYW076qsShLkshAAADL1scZ4o818.php
         * @return string URL
         */         
        userAvatarUrl : function(fdfsPath, size){
            if([30, 48, 160, 'org'].contains(fdfsPath)){
                return 'http://' + DOMAIN_STATIC + '/img/head' + fdfsPath + '.jpg';
            }
            var fdfsPath = fdfsPath || '',
                pos = fdfsPath.indexOf('/'),
                group = fdfsPath.substr(0, pos),
                path = escape(fdfsPath.substr(pos) + '');
        
            if (group == 'group1') {
                return 'http://' + DOMAIN_IMAGE_USER_AVATAR + path;
            } else if(size && [30, 48, 160, 'org'].contains(size)) {
                return 'http://' + DOMAIN_STATIC + '/img/head' + size + '.jpg';
            } else {
                return 'http://' + DOMAIN_STATIC + '/img/head30.jpg';
            }
        },

        /**
         * 视频播放页地址
         * @param string id 视频ID
         * @param string playlist 播放列表名称，默认为空，播放页显示相关视频
         * @param array data 相关数据，比如圈子ID或用户ID
         * {
         *  'circle' : ..., 圈子ID
         *  'user' : ..., 用户ID
         *  ...
         * }
         * @return string
         */
         videoPlayUrl : function(id, playlist, data){
            var url = 'http://' + DOMAIN_SITE + '/v/' + encode4Http(id.toString()) + '.html';
            if (playlist) {
            	data['playlist'] = playlist.toString();
            }
            if (data) {
            	var urlParam = ObjectH.encodeURIJson(data);
            	if (urlParam) {
                	url += '?' + urlParam;
            	}
            }
            return url;
         },

        /**
         * 圈子信息页地址
         * @param int id
         * @return string
         */
         circleUrl : function(id, data, circle){
        	 if (data == undefined) {
        		 data = {};
        	 }
        	 if (circle == undefined) {
        		 circle = {};
        	 }
        	 var url = 'http://' + DOMAIN_SITE;
        	 if (!circle.official && circle.creator) {
            	 url += '/user/' + circle.creator;
        	 }
        	 url += '/circle/' + encode4Http(id.toString());
             if (data.tag) {
                 url += '/' + encode4Http(data.tag.toString());
                 delete data.tag;
             }
        	 var strParam = ObjectH.encodeURIJson(data);
        	 if (strParam) {
        		 url += '?' + strParam;
        	 }
             return url;
         },
             
        /**
         * 个人信息页地址
         * @param int id
         * @return string
         */
         userUrl : function(id, action, data){
        	 var url = 'http://' + DOMAIN_SITE + '/user/' + encode4Http(id.toString());
             if (action) {
                 url += '/' + encode4Http(action.toString());
             }
             if (data) {
            	 var strParam = ObjectH.encodeURIJson(data);
            	 if (strParam) {
            		 url += '?' + strParam;
            	 }          	 
             }
             return url;             
         },
         
         /**
         * 
         * 获取圈子对应九宫缩略图
         * @param string $fdfsPath
         * 
         * @return string
         */
        circlePreviewPic : function (fdfsPath) {
            var pos = fdfsPath.indexOf('/'),
                group = fdfsPath.substr(0, pos),
                path = fdfsPath.substr(pos);
            if (group == 'group1') {                
                return 'http://' + DOMAIN_WEB_STORAGE_CLUSTER + path;
            } else {
                return 'http://' + DOMAIN_STATIC + '/img/circle_default.gif';
            }
        },

         /**
        * 字符串长度截取加点       
        * @param string str
        * @param int length
        * @param bool adddot
        **/
        utf8SubStr : function(str, length, adddot){
        	if (! str) {
        		return str;
        	} else if (! length || str.length <= length) {
        		return str.encode4HtmlValue();
        	}
        	
        	str = str.substr(0, length);
        	if (adddot) {
        		str = str + '...';
        	}
        	
            return  str.encode4HtmlValue();            
        },

         /**
         * 将秒数转换成时间
         */
        sec2time : function(sec){
            if (sec < 1) {
                return '';
            }
            return (Math.floor(sec/60)+(sec%60)/100).toFixed(2).toString().replace('.', ':');            
        },
        
        /**
         * 输出带@用户链接的文本，文本已经按照html转义好，调用者不要再做html转义
         * @param string $strTxt  
         * @param array $arrUser
         * @param array $arrUserLinkParam
         * @return string
         * @example
                    PageUtil.formatUserLinkText
                    (
                        '@共和国防洪 @拉登 @比尔_盖饭 @jag @我爱西沙 @左左哥 @丘比特 @scKicker 真是太有才了'
                        ,  
                            {
                                "1826515151": "我爱西沙", 
                                "1798015323": "共和国防洪", 
                                "794123477": "jag", 
                                "1186951503": "拉登", 
                                "1797646954": "左左哥", 
                                "1648521763": "比尔_盖饭", 
                                "1560858034": "丘比特"
                            }
                        , 
                            {
                                "fuid": '850271823',
                                "stype": 'i'
                            } 
                    );                       
         */
        formatUserLinkText: function( $strTxt, $arrUser, $arrUserLinkParam, $intMaxLen )
        {
            if( $intMaxLen > 0 )
            {
               $strTxt = $strTxt.slice( 0, $intMaxLen );
            }
            
            $strTxt = QW.StringH.encode4Html( $strTxt );
            
            if( !$arrUser )
            {
                return $strTxt;
            }
            var $strUserLinkParam = '';
            if (! $arrUserLinkParam) {
            	$strUserLinkParam = ' data-lks="' + ObjectH.encodeURIJson($arrUserLinkParam) + '"';
            }
            var $arrReplace = {};
            
            for( var key in $arrUser )
            {
                var $strUrl = this.userUrl(key);
                var $strLinkTxt = QW.StringH.encode4Html("@"+$arrUser[key]+"");
                $arrReplace[$strLinkTxt] = '<a href="'+$strUrl+'"'+$strUserLinkParam+' target="_blank">'+$strLinkTxt+"</a>";   
            }
            
            if ( $arrReplace ) {
                for( var key in $arrReplace )
                {
                    //alert( $strTxt + '\n\n' + key )
                    $strTxt = $strTxt.replace( new RegExp( key+'([:：@ \\/／　＠]|$|&nbsp;)', 'gi' ) , $arrReplace[key]+'$1' );
                }
            }
            
            return $strTxt;
        }
    };

    QW.provide('PageUtil', PageUtil);
})();
