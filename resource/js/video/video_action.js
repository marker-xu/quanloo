(function(){
    var mix = QW.ObjectH.mix;
    window.console && console.log("进入video_action.js文件")
    var VideoAction = (function(){
        var videoAction = {};
        var cbFun = function(d, doneClass, $srcData){
            if (typeof d == "string") {
                try{
                    var d = StringH.evalExp(d);
                }
                catch(e){
                    alert('发生错误，请稍候重试');
                    return;
                }
            }     
            if(d.err != 'ok'){
                if(d.err == 'sys.permission.need_login'){
                    $srcData && window.XLogin && XLogin.storeTriggerData( d, $srcData );
                    LOGIN_POPUP ? LOGIN_POPUP() : window.location.href = '/user/login';
                } else {
                    XLogin.ondone();
                    alert(d.msg || '发生错误');
                }
            } else {
                W(document).tweet('active-done-' + QW.vaTimeflag, {
                    className : doneClass
                });
                XLogin.ondone();
            }
        };

        videoAction.send = function(){
            var args = [].slice.call(arguments);
            //argsf = [].slice.call(args[2]);
            var $srcData = args[4];
            if(typeof(args[2][0]) != 'object'){
                var queryData = {};
                args[1].forEach(function(k, i){
                    queryData[k] = args[2][i] || '';
                });                
            } else {
                var queryData = args[2][0];
            }            
            
            if(typeof(args[3]) == 'string'){
                QW.Ajax.get(args[0], queryData, function(data){
                    $srcData && window.XLogin && XLogin.storeTriggerData( data, $srcData );
                    if( window.UPDATE_AVA_POP_STATUS ) UPDATE_AVA_POP_STATUS( args[1], queryData, data  );
                    cbFun(data, args[3]);
                });
            } else {
                QW.Ajax.get(args[0], queryData, function( data ){
                    $srcData && window.XLogin && XLogin.storeTriggerData( data, $srcData );
                    if( window.UPDATE_AVA_POP_STATUS ) UPDATE_AVA_POP_STATUS( args[1], queryData, args[1], data  );
                    if(args[3]) args[3]( data );
                });
            }
            
        };

        mix(videoAction, {
            /*添加视频列表到cookie记录*/
            add2CookieList : function(vid, name, func, opts){
                var cookie = QW.Cookie;
                opts = opts || {};
                mix(opts, {
                    max:10, //最大长度
                    single   : true //是否不允许同一视频多次添加
                });                
                var videoCookieList = StringH.evalExp(cookie.get(name) || '{}');
                if(opts.single){
                    for(var v in videoCookieList){
                        if(videoCookieList[v] == vid){
                            delete videoCookieList[v];
                        }
                    }
                }
                videoCookieList[parseInt((new Date).getTime()/1000)] = vid;
                var _k = ObjectH.keys(videoCookieList);
                while(_k.length > opts.max){
                    delete videoCookieList[_k.shift()];
                }
                cookie.set(name, ObjectH.stringify(videoCookieList));
                if(func){
                    func();
                }
            },
            /*删除视频列表中单个视频*/
            rmFromCookieList : function(vid, name, func){
            
                var ck = QW.Cookie.get( name ).trim();
                
                if( ck )
                {
                    var o = ck.evalExp();
                    
                    for( var k in o )
                    {
                        if( o[k].trim() == vid.trim() )
                        {
                            delete o[k];
                        }
                    }
                    
                    var isNull = true;
                    for( var k in o)
                    {
                        isNull = false;
                        break;
                    }
                    
                    if( isNull )
                    {
                        QW.Cookie.set( name, '' );
                    }
                    else
                    {
                        QW.Cookie.set( name, Object.stringify( o ) );
                    }
                    
                }
            },
            /**
             * 删除指定列表的所有cookie
             */             
            rmAllCookieList: function( $name ){
                QW.Cookie.set( $name, '' )
            },
            /*推视频*/
            like : function(){
                this.send('/video/like', ['id', 'circle'], arguments, 'liked');
            },
            /*评论视频*/
            comment : function(){
                this.send('/video/comment', ['id', 'content', 'circle'], arguments, function(){
                    });
            },
            /*以后观看*/
            watchlater : function( $data, $src, $srcData ){
                var _this = this,
                _argv = [].slice.call(arguments),
                _vid = _argv[0].id,
                _added_class = _argv[1].hasClass('add') ? 'added' : 'newadded';
                this.send('/video/watchlater', ['id'], arguments, function(d){
                    /*如果未登录，将播放列表写入cookie*/
                    if(d.err == 'sys.permission.need_login'){
                        $srcData && window.XLogin && XLogin.storeTriggerData( d, $srcData );
                        LOGIN_POPUP ? LOGIN_POPUP() : window.location.href = '/user/login';
                        _this.add2CookieList(_vid, 'watchlater_list');
                        return;
                    }
                    XLogin.ondone();
                    
                    _argv[1].attr('title', '取消收藏');
                    W(document).tweet('active-done-' + QW.vaTimeflag, {
                        className : _added_class
                    });
                });
            },
            unwatchlater: function( $data, $src, $srcData ){
                var _this = this,
                _argv = [].slice.call(arguments),
                _vid = _argv[0].id,
                _added_class =  'newadd';
                this.send('/video/deletewatchlater', ['id'], arguments, function(d){
                    /*如果未登录，将播放列表写入cookie*/
                    if(d.err == 'sys.permission.need_login'){                    
                        $srcData && window.XLogin && XLogin.storeTriggerData( d, $srcData );
                        LOGIN_POPUP ? LOGIN_POPUP() : window.location.href = '/user/login';
                        _this.rmFromCookieList(_vid, 'watchlater_list');
                        return;
                    }
                    
                    XLogin.ondone();
                    
                    _argv[1].attr('title', '添加到我的收藏');
                    W(document).tweet('active-done-' + QW.vaTimeflag, {
                        className : _added_class
                    });
                });
            },
            /*关注圈子*/
            subscribe : function(data, $src, $srcData){
                var _this = this,
                argu = [].slice.call(arguments);
                var cb = argu[0].cb || 'followed';
                this.send('/circle/subscribe?rnd='+Math.random(), ['id'], argu, cb, $srcData);
            },
            /*取消关注圈子*/
            unsubscribe : function(data, $src, $srcData){
                var _this = this,
                argu = [].slice.call(arguments);
                var cb = argu[0].cb || 'b-follow';
                this.send('/circle/unsubscribe?rnd='+Math.random(), ['id'], argu, cb, $srcData);
            },
            /*关注用户*/
            followuser : function(data, $src, $srcData){
                var _this = this,
                argu = [].slice.call(arguments);
                var cb = argu[0].cb || 'bt-f-done';
                this.send('/user/dofollow?rnd='+Math.random(), ['following', 'hidden'], argu, cb, $srcData);
                
                window.XAtComplete && XAtComplete.reset && XAtComplete.reset();
            },
            /*取消关注用户*/
            unfollowuser : function(data, $src, $srcData){
                var _this = this,
                argu = [].slice.call(arguments);
                var cb = argu[0].cb || 'bt-f-add';
                this.send('/user/unfollow?rnd='+Math.random(), ['following'], argu, cb, $srcData);
                
                window.XAtComplete && XAtComplete.reset && XAtComplete.reset();
            },
            /** 
            /* 心情
            /*喜欢（xh）、围观（wg）、大笑（dx）、鄙视（fn）、囧（jn）*/
            mood : function( $data, $src, $srcData ){
                
                //alert( $data + ', ' + $src + ', ' + $srcData );
              
                var _this = this,
                argu = [].slice.call(arguments);
                var moodMap = {
                    xh : {
                        txt : '喜欢',
                        face : 'xh_mood'
                    },
                    zj : {
                        txt : '震惊',
                        face : 'zj_mood'
                    },
                    gx : {
                        txt : '搞笑',
                        face : 'gx_mood'
                    },
                    bs : {
                        txt : '鄙视',
                        face : 'bs_mood'
                    },
                    bj : {
                        txt : '背景',
                        face : 'bj_mood'
                    }
                };
                this._tmpStr = this._tmpStr || '<dl class="comment user-mood cls"><dt><a href="{0}" target="_blank"><img src="{1}"></a><span class="face {2}"></span></dt><dd class="mood-txt"><a href="{0}" target="_blank">{3}</a><span>正在{4}</span></dd></dl>';
                this.send('/video/mood', ['id', 'mood', 'circle'], argu, function(d){
                    if('ok' == d.err || 'video.already_mooded' == d.err){
                        _this.userinfo(function(userdata){
                            if(argu[0].cb){
                                argu[0].cb(d, userdata);
                            } else {
                                /*瀑布流心情回显*/
                                var BoardBrick = W(argu[1].parentNode('.BoardBrick'), '#waterfall');
                                var _m = argu[0].mood;
                                
                                var _heat = W('.heart', BoardBrick);
                                if(_heat.html() == 0){
                                    _heat.attr('data-first', 'true');
                                }
                                /*更新计数*/
                                if('ok' == d.err){                                    
                                    _heat.html(parseInt(_heat.html()|0) + 1);                                    
                                }
                                if(W('.wrap .user-mood', BoardBrick).length){
                                    var innerWrap = W('.wrap .user-mood', BoardBrick);
                                    g(W('dt span', innerWrap)).className = 'face ' + moodMap[_m].face;
                                    W('.mood-txt span', innerWrap).html('正在' + moodMap[_m].txt);
                                } else {
                                    var tmpHtml = QW.StringH.format(_this._tmpStr, QW.PageUtil.userUrl(userdata._id), QW.PageUtil.userAvatarUrl(userdata.avatar['30'], 30), moodMap[_m].face, QW.StringH.encode4Html(userdata.nick), moodMap[_m].txt);                          
                                    W('.wrap', BoardBrick).insert('beforeend', W(tmpHtml));
                                    _heat.show();                                    
                                }

                                g(argu[1].parentNode('span')).className = 'on_' + moodMap[_m].face;
                                if(_heat.attr('data-first') || 1) {
                                    if(_heat.hasClass('_mood')){
                                        _heat.replaceClass('_mood', moodMap[_m].face);                                        
                                    } else {
                                        for(m in moodMap){
                                            if(_heat.hasClass(m + '_mood')){
                                                _heat.replaceClass(m + '_mood', moodMap[_m].face);
                                                break;
                                            }
                                        }
                                    }
                                }                                
                            }
                        });
                        XLogin.ondone();
                    }else if(d.err == 'sys.permission.need_login'){
                        $srcData && window.XLogin && XLogin.storeTriggerData( d, $srcData );
                        LOGIN_POPUP ? LOGIN_POPUP() : window.location.href = '/user/login';
                    } else {
                        alert(d.msg);
                    }
                });
            },
            /*获取当前登录用户信息*/
            userinfo : function(func, $srcData){
                //alert( 2 + ', ' + $srcData );
                QW.Ajax.get('/user/getinfo?r=' + Math.random(), '', function(userdata){
                    if(userdata.err == 'ok'){
                        func(userdata.data);
                        
                    } else {
                        $srcData && window.XLogin && XLogin.storeTriggerData( userdata, $srcData ); 
                        LOGIN_POPUP ? LOGIN_POPUP() : window.location.href = '/user/login';                                            
                    }
                });
            }
        });
        return videoAction;
    })();

    QW.provide('VideoAction', VideoAction);
    QW.vaTimeflag = 0;
})();

