
MAX_FORWARD_WORDS = window.PARAM_FORWARD_TEXT_MAX_LEN || 140;

/**
 * 个人页JS交互
 * x@btbtd.org  2012/5/9 
 */ 
Dom.ready
(
    function()
    {
        window.PARAM_LASTTIME = window.PARAM_LASTTIME || (parseInt(new Date().getTime()/1000));
        window.PARAM_COUNT = window.PARAM_COUNT || 20;
        
        INIT_TAG_BOX();
        LOADING_MORE_INFO();
        CHECK_NEW_INFO();
        //A_ADD_USER_INFO();
        INIT_FEED_SUBMIT();
        
        
//         alert
//         ( 
//             PageUtil.formatUserLinkText
//             (
//                 '@共和国防洪 @拉登 @比尔_盖饭 @jag @我爱西沙 @左左哥 @丘比特 @scKicker 真是太有才了'
//                 , {
//                     "1826515151": "我爱西沙", 
//                     "1798015323": "共和国防洪", 
//                     "794123477": "jag", 
//                     "1186951503": "拉登", 
//                     "1797646954": "左左哥", 
//                     "1648521763": "比尔_盖饭", 
//                     "1560858034": "丘比特"
//                 }
//                 , 
//                     {
//                         "fuid": '850271823',
//                         "stype": 'i'
//                     } 
//             )
//         );
    }
);
/**
 * 初始化动态的转发及相关事件
 */ 
function INIT_FEED_SUBMIT( $container )
{
    var feed_list_ = W('#feed_list_');
    
    if( !feed_list_.length ) return;
    
    $container = $container || feed_list_;
    
    W(document).delegate
    (
        ".forward_"
        , "click"
        , function( $evt )
        {
            $evt.preventDefault();
            /*
            //新的登陆方式不需要这个判断
            if(! W('#logined_mark').length ) {
            	LOGIN_POPUP();
            	return;
            }
            */
            var p = W(this);
            
            var id = (p.attr('data-id') || '').trim();
            
            if( !id ) return;
            
            var formAr = p.parentNode(".feed-item").query('form');
            
            var form_;
            
            formAr.forEach
            (
                function( $ele )
                {
                    var sid = W($ele).attr('data-id');
                    
                    if( id == sid ) form_ = W($ele);
                }
            );
            
            if( !form_ ) return;
            
            var pnt = form_.parentNode('.form-rt');
            
            if( pnt.css('display') == 'none' )
            {
               feed_list_.query('.form-rt').hide(); 
            }
            
            pnt.toggle();
            
            if( pnt.css('display') == 'block' )
            {
                form_.query('textarea').focus();
                
                count_words( form_ );
            }
            
            
            return false;
        }
    ); 
    
    
    W(document).delegate
    (
        ".submit_forward_"
        , "click"
        , function( $evt )
        {
            $evt.preventDefault();   
            
            var $srcData = {
                'type': 'user_submit_forward_'
                , 'source': this
                , 'event': $evt.type
            }; 
            
            var form_ = W(this).parentNode('form');
            
            if( !form_ ) return;
            var prnt = form_.parentNode('.form-rt');
            
            var curtext = (form_.query( 'textarea[name=curtext]' ).getValue('value')||form_.query( 'textarea[name=curtext]' ).getHtml('html')).trim();
            var curfid = form_.query( 'input[name=curfid]' ).getValue('value').trim();
            var rootfid = form_.query( 'input[name=rootfid]' ).getValue('value').trim();
            var orig_feed_data = form_.query( 'input[name=orig_feed_data]' ).attr('value');           
            
//             if( !curtext )
//             {
//                 alert( '转发内容不能为空!' );
//                 return false;
//             }
            
            if( curtext.length > MAX_FORWARD_WORDS )
            {
                alert( '转发内容过长!' );
                return false;
            }
            
            prnt.hide();
            
            QW.Ajax.post
            (
                '/user/doforwardfeed'
                , { "curtext": curtext, "curfid": curfid, "rootfid": rootfid, "orig_feed_data": orig_feed_data}
                        
                , function( $d )
                {
                    if( $d.err != 'ok' )
                    {
                        prnt.show();
                    }                
                
                	if ($d.err == 'sys.permission.need_login') {
                        $srcData && window.XLogin && XLogin.storeTriggerData( $d, $srcData );
                		LOGIN_POPUP();
                	} else if( $d.err == 'ok' ) {
                        form_.query( 'textarea[name=curtext]' ).setValue('');
                        var box = form_.parentNode().parentNode();
                        
                        var tip = document.createElement('div');
                        tip.innerHTML = '<div class="rt-succ"><div class="rt-succ-inner">转发成功</div></div>';
                        
                        XRemind.exec
                        (
                            {
                                "type": XRemind.SUCCESS,
                                "tip": tip,
                                "injectCallback":
                                function( $e )
                                {
                                    box.appendChild( $e );
                                }
                            }
                        );
                        XLogin.ondone();
                    }
                    else
                    {
                    	var msg = getMessage($d.msg) || '转发时发生服务端错误';
                        alert(msg);
                        prnt.show();
                    }
                }
                        
                , {
                    onerror:
                    function()
                    {
                        alert( '转发时发生网络错误!' );
                        prnt.show();
                    }
                }
            );
            
            return false;
        }
    );
    
    
    W(document).delegate
    (
        ".forward_txa_"
        , "keydown"
        , function( $evt )
        {
            var p = W(this);
            
            if( p.get('bind') == "1" ) return;            
            p.set('bind', "1");
            
            p.on
            (
                "keypress",
                function( $e )
                {
                    if( $e.keyCode === 10 )
                    {
                        W(this).parentNode('form').query('.submit_forward_').fire("click");
                        return false;
                    }
                }
            );
            
            onpropertychange_f
            (
                p,
                function( $evt )
                {
                    var sp = W(this);    
                    count_words( sp.parentNode('form') );             
                }
            );
        }
    );
    
    function count_words( $form )
    {
        var tips = $form.query('div.tips');
        var txa = $form.query('textarea[name=curtext]');
                        
        var text = txa.getValue().trim();
        
        if( !tips.length ) return;
        if( text.length > MAX_FORWARD_WORDS )
        {
            tips.html( '<div class="tips">你输入的字数已经超出 <span>'+(text.length-MAX_FORWARD_WORDS)+'</span>字</div>' );
        }
        else
        {
            tips.html( '<div class="tips">你还可以输入 <span>'+(MAX_FORWARD_WORDS-text.length)+'</span>字</div>' );
        }
    }
}  
/**
 * 检测是否有新动态
 */ 
