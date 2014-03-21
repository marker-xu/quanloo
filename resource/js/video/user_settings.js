/**
 * 用户设置页
 * x@btbtd.org  2012-2-23 
 */ 
Dom.ready
(
    function()
    {
        TAB_SETTING();
        TAB_HOMEPAGE();
        TAB_PASSWORD();
        TAB_AVATAR();
    }
);

/**
 * TAB-修改头像
 */
function TAB_AVATAR()
{
    
} 

/**
 * TAB-修改密码
 */
function TAB_PASSWORD()
{
    var box = W('#password_');
    if( !box.length ) return;
    
    var server = '/user/modifypassword';
    
    var form = box.query('form');
    var submit = box.query( '.btn-complete' );
    
    var csrf_token = box.query('input[name=csrf_token]');
    
    var pass_old = box.query('input[name=pass_old]');
    var password = box.query('input[name=password]');
    var pass_again = box.query('input[name=pass_again]');
    
    pass_old.on('blur', checkPwdFunc ).on('keyup',checkPwdFunc);
    password.on('blur', checkPwdFunc ).on('keyup',checkPwdFunc);
    pass_again.on('blur', checkPwdFuncEx ).on('keyup',checkPwdFuncEx);
    
    var final_tips = box.query('.final_tips');
    
    form.on
    (
        'submit',
        function( $e )
        {
            $e.preventDefault();
            
            return false;
        }
    );
    
    submit.on
    (
        'click',
        function()
        {            
            if( !checkPwdFunc.call( pass_old ) ){ return false; }
            if( !checkPwdFunc.call( password ) ){ return false; }
            if( !checkPwdFuncEx.call( pass_again ) ){ return false; }
            
            QW.Ajax.post
            (
                server
                , { "csrf_token": csrf_token.val().trim()
                        , "pass_old": pass_old.val().trim()
                        , "password": password.val().trim() 
                        , "pass_again": pass_again.val().trim() 
                }
                , function( $d )
                {                 
                    if( $d.err == 'sys.permission.need_login' )
                    {
                        LOGIN_POPUP();
                        return false;
                    }
                    
                    if( $d.err == 'ok' )
                    {
                        alert( $d.msg || '操作成功!');
                    }
                    else
                    {
                        alert( $d.msg || '操作失败!' );
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
            
            return false;
        }
    );  
    
    function checkPwdFuncEx()
    {
        var tips = W(this).parentNode('.form_module').query('.tips');            
        switch( checkPwd( W(this).val() ) )
        {
            case 2:
            {
                tips.html( '密码不能为空!' );
                return false;
            }
            
            case 3:
            {
                tips.html( '密码不能小于6位!' );
                return false;
            }
        }          
        
        if( password.val().trim() !== pass_again.val().trim() )
        {
            tips.html( '新密码和验证密码不一致!' );
            return false;
        }
        else
        {
            tips.html('');
        }
        
        return true;
    }
    
    function checkPwdFunc()
    {
        var tips = W(this).parentNode('.form_module').query('.tips');            
        switch( checkPwd( W(this).val() ) )
        {
            case 2:
            {
                tips.html( '密码不能为空!' );
                return false;
            }
            
            case 3:
            {
                tips.html( '密码不能小于6位!' );
                return false;
            }
            
            case 1:
            {
                tips.html('');
                break;
            }
        }
        
        return true;
    }
    
    function checkPwd( $s )
    {        
        $s = $s.trim();
        
        if( !$s ) return 2;
        if( $s.length < 6 ) return 3;
        
        return 1;
    }
} 

/**
 * TAB-个人设置
 */ 
function TAB_SETTING()
{
    var box = W('#setting_');
    if( !box.length ) return;
    
    var server = '/user/setting';
    
    var formx = box.query('form');
    var submit = box.query( '.btn-complete' );
    var csrf_token = box.query('input[name=csrf_token]');
    var tips = box.query('#nicktips');
    var name = box.query('input[name=name]');
    var intro = box.query('textarea[name=intro]');
    var email = box.query('input[name=email]');
    var emailTips = box.query('#emailtips');
    
    formx.on
    (
        'submit',
        function( $e )
        {
            $e.preventDefault();
            $e.stopPropagation();
            
            return false;
        }
    );
    
    name.on
    (
        'keyup',
        function()
        {
            var p = W(this);
            var reg = /^[\u4e00-\u9fa5\_a-zA-Z0-9\-\u3002\u2022]{2,10}$/;
            if( !p.val().trim() )
            {
                tips.html( '昵称不能为空!' );
                return;
            }
            
            if( p.val().trim().length > 10 )
            {
                tips.html( '昵称不能超过10个字符!' );
                return;
            }
            if(!reg.test(p.val().trim())) {
            	tips.html( '昵称格式错误!' );
            	return;
            }
            tips.html( '' );
            
            QW.Ajax.get
            (
                "/user/is_nick_reged"
                ,{ nick:this.value.trim() }
                ,function( $d )
                {
                    if( $d.err == 'ok' )
                    {
                        tips.html('');
                    }
                    else
                    {
                        tips.html( getMessage($d.msg) || '该昵称已经被其他用户使用，换个别的吧!');
                    }
                }
            );
        }
    );
    
    var checkEmail = function() {
    	var objNativeCurEle = document.getElementById('email');
    	var objCurEle = W(objNativeCurEle);
    	if (objCurEle.val().trim() != '' && ! QW.Valid.check(objNativeCurEle)) {
    		//如果填写了email，则必须格式正确
    		emailTips.html('邮箱格式错误');
    		return false;
    	} else {
    		emailTips.html('');
    	}
    	return true;
    };
    email.on('blur', checkEmail);
    
    submit.on
    (
        'click',
        function( $e )
        {
            $e.preventDefault();
            $e.stopPropagation();
        
            if( !name.val().trim() || ! checkEmail())
            {
                return false;
            }
            
            if( name.val().trim().length > 10 )
            {
                return false;
            }
            QW.Ajax.post
            (
                server
                , { "csrf_token": csrf_token.val().trim()
                        , "name": name.val().trim()
                        , "intro": intro.val().trim()
                        , "email": email.val().trim()
                        //添加多两个字段
                        , "tags": W("#tags").getValue()
                        ,"accept_subscribe":W("#accept_subscribe:checked").length > 0 ? 1: 0
                }
                , function( $d )
                {   
                    if( $d.err == 'sys.permission.need_login' )
                    {
                        LOGIN_POPUP();
                        return false;
                    }
                
                    var msg = getMessage( $d.msg );
                    if( $d.msg && $d.msg.intro ) 
                    {
                        msg = "个性签名" + msg;
                    }
                    else if( $d.msg && $d.msg.input_tag ) 
                    {
                    	msg = "用户标签" + msg;
                    }
                    
                    alert(  msg  || '操作成功!');
                    
                    if( $d.err == "ok" )
                    {
                        location.replace( location.href.split('#')[0] );
                    }
                    
                    return false;
                }
                
                , {
                    onerror:
                    function()
                    {
                        alert( e.msg || '网络错误!' );
                    }
                }
            );
            
            return false;
        }
    );
}

/**
 * TAB-制定主页
 */ 
function TAB_HOMEPAGE()
{
    var box = W('#custom_homepage');
    
    if( !box.length ) return;
    
    var MAX_SELECTED_ITEMS = 8;
    var selectedList = box.query('.selectedList > li');
    var list = box.query('.list');
    var listItems = list.query('> li');
    var submit = box.query( '.btn-complete' );
    var temp_box = document.getElementById("temp_box");
    var csrf_token = box.query('input[name=csrf_token]');
    
    listItems.forEach
    (
        function(ele)
        {
            if( W(ele).hasClass('selected') )
            {
                W(ele).removeNode();
            }
        }
    );
    
    listItems.on
    (
        'mousedown', addItemEvent
    );  
    
    selectedList.query( 'em' ).on( 'mousedown', selectedDeleteEvent );
    
    function addItemEvent( $e )
    {
        $e.preventDefault();
        
        if( isMaxSelected() ) return false;
        
        var p = W(this);
        
        var selectItem = getFirstNoDataSelectItem();
        
        selectItem.addClass('has_tag');
        selectItem.html
        (
            '<label>' + p.html().replace(/<[^>]+?>/g, '') + '</label><em title="删除"></em>'
        );
        selectItem.attr( 'circle-id', p.attr('circle-id') );
        //selectItem.query('label').on( 'mousedown', function($e){ $e.preventDefault();return false;} );
        selectItem.query('em').on
        (
            'mousedown',
            selectedDeleteEvent
        );
        
        p.removeNode();        
        
        return false;
    }
    
    function selectedDeleteEvent( $e )
    {
        $e.preventDefault();
        $e.cancelBubble = true;
        
        var p = W(this).parentNode();
        
        var item = 
        W(
            '<li circle-id="'
                +p.attr('circle-id')+'"><span>'
                +p.query('label').html()+'</span></li>'
        );
        
        list.appendChild( item );
        //item.query('span').on( 'mousedown', function($e){ $e.preventDefault();return false;} );
        item.on('mousedown', addItemEvent);
        
        p.removeClass('has_tag');
        p.html('');
        p.attr('circle-id','');
        
        return false;
    }
    
    box.query('.selectedList > li').forEach
    (
        function( $ele )
        {
            initDrag( $ele, temp_box );
        }
    );
    
    submit.on
    (
        'click',
        function( $e )
        {
            $e.preventDefault();
            
            var items = [];
            
            box.query('.selectedList > li').forEach
            (
                function($ele)
                {
                    if( W($ele).attr('circle-id') )
                    {
                        items.push( W($ele).attr('circle-id') );
                    }
                }
            );
            
            QW.Ajax.post
            (
                '/user/sethomepage'
                , { "csrf_token": csrf_token.val().trim(), "circles_set": items.join(',') }
                , function( $d )
                {                 
                    if( $d.err == 'sys.permission.need_login' )
                    {
                        LOGIN_POPUP();
                        return false;
                    }
                    
                    if( $d.err == 'ok' )
                    {
                        alert($d.msg || '操作成功!');
                    }
                    else
                    {
                        alert( $d.msg || '操作失败!' );
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
            
            return false;
        }
    );
    
    function getFirstNoDataSelectItem()
    {
        var r;
        
        box.query('.selectedList > li').forEach
        (
            function( $e )
            {
                if( r ) return;
                
                if( !W($e).hasClass('has_tag') )
                {
                    r = W($e);
                    return false;
                }
            }
        );
        
        return r;
    }
    
    function isMaxSelected()
    {
        var c = 0;
        
        box.query('.selectedList > li').forEach
        (
            function( $e )
            {
                if( W($e).hasClass('has_tag') )
                {
                    c++;
                }
            }
        );        
        
        if( c >= MAX_SELECTED_ITEMS ) return true;
    
        return false;
    }
    
    
    function initDrag( $item, $box )
    {
        XCloneDrag.exec
        ( 
            {
                dragItem: $item
                
                , cloneItemAppendNode: $box
                
                , checkDownCallback:
                function( $evt, $srcEle )
                {
                    return $srcEle.getAttribute('circle-id');
                }
                
                , afterDownCallback: 
                function( $evt, $srcEle, $dragEle )
                {
                    //document.title = 'down';
                }
                
                , afterMoveCallback: 
                function( $evt, $srcEle, $dragEle )
                {
                    //document.title = 'move';
                }
                
                , afterUpCallback: 
                function( $evt, $srcEle, $dragEle )
                {
                    //document.title = 'up';
                    
                    var findList = [];
                    var findItem;
                    
                    box.query('.selectedList > li').forEach
                    (
                        function( $ele )
                        {
                            var pos = ele_pos_f( $ele );
                            
                            if( 
                                (pos.left + pos.width) > $dragEle.offsetLeft
                                && ( pos.left <  $dragEle.offsetLeft + $dragEle.offsetWidth)
                            )
                            {
                                findList.push( $ele );
                            }
                        }
                    )
                    
                    
                    if( findList.length === 1 )
                    {
                        findItem = findList[0];
                    }
                    else if( findList.length > 1 )
                    {
                        var mid = $dragEle.offsetLeft + $dragEle.offsetWidth / 2;
                        
                        for( var i = 0; i < findList.length; i++ )
                        {
                            var item = findList[i];                            
                            var pos = ele_pos_f( item );
                            
                            if( pos.left + pos.width > mid )
                            {
                                findItem = item;
                                break;
                            }
                        }
                    }
                    
                    if( findItem )
                    {                                   
                        if( findItem.getAttribute('circle-id') == $srcEle.getAttribute('circle-id') )
                        {
                            return;
                        }
                        xswap( $srcEle, findItem );
                    }
                }
            } 
        );
    }
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