Dom.ready(function(){
    var trim = QW.StringH.trim,
    mix = QW.ObjectH.mix;

    QW.pageGlobal = {};

    QW.use('Ajax,Twitter,Cookie' , function(){
        W(document).on("click",function(e){
            var action_map = {
                'add'     : 'watchlater',
                'newadd'   : 'watchlater',
                "newadded" : "unwatchlater",
                'b-follow': 'subscribe',
                'followed': 'unsubscribe',
                'i-mood'  : 'mood',
                'bt-f-add': 'followuser',
                'bt-f-del': 'unfollowuser',
                'bt-f-done': 'unfollowuser'
            };
           
            var w = W(e.target);
            for(var i in action_map){
                if(w.hasClass(i) || (w.parentNode('a').length >0 && w.parentNode('a').hasClass(i))) {
                    e.preventDefault();
                    
                    QW.vaTimeflag = (new Date).getTime();
                    if(w.parentNode('a').length >0 && w.parentNode('a').hasClass(i)){
                        w = w.parentNode('a');
                    }
                                
                    var $srcData = {
                        'type': i
                        , 'source': w
                        , 'event': e.type
                    };
                                        
                    w.receive('active-done-' + QW.vaTimeflag, function(d){
                        w.replaceClass(i, d.className);
                    });
                    VideoAction[action_map[i]](QW.StringH.evalExp(w.attr('data-action')), w, $srcData);
                    break;
                }
            }
        });
    });
    
    /*心情提示*/
    var w = W('.black-tips');
    W(document).delegate('.BoardBrick .interactive .i-mood-tips', 'mouseover', function(e){
        e.preventDefault();
        var title = W(this).attr('title');
        W(this).attr('title', '').attr('data-title', title);
        w.css('top', (W(this).getXY()[1] - 22) + 'px').css('left', (W(this).getXY()[0] - 4) + 'px');
        W('span', w).html(title);
    }).delegate('.BoardBrick .interactive .i-mood-tips', 'mouseout', function(e){
        e.preventDefault();
        W(this).attr('title', W(this).attr('data-title'));
        w.css('left', '-999em');
    });

    /*提交瀑布流上评论*/
    var post_comment = function(el){   
    
        if( window.VIDEO_COMMENT_LOCK ) return;
                 
        var wp = el.parentNode('.BoardBrick'),
        content = el.val();
        if(!content){
            return;
        }
        
        window.VIDEO_COMMENT_LOCK = true;
        
        QW.use('Ajax', function(){
            VideoAction.userinfo(function(usr){
                QW.Ajax.post(
                    QW.Config.get('host') + '/video/comment',
                    mix(QW.StringH.evalExp(W('.post' ,wp).attr('data-action')), {
                        'content' : content,
                        "format" :"json"
                    }),
                    function(d){
                        window.VIDEO_COMMENT_LOCK = false;
                                            
                        if('ok' == d.err){
                          
                            var el = d.data
                            var _c = W(QW.StringH.format(comment_html, PageUtil.userUrl(usr._id), PageUtil.userAvatarUrl(usr.avatar['30'], 30), QW.StringH.encode4Html(usr.nick), QW.PageUtil.formatUserLinkText(content, el.data[0]['users'], null, 60)));
                            W('.commentBox', wp).insert('afterbegin', _c).removeClass('hideComment');
                            W('.comment_text', wp).val('');
                            W('.put', wp).hide();
                        } else {
                            alert((d.msg && d.msg.content) || d.msg || '抱歉，发生错误，请稍候重试');
                        }
                    },
                    {
                        onerror:
                        function()
                        {
                            window.VIDEO_COMMENT_LOCK = false;
                        }
                    }
                
                    );
            });            
        });
    }
    /*评论html模板*/
    var comment_html = '<dl class="comment cls"><dt><a href="{0}"><img src="{1}"></a><span class=""></span></dt><dd><a href="{0}">{2}</a>：{3}</dd></dl>';    
    /*瀑布流上评论事件*/
    W(document).delegate('.BoardBrick .i-comment', 'click', function(e){
        e.preventDefault();
        
        var $srcData = {
            'type': 'i-comment'
            , 'source': this
            , 'event': e.type
        };
        
        //alert( 1 + ', ' + $srcData );
        
        var _this = this;
        var wp = W(this).parentNode('.BoardBrick');
        VideoAction.userinfo(function(usr){
            var wput = W('.put', wp);
            W('.h', wput).attr('src', PageUtil.userAvatarUrl(usr.avatar['30'], 30));            
            setTimeout(function(){
                W('textarea', wput).first().focus();
            },0);
            wput.show();
            XLogin.ondone();
        }, $srcData);
    }).delegate('.BoardBrick .comment_text', 'focus', function(e){        
        W(this).attr('id', 'comment-focus');        
    }).delegate('.BoardBrick .comment_text', 'blur', function(e){
        var wp = W(this).parentNode('.BoardBrick');
        var _this = this;
        W(this).attr('id', '');
        setTimeout(function(){
            if(!_this.value){
                W('.put', wp).hide();
            }
        }, 200);
    }).delegate('.BoardBrick .post', 'click', function(e){
        var wp = W(this).parentNode('.BoardBrick');        
        post_comment(W('.comment_text', wp));
    }).on('keydown', function(e){
        if(QW.EventH.getCtrlKey() && 13 == e.keyCode){
            if(W('#comment-focus').length){                
                post_comment(W('#comment-focus'));
            }
        }     
    });

    /*瀑布流上视频分享*/
    var doShare = function(target,type){
        target.query('.submit__').on('click', function(e){
            e.preventDefault();
            var _this = this;
            if(type==1){
                XShare.exec({
                    textContent : target.query('.share_text').val(),
                    image : QW.pageGlobal.shareImgae1,
                    type : trim(W(_this).attr('data-sns')), 
                    "shareType": "circle"
                });
                QW.pageGlobal.p1.hide();    
            }
            if(type==2){
                XShare.exec({
                    textContent : target.query('.share_text').val(),
                    image : QW.pageGlobal.shareImgae2,
                    type : trim(W(_this).attr('data-sns')), 
                    "shareType": "video"
                });
                QW.pageGlobal.p2.hide();   
            }  
        });
    };
    /*W('#share_video_popup .submit__').on('click', function(e){
            e.preventDefault();
            var _this = this;
            
            //log
             var share_id = "";
            switch(W(_this).attr('data-sns')){
                case "sina_weibo":
                    share_id = '0';
                    break;
                case "tencent_weibo":
                    share_id = '1';
                    break;
            }
           QW.Marmot.log({
                page_id:'click_share',
                video_id:W("#share_video_popup").attr("vid"),
                share_id:share_id,
                "fuid": window.UID,
                "stype": 'i'
            });
        });*/

    /**
     * 未登录分享窗口
     * 辉哥
     */
    function nologinShare(dataSns,type){
        var feedid = '';
        if( dataSns )
        {
            feedid = dataSns.feedid || '';
        }
        QW.use('Panel', function(){
            var videoData = dataSns;
            var panel_html = trim(W('#share_panel_html').html());
            var image = "";
            if(type == 1){
                var wpanel_1 = W('#share_circle_popup');
                if(!wpanel_1.length){
                    var panel_html_1 = QW.StringH.format(panel_html,'分享圈子','把这个圈子分享给站外好友','share_circle_popup');
                    wpanel_1 = W(panel_html_1);              
                    W('body').appendChild(wpanel_1);
                    wpanel_1.query('.share_text').attr('data-template','我在圈乐网发现了一个有意思的视频圈子——{0}，点击链接来一起看看吧：{1}');
                    QW.pageGlobal.p1 = new Dialog(g('share_circle_popup'));
                } 
                doShare(wpanel_1,1);
                image = videoData.image;
                var circleUrl = videoData.url,
                circleName = videoData.circleName,
                circleContent = QW.StringH.format(wpanel_1.query('.share_text').attr('data-template'), circleName, circleUrl);
                wpanel_1.query('.share_text').val(circleContent);
                //把cid传给弹出层
                wpanel_1.attr("cid",videoData.id);
                QW.pageGlobal.shareImgae1 = image;
                QW.pageGlobal.p1.show();
            }
            if(type == 2){
                var wpanel_2 = W('#share_video_popup');
                if(!wpanel_2.length){
                    var panel_html_2 = QW.StringH.format(panel_html,'分享视频','把这个视频分享给站外好友','share_video_popup');
                    wpanel_2 = W(panel_html_2);               
                    W('body').appendChild(wpanel_2);
                    wpanel_2.query('.share_text').attr('data-template','我在圈乐网 看过【{0}】，点击链接就可以观看：{1}。更多视频，等你发现！');
                    QW.pageGlobal.p2 = new Dialog(g('share_video_popup'));
                }
                doShare(wpanel_2,2);
                image = videoData.image;
                var url = QW.PageUtil.videoPlayUrl(videoData.id) + ["?fuid=", window.UID, "&stype=o", "&feedid=", feedid].join("") ,
                title = trim(videoData.title),
                content = QW.StringH.format(wpanel_2.query('.share_text').attr('data-template'), title, url);
                wpanel_2.query('.share_text').val(content);
                //把vid传给弹出层
                wpanel_2.attr("vid",videoData.id);
                QW.pageGlobal.shareImgae2 = image;
                QW.pageGlobal.p2.show();
            }
        });
    }
    /**
     * 视频分享到第三方浮层
     * */
    /**
     * 分享浮层模型
     * @param Object d
     * @param int type  1 = circle 2 = video
     */
    var ModelSharePop = function(d,type){
        if( d && d.id ){
            var params = {
                "rnd": new Date().getTime(),
                "type": type
            };
            if(type == 1){
                params['cid'] =  d.id;
            }else{
                params['vid'] =  d.id;
                if (d.cid) {
                	params['cid'] =  d.cid;
                }
            }
            var wpanel = W('#sns_share_popup');
            wpanel.attr('data-feed-param', ObjectH.encodeURIJson(params));
            QW.use('Panel', function(){
                QW.Ajax.get('/connect/sharetips', params, function(e){
                    if( e && e.err == "ok" && e.data && e.data.html ){
                        var html = e.data.html;
                        wpanel.html(e.data.html);
                        QW.pageGlobal.p = new Dialog(wpanel[0]);
                        QW.pageGlobal.p.show();

                        function checkWbTxt(obj){
                            var val=obj.value;
                            var len=val.length;
                            //汉字和全角占两个字符
                            var addLen=(val.match(/[^\x00-\xff]|[\u4E00-\u9FA5]/g)||'').length;
                            var num=140-Math.ceil((len+addLen)/2);
                            return num;
                        }
                        W('#sns_share_popup .content').on('keyup',function(e){
                            var wThis = W(this),
                            num = checkWbTxt(wThis[0]);
                            W('#textlength').html(num);
                        }).fire('keyup');
                        
                        W('#sns_share_popup .close__').on('click',function(e){
                            e.preventDefault();
                            QW.pageGlobal.p.hide();
                        });
                        openidShareSubmit();
                    }else{
                        nologinShare(d,type);
                    //LOGIN_POPUP();
                    }
                });

            });
        }
    };
    W(document).delegate('.BoardBrick .sharer, .video_share_', 'click', function(e){
        e.preventDefault();
        var _this = this;
        var d = W(_this).attr('data-sns').trim().evalExp();
        ModelSharePop(d,2);
    });
    /**
     * 圈子分享到第三方浮层
     * */
    W(document).delegate('.share_group','click',function(e){
        e.preventDefault();
        var _this = this;
        //console.log(W(_this).attr('data-action'));
        var d = W(_this).attr('data-action').trim().evalExp();
        ModelSharePop(d,1);
    });
    /**
     * 提交分享内容到第三方
     * */
    function openidShareSubmit(){
        //W(document).delegate('#openidshare', 'submit', function(e){
        W('#openidshare').on('submit', function(e){
            e.preventDefault();
            var _this = this;
            function checkWbTxt(obj){
                var val=obj.value;
                var len=val.length;
                //汉字和全角占两个字符
                var addLen=(val.match(/[^\x00-\xff]|[\u4E00-\u9FA5]/g)||'').length;
                var num=140-Math.ceil((len+addLen)/2);
                return num;
            }
            var shareGroupValid = function(frm){
                var frmels = frm.elements;
                var validType = false,
                valideContent = false,
                validPicurl = false;
                for(var i=0; i< frmels.length; i++){
                    var name = frmels[i].name;
                    var type = frmels[i].type;
                    if(name == 'type[]' && frmels[i].checked){
                        validType =  true;
                    }
                    if(name=='content'){
                        var num = checkWbTxt(frmels[i]);
                        valideContent = num < 0 ? false : true;
                    }
                    if(name == 'picurl' && frmels[i].value.length > 0){
                        validPicurl = true;
                    }
                }
                return {
                    'all' : validType && valideContent && validPicurl,
                    'type' : validType,
                    'content' : valideContent,
                    'picurl' : validPicurl
                };
            };
    	
            var frm = document.openidshare,
            valid = shareGroupValid(frm);
            //console.log(frm);
            //console.log(valid);
            if(valid.all){
            	var feedParam = W('#sns_share_popup').attr('data-feed-param');
                var data = NodeH.encodeURIForm(frm);
                if (feedParam) {
                	data = data + "&feedparam=" + encodeURIComponent(feedParam);
                }
                QW.Ajax.post('/connect/share', data, function(e){
                    if(e.err && e.err == 'ok'){
                        if(QW.pageGlobal.p){
                            QW.pageGlobal.p.hide();
                            QW.pageGlobal.p = new Dialog(g("sns_share_succ_popup"));
                            QW.pageGlobal.p.show();
                            W('#sns_share_succ_popup .close__').on('click',function(e){
                                e.preventDefault();
                                QW.pageGlobal.p.hide();
                            });
                            setTimeout("QW.pageGlobal.p.hide()", 3000);
                        }
                    }else {
                        alert(e.msg || '系统错误，请重试！');
                    }
                });
            }else{
                if(!valid['type']){
                    alert('请先选择要分享到的网站');
                    return;
                }
                if(!valid['content']){
                    var len = frm['content'].value.length,
                    num = 140 - len;
                    W('#textlength').html(num);
                    if(len == 0){
                        alert('分享内容不能为空');
                        return;
                    }
                    if(len > 140){
                        alert('分享内容不能超过140字');
                        return;
                    }
               
                }
                if(!valid['picurl']){
                    alert('分享的图片不合法');
                    return;
                }
            }
            return false;
        });
    }
    /**
     * 选择要分享到的第三方网站
     */
    W(document).delegate('#openidshare .bind-list-bd a', 'click', function(e){
        //TODO 选中
        e.preventDefault();
        var wthis = W(this);
        if(wthis.hasClass('bt-bind-ok')){
            wthis.nextSibling('input')[0].checked = true;
            wthis[0].className = 'bt-bind-slt';
            e.stopPropagation();
            return;
        }
        if(wthis.hasClass('bt-bind-slt')){
            wthis.nextSibling('input')[0].checked = false;
            wthis[0].className = 'bt-bind-ok';
            e.stopPropagation();
            return;
        }
        if(wthis.hasClass('bt-bind-err')){
            var id =  wthis[0].id;
            if(id == 'bind-tqq'){
                tqqBind();
            }
            if(id == 'bind-sina'){
                sinaBind();
            }
            e.stopPropagation();
            return;
        }
        
    });
    /**
     * 绑定提示tips
     */
    var wBindTips = W('.share-bind-tips');
    W(document).delegate('#openidshare .bind-list-bd a', 'mouseenter', function(e){
        //TODO 选中
        e.preventDefault();
        var wthis = W(this),
        thisXY = wthis.getXY(),
        dtitle = wthis.attr('data-title');

        if(wthis.hasClass('bt-bind-ok')){
            var title = '点击后同时分享到' + dtitle;
        }else if(wthis.hasClass('bt-bind-err')){
            var title = '绑定'+ dtitle +'账号，分享给朋友';
        }else{
            var title = '点击后不分享到' + dtitle;
        }
        wBindTips.css('top', (thisXY[1] - 32) + 'px').css('left', (thisXY[0] - 10) + 'px');
        W('.text', wBindTips).html(title);
        wBindTips.show();
    }).delegate('#openidshare .bind-list-bd a','mouseleave',function(e){
        //TODO 选中
        e.preventDefault();
        var wthis = W(this);
        wBindTips.hide();
    });

    W(document).delegate('.BoardBrick, .video_hover_', 'mouseenter', function(e){
        W('.action', W(this)).show();
    }).delegate('.BoardBrick, .video_hover_', 'mouseleave', function(e){
        W('.action', W(this)).hide();
    });

    //创建圈子,会弹出一个层
    function setError(expr, msg){
        W(expr).html(msg);
    }
    //创建圈子与更新圈子时都要做验证
    function validate(form){
        var title = form.title.value;
        var cat = form.cat;
        var tags = form.tags.value;
        if(QW.StringH.trim(title).length == 0 ){
            return setError("#title-msg","圈子名不能为空")
        }
        if( title.length > 32){
            return setError("#title-msg","不超过32个")
        }
        if(cat.selectedIndex == 0){
            return setError("#cat-msg","请选择分类")
        }
        tags = tags.replace(/\，/g,",");//处理全角半角逗号
        form.tags.value = tags;
        var tn = tags.match(/[^, ]+/g) || []

        if(tn.length >= 10){
            return setError("#tags-msg","最多输入10个标签")
        }
    }
    W("body").delegate(".js_creatCircle","click", function( $evt ){
        $evt.preventDefault();
        
        var $srcData = {
            'type': 'creatCircle'
            , 'source': this
            , 'event': $evt.type
        };
    
        Ajax.get("/circle/addcircle",function(json){
            if(json.err == "ok"){
                var dialog = XDialog.exec({
                    "tpl":json.data.html 
                    , "cancelCallback": function(){XLogin.ondone();}
                    , "closeCallback": function(){XLogin.ondone();}
                });
                var target = W(dialog.model.dialog);
                var form = target.query("#add-circle")[0];
                W("select,input", form).on("focus",function(){
                    W(".tips", form).html("");
                });
                function closeDialog(){
                    XLogin.ondone();
                    dialog.view.close()
                }
                target.delegate(".btn_cannel_circle","click",closeDialog)
                target.query(".btn_creat_circle").on("click",function(t){
                    validate(form);
                    QW.Ajax.post( "/circle/addcircle?r="+Math.random(),QW.NodeH.encodeURIForm(form), function(json){
                        if(json.err == "ok"){
                            var chtml ='<div style="height:163px;padding-top:110px;" class="panel-invite-friend fbk_success"><h3 class="title"><span>恭喜您创建成功!</span></h3>';
                            target.query(".bd").html( chtml );
                            target.query(".bt-close").on("click", closeDialog);//立即关闭
                            setTimeout(function(){//	3s后，自动跳转至“我的主页-我的圈子-创建的圈子”页面
                                location.href = json.forward;
                            },1500);
                        }else{
                            for(var i in json.msg){
                                setError("#"+i+"-msg",json.msg[i])
                            }
                        }
                        XLogin.ondone();
                    });
                })
            }else{
                $srcData && window.XLogin && XLogin.storeTriggerData( json, $srcData );
                LOGIN_POPUP()
            }

        })
    });
    //点击瀑布流中的砖头中的删除按钮，调用此方法
    function removeVideoFormCircle(vid, cid, callback){
        QW.Ajax.get( "/circle/removecircledvideo",{
            cid:cid,
            vid:vid
        }, function(json){
            if( json.err == "ok" ){
                callback()
            }else{
                alert(json.msg)
            }
        });
    }
    W("html").delegate(".circle_del","click",function(e){
        e.preventDefault();
        var vid = this.getAttribute("video-id");
        var cid = this.getAttribute("circle-id");
        var self = this;
        removeVideoFormCircle(vid, cid, function(){
            W(self).parentNode(".BoardBrick").removeNode();
        });
    });
    //共用于编辑圈子与视频的页面!
    function getConfirmHTML(str){
        return  '<center><br/></br/>'+ str+
        '<br/></br/><div style="padding-bottom: 61px;">' +
        '<a class="comm_btn-blue in-block js_confirm" href="javascript:void(0);"><span>确认</span></a>&emsp;&emsp;'+
        '<a class="comm_btn-gray in-block js_cancel" href="javascript:void(0);"><span>取消</span></a>'+
        '</div></center>'
    }

    W("html").delegate(".js_del_video","click",function(){
        //获取模板并重置样式与ID
        var cid = this.getAttribute("data-cid");
        var vid = this.getAttribute("data-vid");
        var forward = this.getAttribute("data-forward");
        var context = getConfirmHTML("你确认要删除该视频吗？");
        var tmpl =  W("#edit_video")
        .cloneNode(true)
        .css({
            width:"370px",
            height:"300px"
        })
        .set("id","");
        //修改右上角的按钮样式
        tmpl.query(".js_del_video")[0].className = "close close__"
        //重置里面的内容
        tmpl.query(".hd h3").setHtml("删除视频");
        tmpl.query(".bd").setHtml( context );
        //生成弹出窗口
        var dialog = XDialog.exec({
            "tpl":tmpl[0].outerHTML
        });
        //绑定事件
        var target = W(dialog.model.dialog);
        target.query(".js_cancel").on("click",function(){
            dialog.view.close();
        })
        //确认后删除并跳转
        target.query(".js_confirm").on("click",function(){
            removeVideoFormCircle( vid, cid, function(){
                location.href = forward;
            });
        })
       
    })

    //在http://dev.quanloo.com:8181/user/editcircle?cid=XXX页面中，点击右上边的删除按钮
    W("html").delegate(".js_del_circle","click",function(){
        var cid = this.getAttribute("data-cid");
        var forward = this.getAttribute("data-forward");
        var context = getConfirmHTML("你确认要删除该圈子吗？");
        var tmpl =  W("#edit_circle")
        .cloneNode(true)
        .css({
            width:"370px",
            height:"300px"
        })
        .set("id","");
        //修改右上角的按钮样式
        tmpl.query(".js_del_circle")[0].className = "close close__"
        //重置里面的内容
        tmpl.query(".hd h3").setHtml("删除圈子");
        tmpl.query(".bd").setHtml( context );
        console.log(tmpl[0].outerHTML)
        //生成弹出窗口
        var dialog = XDialog.exec({
            "tpl":tmpl[0].outerHTML
        });
        var target = W(dialog.model.dialog);
        target.query(".js_cancel").on("click",function(){
            dialog.view.close();
        })
        target.query(".js_confirm").on("click",function(){
            QW.Ajax.post( "/user/removecircle",{
                cid:cid
            }, function(json){
                location.href = forward;
            });
        })
    });

    W("html").delegate(".circle_edit","click",function(e){
        e.preventDefault();
        var vid = this.getAttribute("video-id");
        var cid = this.getAttribute("circle-id");
        location.href = "/circle/editvideo?circle=" +cid+"&video="+vid;
    })
    Dom.ready(function(){
        //完成按钮
        //http://dev.quanloo.com:8181/user/editcircle?cid=15386
        W("#edit_circle_btn").on("click",function(){
            var form = W("#add-circle")[0];
            validate(form);
            QW.Ajax.post( "/user/editcircle",QW.NodeH.encodeURIForm(form), function(json){
                if(json.err =="ok"){
                    location.href = json.forward;
                }else{
                    for(var i in json.msg){
                        setError("#"+i+"-msg",json.msg[i])
                    }
                }
            });

        })
    });

  
    //圈一下，用于首页瀑布流中的砖头听“圈一下”功能与编辑视频页面，
    //如：http://dev.quanloo.com:8181/circle/editvideo?circle=15546&video=ab081949cc214f9cb95655739e3ad86a###
    //==============================================================
    //==============================================================
    //==============================================================
    function circleDown(target){
        var selectHasShow = false;
        //点击这个伪下拉开框展开真正的下拉框
        target.query(".circle_first").on("click",function(){
            W(".circle_first").hide().nextSibling("div").show();//隐藏自身，再显示下拉框
            target.query(".tips").html("");
            selectHasShow = true;
        });
        var cidInput = target.query("[name=cid]")[0];
        //重置视频的圈子ID，并收起UI列表
        function resetCid(title, id){
            if( id ){
                cidInput.value = id;
                selectHasShow = false;
                title = title.length > 10 ?title.slice(0, 10)+"...":title
                target.query(".circle_first span").setHtml(title);
            }
            target.query(".circle_first").show().nextSibling("div").hide();
        }
        //当点击面板的其他位置收起下拉框
        target.on("click",function(e){
            if(selectHasShow){//下拉框必须展开
                var check = W(e.target).parentNode(".options").length;
                //如果是在下拉开框内点击或是在.circle_first上点击，不理会
                if(check || /show-select|circle_first/.test(e.target.className) )
                    return
                //如果是在下拉框的其他地方点击
                resetCid();
            }
        });
        //将这个视频加入到选中的圈子中
        //当它获得焦点时清空
        target.delegate("#new-circle","keydown", function(){
            setError(".lot_r .tips","")
        })
        //迅速创建圈子
        target.delegate(".layout_submit","click",function(){
            var title = target.query("#new-circle")[0].value;
            if(QW.StringH.trim(title).length == 0 ){
                return setError("#title-msg","圈子名不能为空")
            }
            if( title.length > 32){
                return setError("#title-msg","不超过32个")
            }
            QW.Ajax.post( "/circle/quickaddcircle",{
                title:title
            }, function(json){
                if(json.err == "ok"){//刷新UI列表，将新圈子的ID赋给隐藏域cid中，此时还收起UI列表
                    W(".options").setHtml(json.data.circle_list + W(".circle_list_ft")[0].outerHTML);
                    var cur = json.data.current_circle;
                    resetCid( title, cur._id);
                    title = title.length > 10? title.slice(0,10)+"...":title
                    target.query(".circle_first span").setHtml(title);
                }else{
                    setError("#title-msg",json.msg.title)
                }
            });
        });
        //第一次点击移除circle_placeholder，实现IE下的placeholder
        console.log("lllllllll")
        target.query(".circle_placeholder").on("focus", function fn(){
            W(this).removeClass("circle_placeholder").set("value","").un("focus", fn)
        });
        //点击下拉框的选框，让里面的LI元素移到正确的位置（与.show-select元素具有相同内容的LI元素移到可视区）
        target.delegate(".circle_list a","click",function(){
            var id = this.getAttribute("cid");
            W(".selectItem").removeClass("selectItem")
            var title = this.innerHTML;
            var li = this.parentNode;
            var ul = li.parentNode;
            li.className = "selectItem";
            for(var i = 0; i < ul.children.length; i++){
                if(ul.children[i] == li){
                    break;
                }
            }
            var height = parseInt(W(li).css("height")) || 25
            ul.scrollTop = i * height;
            resetCid( title, id);
        });
    }
    //==============================================================
    //==============================================================
    //==============================================================
    //点击圈一下功能
    W("body").delegate(".circle_down","click", function(e){
        e.preventDefault();
        
        var $srcData = {
            'type': 'circle_down'
            , 'source': this
            , 'event': e.type
        };
        
        Ajax.get("/circle/circleone", { vid:this.getAttribute("video-id") },function(json){
            
            if(json.err == "ok"){
                var dialog = XDialog.exec({
                    "tpl":json.data.html
                });
                var target = W(dialog.model.dialog);
                var form = target.query("form")[0];
                circleDown(target)
                function closeDialog(){
                    dialog.view.close()
                }
     
                //将这个视频加入到选中的圈子中
                //点击圈下来按钮
                target.delegate(".btn_circle_down","click",function(){
                    var msg = "请选择圈子"
                    if(!parseFloat(form.cid.value) ){
                        return setError(".lot_r .tips",msg)
                    }
                    target.query(".circle_placeholder").fire("focus");
                    if( form.note.length > 32){
                        return setError("#title-msg","圈子描述字数应在0~100之间")
                    }

                    QW.Ajax.post( "/circle/circleone",QW.NodeH.encodeURIForm(form), function(json){
                        if(json.err == "ok"){
                            //拼装一个成功页面
                            var chtml ='<div style="height:163px;padding-top:110px;"'+
                            ' class="panel-invite-friend fbk_success"><h3 class="title"><span>圈下来了！</span></h3></div>';
                            target.query(".bd").html( chtml );
                            setTimeout(closeDialog,1500);//请稍候3秒钟后自动关闭
                        }else{
                            return setError(".lot_r .tips",json.msg || msg)
                        }
                    });
                });
               
                //点击取消按钮
                target.delegate(".btn_cannel_circle","click",function(){
                    closeDialog()
                });
                
                XLogin.ondone();
            }else{
                $srcData && window.XLogin && XLogin.storeTriggerData( json, $srcData );
                LOGIN_POPUP()
            }
        })
    });

    Dom.ready(function(){
        if(W("#edit_video").length){
            var target  = W("html");
            circleDown(target);
            W("#edit_video_btn").on("click",function(){
                var form = W("#edit_video_form")[0];
                var msg = "请选择圈子"
                if(!parseFloat(form.cid.value) ){
                    return setError(".lot_r .tips",msg)
                }
                if( form.note.length > 32){
                    return setError("#title-msg","圈子描述字数应在0~100之间")
                }
                QW.Ajax.post( "/circle/editvideo",QW.NodeH.encodeURIForm(form), function(json){
                    if(json.err == "ok"){
                        //拼装一个成功页面
                        location.href =  json.data.url;           //form.old_cid
                    }else{
                        return setError(".lot_r .tips",json.msg || msg )
                    }
                });
              
            })
        // /circle/editvideo?circle=15529&video=d24c2f0adcf3ba70ec238b1213a1e34c&new_circle=15529&note=newnewnote
        }
    })
});

