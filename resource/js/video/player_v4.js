/**
 * 播放页 JS 交互
 * x@btbtd.org   2012/4/25
 */
 
Dom.ready
(
    function()
    { 
        //PLAY_NEXT();

        COMMENT_HOVER();
        COMMENT_TAB();
        COMMENT_RECOUNT();
        INIT_ALL_FOCUS();
        searchSug2();
        monitarSug();
        
        /**
         * 圈内视频  - 划动菜单
         */                 
        XItemSlider.exec
        (
            {
                prev: 'circleslidePrev',
                next: 'circleslideNext',
                list: 'circleslideList',
                
                "fixPosition": true,
                "itemWidth": 102,
                "mainWidth": 562
            }
        );
        
        /**
         * 相关视频  - 划动菜单
         */                 
        XItemSlider.exec
        (
            {
                prev: 'relatedslidePrev',
                next: 'relatedslideNext',
                list: 'relatedslideList',
                
                "fixPosition": true,
                "itemWidth": 102,
                "mainWidth": 562
            }
        );
        
        W('a.add_watch_later__, a.remove_watch_later__').on
        (
            "click"
            , function( $evt )
            {
                $evt.preventDefault();
                //$evt.stopPropagation();
                
                var p = W(this);
                var id = p.attr('data-id');
                
                if( p.hasClass('add_watch_later__') )
                {                
                    if( id ) ADD_WATCH_LATER( id, function( $d ){
                    
                        if( $d.err == 'ok' )
                        {
                            p.addClass('added');
                            p.removeClass('add_watch_later__');
                            p.addClass('remove_watch_later__');
                        }
                    } );
                    
                }
                else if( W(this).hasClass('remove_watch_later__') )
                {
                    if( id ) REMOVE_WATCH_LATER( id, function( $d ){   
                    
                        if( $d.err == 'ok' )
                        {
                            p.removeClass('added');
                            
                            p.addClass('add_watch_later__');
                            p.removeClass('remove_watch_later__');  
                        }              
                    });
                }
                
                return false;
            }
        );  
        
        W( 'a.share_' ).on
        (
            'click',
            function( $evt )
            {
                $evt.preventDefault();
            
                var type = W(this).getAttr('data-sns').trim();              
                var content = W('#share_txa').val().trim();
                
                XShare.exec( { "type": type, "textContent": content, "image": window.VIDEO_THUMB||'', "shareType": "video" } ); 
                
                return false;
            }
        );
        
        VIDEO_COMMENT();
        

        PLAYER.moodevent();
        
        W('#comment_tab li.xmood').on
        (
            "click",
            function()
            {
                LOAD_MOOD();
            }
        );
    }
);
/**
 * 播放页, 登陆后的回调操作
 */
function PLYAER_LOGINED_CALLBACK( $loginedData )
{
    
} 
/**
 * 大家都在看
 */ 
