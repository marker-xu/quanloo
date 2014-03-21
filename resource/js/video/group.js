
Dom.ready
(
    function()
    {
        if( window.INIT_GROUP_TREND ) INIT_GROUP_TREND();        
        INIT_TAG_BOX_FOR_END_CIRCLE();
    }
);

/**
 * 自定义标签通用方法
 * x@btbtd.org  2012/4/24 
 */ 
function INIT_TAG_BOX_FOR_END_CIRCLE()
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
                'type': 'circle_list_tag_box'
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
                if( W(this).hasClass('tag-sys') )
                {
                    if( 
                        W(this).query('.tag_link').html().trim() !== '不限'
                        && W(this).query('.del').length
                    )
                    {
                       W(this).addClass('tag-item-del'); 
                    }
                }
                else
                {
                    W(this).addClass('tag-item-del');
                }
                //if( !W(this).hasClass('tag-sys') ) W(this).addClass('tag-item-del');
                
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