/**
 * 第三方登录
 */
var openWind = function(url){
    var top=(document.body.clientHeight-420)/2;
    var left=(document.body.clientWidth-520)/2;
    window.open(url,'connect_window', 'height=420, width=560, toolbar =no, menubar=no, scrollbars=yes, resizable=no,top='+top+',left='+left+', location=no, status=no');
},
sinaLogin=function(){
    openWind('/connect/?type=2');
},
tqqLogin = function() {
    openWind('/connect/?type=3');
},
bindEvent = function(bindtype){
    openWind('/connect/?type='+bindtype);
    W('#sns_bind_popup .bindtype').forEach(function(el){
        var type = W(el).attr('type');
        type = parseInt(type, 10);
        W(el).hide();
        if(type == bindtype){
            W(el).show();
        }
    });
    W('#sns_bind_popup .retry').attr('bindtype',bindtype);
    W('#sns_bind_popup .isok').attr('bindtype',bindtype);
    if(!QW.pageGlobal.p2){
        QW.pageGlobal.p2 = new Dialog(g("sns_bind_popup"));
        QW.pageGlobal.p2.modal = false;
    }
    QW.pageGlobal.p2.show();
    return;
},
sinaBind = function(){
    bindEvent(2);
    return false;
},
tqqBind = function(){
    bindEvent(3);
    return false;
}; 
W(document).delegate('#sns_bind_popup .retry','click',function(e){
    var bindtype = W(this).attr('bindtype');
    bindtype = parseInt(bindtype,10);
    e.preventDefault();
    if(bindtype==2){
        window.sinaBind();
        return;
    }
    if(bindtype==3){
        window.tqqBind();
        return;
    }
});
W(document).delegate('#sns_bind_popup .isok','click',function(e){
    e.preventDefault();
    var bindtype = W(this).attr('bindtype');
    bindtype = parseInt(bindtype,10);
    //console.log('isok'+bindtype);
    QW.Ajax.get('/connect/checkbind',{
        'type':bindtype,'time': new Date().getTime()
    },function(ret){
        if(ret.err === 'ok'){
            if(/user\/syncconnect/.test(document.URL)){
                window.location.reload();
            }else{
                if(bindtype==2){
                    W('#bind-sina')[0].className = 'bt-bind-slt';
                    W('#bind-sina')[0].parentNode.getElementsByTagName('input')[0].checked = true;
                }
                if(bindtype==3){
                     W('#bind-tqq')[0].className = 'bt-bind-slt';
                     W('#bind-tqq')[0].parentNode.getElementsByTagName('input')[0].checked = true;
                }
            }
        }else{
            
            QW.pageGlobal.p3 = new Dialog(g("sns_bind_err_popup"));
            QW.pageGlobal.p3.modal = false;
            QW.pageGlobal.p3.show();
        }
        QW.pageGlobal.p2.hide(); 
    });
});
W(document).delegate('#sns_bind_err_popup .close__','click',function(e){
     e.preventDefault();
     QW.pageGlobal.p3.hide();
});
/**
 * 第三方登录
 */
var openWind = function(url){
    var top=(document.body.clientHeight-420)/2;
    var left=(document.body.clientWidth-520)/2;
    window.open(url,'connect_window', 'height=420, width=560, toolbar =no, menubar=no, scrollbars=yes, resizable=no,top='+top+',left='+left+', location=no, status=no');
},
sinaLogin=function(){
    openWind('/connect/?type=2');
},
tqqLogin = function() {
    openWind('/connect/?type=3');
},
qqLogin=function(){
    openWind('/connect/?type=6');
},
doubanLogin = function() {
    openWind('/connect/?type=5');
},
renrenLogin=function(){
    openWind('/connect/?type=4');
};