function INIT_ALL_FOCUS()
{
    var box = W('#all_focus');
    
    if( !box.length ) return;
    
    var page_count_ = box.query('label.page_count_');
    var next_ = box.query('a.next_');
    var prev_ = box.query('a.prev_');
    
    var list = box.query('div.content > ul');
    
    if( !list.length ) return;
    
    var pointer = 0;
    
    list.hide();
    W(list[0]).show();
    update_status();
    
    prev_.on
    (
        'click', 
        function( $evt )  
        {
            $evt.preventDefault();
            
            var p = W(this);
            if( p.hasClass('mprev-dis') ) return;
            
            list.hide();
            pointer--;
            
            W( list[pointer] ).show();            
            inner_ajax( list[pointer] );
            
            update_status();
        }
    );
    
    next_.on
    (
        'click', 
        function( $evt )  
        {
            $evt.preventDefault();
            
            var p = W(this);
            if( p.hasClass('mnext-dis') ) return;
            
            list.hide();
            pointer++;
            
            W( list[pointer] ).show();            
            inner_ajax( list[pointer] );
            
            update_status();
        }
    );
    
    function inner_ajax( $obox )
    {
        var obox = $obox;
        var sbox = W( obox );
        
        if( !obox.LOCK && sbox.attr('data-url') )
        {
            obox.LOCK = true;
            
            QW.Ajax.get
            (
                sbox.attr('data-url')
                , { "rnd": Math.random() }
                        
                , function( $d )
                {       
                    if( typeof $d == 'object' )
                    {
                        if( $d.err == 'ok' )
                        {
                            sbox.html( $d.data );
                        }
                        else
                        {
                            obox.LOCK = false;
                        }
                    }
                    else if( typeof $d == 'string' )
                    {
                        sbox.html( $d );
                    }   
                }
                        
                , {
                    onerror:
                    function()
                    {
                        obox.LOCK = false;
                    }
                }
            );
        }
    }
    
    function update_status()
    {        
        var pos_pointer = pointer + 1;
    
        page_count_.html( pos_pointer + ' / ' + list.length )
    
        if( list.length > 1 )
        {
            if( pos_pointer <= 1 )
            {
                prev_.addClass( 'mprev-dis' );
                prev_.removeClass( 'mprev' );
                
                next_.removeClass( 'mnext-dis' );
                next_.addClass( 'mnext' );
            }
            else
            {
                prev_.removeClass( 'mprev-dis' );
                prev_.addClass( 'mprev' );
                
                next_.removeClass( 'mnext-dis' );
                next_.addClass( 'mnext' );
            }
            
            if( pos_pointer >= list.length )
            {
                prev_.removeClass( 'mprev-dis' );
                prev_.addClass( 'mprev' );
                
                next_.addClass( 'mnext-dis' );
                next_.removeClass( 'mnext' );  
            }
        }
        else
        {
            prev_.addClass( 'mprev-dis' );
            prev_.removeClass( 'mprev' );
            
            next_.addClass( 'mnext-dis' );
            next_.removeClass( 'mnext' );
        }
    }
}
/**
 * 加载心情
 */ 
function LOAD_MOOD()
{
    QW.Ajax.get
    (
        '/video/moods'
        , { "id": VIDEO_ID }
                
        , function( $d )
        {       
            var box = W('#comment_tab div.mood_content');
            if( $d.err == 'ok' )
            {
                if( box.length )
                {
                    box.html( $d.data.data );
                }
            }
        }
                
        , {
            onerror:
            function()
            {
            }
        }
    );
}
/**
 * 显示心情 及 TIPS
 */ 
var PLAYER = {
	mood : function($data, $src, $srcData){  
	    argu = [].slice.call(arguments);
	    var moodMap = {
	        xh : {
	            txt : '喜欢',
	            face : 'xh_mood'
	        },
	        wg : {
	            txt : '围观',
	            face : 'wg_mood'
	        },
	        dx : {
	            txt : '大笑',
	            face : 'dx_mood'
	        },
	        fn : {
	            txt : '鄙视',
	            face : 'fn_mood'
	        },
			jn : {
				txt : '囧',
	            face : 'jn_mood'
			}
	    };
	    VideoAction.send('/video/mood', ['id', 'mood', 'circle'], argu, function(d){
            
            if( d.err == "sys.permission.need_login" )
            {
                $srcData && window.XLogin && XLogin.storeTriggerData( d, $srcData );
                LOGIN_POPUP();
                return;
            }
        
            if('ok' == d.err || 'video.already_mooded' == d.err){
            
                W('#comment_action .xmood span').attr('className', '');
                W('#comment_tab li.xmood').fire('click');
            
            	VideoAction.userinfo(function(userdata){
            		var _m = argu[0].mood;
            		g(argu[1].parentNode()).className = 'on_' + moodMap[_m].face;
                });
                
                if( 'video.already_mooded' == d.err )
                {
                    DISPLAY_MESSAGE( getMessage(d.msg), 2, 'comment_tips' );
                }
                else if( 'ok' == d.err )
                {
                    DISPLAY_MESSAGE( '标记心情成功!', 2, 'comment_tips' );
                }
                
                XLogin.ondone();
            }
        });
	},
	moodevent : function(){
		var _this = this;
		W('.i-mood2').on('click',function(e){
			e.preventDefault();
            
            var $srcData = {
                'type': 'player_mood'
                , 'source': this
                , 'event': e.type
            };
            
			var w = W(this);
			_this.mood(QW.StringH.evalExp(w.attr('data-action')), w, $srcData);
		});
	}
};
function COMMENT_HOVER()
{
    var xtips_ = W('#xtips_');    
    var comment_action = W('#comment_action');
    
    if( !(xtips_.length&&comment_action.length) ) return;
    
    var moods = comment_action.query( '.xmood a' );
    
    moods.on
    (
        "mouseenter",
        function()
        {
            var p = W(this);
            var tit = p.attr('data-title');
            if( !tit ) return;
            
            xtips_.show();
            xtips_.query('.xtips_m').html( tit ); 
            
            var pos = p.getXY();
            
            var l = pos[0] - 6 + 'px';
            var r = pos[1] - 25 + 'px';
            
            xtips_.css
            (
                {
                    "left": l,
                    "top": r
                }
            );
            
            //document.title = l + ', ' + r;
        }
    )
    .on
    (
        "mouseleave",
        function()
        {
            xtips_.hide();
        }
    );
}