function CHECK_NEW_INFO()
{
    var new_info_ = W('#new_info_');
    
    if( !window.XTimer ) return;
    if( !new_info_.length ) return;
    
    window.NEW_FEED_API = window.NEW_FEED_API || '/user/newfeednum';
        
    XTimer.exec
    (
        {
            "second": 10
            
            , "tickCallback": 
                function( $second )
                {
                    //document.title = $second;
                }
                
            , "doneCallback":
                function( $second )
                {
                    QW.Ajax.post
                    (
                        window.NEW_FEED_API
                        , { "tm": window.PARAM_LASTTIME, "rnd": Math.random() }
                        , function( $d )
                        {   
                            CHECK_NEW_INFO();
                            
                            if( $d.err == "ok" && $d.data > 0 )
                            {
                                new_info_.html( '<a href="'+new_info_.attr('data-url')+'" onclick="reload_page()">你有新的动态，点击查看</a>' );
                                new_info_.show();
                            }
                            else
                            {
                                new_info_.hide();
                            }
                        }
                        
                        , {
                            onerror:
                            function()
                            {
                                CHECK_NEW_INFO();
                            }
                        }
                    );
                    
                }
        }
    );
    
}

/**
 * 加载更多动态
 */ 
function LOADING_MORE_INFO()
{
    //loading_more_info_
    
    var feed_list_ = W('#feed_list_');    
    if( !feed_list_.length ) return;
    
    var loader = W('#loader');
        
    if( !window.FEED_OFFSET )
    {
        window.FEED_OFFSET = window.PARAM_COUNT;
    }
    
    window.FEED_LOADING_LOCK = false;    
    window.MORE_FEED_API = window.MORE_FEED_API || '/user/userfeed'; 

    W(document).delegate
    (
        ".loading_more_info_"
        , "click"
        , function( $evt )
        {
            $evt.preventDefault();
            $evt.stopPropagation();
            
            if( window.FEED_LOADING_LOCK )
            {
                alert( '正在加载中, 请稍候...' )
                return;
            }
            
            var pointer = W(this);
            
            window.FEED_LOADING_LOCK = true;
            
            var source = W(this);
                    
            loader.show();
            pointer.hide();
            
            QW.Ajax.post
            (
                window.MORE_FEED_API
                , { 
                    "offset": window.FEED_OFFSET
                    , "count": window.PARAM_COUNT
                    , "tm": window.PARAM_LASTTIME
                    , "format": 'html'
                    , "type": window.PARAM_SUBTYPE
                    , "uid": window.USER_ID 
                }
                , function( $d )
                {  
                    loader.hide();
                    pointer.show();
                    window.FEED_LOADING_LOCK = false;
                        
                    if( $d.err == 'sys.permission.need_login' )
                    {
                        LOGIN_POPUP();
                        return;
                    }
                    if( $d.err == 'ok' )
                    {
                    	var html = ( $d.data.data || '' ).toString().trim();
                        if( html ) {
                            var temp = W('<div></div>').html( html );
                            
                            temp.query('> div').forEach
                            (
                                function($ele)
                                {
                                    feed_list_.appendChild( $ele );
                                }
                            );
                            temp = null;
                            
                        }
                        if (! $d.data.has_more) {
                            pointer.hide();
                        }
                    }
                    
                    window.FEED_OFFSET += window.PARAM_COUNT;
                }
                
                , {
                    onerror:
                    function()
                    {
                        loader.hide();
                        pointer.show();
                        window.FEED_LOADING_LOCK = false;
                        alert( '网络错误!' );
                    }
                }
            );
            
            return false;
        }
    ); 
}
