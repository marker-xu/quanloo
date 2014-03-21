/**
 * video 通用定义 & 通用脚本
 * x@btbtd.org  2012-2-13  
 */ 

window.MAX_INDEX = 9999; 
window.POPUP_LIST = [];
window.isIE = !-[1,];
//以后再也不报错了
window.console = window.console || {
    log: function(){}
}

Dom.ready
(
    function()
    {
        /**
         * 返回页面顶部
         */
        if( W('#gotop').length ) GoTop.exec( {"id": "gotop", "right": 60, "bottom": 40 } );
        if( W('#feed_back').length ) GoTop.exec( {"id": "feed_back", "top": 400, "left": 1, "autoHide": false } );   
        
        USER_MENU();
        if(!window.COMPLETE_SDID) {
        	FAST_LOGIN();
        }
        
        DISCOVER();
        WATCHED();
        WATCH_LATER();
        
        INVITE_FRIEND();
        
        SHARE_GROUP();

        searchSug();
        
        COMMON_DELEGATE();
        
        NEW_INFO_TIPS();
        INIT_AVATAR_POPUP();     
        
        XLinkParams.exec
        (
            {
                'data_key': 'data-lks'
            }
        );
        
        XRemind.CLASSNAME =
        {
            'success': 'remind_success'
            , 'failed': 'remind_failed'
            , 'alter': 'remind_alter'
        };
                
        /**
         * 如果已登录, 则上报分享
         */                 
        if( W('#logined_mark').length )
        {
            XShare.sharingCallback = SHARE_REPORT_CALLBACK;
        }        
                
        if( window.isIE ) W(document.body).focus(); 
        XAtComplete.updateQuanloo =
        function( $data )
        {
            if( $data && $data.sug )
            {
                XAtComplete.update( $data.sug, $data.query );
            }    
        };
        
        XLogin.loginedCallback = XLoginLoginedCallabck;
        XLogin.debug = false;
    }    
);

/**
 * XLogin 登陆后回调 更新全局状态, 比如头部
 */       