/**
 * 视频评论
 * x@btbtd.org  2012/4/25 
 */ 
function VIDEO_COMMENT()
{
    var comment_form = W('#comment_form');
    
    if( !comment_form.length ) return;
    
    COMMENT_LENGTH = 200;
    COMMENT_TPL =
    [
    "<li class=\"clearfix\">\n"
    ,"    <dl><dt><a href=\"/user/{id}\" target=\"_blank\"><img src=\"{avatar}\" class='ava_popup_' data-id='{id}'></a></dt>\n"
    ,"        <dd>\n"
    ,"            <div class=\"label\">\n"
    ,"                <div class=\"sleft\">\n"
    ,"                    <a href=\"/user/{id}\" class=\"name ava_popup_\" data-id='{id}' target=\"_blank\">{name}</a>\n"
    ,"                 </div>\n"
    ,"                <div class=\"sright\">\n"
    ,"                    <span class=\"datetime\">{time}</span>\n"
    ,"                </div>\n"
    ,"            </div>\n"
    ,"            <div class=\"content\">{content}</div>\n"
    ,"        </dd>\n"
    ,"    </dl>\n"
    ,"</li>\n"
    ].join('');
    
    var submit_btn = comment_form.query('.bt-submit');
    var text_area = comment_form.query( 'textarea' );
    var no_comment = W('#no_comment');
    var comment_list = W('#comment_list');
    
    controlTip( text_area[0], '说点什么, 可以@好友' );
    
    comment_form.on
    (
        "submit",
        function( $evt )
        {
            $evt.preventDefault();
            $evt.stopPropagation();
            
            return false;
        }
    );
    
    submit_btn.on
    (
        'click',
        function( $e )
        {
            $e.preventDefault();
            $e.stopPropagation();
            
            if( window.PLAYER_COMMNET_LOCK ) return;
            
            var content = text_area.val().trim();
            var controltip = text_area.attr('controltip') || '说点什么';
            
            if( !content || content == controltip)
            {
                DISPLAY_MESSAGE( '请说几句再提交内容!', 2, 'comment_tips' );
                return false;
            }
            
            if( content.length > COMMENT_LENGTH )
            {
                DISPLAY_MESSAGE( '评论内容不能超过' + COMMENT_LENGTH + '字', 2, 'comment_tips' );
                return false;
            }

            var $srcData = {
                'type': 'video_comment'
                , 'source': this
                , 'event': $e.type
            };
            
            window.PLAYER_COMMNET_LOCK = true;
            
            QW.Ajax.post
            (
                '/video/comment', 
                { "id": VIDEO_ID, "content": content, "circle": CIRCLE_ID, "rnd": Math.random(), "format": "json" }
                , function( $d )
                {
                    window.PLAYER_COMMNET_LOCK = false;
                
                    $d = $d || {};
                    
                    var msg = getMessage($d.msg);
                    
                    if( $d.err == 'ok' )
                    {
                        text_area.val('');
                        try{ text_area.fire('input'); }catch(ex){}
                        try{ text_area.fire('propertychange'); }catch(ex){}
                        no_comment.hide();
                        
                        var result = [];
                                
                        for( var i = 0; i < $d.data.data.length; i++ )
                        {
                            var item = $d.data.data[i];
                            
                            var temp = COMMENT_TPL;
                            
                            if( item.user_id === 0 )
                            {
                                temp = 
                                [
                                "<li class=\"msg-item\">\n"
                                ," <img src=\"{avatar}\" alt=\"\" class=\"pic\" />\n"
                                ,"	<div class=\"msg\">\n"
                                ,"		{name}n"
                                ,"		<div class=\"con\">{content}</div>\n"
                                ,"	</div>\n"
                                ,"</li>\n"
                                ].join('');
                            }
                           
                            var url = '/user/'+item.user_id;                           
                            temp = temp
                                .replace( /\{avatar\}/gi, (item.avatar||'"user.png') )
                                .replace( /\{name\}/gi, item.nick.encode4Html() )
                                .replace( /\{content\}/gi, QW.PageUtil.formatUserLinkText(item.data, item.users) )
                                .replace( /\{id\}/gi, item.user_id )
                                .replace( /\{time\}/gi, item.create_time_str)
                                .replace( /\{url\}/gi, url )
                                ;
                            
                            result.push( temp );
                            
                            //alert( QW.PageUtil.formatUserLinkText(item.data, item.users) )
                        }
                        
                        comment_list.html( result.join('') + comment_list.html() );
                        W('#comment_tab li.cmt').fire('click');
                        DISPLAY_MESSAGE( '你的评论已添加.', 1, 'comment_tips' );
                        
                        W('#more_comment').show();
                        
                        XLogin.ondone();
                    }
                    else
                    {
                        if( $d.err == "sys.permission.need_login" )
                        {
                            $srcData && window.XLogin && XLogin.storeTriggerData( $d, $srcData );
                            LOGIN_POPUP();
                            return;
                        }
                        DISPLAY_MESSAGE( msg || '评论发送失败，请重试!', 2, 'comment_tips' );
                    }
                    
                    var fuid = get_url_param( location.href, "fuid" );
                    var feedid = get_url_param( location.href, "feedid" ) || 0;
                    
                    if( fuid && fuid != "0" )
                    {
                        var stype = get_url_param( location.href, "stype" );
                    
                        QW.Marmot.log({"page_id": "click_comment", "fuid": fuid, "feedid": feedid, "stype": (stype||'i')} );
                    }                    

                }
                
                , {
                    onerror:
                    function()
                    {
                        window.PLAYER_COMMNET_LOCK = false;
                        DISPLAY_MESSAGE( '网络连接中断，请稍后重试!', 2, 'comment_tips' );
                    }
                }
            );
            
            
            return false;
        }
    );
    
    text_area.on
    (
        "keypress",
        function( $e )
        {
            //document.title = $e.keyCode;
        
            if( $e.keyCode === 10 )
            {
                submit_btn.fire( 'click' );
            }
        }
    );
    
    var more_comment = W('#more_comment');
    var MORE_COMMENT_LOCK = false;
    if( more_comment.length )
    {
        more_comment.on
        (
            "click",
            function( $evt )
            {
                if( MORE_COMMENT_LOCK ) return;
                
                MORE_COMMENT_LOCK = true;
                
                var offset = comment_list.query('> li').length;
                var comment_page = 10;
                
                // http://dev.quanloo.com:8181/video/comments?id=e292d41edff3e9ca24bef35dbd768f66&offset=0&count=16&template=comments2
                QW.Ajax.get
                (
                    '/video/comments'
                    , { "id": window.VIDEO_ID, "offset": offset, "count": comment_page, "template": "comments2"
                        ,"rnd": Math.random() 
                    }                            
                    , function( $d )
                    {       
                        if( $d && $d.err == "ok" && $d.data && $d.data.count && $d.data.data )
                        {
                            var temp = W("<ul></ul>");
                            temp.html( $d.data.data);
                            
                            temp.query( '> li' ).forEach
                            (
                                function( $ele )
                                {
                                    comment_list.appendChild( $ele );
                                    INIT_COMMENT_HOVER( W($ele) );
                                }
                            );
                        }
                        else
                        {
                        	W('#more_comment').hide();
                            DISPLAY_MESSAGE( '没有更多评论了!', 2, 'no_more_comment' );
                        }
                        if (! $d.data.has_more) {
                        	W('#more_comment').hide();
                        }
                        
                        MORE_COMMENT_LOCK = false;
                    }
                            
                    , {
                        onerror:
                        function()
                        {
                            MORE_COMMENT_LOCK = false;
                        }
                    }
                );
            }        
        );
    }
}
/**
 * 计算评论字数
 */ 
function COMMENT_RECOUNT()
{
    var comment_form = W('#comment_form');
    var comment_recount = W('#comment_recount');  
    var text_area = comment_form.query('textarea');
    
    if( !(comment_form.length && comment_recount.length && text_area.length) ) return;
    
    var COMMENT_LENGTH = 200;
    
    onpropertychange_f( text_area, calc );
    
    function calc( $evt )
    {
        var content = text_area.val().trim();
        
        if( content.length <= COMMENT_LENGTH )
        {
            var attr = text_area.attr('controltip');
            
            var len = content.length;
            
            if( attr == content )
            {
                len = 0;
            }
        
            comment_recount.html( '还可以输入<b>'+ (COMMENT_LENGTH - len) +'</b>字' );
        }
        else
        {
            comment_recount.html( '已经超出<b class="red">'+ (content.length - COMMENT_LENGTH) +'</b>字' );
        }
    }
    
    text_area.on
    (
        "focus",
        function( $e )
        {
        	calc();
        }
    );
    
    calc();
}
function DISPLAY_MESSAGE( $msg, $type, $box )
{
    XRemind.exec
    (
        {
            "type": $type,
            "msg": $msg,
            "injectCallback":
            function( $e )
            {
                $box = document.getElementById($box);
                if( $box )
                {
                    W($box).html('');
                    $box.appendChild( $e );
                }
            }
        }
    );
}