function XLoginLoginedCallabck()
{
    QW.Ajax.get
    (
        '/user/getheadcontent'
        , { "rnd": new Date().getTime(), 'page': window.PAGE_TYPE || '' }
                
        , function( $d )
        {
            if( $d && $d.err == 'ok' && $d.data && $d.data.html )
            {
                var hbox = W('#hd');
                
                if( hbox.length )
                {
                    hbox.html( $d.data.html );
                    
                    USER_MENU();
                    NEW_INFO_TIPS();
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
 * 初始化通用头像 POPUP
 */ 
function INIT_AVATAR_POPUP()
{    
    window.CUR_AVATAR = window.CUR_AVATAR || null;
    window.AVA_DATA = window.AVA_DATA || {};
    
    var avatar_box = W('#avatar_box');
    
    var isIE = !!window.ActiveXObject;
    var isIE8 = isIE && !!document.documentMode;
    
    var loading =
    [
    "    <div class=\"avatar_inner\">\n"
    ,"        <div class=\"avatar_tips\">\n"
    ,"            <!--<div class=\"ava_arrow ava_uarrow\"></div>-->\n"
    ,"            <div class=\"ava_arrow ava_barrow\"></div>\n"
    ,"            <div class=\"ava_loading\">正在加载中, 请稍候...</div>        \n"
    ,"        </div>\n"
    ,"    </div>\n"
    ].join('');    
    
    if( !avatar_box.length )
    {
        //这里自动生成节点 avatar_box
        var tpl = 
        [
        "<div id=\"avatar_box\" class=\"avatar_box\" style='display:none'>\n"
        , loading
        ,"</div>\n"
        ].join('');
        
        W('body').appendChild( W(tpl) );
        
        avatar_box = W('#avatar_box');
    }

    W(document).delegate
    (
        ".ava_popup_",
        "mouseenter",
        function( $evt )
        {        
            var $ele = W(this);
            window.CUR_AVATAR = $ele;
            
            var id = $ele.attr( 'data-id' );            
            if( !id ) return;
            
            avatar_box.css( 'z-index', getMaxIndex() );
            
            clearDealy();
                        
            if( !(window.AVA_DATA[id] && window.AVA_DATA[id].data) )
            {
                this.LOCK = true;
                
                if( typeof window.AVA_DATA[id] == 'undefined' )
                {
                    window.AVA_DATA[id] = {};
                }
                if( typeof window.AVA_DATA[id].data == 'undefined' )
                {
                    window.AVA_DATA[id].data = '';
                }
                //0,1,2   0:未作改变, 1:已关注, 2:取消关注
                window.AVA_DATA[id].isFocus = window.AVA_DATA[id].isFocus || 0;
                
                //inner_update( loading, id );
                setDealy( loading, id );
                
                QW.Ajax.get
                (
                    '/user/userinfotip'
                    , {"id": id}
                    , function( $d )
                    {                           
                        if( $d.err == 'sys.permission.need_login' )
                        {
                            LOGIN_POPUP();
                            window.AVA_DATA[id] = null;
                            return;
                        }
                        
                        if( $d && $d.err == 'ok' )
                        {
                            window.AVA_DATA[id].data = $d.data.data;
                            //inner_update( $d.data.data, id );
                            setDealy( $d.data.data, id );
                        }
                    }
                    
                    , {
                        onerror:
                        function()
                        {
                            window.AVA_DATA[id] = null;
                        }
                    }
                );
            }
            else
            {            
                //inner_update( window.AVA_DATA[id].data, id );
                setDealy( window.AVA_DATA[id].data, id );
            }
        }
    ).delegate
    (
        ".ava_popup_",
        "mouseleave",
        function( $evt )
        {        
            clearDealy();
            if( !window.CUR_AVATAR ) return;
            
            if( avatar_box.css('display') == 'none' ) return;
            
            var $ele = window.CUR_AVATAR;
            
            if( checkSourceBoundary( $ele, $evt.pageX, $evt.pageY ) )
            {
                avatar_box.hide();
                return;
            }
        }
    );
    
    W(window).on
    (
        'resize',
        function()
        {
            clearDealy();
            if( avatar_box.css('display') == 'none' ) return;
            //inner_update( '' );
            setDealy( '' );
        }
    );
    
    function setDealy( $data, $id )
    {
        clearDealy();
        
        window.AVA_TIP_DEALY =
        setTimeout
        (
            function()
            {
                inner_update( $data, $id )
            }
            , 500
        );
    }
    
    function clearDealy()
    {
        if( window.AVA_TIP_DEALY )
        {
            clearTimeout( window.AVA_TIP_DEALY );
            window.AVA_TIP_DEALY = 0;
        }
    }
    
    function inner_update( $data, $id )
    {         
        var $ele = window.CUR_AVATAR;
        if( !$ele ) return;
    
        if( $data ) avatar_box.html( $data );
      
        var pos = $ele.getXY();
        var e_size = $ele.getSize();
        avatar_box.show();
        var ava_size = avatar_box.query('.avatar_tips').getSize();
        
        var l = pos[0];
        var t = pos[1] - ava_size.height - 4;
        
        var vp = viewport_f();
        vp.width -= 5;
        var max_x = l + ava_size.width;
        
        if( max_x > vp.width )
        {
            l = l - ava_size.width + e_size.width;
            
            avatar_box.query( '.ava_barrow' ).addClass('arr_inright');
        }
        else
        {
            avatar_box.query( '.ava_barrow' ).removeClass('arr_inright');
        }
        
        avatar_box.css( { "left": l + 'px', "top": t +  'px' } );
                
        if( $id )
        {
            var uf = avatar_box.query( 'a.user_focus_' );
            
            if( uf.length )
            {              
                //document.title = 0 + ', ' + $id;
                if( window.AVA_DATA[$id].isFocus === 1 )
                {
                    //document.title = 1 + ', ' + $id;
                    uf.removeClass('bt-f-done');
                    uf.removeClass('bt-f-add');
                    
                    uf.addClass('bt-f-done');
                }
                else if( window.AVA_DATA[$id].isFocus === 2 )
                {
                    //document.title = 2 + ', ' + $id;
                    uf.removeClass('bt-f-done');
                    uf.removeClass('bt-f-add');
                    
                    uf.addClass('bt-f-add');
                }
            }
        }
    }
    
    avatar_box.on
    (
        'mouseleave', 
        function( $evt )
        {
            if( !window.CUR_AVATAR ) return;
            if( avatar_box.css('display') == 'none' ) return;
            var $ele = window.CUR_AVATAR;
            
            if( checkBoxBoundary( $ele, $evt.pageX, $evt.pageY ) )
            {
                avatar_box.hide();
                return;
            }
        }
    );  
      
    function checkBoxBoundary( $ele, $pageX, $pageY )
    {
        var isOut = false;
        if( avatar_box.css('display') == 'none' )
        {
            return true;   
        }
        
        var pos = $ele.getXY();
        var e_size = $ele.getSize();
        var ava_pos = avatar_box.getXY();
        var ava_size = avatar_box.query('.avatar_tips').getSize();
        
        var pageXForR = $pageX;
        var pageYForB = $pageY;
        
        var XPageY = $pageY + 4;
        
        if( isIE8 )
        {
            pageXForR += 3;
            XPageY += 2;
        }
                
        //判断左边界
        if( $pageX < ava_pos[0] )
        {
            isOut = true;
        }
        //判断右边界
        else if( pageXForR >= ava_pos[0] + ava_size.width && $pageY <= pos[1] )
        {
            isOut = true;
        }       
        //判断底边界
        else if( XPageY >= pos[1] && checkSourceBoundary( $ele, $pageX, XPageY  ) )
        {
            isOut = true;
        }
        //判断顶边界
        else if( $pageY <= ava_pos[1] )
        {
            isOut = true;
        }
        
        return isOut;
    }
    
    function checkSourceBoundary( $ele, $pageX, $pageY )
    {
        if( avatar_box.css('display') == 'none' )
        {
            return true;   
        }
        
        var isOut = false;
        
        var pos = $ele.getXY();
        var e_size = $ele.getSize();
        var ava_pos = avatar_box.getXY();
        var ava_size = avatar_box.query('.avatar_tips').getSize();
                
        var pageXForR = $pageX;
        var pageYForB = $pageY;
        
        if( isIE8 )
        {
            pageXForR += 3;
            pageYForB += 3;
        }
        
        //判断左边界
        if( $pageX < pos[0] )
        {
            isOut = true;
        }
        //判断右边界
        else if( pageXForR >= pos[0] + e_size.width && $pageY >= pos[1] )
        {
            isOut = true;
        }        
        //判断底边界
        else if( pageYForB >= pos[1] + e_size.height )
        {
            isOut = true;
        }  
        
        return isOut;
    }
    /**
     * 更新弹出框的关注状态
     */         
    window.UPDATE_AVA_POP_STATUS = 
    function( $action_data, $query_data, $req_data )
    {
        if( !( $req_data && $req_data.err == 'ok' ) ) return;
        if( !( $query_data && $query_data.following) ) return;
        $query_data.following = $query_data.following.toString();
        
        if( !window.AVA_DATA ) window.AVA_DATA = {};
        if( !window.AVA_DATA[$query_data.following] ) window.AVA_DATA[$query_data.following] = {};
        
        if( $action_data.length )
        {
            var fk = $action_data.join(',');
            //document.title = 3;                        
            if( fk == 'following' )//取消关注
            {
                W('a.bt-f-add,a.bt-f-done').forEach
                (
                    function( $ele )
                    {
                        isInData( $ele, $query_data.following, 2 );
                    }
                );
                //document.title = 4 + ', ' +$query_data.following;
            }
            else if( fk == 'following,hidden' )//开始关注
            {
                W('a.bt-f-add,a.bt-f-done').forEach
                (
                    function( $ele )
                    {
                        isInData( $ele, $query_data.following, 1 );
                    }
                );
                //document.title = 5 + ', ' +$query_data.following;
            }
        }
    };
    
    function isInData( $a, $id, $type )
    {
        window.AVA_DATA[$id].isFocus = $type; 
            
        //document.title = 0;
        var da = W($a).attr('data-action');
        if( !da ) return;
        //document.title = 1 + ', ' + da + ', ' + $id; 
        var data = String.evalExp(da);
        if( !(data && data.following) ) return;
        //alert($id +', ' + data.following)
        //document.title = 2 + ', ' + da + ', ' + $id;
        if( data.following.toString() !== $id.toString() ) return;
        //document.title = 3 + ', ' + da + ', ' + $id;
        
        switch( $type )
        {
            case 2:
            {
                W($a).addClass('bt-f-add').removeClass('bt-f-done'); 
                break;
            }
            
            case 1:
            {
                W($a).addClass('bt-f-done').removeClass('bt-f-add');
                break;
            }
        }
    }
}
/**
 * 头部新动态
 * x@btbtd.org 2012/5/15 
 */
function NEW_INFO_TIPS()
{
    var hd_new_info_tip_ = W('#hd_new_info_tip_');
    var hd_new_info_box_ = W('#hd_new_info_box_');
    var hd_new_info_cnt = W('#hd_new_info_cnt');
    
    if( !(hd_new_info_tip_.length && hd_new_info_box_.length && hd_new_info_cnt.length) ) return;
    
    var num = hd_new_info_tip_.query('span.num');
    hd_new_info_tip_.on("mouseover",function(){
        W(this).addClass("list_ico_hover");
    }).on("mouseout",function(){
        W(this).removeClass("list_ico_hover");
    });
        
    initHover();
    
    hd_new_info_cnt
    .on
    (
        "mouseenter",
        function( $evt )
        {
            $evt.stopPropagation();
            $evt.preventDefault();
            
            hd_new_info_box_.show();
            
            if( !hd_new_info_box_.html().trim() )
            {
                hd_new_info_box_.html( '<p style="margin:10px 2px;text-align:center;" class="temp">正在加载动态数据, 请稍候...</p>' );
            }
            
            QW.Ajax.get
            (
                '/user/headerfeedtip'
                        
                , function( $d )
                {       
                    if( $d.err == "ok" && $d.data )
                    {
                        if( $d.data.unreadmsgnum && $d.data.unreadmsgnum > 0 )
                        {
                            num.html( $d.data.unreadmsgnum );
                            num.show();                            
                        }
                        else
                        {
                            num.html( 0 );
                            num.hide();
                        }
                        
                        if( $d.data.data )
                        {
                            hd_new_info_box_.html( $d.data.data );
                            initHover();
                        }
                    }
                    
                    hd_new_info_box_.query('.temp').removeNode();
                    
                    if( !hd_new_info_box_.html().trim() )
                    {
                        hd_new_info_box_.html( '<p style="margin:10px 2px;text-align:center;" class="temp">现在没有新动态...</p>' );
                    }
                    
                }
                        
                , {
                    onerror:
                    function()
                    {
                    }
                }
            );
            
            return false;
        }
    )
    .on
    (
        "mouseleave",
        function( $evt )
        {
            $evt.stopPropagation();
            $evt.preventDefault();
            
            hd_new_info_box_.hide();
            
            return false;
        }
    );
    
    function initHover()
    {
        var list = hd_new_info_box_.query('ul > li');
        
        list
        .on
        (
            "mouseover",
            function()
            {
                var p = W(this);
                
                list.removeClass('li_hover');
                p.addClass('li_hover');
            }
        )
        ;
    }
}

/*
 如果没有被阻止冒泡行为, 是不需要主动触发 invoke 的~ 
 只要有 data-lks 属性就会触发
 
//瀑布里面的砖头的播放链接，现在为了SEO不能带参数，通过XLinkParams组件进行拼装再跑转
W("body").delegate(".playurl", "click", function(){
    XLinkParams.invoke( this );
});
*/

/**
 * 分享上报回调
 * x@btbtd.org  2012/5/10 
 */
function SHARE_REPORT_CALLBACK( $type, $shareUrl, $param )
{
    var p = $param.textContent.split('/');

    var id = p[p.length-1];
    id = id.trim().replace( /[\s].*/, '' );
    id = id.replace( /[^0-9a-zA-Z].*/, '' );
    
    //$param.shareType = video / circle
    
    //alert( id + ', ' + $param.shareType )   
    
    QW.Ajax.get
    (
        '/video/sharefeed'
        , { "id": id, "shareType": $param.shareType, "rnd": Math.random() }
                
        , function( $d )
        {       
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
 * 通用委托
 * x@btbtd.org  2012/5/9 
 */ 
function COMMON_DELEGATE()
{
    /**
     * 登录POPUP
     */         
    W('a.login__, textarea.login__').on
    (
        "click"
        , function( $evt )
        {
            $evt.preventDefault();
            $evt.stopPropagation();
            
            window.XLogin && XLogin.resetData && XLogin.resetData();
            LOGIN_POPUP();
            
            return false;
        }
    );   
    /**
     * 全局屏蔽 针对 A 链接为 # 或者 javascript: 的默认行为
     */         
    W(document).delegate
    (
        "a", "click",
        function( $evt )
        {
            var src = (W(this).attr('href')||'').trim();
            
            if( 
                ( /^\#/.test(src) ) 
                && !(
                        W(this).hasClass('gotop') 
                        || W(this).hasClass('top') 
                        || W(this).hasClass('go-top') 
                        || W(this).attr('id') == 'gotop'
                ) 
            )
            {
                $evt.preventDefault();
                
//                 var mar = W(this).attr('data--marmot');
//                 
//                 if( mar )
//                 {
//                     try
//                     {
//                         mar = mar.evalExp();                        
//                         if( mar )
//                         {
//                             QW.Marmot.log( mar );
//                         }                        
//                     }catch(ex){}
//                 }
                
            }
        }
    );
    
    /**
     * 只要标签带CSS class stop_propa_, 则阻止冒泡事件
     */         
    W(document).delegate
    (
        "*", "click",
        function( $evt )
        {
            var p = W(this);
            
            if( p.hasClass('stop_propa_') )
            {
                $evt.stopPropagation();
            }
        }
    );
    
    /**
     * 只要 input, textarea 带有CSS CLASS xat_control_, 则在为焦点时, 初始化 微博@功能
     */         
    W(document).delegate
    (
        "input.xat_control_, textarea.xat_control_", 'focus',
        function($evt)
        {
            var p = this;
            INIT_XATCOMPLETE( p );
            
            setTimeout( function(){ INIT_XATCOMPLETE( p ); }, 500 );
        }
    );
}
/**
 * 初始化微博@功能
 */ 
function INIT_XATCOMPLETE( $ele )
{
    if(!W('#logined_mark').length) return;
    if(!window.XAT_REQUEST_URL) return;
    if( $ele.INIT_XATCOMPLETE ) return;
    $ele.INIT_XATCOMPLETE = true;
    
    var qid = get_url_param( window.XAT_REQUEST_URL || '', 'qid' );
    
    if( !qid )
    {   
        var hd = W('#logined a.headp');
        
        if( !hd.length )
        {
            if( window.XAT_TM ) clearTimeout( window.XAT_TM );
            
            window.XAT_TM = 
            setTimeout
            (
                function()
                {
                    INIT_XATCOMPLETE( $ele );
                }
                , 200
            );
            
            return;
        }
        
        var temp = (hd.get('href')||'').split('/');
        
        if( temp.length )
        {
            qid = temp[ temp.length-1 ];          
            window.XAT_REQUEST_URL = add_url_param( window.XAT_REQUEST_URL || '', {'key': 'qid', 'value': qid} );
        }
    }
    else
    {
        if( window.XAT_TM ) clearTimeout( window.XAT_TM );
    }
    
    XAtComplete.exec
    (
        {
            "trigger": $ele
            , "data_url": window.XAT_REQUEST_URL
        }
    );  
}
/*搜索提示emment*/
function searchSug(){
    /*suggest*/
    QW.use('Suggest', function(){
        var sug = new QW.Suggest({
            textbox: '#search-text',
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
                    str += '<ul class="info" style="display:'+(isFirst?'block':'none')+';">';
                    str += (function(info){
                                if(!hasInfo)return '';
                                var itemString = "";
                                info.forEach(function(dat){
                                   itemString += fmtfun(dat);
                                });
                                return itemString;
                            })(oData.info);
                    str += '</ul></div>';
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
            var ulList = W('.panel-suggest .bd').query('ul');
            ulList.hide();
            ulList.item(index).show();
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
        W("#search-text").on("blur",function(event){
            clearTimeout(_timer);
            _timer = setTimeout(function(){sug.hide();},200);
        });
    });
    W('#search-form').on('submit', function(e){        
        if(!QW.StringH.trim(W('#search-text').val())){
            e.preventDefault();
        }
    }); 
    //搜索框提示文字
    var sch = W(".search-frame");
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
/**
 * 获取页面上最大的z-index值, 并+1
 * x@btbtd.org  2012-3-23  
 */ 
function getMaxIndex()
{
    window.MAX_INDEX = window.MAX_INDEX || 9999;
    return ++window.MAX_INDEX;
}
/**
 * 添加到以后观看
 * x@btbtd.org  2012/4/25 
 */ 
function ADD_WATCH_LATER( $id, $cb )
{
    QW.Ajax.get
    (
        '/video/watchlater'
        , {"id": $id}
        , function( $d )
        {          
            if( $d.err == 'sys.permission.need_login' )
            {
                //QW.VideoAction.add2CookieList($id, 'watchlater_list');
                LOGIN_POPUP();
            }
            
            if( $cb )
            {
                $cb( $d );
            }
        }
        
        , {
            onerror:
            function()
            {
                alert( e.msg || '网络错误!' );
            }
        }
    );
}
/**
 * 移除掉以后观看
 * x@btbtd.org  2012/4/25 
 */ 
function REMOVE_WATCH_LATER( $id, $cb )
{
    QW.Ajax.get
    (
        '/video/deletewatchlater'
        , {"id": $id}
        , function( $d )
        {          
            if( $d.err == 'sys.permission.need_login' )
            {
                //QW.VideoAction.rmFromCookieList($id, 'watchlater_list');
                LOGIN_POPUP();
            }
            
            if( $cb )
            {
                $cb( $d );
            }
        }
        
        , {
            onerror:
            function()
            {
                alert( e.msg || '网络错误!' );
            }
        }
    );
}

function HIDE_OTHER_POPUP( $popup )
{
    for( var i = 0; i < POPUP_LIST.length; i++ )
    {
        if( $popup != POPUP_LIST[i] )
        {
            POPUP_LIST[i].hide();
        }        
    }
    W('#topBtnA .selected').removeClass('selected');
    
    if( $popup.get('id') != 'login_popup' )
    {
        W('#fast_login').removeClass('hoverstlye');
    }
}
/**
 * 获取通用证登录IFRAME路径
 */ 
function LOGIN_URL(isMiddle)
{
    var url = '';
    var f = location.href.split('#')[0].split('/').slice(3).join('/');
    if(isMiddle) {
    	if(f.indexOf("?")>-1) {
        	f+="&middle=1";
        } else {
        	f+="?middle=1"
        }
    }
    
    if( window.SITE_URL ) url = window.SITE_URL;            
    url += '/user/login?f=' + encodeURIComponent( f )               
    url = "https://login.sdo.com/sdo/Login/LoginFrameFC.php?returnURL="+encodeURIComponent(url)+"&appId=317&areaId=0&target=iframe";
    
    return url;
}
/**
 * 取正确的提示信息
 */ 
function getMessage( $msg )
{
    if( typeof $msg == 'object' )
    {
        for( var k in $msg ) return $msg[k];
    }
    return $msg;
}
/**
 * 登录弹出框(大)
 */
function LOGIN_POPUP( $ext )
{
    CHECK_STAT();
    
    $ext = $ext || {};
    
    QW.Ajax.get
    (
        $ext.url || '/user/login_label'
        , function( $d )
        {          
        	QW.Marmot.log({page_id: 'click_login', position:'page_op'});
            $d = $d.replace( /\{url\}/gi, LOGIN_URL(true) );
            
            if( window.LGOIN_POP_INS ){ try{ window.LGOIN_POP_INS.view.close(); }catch(ex){} }
            window.LGOIN_POP_INS = XDialog.exec( 
                { 
                    "tpl": $d
                    , 'closeCallback': function(){ window.LGOIN_POP_INS = null; } 
                    , 'cancelCallback': function(){ window.LGOIN_POP_INS = null; } 
            });
        }
        
        , {
            onerror:
            function()
            {
                alert( e.msg || '网络错误!' );
            }
        }
    );
} 
/**
 * 检查用户的通行证状态, 以防止登录通行证, 但未登陆系统
 * <script id="checkstat_scr" src="https://cas.sdo.com/cas/loginStateService?method=checkstat"></script> 
 */ 
function CHECK_STAT()
{
    if( W('#checkstat_scr').length )
    {
        W('#checkstat_scr').removeNode();
    }    
    
    var scr = document.createElement('script');
    scr.id = 'checkstat_scr';
    scr.src = 'https://cas.sdo.com/cas/loginStateService?method=checkstat';
    document.body.appendChild( scr );
}
/**
 * 快捷登录 - 头部
 * login_hover 
 */ 
function FAST_LOGIN()
{   
    var fast_login = W('.js_fast-login');   
    if( !fast_login.length ) return;
    
    window.XLogin && XLogin.resetData && XLogin.resetData();
    W("body").delegate('.js_fast-login', 'click', function($evt){
        //$evt.stopPropagation();
        $evt.preventDefault();
        //QW.Marmot.log({page_id: 'click_login', position:'top_header'});
        LOGIN_POPUP();
    
        return false;
    }); 
   /* fast_login.on
    (
        "click",
        function( $evt )
        {
            //$evt.stopPropagation();
            $evt.preventDefault();
            //QW.Marmot.log({page_id: 'click_login', position:'top_header'});
            LOGIN_POPUP();
        
            return false;
        }
    );*/
    
    return;
 
//     var fast_login = W('#fast_login');    
//     var login_popup = W('#login_popup');
//     
//     if( !( fast_login.length && login_popup.length ) ) return;
    
//     if( !Browser.firefox )
//     {    
//         if( !window.FAST_INITED )
//         {
//             login_popup.query('iframe').set('src', LOGIN_URL() );
//             window.FAST_INITED = true;
//         }
//     }
    
    if( !window.FAST_INITED )
    {
        login_popup.query('iframe').set('src', LOGIN_URL() );
        window.FAST_INITED = true;
    }
    
    POPUP_LIST.push( login_popup );
    
    fast_login.on(
        'click',
        function( $e )
        {
            $e.preventDefault();    
            $e.stopPropagation(); 
            
            if( !fast_login.hasClass( 'hoverstlye' ) )
            {
                CHECK_STAT();
            }
    
//             if( Browser.firefox )
//             {    
//                 if( !window.FAST_INITED )
//                 {
//                     login_popup.query('iframe').set('src', LOGIN_URL() );
//                     window.FAST_INITED = true;
//                 }
//             }
            
            HIDE_OTHER_POPUP( login_popup );
        
            if( fast_login.hasClass( 'hoverstlye' ) )
            {
                fast_login.removeClass( 'hoverstlye' );
                
                login_popup.css('visibility', 'hidden');
                return;
            }
            
            fast_login.addClass( 'hoverstlye' );
            
            login_popup.css('visibility','visible');
            login_popup.css('display','block');      
                
            updateSize( fast_login, login_popup);            
                          
            login_popup.css( 'z-index', getMaxIndex() );
            
            return false;
        }
    );
    
    W(document).on
    (
        'click',
        function( $e )
        {        
            if( fast_login.hasClass( 'hoverstlye' ) )
            {
                fast_login.removeClass( 'hoverstlye' );
                
                login_popup.css('visibility', 'hidden');
                return;
            }
        }
    );
    
    W(window).on( 'resize', function(){ updateSize( fast_login, login_popup); } );
    
    function updateSize( $btn, $box )
    {            
        var xy = $btn.getXY();
        var size = $btn.getSize();
            
        var css =                
            {
                "left": xy[0] + size.width - $box.getSize().width + 'px'
                , "top": xy[1] + size.height + 'px'
            };
            
        $box.css( css );
    }
    
}

/**
 * 分享圈子
 */
function SHARE_GROUP(){}
/*
function SHARE_GROUP( $container )
{
    var $list = $container ? $container.query('a.share_group') : W('a.share_group');
    
    $list.on
    (
        'click',
        function( $e )
        {   
            $e.preventDefault();
            var d = W(this).attr('data-action').trim().evalExp();
            
            if( d && d.id )
            {
                QW.Ajax.get
                (
                    '/connect/sharetips', 
                    { 'cid': d.id, 'url': d.url, "rnd": new Date().getTime() }
                    , function( e )
                    {
                        if( e && e.err == "ok" && e.data && e.data.html )
                        {                        
                            var html = e.data.html;
                            html = html.replace( /\{url\}/gi, d.url );
                            
                            XDialog.exec
                            (
                                {
                                    "tpl": html
                                    , submitCallback: 
                                    function( $ele, $evt )
                                    { 
                                        var box = W(this.model.getDialog());
                                        
                                        var type = W($ele).getAttr('data-sns').trim();                
                                        var content = box.query('textarea').val().trim();
                                        
                                        XShare.exec( { "type": type, "textContent": content, "shareType": "circle" } );
                                                                
                                        return false;
                                    }
                                }
                            );
                            SHARE_GROUP_EVENT();
                        }
                        else
                        {
                            LOGIN_POPUP();
                        }
                        
                    }
                    
                    , {
                        onerror:
                        function()
                        {
                            alert( e.msg || '网络错误!' );
                        }
                    }
                );
            }
        }
    );
    
}

*/

/**
 * 邀请好友加入圈子
 */
function INVITE_FRIEND( $container )
{   
    W(document).delegate
    (
        "a.invite_friend",
        "click",
        function( $e )
        {
            $e.preventDefault();
            
            var $srcData = {
                'type': 'invite_friend'
                , 'source': this
                , 'event': $e.type
            };

            var d = W(this).attr('data-action').trim().evalExp();
            
            if( d && d.id )
            {
                QW.Ajax.get
                (
                    '/circle/invitefriend', 
                    { 'circle_id': d.id, 'url': d.url, "rnd": new Date().getTime() }
                    , function( e )
                    {
                        if( e && e.err == "ok" && e.data && e.data.html )
                        {                  
                            var popupIns = 
                            XDialog.exec
                            (
                                {
                                    "tpl": e.data.html
                                    
                                    , submitCallback: 
                                    function( $ele, $evt )
                                    {
                                        var box = W( this.model.getDialog() );
                                        var itemList = box.query('.itemList');
                                        var link_url = box.query('input[name=link_url]');
                                        
                                        var result = [];
                                        var isOk = true;              
                                        
                                        itemList.query('input[type=text]').forEach
                                        (
                                            function( $ele )
                                            {
                                                var m = W($ele).val().trim();
                                                
                                                if( m )
                                                {
                                                    if( isEmail( m ) )
                                                    {
                                                        result.push( m );
                                                    }
                                                    else
                                                    {
                                                        isOk = false;
                                                        return false;
                                                    }
                                                }
                                            }
                                        );
                                        
                                        if( !isOk )
                                        {
                                            alert( '请填写正确的邮箱!' );
                                            return false;
                                        }
                                                        
                                        
                                        if( result.length )
                                        {
                                            var id = box.query('input[name=id]').val();  
                                        
                                            QW.Ajax.get
                                            (
                                                '/user/sendinvite', 
                                                { 'type': 'circle', 'content': link_url.val(), 'mail_list': result.join(), "circle_id": id }
                                                , function( e )
                                                {
                                                    if( e && e.err == "ok" )
                                                    {
                                                        //alert( '操作成功' )
                                                        INVITE_FRIEND_SUCCESS( e ); 
                                                    }
                                                    else
                                                    {
                                                        alert( e.msg || '操作失败!' );
                                                    }
                                                    
                                                }
                                                
                                                , {
                                                    onerror:
                                                    function()
                                                    {
                                                        alert( e.msg || '网络错误!' );
                                                    }
                                                }
                                            );
                                            
                                        }
                                        else
                                        {
                                            alert( '请至少输入一个邮箱!' );
                                            return false;
                                        }
                
                
                                    }
                                    
                                    , initCallback:
                                    function( $dialog )
                                    {                         
                                        var box = W( this.model.getDialog() );
                                        var addOne = box.query('.addOne');
                                        var link_url = box.query('input[name=link_url]');
                                        var copy_link = box.query('.copy_link');
                                        var itemList = box.query('.itemList');
                                        
                                        setTimeout
                                        (
                                            function()
                                            {
                                                itemList.query('input[type=text]')[0].focus();
                                            }
                                            , 200
                                        );
                            
                                        copy_link.on
                                        (
                                            'click',
                                            function( $evt )
                                            {
                                                $evt.preventDefault();
                                                link_url.focus();
                                                link_url[0].select();
                                                
                                                copy_f( link_url.val() );
                                                
                                                return false;
                                            }
                                        );
                                        
                                        addOne.on
                                        (
                                            'click',
                                            function()
                                            {
                                                var max = 5;
                                            
                                                var len = itemList.query('> div').length;
                                                
                                                if( len >= max )
                                                {
                                                    alert('每次最多只能邀请 ' + max +'人.' );
                                                    return;
                                                }
                                                
                                                var newNode = W(itemList.query('> div')[0]).cloneNode(true);
                                                itemList.appendChild( newNode );
                                                newNode.query('input').val('').focus();
                                                
                                                itemList.query('> div').last().appendChild( addOne );
                                            }
                                        );
                                    }
                                }
                            );
                            
                            XLogin.ondone();
                        }
                        else
                        {
                            $srcData && window.XLogin && XLogin.storeTriggerData( e, $srcData );
                            LOGIN_POPUP();
                        }
                        
                    }
                    
                    , {
                        onerror:
                        function()
                        {
                            alert( e.msg || '网络错误!' );
                        }
                    }
                );
            }
            
            return false;
        }
    );      
} 
/**
 * 邀请好友成功
 */
function INVITE_FRIEND_SUCCESS( $d )
{
    var timeCount = 8;
    var timerInterval;

    if( $d && $d.data && $d.data.html )
    {
        XDialog.exec
        (
            {
                tpl: $d.data.html
                
                , closeCallback:
                function( $ele, $evt )
                {
                    if( timerInterval )
                    {
                        clearInterval( timerInterval );
                    }
                }
                
                , initCallback: 
                function( $ele, $evt )
                {
                    var p = this;
                    var dialog = W( this.model.getDialog() );
                    
                    dialog.query( '.timer__' ).html( timeCount );
                    
                    timerInterval = 
                    setInterval
                    (
                        function()
                        {
                            timeCount--;
                            if( timeCount < 1 )
                            {
                                p.close();
                                clearInterval( timerInterval );
                                return;
                            }
                            dialog.query( '.timer__' ).html( timeCount );
                        }
                        , 1000
                    );
                }
            }
        );
    }
    else
    {
        alert( '操作成功' );
    }
}
/**
 * 用户名菜单
 */ 
function USER_MENU()
{
    var target = W(".logined_wrap");
    var pop = W(".logined_");
    target.on("mouseover",function(){
        pop.show();
        W(this).addClass("logined_pop");
    }).on("mouseout",function(){
        pop.hide();
        W(this).removeClass("logined_pop");
    });
    /*W("#logined .headp").click(function(){
        $e.preventDefault();
    });*/
}
/**
 *  发现
 */
 function DISCOVER(){
    var target = W("a.discover_").parentNode("li");
    var pop = W("div.popup_discover_");
    target.on("mouseover",function(){
        pop.show();
    }).on("mouseout",function(){
        pop.hide();
    });
 }
/**
 * 我看过的
 */
function WATCHED()
{
    if(W('#logined_mark').length )return;

    var isOut = true;
    var watched_ = W('a.watched_');    
    var popup_watched_ = W('div.popup_watched_');  
    POPUP_LIST.push( popup_watched_ );
    
    var popup_watched_del_all = popup_watched_.query('.del_all');                    
    popup_watched_del_all.on
    (
        'click',
        function()
        {
            popup_watched_.hide();
            watched_.removeClass('selected');
        }
    );
    popup_watched_.on
    (
        'click',
        function($e)
        {
            $e.stopPropagation();   
        }
    ).on('mouseover',function(){
        isOut = false;
    }).on('mouseout',function(){
        isOut = true;
        clearPop();
    });

     watched_.on("click",function(e){
        e.preventDefault();   
     });
        
    watched_.on
    (        
        'mouseover',
        function($e)
        {
            isOut = false;
            //HIDE_OTHER_POPUP(popup_watched_);
            
            if( popup_watched_.css('display') == 'none' )
            {            
                var watched_str = QW.Cookie.get('watched_list').trim();
                if( watched_str )
                {
                    var watched = watched_str.evalExp();
                    var watchedAr = [];
                    
                    for( var k in watched )
                    {
                        watchedAr.push( watched[k] );
                    }
                    
                    watchedAr = watchedAr.reverse();
                    
                    QW.Ajax.get
                    (
                        '/video/getmulti', { "ids": watchedAr.join(',') },
                        function( $d )
                        {
                            //alert( Object.stringify( $d ) )
                            if( $d && $d.err == 'ok' && $d.data )
                            {
                            
                                var tpl = 
                                [
                                "<div class=\"li alt{zebra}\">\n"
                                ,"	<span class=\"bt-del\"></span>\n"
                                ,"	<div class=\"name\"><a href=\"{url}\">{name}</a></div>\n"
                                ,"	<div class=\"label\">观看时间：{watched_time}</div>\n"
                                ,"    <input type=\"hidden\" name=\"id\" value=\"{id}\" />\n"
                                ,"</div>\n"
                                ].join('');
                                
                                var result = [];                    
                                var watched_item = '';
                                var _z = 1;
                                
                                for( var i = 0; i < watchedAr.length; i++ )
                                {
                                    for( var k in $d.data )
                                    {
                                        var zebra_class = _z++ % 2 ? ' zebra' : '';
                                        var item = $d.data[k];                                    
                                        
                                        if( watchedAr[i].toString().trim() != item._id.toString().trim() ) continue;
                                        
                                        for( var subk in watched )
                                        {
                                            if( watched[subk] === k )
                                            {
                                                var dt = new Date();
                                                dt.setTime( parseInt(subk) * 1000 );
                                                
                                                watched_item = [ dt.getMonth()+1,'月'
                                                                , dt.getDate(), '日 '
                                                                , pad_char_f(dt.getHours()), ':'
                                                                , pad_char_f(dt.getMinutes()), ':'
                                                                , pad_char_f(dt.getSeconds()) 
                                                                ].join('')
                                                break;
                                            }
                                        }
                                         
                                        result.push
                                        (
                                            tpl
                                            .replace( /\{name\}/gi, item.title )
                                            .replace( /\{id\}/gi, item._id )
                                            .replace( /\{url\}/gi, '/video?id=' + item._id )
                                            .replace( /\{watched_time\}/gi, watched_item )
                                            .replace( /\{zebra\}/gi, zebra_class )
                                        );
                                    }
                                }
                                popup_watched_.query('.ul').html( result.join('') );
                                
                                popup_watched_.query('.bt-del').on
                                (
                                    'click',
                                    function()
                                    {         
                                        var vid = W(this).parentNode('div').query( 'input' ).get('value');
                                        
                                        QW.VideoAction.rmFromCookieList( vid, 'watched_list' )
                                        
                                        W(this).parentNode('div').removeNode();
                                    }
                                );
                                
                                popup_watched_del_all.show();
                            }
                        }
                    );        
                } 
                else
                {
                    popup_watched_del_all.fire('click');
                }
                
               // updateSize( watched_, popup_watched_);
                
                W(this).addClass('selected');
                popup_watched_.show();
                popup_watched_.css( 'z-index', getMaxIndex() );
            }
            /*else
            {                
                popup_watched_.hide();
                W(this).removeClass('selected');
            }*/
            
            return false;
        }
        
        
    );
    watched_.on('mouseout',function(){
        isOut = true;
        clearPop();
    });
    function clearPop(){
        setTimeout(function(){
            if(isOut){
                popup_watched_.hide();
                watched_.removeClass('selected');
            }
        },20)
    }
    
    //W(window).on( 'resize', function(){ updateSize( watched_, popup_watched_); } );
    
    /*function updateSize( $btn, $box )
    {            
        var xy = $btn.getXY();
        var size = $btn.getSize();
            
        var css =                
            {
                "left": xy[0] + size.width / 2 - 50 + 'px'
                , "top": xy[1] + size.height + 'px'
            };
            
        $box.css( css );
    }*/
} 
 
/**
 * 以后观看
 */
function WATCH_LATER()
{
    if( W('#logined_mark').length ) return;
    var isOut = true;
    var watch_later_ = W('a.watch_later_');
    
    var popup_watch_later_ = W('div.popup_watch_later_'); 
        
    POPUP_LIST.push( popup_watch_later_ );

    var popup_watch_later_del_all = popup_watch_later_.query('.del_all');                    
    popup_watch_later_del_all.on
    (
        'click',
        function()
        {
            popup_watch_later_.hide();
            watch_later_.removeClass('selected');
        }
    );

    popup_watch_later_.on
    (
        'click',
        function($e)
        {
            $e.stopPropagation();   
        }
    ).on('mouseover',function(){
        isOut = false;
    }).on('mouseout',function(){
        isOut = true;
        clearPop();
    });

    
    watch_later_.on
    (
        'mouseover',
        function( $e )
        {
            isOut = false;       
            
            //HIDE_OTHER_POPUP( popup_watch_later_ );
        
            if( popup_watch_later_.css('display') == 'none' )
            {
                var watchlater_list_str = QW.Cookie.get('watchlater_list').trim();
                if( watchlater_list_str )
                {
                    var watchlater = watchlater_list_str.evalExp();
                    var watchlaterAr = [];
                    
                    for( var k in watchlater )
                    {
                        watchlaterAr.push( watchlater[k] );
                    }
                    watchlaterAr = watchlaterAr.reverse();
                    
                    QW.Ajax.get
                    (
                        '/video/getmulti', { "ids": watchlaterAr.join(',') },
                        function( $d )
                        {
                            //alert( Object.stringify( $d ) )
                            if( $d && $d.err == 'ok' && $d.data )
                            {
                                var tpl = 
                                [
                                "<div class=\"li alt{zebra}\">\n"
                                ,"	<span class=\"bt-del\"></span>\n"
                                ,"	<div class=\"name\"><a href=\"{url}\">{name}</a></div>\n"
                                ,"	<div class=\"label\">添加时间：{watched_time}</div>\n"
                                ,"    <input type=\"hidden\" name=\"id\" value=\"{id}\" />\n"
                                ,"</div>\n"
                                ].join('');       
                                  
                                var result = [];
                                var watched_item = '';
                                var _z = 1;
                                
                                for( var i = 0; i < watchlaterAr.length; i++ )
                                {        
                                    for( var k in $d.data )
                                    {
                                        var zebra_class = _z++ % 2 ? ' zebra' : '';
                                        var item = $d.data[k];
                                        
                                        if( watchlaterAr[i].toString().trim() != item._id.toString().trim() ) continue;
                                        
                                        for( var subk in watchlater )
                                        {
                                            if( watchlater[subk] === k )
                                            {
                                                var dt = new Date();
                                                dt.setTime( parseInt(subk) * 1000 );
                                                
                                                watched_item = [ dt.getMonth()+1,'月'
                                                                , dt.getDate(), '日 '
                                                                , pad_char_f(dt.getHours())
                                                                , ':', pad_char_f(dt.getMinutes())
                                                                , ':', pad_char_f(dt.getSeconds()) 
                                                                ].join('')
                                                break;
                                            }
                                        }
                                         
                                        result.push
                                        (
                                            tpl
                                            .replace( /\{name\}/gi, item.title )
                                            .replace( /\{id\}/gi, item._id )
                                            .replace( /\{url\}/gi, '/video?id=' + item._id )
                                            .replace( /\{watched_time\}/gi, watched_item )
                                            .replace( /\{zebra\}/gi, zebra_class )
                                        );                        
                                    }
                                }
                                popup_watch_later_.query('.ul').html( result.join('') );
                                
                                popup_watch_later_.query('.bt-del').on
                                (
                                    'click',
                                    function()
                                    {
                                        var vid = W(this).parentNode('div').query( 'input' ).get('value');
                                        
                                        QW.VideoAction.rmFromCookieList( vid, 'watchlater_list' )
                                        
                                        W(this).parentNode('div').removeNode();
                                    }
                                );
                            }
                        }
                    );        
                }
                else
                {
                    popup_watch_later_.query('.ul').html( '' );
                    popup_watch_later_.fire('click');;
                }
                
               // updateSize( watch_later_, popup_watch_later_);
                
                W(this).addClass('selected');
                popup_watch_later_.show();
                popup_watch_later_.css( 'z-index', getMaxIndex() );
            }
            /*else
            {
                popup_watch_later_.hide();
                W(this).removeClass('selected');
            }*/
            
            return false;
        }
    );
    
    watch_later_.on('mouseout',function(){
        isOut = true;
        clearPop();
    });
    function clearPop(){
        setTimeout(function(){
            if(isOut){
                popup_watch_later_.hide();
                watch_later_.removeClass('selected');
            }
        },20)
    }
    //W(window).on( 'resize', function(){ updateSize( watch_later_, popup_watch_later_); } );
    
   /* function updateSize( $btn, $box )
    {            
        var xy = $btn.getXY();
        var size = $btn.getSize();
            
        var css =                
            {
                "left": xy[0] + size.width / 2 - 50 + 'px'
                , "top": xy[1] + size.height + 'px'
            };
            
        $box.css( css );
    }*/
} 
/**
 * 自定义标签通用方法
 * x@btbtd.org  2012/4/24 
 */ 
function INIT_TAG_BOX()
{
    var tag_box = W('#tag_box');
    
    if( !tag_box.length ) return;
    
    var URL_ADD = window.GROUP_ADD_URL || '/circle/addTag';
    var URL_DEL = window.GROUP_DEL_URL || '/circle/delTag';
    var MAX_USER_TAG = 20;
    var formaddtag = W('#formaddtag');
    var xplus = W('#xplus');
    
    tag_list().forEach
    (
        function( $ele )
        {
            init_event( $ele );
        }
    );      
    
    W(document).on
    (
        'click', 
        function( $evt )
        {    
            tag_box.query('.tagadd').hide();
        } 
    );
    
    var iptText = tag_box.query('.ipt-text');
    var iptSubmit = tag_box.query('.ipt-submit');
    
    tag_box.query('.ipt-text, .ipt-submit').on
    (
        'click',
        function( $evt )
        {
            $evt.preventDefault();
            $evt.stopPropagation();
        }
    );
    
    hide_or_show_add();
    
    iptSubmit.on
    (
        'click',
        function( $evt )
        {
            var tag = iptText.val().trim() || '';
            
            var $srcData = {
                'type': 'circle_user_tag_box'
                , 'source': this
                , 'event': $evt.type
            };
            
            if(!tag)
            {
                if( window.IS_USER_TAG )
                {
                    alert('兴趣标签不能为空!');
                }
                else
                {
                    alert('圈子标签不能为空!');
                }
                
                return false;
            }
            
            if( /\<|\>|"|'/.test(tag) )
            {
                alert('标签名不能含有特殊字符(<>"\')!');
                return false;  
            }
            
            show_or_hide_input( false );
        
            QW.Ajax.post
            (
                URL_ADD
                , { "tag": tag, "circle": window.CIRCLE_ID, "cat": window.CIRCLE_ID }
                , function( $d )
                {
                    var isErr = true;
                
                    if( $d.err == 'ok' )
                    {
                        tag_box.query('.ipt-text').val('');
                        //alert('已经添加标签 "' + tag +'"');
                        
                        var tagText = tag.encode4Html();
                        var tagUriEncode = encodeURIComponent(tag);
                        var newTag = null;
                        
                        if( window.IS_USER_TAG )
                        {
                            tagText = '<a href="/search?q='+encodeURIComponent(tag)+'" target="_blank">'+tag.encode4Html()+'</a>';
                            newTag = W('<div class="tag-item tag-cus"><span class="con tag_link">'+tagText+'</span><span class="del"></span></div>');
                        } else {
                        	var baseUrl = tag_box.attr('url_base');
                        	newTag = W('<div class="tag-item tag-cus"><a href="'+baseUrl+"/"+tagUriEncode+'"><span class="con tag_link">'+tagText+'</span></a><span class="del" data="'+tagUriEncode+'"></span></div>');
                        }             
                        tag_box.query('.taglist').insertBefore( newTag, tag_box.query('.tag-item-add') );
                        init_event(newTag);
                        iptText.val( '' );
                        
                        isErr = false;
                        XLogin.ondone();
                    }
                    else if( $d.err == 'sys.permission.need_login' )
                    {
                        $srcData && window.XLogin && XLogin.storeTriggerData( $d, $srcData );
                        LOGIN_POPUP();
                    }
                    else if( $d.err == 'usr.submit.valid' )
                    {
                        alert( getMessage($d.msg) || '操作失败' );
                        show_or_hide_input( true );
                    }
                    else
                    {
                        alert( getMessage($d.msg) || '操作失败' );
                        show_or_hide_input( true );
                    }
                    
                    hide_or_show_add( isErr );
                }
                
                , {
                    onerror:
                    function()
                    {
                        alert( e.msg || '网络错误!' );
                    }
                }
            );
        }
    );    
    
    formaddtag.on
    (
        "submit",
        function($evt)
        {
            $evt.preventDefault();
            $evt.stopPropagation();
            
            iptSubmit.fire('click');
        
            return false;
        }
    );
    
    xplus.on
    (
        'click',
        function(e)
        {
            e.preventDefault();
            e.stopPropagation();//这个不能注掉, 影响逻辑
            
            if( W(this).hasClass('xplus_tag_dis') )
            {
                return false;
            }
            else
            {
                tag_box.query('.tagadd').toggle();
                
                if( tag_box.query('.tagadd').css('display') != 'none' )
                {
                    tag_box.query('.ipt-text').focus();
                }
            }
            
            QW.Marmot.log({page_id:'click_addtag'});
            
            return false;
        }
    );
    
    function show_or_hide_input( $show )
    {
        if( $show )
        {
            tag_box.query('.tagadd').show();
            tag_box.query('.ipt-text').focus();
        }
        else
        {
            tag_box.query('.tagadd').hide();
        }
    }
    
    function hide_or_show_add( $isErr )
    {
        if( tag_box.query('.tag-cus').length >= MAX_USER_TAG )
        {
            xplus.addClass('xplus_tag_dis');
            xplus.removeClass('xplus_tag');
        }
        else
        {
            if( !$isErr ) 
            {
                xplus.addClass('xplus_tag');
                xplus.removeClass('xplus_tag_dis');
            }
        }
    }
    
    function init_event( $ele )
    {       
        W($ele).on
        (
            'mouseenter',
            function()
            {
                if( !W(this).hasClass('tag-sys') ) W(this).addClass('tag-item-del');
            }
        ).on
        (
            'mouseleave',
            function()
            {
                W(this).removeClass('tag-item-del');
            }
        );
        
        W($ele).query('.del').on
        (
            'click',
            function($evt)
            {
                var me = W(this);
            	var prnt = W(me.parentNode());
                var tag = me.attr('data');
                
                if(tag) {
                	tag = decodeURIComponent(tag);
                } else {
                	tag = prnt.query('a').html().trim();
                }
                
                QW.Ajax.post
                (
                    URL_DEL
                    , { "tag": tag, "circle": window.CIRCLE_ID, "cat": window.CIRCLE_ID }
                    , function( $d )
                    {
                        if( $d.err == 'ok' )
                        {
                            prnt.removeNode();
                            //alert('已经删除标签 "' + tag +'"');
                        }
                        else if( $d.err == 'usr.submit.valid' )
                        {
                            alert( getMessage($d.msg) || '操作失败' );
                        }
                        else
                        {
                        
                        }
                        
                        hide_or_show_add();
                    }
                    
                    , {
                        onerror:
                        function()
                        {
                            alert( e.msg || '网络错误!' );
                        }
                    }
                );
            }
        );
    }
    
    function tag_list()
    {
        return tag_box.query( '.tag-item' );
    }
}//end INIT_TAG_BOX
/**
 * 判断字符串是否为电子邮件
 * 此判断较为灵活~
 * x@btbtd.org  2012-2-21  
 */ 
function isEmail( $s )
{
    return /.+\@.+\.[\w\W]{2,}/.test( $s )
}
/**
 * 取屏幕的可用大小相关值
 * @author      suches@btbtd.org
 * @date        2008-9-19
 */         
function viewport_f() 
{
    var myWidth = 0, myHeight = 0;
    if(typeof(window.innerWidth ) == 'number' ) 
    {/* Non-IE */
        width_i = window.innerWidth; height_i = window.innerHeight;
    } 
    else if 
    (
        document.documentElement &&( document.documentElement.clientWidth || document.documentElement.clientHeight ) 
    ) 
    {/* IE 6 */
        width_i = document.documentElement.clientWidth; height_i = document.documentElement.clientHeight;
    } 
    else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) 
    {/* IE 4 */
        width_i = document.body.clientWidth; height_i = document.body.clientHeight;
    }
    
    var sLeft = document.documentElement.scrollLeft || document.body.scrollLeft;
    var sTop = document.documentElement.scrollTop || document.body.scrollTop;
    var oWidth = document.documentElement.offsetWidth || document.body.offsetWidth;
    var oHeight = document.documentElement.offsetHeight || document.body.offsetHeight
    
    return {
                width:width_i
                , height:height_i
                , max_width: width_i + sLeft
                , max_height: height_i + sTop
                , scrollLeft: sLeft
                , scrollTop: sTop
                
                , body_width: oWidth
                , body_height: oHeight
          };
}
/**
 *  @description    兼容各浏览器的附加事件函数
 *  @author         suches
 *  @date           2008-9-22
 */ 
function attach_event_f(e_, event_name_s, func_f, capture_b)
{
    if(document.addEventListener){e_.addEventListener(event_name_s, func_f, capture_b);}
    else if(document.attachEvent)
    {
        event_name_s = 'on'+event_name_s;
        if(e_[event_name_s]==null){e_[event_name_s] = func_f;} else{e_.attachEvent(event_name_s, func_f);}
    }
}
/**
 * 复制到粘贴板 (IE only)
 * x@btbtd.org  2012-2-21 
 */ 
function copy_f(text)  
{  
    if(window.clipboardData)  
    {  
        window.clipboardData.setData('text',text);  
    }  
    else  
    {  
        
    }    
    return false;  
} 
/**
 * 兼容各浏览器的附加事件函数
 * @author         suches
 * @date           2008-9-22
 */ 
function detach_event_f(e_, event_name_s, func_f, capture_b)
{
    if(document.removeEventListener){e_.removeEventListener(event_name_s, func_f, capture_b);}
    else if(document.detachEvent)
    {
        event_name_s = 'on'+event_name_s;
        if(e_[event_name_s]){e_.detachEvent(event_name_s, func_f);}
    }
}
/**
 * 获取鼠标位于屏幕X轴绝对位置
 * x@btbtd.org  2012-3-1 
 */ 
function mouseX(evt) 
{
    if (evt.pageX) return evt.pageX;
    else if (evt.clientX)
        return evt.clientX 
                + (document.documentElement.scrollLeft 
                ? document.documentElement.scrollLeft 
                : document.body.scrollLeft);
    else return null;
}
/**
 * 获取鼠标位于屏幕Y轴绝对位置
 * x@btbtd.org  2012-3-1 
 */ 
function mouseY(evt) {
    if (evt.pageY) return evt.pageY;
    else if (evt.clientY)
        return evt.clientY 
                + (document.documentElement.scrollTop 
                ? document.documentElement.scrollTop 
                : document.body.scrollTop);
    else return null;
}
/**
 * @description    取某个元素的座标和大小
 * @author         suches
 * @date           2008-9-9
 */ 
function ele_pos_f(arg_e)
{
    var a = [], t = arg_e.offsetTop, l = arg_e.offsetLeft, w = arg_e.offsetWidth, h = arg_e.offsetHeight; 
    while(arg_e = arg_e.offsetParent){ t += arg_e.offsetTop; l += arg_e.offsetLeft; } 
    return {top:t, left:l, width:w, height:h};
}
/**
 * 文件框/内容框 输入tip
 * @author      suches@btbtd.org
 * @date        2011-7-31  
 * @requires    getId, attach_event_f
 * @example
 *      controlTip( document.getElementById("comment_content"), msg );  
 */ 
function controlTip( $control, $msg, $params )
{
    //alert( $control )   
    if( !($control = getId( $control ) ) ) return;
    $params = $params||{};
    $params.normalColor = $params.normalColor || '#000000';
    $params.tipColor = $params.tipColor || '#999999';
    $control.setAttribute('controltip', $msg);
        
    attach_event_f
    ( 
        $control,
        'focus',
        function($evt)
        {
            if( $control.value == $msg )
            {
                $control.value = '';
            }
            $control.style.color = $params.normalColor;
        } 
    );
    
    attach_event_f
    ( 
        $control,
        'blur',
        function($evt)
        {
            if( $control.value == '' )
            {
                $control.value = $msg;
                $control.style.color = $params.tipColor;
            }
        } 
    );
    
    if( !$control.value || $control.value == $msg )
    {
        $control.value = $msg;
        $control.style.color = $params.tipColor;
    }
}
/**
 * 从对象ID取得DOM对象
 * @date        2011-7-21      
 */         
function getId($id)
{
    return typeof $id==="string"?document.getElementById($id):$id;
}

/**
 * 阻止事件冒泡
 * @author      suches@btbtd.org
 * @date        2011-7-26                  
 */                 
function stopPropagation($evt)
{            
    $evt = $evt || window.event;
    if( $evt.preventDefault ) $evt.stopPropagation(); else $evt.cancelBubble = false;
}
    
/**
 * 从 class 获取DOM节点
 * @author      suches@btbtd.org
 * @date        2011-8-1
 * @requires    HasClass               
 */
function getClass( $class, $box, $r )
{
    $box = $box || document.body;
    $r = $r || [];
    
    var first = $box.firstChild;
    
    if( first )
    {
        if( HasClass( first, $class ) )
        {
            $r.push( first );
        }
        
        if( first.childNodes.length )
        {
            arguments.callee( $class, first, $r );
        }
        
        while( first = first.nextSibling )
        {
            if( HasClass( first, $class ) )
            {
                $r.push( first );
            }
        
            if( first.childNodes.length )
            {
                arguments.callee( $class, first, $r );
            }
        }
    }
    
    return $r;
}         
/**
 * 设置HTML 元素透明度
 * @author      suches@btbtd.org
 * @date        2008-9-5
 */ 
function opacity_f(e_, opacity_i)
{
    e_.style.opacity = opacity_i/100;
    e_.style.filter = 'alpha(opacity=' + opacity_i + ')';
    return;
}
//***Adds a new class to an object, preserving existing classes
function AddClass(obj,cName){ KillClass(obj,cName); return obj && (obj.className+=(obj.className.length>0?' ':'')+cName); }

//***Removes a particular class from an object, preserving other existing classes.
function KillClass(obj,cName){ return obj && (obj.className=obj.className.replace(new RegExp("^"+cName+"\\b\\s*|\\s*\\b"+cName+"\\b",'g'),'')); }

//***Returns true if the object has the class assigned, false otherwise.
function HasClass(obj,cName){ return (!obj || !obj.className)?false:(new RegExp("\\b"+cName+"\\b")).test(obj.className) }

/**
 * 遍历键值对象或数组
 * @author      suches@btbtd.org
 * @date        2011-7-24
 */        
function xeach( $list, $callback )
{
    var count = 0;
    
    if( $list.length )
    {
        for( var i = 0, j = $list.length; i < j; i++ )
        {
            if( inner( i, count, $callback ) === false )
            {
                return;
            }
            count++;
        }
    }
    else
    {    
        for( var o in $list )
        {
            
            if( inner( o, count, $callback ) === false )
            {
                return;
            }
            count++;
        }
    }
    
    function inner( $key )
    {
        if( $callback )
        {
            return $callback( $list[$key], count );
        }
    }
}
/**
 * 删除数组指定索引
 * @author      suches@btbtd.org
 * @date        2009-4-21
 */          
function remove_array_item( arr, index_ )
{
  return arr = arr.slice(0, index_).concat( arr.slice(index_+1) );
}  
/**
 * X/Y 轴数据类
 * @author      suches@btbtd.org
 * @date        2012-2-24  
 */     
function Point( $x, $y )
{
    this.x = $x;
    this.y = $y;
}
/**
 * 交换DOM对象位置函数
 * x@btbtd.org  2012-3-26 
 */ 
function xswap( $src, $tar )
{
    var temp = $src.cloneNode(true);
    temp.style.display = 'none';
    
    $tar.parentNode.insertBefore( temp, $tar );
    $src.parentNode.insertBefore( $tar, $src );
    temp.parentNode.insertBefore( $src, temp );
    temp.parentNode.removeChild( temp );
}
/**
 * 阻止事件默认行为
 * @author      suches@btbtd.org
 * @date        2011-7-26                  
 */                 
function preventDefault($evt)
{            
    $evt = $evt || window.event;
    if( $evt.preventDefault ) $evt.preventDefault(); else $evt.returnValue = false;
}
/**
 * 字符串补长函数
 * x@btbtd.org      2008-9-24
 */
function pad_char_f(input_s, len_i, char_s)
{/* shawl.qiu code, return string */      
  len_i   =  len_i||2; 
  char_s  = char_s||"0"; 
  
  input_s = input_s.toString();
  if(input_s.length>len_i){ return input_s; }
  input_s   = new Array(len_i+1).join(char_s)+input_s;
  
  return input_s.slice(input_s.length-len_i);
}/* function pad_char_f(input_s, len_i, char_s) */
/**
 * 删除URL参数
 * x@btbtd.org  2012/4/24  
 * @example
        var url = del_url_param( location.href, 'tag' );
 */ 
function del_url_param( $url, $key )
{
    var sharp = '';
    if( $url.indexOf('#') > -1 )
    {
        sharp = $url.split('#')[1];
        $url = $url.split('#')[0];
    }
    
    if( $url.indexOf('?') > -1 )
    {
        var params = $url.split('?')[1];
        var $url = $url.split('?')[0];
        
        var paramAr = params.split('&');
        var newParamAr = [];
        for( var i = 0; i < paramAr.length; i++ )
        {
            var items = paramAr[i].split('=');
            
            items[0] = items[0].replace(/^\s+|\s+$/g, '');
             
            if( items[0].toLowerCase() == $key.toLowerCase() )
            {
                continue;
            } 
            newParamAr.push( items.join('=') )
        }
        $url += '?' + newParamAr.join('&');
    }
    
    if( sharp )
    {
        $url += '#' + sharp;
    }
    
    return $url;
}
/**
 * 添加URL参数
 * x@btbtd.org  2012/4/24 
 * @require     del_url_param, 删除URL参数
 * @example
        var url = add_url_param( location.href, {'key': 'tag', 'value': tag } );
 */ 
function add_url_param( $url, $param )
{
    var sharp = '';
    if( $url.indexOf('#') > -1 )
    {
        sharp = $url.split('#')[1];
        $url = $url.split('#')[0];
    }
    
    $url = del_url_param($url, $param.key);
    
    if( $url.indexOf('?') > -1 )
    {
        $url += '&' + $param.key +'=' + $param.value;
    }
    else
    {
        $url += '?' + $param.key +'=' + $param.value;
    }
    
    if( sharp )
    {
        $url += '#' + sharp;
    }
    
    $url = $url.replace(/\?\&/g, '?' );
    
    return $url;   
}
/**
 * 取URL参数的值
 * x@btbtd.org  2012/4/24 
 * @example
        var defaultTag = get_url_param(location.href, 'tag');  
 */ 
function get_url_param( $url, $key )
{
    var result = '';
    if( $url.indexOf('#') > -1 ) $url = $url.split('#')[0];
    
    if( $url.indexOf('?') > -1 )
    {
        var paramAr = $url.split('?')[1].split('&');
        for( var i = 0; i < paramAr.length; i++ )
        {
            var items = paramAr[i].split('=');
            
            items[0] = items[0].replace(/^\s+|\s+$/g, '');
             
            if( items[0].toLowerCase() == $key.toLowerCase() )
            {
                result = items[1];
                break;
            } 
        }
    }
    
    return result;
}

/**
 * 重载页面
 */ 
function reload_page( $url )
{
    $url = ($url || location.href).split('#')[0];
    
    location.reload( $url );
}
/**
 * 兼容的添加 onpropertychange 事件
 * x@btbtd.org  2008-9-11 
 * @example
 *        onpropertychange_f( eleObj, function($evt){ alert('ok') } );
 */ 
function onpropertychange_f($ele, $func, $capture)
{
    if(document.addEventListener) return W($ele).on("input", $func);
    if(document.attachEvent) return W($ele).on("propertychange",$func);
    return false;
}
/**
 * 判断是否为 Firefox 浏览器
 * x@btbtd.org  2012/6/19 
 */ 
function isFF()
{
    return ( navigator.userAgent.indexOf('Firefox') >= 0 );
}
/**
 * 获取 控件 光标位置
 * x@btbtd.org  2012-3-1 
 */   
function get_cursor(target) 
{
    var r = 0;
    if (typeof target.selectionStart == "number"
        && typeof target.selectionEnd == "number") 
    {
        r = target.selectionStart;
    } 
    else if (document.selection) 
    {  
        var trs = document.selection.createRange();  
        if(trs.parentElement()== target)
        {   
          var tro = document.body.createTextRange();    
          tro.moveToElementText(target);   
           
          for (r=0; tro.compareEndPoints("StartToStart", trs) < 0; r++)
          {    
            tro.moveStart('character', 1);    
          }   
          
          for (var i = 0; i <= r; i ++)
          {   
            if (target.value.charAt(i) == '\n') r++;   
          }    
        }   
    }
    return r;
}
/**
 * 设置 控件 光标位置
 * x@btbtd.org  2012-3-1 
 */   
function set_cursor(ctrl, pos)
{
    if(ctrl.setSelectionRange)
    {
        ctrl.focus();
        ctrl.setSelectionRange(pos,pos);
    }
    else if (ctrl.createTextRange) 
    {
        var tro = ctrl.createTextRange();   
        var LStart = pos;   
        var LEnd = pos;   
        var start = 0;   
        var end = 0;   
        var value = ctrl.value;
           
        for(var i=0; i<value.length && i<LStart; i++)
        {   
          var c = value.charAt(i);   
          if(c!='\n') start++;  
        }
           
        for(var i=value.length-1; i>=LEnd && i>=0; i--)
        {   
          var c = value.charAt(i);   
          if(c!='\n') end++;  
        }   
        tro.moveStart('character', start);   
        tro.moveEnd('character', -end);   
        tro.select();   
        ctrl.focus();   
    }
}
/**
 * 取得正确的按键CODE
 * x@btbtd.org 2012/7/3 
 */ 
function keycode_f( $evt )
{
    $evt = $evt || window.event;
    return {
        "keyCode": $evt.keyCode || $evt.which
        , "shiftKey": $evt.shiftKey
        , "ctrlKey": $evt.ctrlKey
        , "altKey": $evt.altKey
    };
}
/**
 * DOM方式添加脚本块
 * @author      suches@btbtd.org
 * @date        2011-7-5  
 */ 
function XAppendScript( $url, $box )
{
    var ele = document.createElement("script");  
    ele.src = $url;  
    if(!$box) $box = document.body;
    $box.appendChild( ele );
    return ele;
} 
/**
 * 取DOM对象的运行时样式
 * x@btbtd.org  2012/7/13
 */ 
function style_f( $ele, $style)
{
    var r = '', isIE = !!window.ActiveXObject;
    if( isIE ){ r = $ele.currentStyle[$style]; }
    else{ r = getComputedStyle($ele)[$style]; }
    return r;
}