/**
 * 播放到下个视频
 * x@btbtd.org  2012/6/4 
 */ 
function PLAY_NEXT()
{
    return;
    
    var box = W('#asidetabcon');
    if( !box.length ) return;
    
    var list = box.query('a.next_play_');
    if( !list ) return;
    
    var vid = VIDEO_ID;
    
    var finded;
    
    list.forEach
    (
        function( $ele )
        {
            var svid = W($ele).attr('data-vid');
            
            if( !svid ) return;
            svid = svid.trim();  
            
            if( vid == svid )
            {
                finded = $ele;
                return false;
            } 
        }
    );
    
    if( !finded )
    {
        finded = list[0];
    }
    
    W(finded).fire('click');
}
/**
 * 播放结束回调
 */
function PLAY_END_ACTION(opt)
{
    PLAY_NEXT();
    
    var video_box_ = W('#video_box_');
    
    if( !video_box_.length ) return;
    
    var getParam = { "id": window.VIDEO_ID||'', "circle": window.CIRCLE_ID||'', "referer":window.REFERER || '', "rnd": Math.random() };
    if (opt) {
    	QW.ObjectH.mix(getParam, opt);
    }
    QW.Ajax.get
    (
        '/video/player_finish'
        , getParam                
        , function( $d )
        {
            if( $d && $d.err == 'ok' )
            {
                video_box_.html( $d.data );
                init_event();
            }
            else
            {
                //alert('内部错误!');
            }
        }
                
        , {
            onerror:
            function()
            {
                //alert('网络失败');
            }
        }
    );
    
    function init_event()
    {
        var end_list_ = W('#end_list_');
        if( !end_list_.length ) return;
        
        var pre_page_ = W('a.pre_page_');
        var next_page_ = W('a.next_page_');
        
        if( !(pre_page_.length || next_page_.length ) ) return;
    
        var items = end_list_.query('> li');
        var list_len = items.length;
        var page_len = 3;
        
        var ids = [];
        
        items.forEach
        (
            function($ele)
            {
                if( W($ele).attr('data-id') )
                {
                    ids.push( W($ele).attr('data-id') );
                }
            }
        );
            
        QW.Marmot.log({
            "page_id": "recommendation"
            , "rec_zone": end_list_.attr('data-rec-zome') 
            , "item_list": ids.join()
            //, "user_id": window.UID || ''
        });
        
        if( list_len <= page_len ) 
        {
            pre_page_.hide();
            next_page_.hide();
            return
        }
        
        /**
         * 最受欢迎 - 垂直划动
         */         
        XItemSlider.vexec
        (
            {
                prev: 'end_list_pre',
                next: 'end_list_next',
                list: 'end_list_',
                
                "fixPosition": true,
                "itemHeight": 100,
                "mainHeight": 330
            }
        );
        
    }
} 
/**
 * 评论TAB
 */ 
function COMMENT_TAB()
{
    var box = W('#comment_tab');
    if( !box.length ) return;
    
    var tabs = box.query( '> ul.xhd > li' );
    
    tabs.on
    (
        "click",
        function()
        {
            var for_ = W(this).attr('data-for');
            
            if( !for_ ) return;
            
            tabs.removeClass('on');
            W(this).addClass('on');
            
            var list = box.query("> .list_item");
            list.hide();
            
            box.query("> ."+for_).show();
        }
    );
    
    box.query( '.comment_content ul > li' ).forEach
    (
        function( $le )
        {
            INIT_COMMENT_HOVER( W($le) );
        }
    );
    
}
/**
 * 评论列表 hover 状态
 */ 
function INIT_COMMENT_HOVER( $li )
{
    var box = W('#comment_tab');
    if( !box.length ) return;
    
    $li = W($li);
    $li.on
    (
        "mouseenter",
        function()
        {
            //box.query( '.comment_content ul > li' ).removeClass('on');
            W(this).addClass('on');
        }
    )
    .on
    (
        "mouseleave",
        function()
        {
            W(this).removeClass('on');
        }
    );
}


/**
 * 播放器 播放结束回调-----------------------------------------------------------
 */ 
/**
 * 播放结束回调 - 土豆
 */ 
function playEnd()
{
    PLAY_END_ACTION();
}
/**
 * 播放结束回调 - 优酷
 */ 
function onPlayerComplete()
{
    PLAY_END_ACTION();
}
/**
 * 播放结束回调 - 新浪\激动\搜狐 无内嵌
 */ 
function playCompleted()
{
    PLAY_END_ACTION();
}
/**
 * 播放结束回调 - 56
 */ 
function s2j_onPlayOver()
{
    PLAY_END_ACTION();
}
/**
 * 播放结束回调 - ku6 前提是参数为api=1
 */ 
function playFinish()
{
    PLAY_END_ACTION();
}
//youku播放失败回调
function onPlayerError(){
	PLAY_END_ACTION({reqtype:'playererror'});
}
//56播放失败回调
function s2j_onPlayError(){
	PLAY_END_ACTION({reqtype:'playererror'});
}
/**
 * 如果播放页是异步登陆, 则有可能SUG不起作用
 * 该函数修复这个问题 
 */ 
function monitarSug()
{
    W('#search-text2').attr('aj_detect', 1);
 
    W(document).delegate
    (
        "input.search-frame2", "click",
        function( $evt )
        {
            var p = W(this);
            
            if( !p.attr('aj_detect') )
            {
                p.attr('aj_detect', 1);
                searchSug2();
            }
        }
    );
}

/*搜索提示emment*/
function searchSug2(){
    /*suggest*/
    QW.use('Suggest', function(){
        var sug = new QW.Suggest({
            textbox: '#search-text2',
            autoFocusFirstItem: false,
            dataUrl: QW.Config.get('suggest_uri') + "?r="+Math.random(),
            uiItemNumber: 10,            
            uiReferEl: '.search-wd',
            uiBaseLayerConfig:{
                autoPosition: false,
                left:0,
                top:0
            },
            uiRender:function(oData){
                var isFirst = this._prop.items.length==0;
                var fmtfun = formatSugItem;
                var hasInfo = oData.info.length>0?true:false;
                var str = '<div class="item"><span class="key '+(hasInfo?'haschild':'')+'">'+ QW.StringH.encode4Html(oData.display) +'</span>';
                   /* str += '<ul class="info" style="display:none;">';
                    str += (function(info){
                                if(!hasInfo)return '';
                                var itemString = "";
                                info.forEach(function(dat){
                                   itemString += fmtfun(dat);
                                });
                                return itemString;
                            })(oData.info);
                    str += '</ul>';*/
                    str += '</div>';
                var itemQw = W(str);
                itemQw.on('mouseover', function(){
                    var panelLayer = W(this).parentNode('div').parentNode('div');
                        panelLayer.query('ul').hide();
                    W(this).query('ul').show();
                });
                return itemQw;
            },
            uiHighlighter:function(oEl,i){
                var isNullKey = sug.get('keyword')=='.toplist';
                var elKeyEl = QW.NodeW(oEl).query('.key').core[0];
                var sHtml = elKeyEl.innerHTML;
                var _key = sug.getKeyword();
               
                var _index = sHtml.indexOf(_key);
                if(isNullKey){//空key显示排行榜
                    var istr = '<b class="b-index">'+(i==9?'':'&nbsp;')+(i+1)+'. </b>';
                    elKeyEl.innerHTML = istr+sHtml;
                }else if(_index!=-1){
                    var _a = sHtml.slice(0,_index);
                    var _b = sHtml.slice(_index + _key.length);
                    if(_a!="")_a = '<b>'+_a+'</b>';
                    if(_b!="")_b = '<b>'+_b+'</b>';
                    var st = _a + _key + _b;
                    elKeyEl.innerHTML = st;
                }
            },
            dataHandler:function(sKey, oData, oThis){
                //继承
                if (oData.sug) {
                    for(var i = 0; i < oData.sug.length; i++){
                        oData.sug[i].key = oData.sug[i].display = oData.sug[i].q;
                        oData.sug[i].val = ' ';
                    }
                    oThis._read(oData.sug);
                    oThis._prop.cache.pushCache(sKey, oData.sug);
                }else{
                    oThis._read([]);
                    oThis._prop.cache.pushCache(sKey, []);
                }
                //扩展
                var dataList = oData.sug,
                    dataHasInfo = false,
                    wrap = W(".panel-suggest .bd");
                if(typeof dataList == "undefined")return;
                dataList.forEach(function(dat){
                    if(dat.info.length>0 && !dataHasInfo){
                        dataHasInfo= true;
                    }
                });
                if(dataHasInfo){
                    wrap.css("height","300px");
                }else{
                    wrap.css("height","auto");
                }
            },
            callback: 'cb',
            keyword: 'wd'
        });                     
        var formatSugItem = function(infoItem){
            var objk = (function(obj){
                            var tyclass = "",itemStr = "";
                            var _obj = obj;
                            var filerText = function(tx){
                                return (!!QW.StringH.trim(tx))?tx:'未知';
                            };
                            var stopDefaut = 'onmousedown="QW.EventH.stopPropagation(null, this);" target="_blank"';
                            switch(_obj.type){
                                case "圈子":
                                    tyclass = "ty-circle";
                                    itemStr += (function(){
                                        var str="";
                                        _obj.info.forEach(function(ob,i){
                                            str += '<div class="row '+(function(){
                                                var al = _obj.info.length;
                                                if(al<2)return '';
                                                return (i<al-1?'dashed':'')
                                            })()+'">';
                                            str += '<span style="display:'+(parseInt(ob.like)<1?'none':'inline')+';" class="r">粉丝：'+ob.like+'</span><a '+stopDefaut+' href="'+PageUtil.circleUrl(parseInt(ob.cid), null, ob)+'">'+ob.title+'</a></div>';
                                        });
                                        return str;
                                    })();
                                break;
                                case "综艺":
                                    tyclass = "ty-entertainment";
                                    itemStr += '<div class="cls row"><div class="info-item"><div class="title">'+_obj.title+'</div><p class="lists">';
                                    itemStr += (function(){
                                        var str="";
                                        _obj.play_url.forEach(function(ob,i){
                                            str += '<a '+stopDefaut+' class="play" href="'+ob.play_url+'">'+ob.date+' '+ob.desc+'</a>';
                                        });
                                        return str;
                                    })();
                                    itemStr += '</p></div><a '+stopDefaut+' class="pic-view" href="'+_obj.play_url[0].play_url+'"><img src="'+PageUtil.videoThumbnailUrl(_obj.cover_url)+'"></a></div>';
                                break;
                                case "动漫":
                                    tyclass = "ty-cartoon";
                                    itemStr += '<div class="cls row"><div class="info-item"><div class="title">'+_obj.title+'</div><p class="lists">';
                                    itemStr += '导演：'+ filerText(_obj.director)+'<br/>主演：'+filerText(_obj.player)+'<br/>';
                                    itemStr += '</p><div class="video-group">';
                                    itemStr += (function(){
                                        var str="",
                                            isOver5=_obj.play_url.length>5;
                                        _obj.play_url.forEach(function(ob,i){
                                            if(isOver5 && i==3){
                                                var hasNo4 = QW.StringH.trim(ob.video_no) == "4";  
                                                if(!hasNo4){
                                                    str += '<a '+stopDefaut+' style="cursor:default;" href="#">...</a>';
                                                }
                                            }
                                            str += '<a '+stopDefaut+' href="'+ob.play_url+'">'+ob.video_no+'</a>';
                                        });
                                        return str;
                                    })();
                                    itemStr += '</div></div><a '+stopDefaut+' class="pic-view" href="'+_obj.play_url[0].play_url+'"><img src="'+PageUtil.videoThumbnailUrl(_obj.cover_url)+'"></a></div>';
                                break;
                                case "电视剧":
                                    tyclass = "ty-tv";
                                    itemStr += '<div class="cls row"><div class="info-item"><div class="title">'+_obj.title+'</div><p class="lists">';
                                    itemStr += '导演：'+ filerText(_obj.director)+'<br/>主演：'+filerText(_obj.player)+'<br/>';
                                    itemStr += '</p><div class="video-group">';
                                    itemStr += (function(){
                                        var str="",
                                            isOver5=_obj.play_url.length>5;
                                        _obj.play_url.forEach(function(ob,i){
                                            if(isOver5 && i==3){
                                                var hasNo4 = QW.StringH.trim(ob.video_no) == "4";  
                                                if(!hasNo4){
                                                    str += '<a '+stopDefaut+' style="cursor:default;" href="#">...</a>';
                                                }
                                            }
                                            str += '<a '+stopDefaut+' href="'+ob.play_url+'">'+ob.video_no+'</a>';
                                        });
                                        return str;
                                    })();
                                    itemStr += '</div></div><a '+stopDefaut+' class="pic-view" href="'+_obj.play_url[0].play_url+'"><img src="'+PageUtil.videoThumbnailUrl(_obj.cover_url)+'"></a></div>';
                                break;
                                case "电影":
                                    tyclass = "ty-movie";      
                                    itemStr += '<div class="cls row"><div class="info-item"><div class="title">'+_obj.title+'</div><p class="lists">';
                                    itemStr += '导演：'+ filerText(_obj.director)+'<br/>主演：'+filerText(_obj.player)+'<br/>';
                                    itemStr += '</p><a '+stopDefaut+' class="play" href="'+_obj.play_url+'">播放影片</a></div><a '+stopDefaut+' class="pic-view" href="'+_obj.play_url+'"><img src="'+PageUtil.videoThumbnailUrl(_obj.cover_url)+'"></a></div>';
                                break;
                                case "人物":
                                    tyclass = "ty-superstar";
                                    itemStr += '<div class="cls row"><div class="info-item"><div class="title">'+_obj.name+'</div><p class="lists">';
                                    itemStr += (function(){
                                        var str="";
                                        _obj.works.forEach(function(ob,i){
                                           str += '<a '+stopDefaut+' class="play" href="'+ob.play_url+'">'+ob.title+'</a>';
                                        });
                                        return str;
                                    })();
                                    itemStr += '</p></div><a '+stopDefaut+' style="cursor:default;" class="pic-view"><img src="'+PageUtil.videoThumbnailUrl(_obj.cover_url)+'"></a></div>';
                                break;
                            } 
                            return {className:tyclass,typeName:obj.type,str:itemStr};
                       })(infoItem);
            var str = '<li class="'+objk.className+'">';
                str += '<dl><dt>'+objk.typeName+'</dt><dd>';
                str += objk.str;
                str += '</dd></dl></li>';
            return str;
        };
        function doSug (e){
            if(sug.getKeyword()) {
                var baseSearchUri = QW.Config.get('host') + '/search?q=' + encodeURIComponent(sug.getKeyword()),
                    query_src = '';
                W('.panel-suggest .item').forEach(function(i){                        
                    if (sug.getKeyword() == QW.StringH.stripTags(W(i).html())) {
                        query_src = '&query_src=2';
                        return;
                    }
                });
                window.location = baseSearchUri + query_src;
            }
        }        
        sug.on('enter', doSug);
        sug.on('itemselect', doSug);
        sug.on('itemfocus',function(e){
            var index = e.target.index;
            if(index<0)return;
        });
        sug.on('focus',function(e){
            var ts = this;
            if(!ts.getKeyword()){
                ts.suggest('.toplist');
            }
        });
        sug.on('backspace',function(e){
            var ts = this;
            if(ts.getKeyword().length == 1){
                ts.suggest('.toplist');
            }
        });
        var _timer = -1;
        W("#search-text2").on("blur",function(event){
            clearTimeout(_timer);
            _timer = setTimeout(function(){sug.hide();},200);
        });
    });
    W('#search-form2').on('submit', function(e){        
        if(!QW.StringH.trim(W('#search-text').val())){
            e.preventDefault();
        }
    }); 
    //搜索框提示文字
    var sch = W(".search-frame2");
    sch.on("click",function(){
        if (W(this).val() == W(this).attr('title')) {
            W(this).val("");
        }
    });
    sch.on("blur",function(){
        if (W(this).val() == "") {
            W(this).val(W(this).attr('title'));
        } 
    }); 
}