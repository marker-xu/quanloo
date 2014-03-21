/**
 * 圈子动态
 * x@btbtd.org      2012-3-31 
 */ 
function INIT_GROUP_TREND()
{
    window.isIE6 = !-[1,] && !window.XMLHttpRequest;
    
    var smallBox = W('#circlefeedmini');
    var openBox = W('#circlefeedall');
    var INITED = false;    
    var PRE_SECTION = -1;
    var openURL = '/circle/feeds';
    
    if( !( smallBox.length && openBox.length ) ) return;
        
    openBox.css( 'position', 'absolute' );
    W('body').appendChild( openBox );
    
    if( isIE6 )
    {
        if( W('#group_trend_anchor').length )
        {
            W('#group_trend_anchor').html('&nbsp;');
        }   
    }
    
    smallBox.query('.open__').on
    (
        'click',
        function( $evt )
        {
            $evt.preventDefault();
            
            hideSmall();
            
            return false;
        }
    ); 
    
    openBox.query('.close__').on
    (
        'click',
        function( $evt )
        {
            $evt.preventDefault();
            
            hideOpen();
            
            return false;
        }
    );
    
    var openList = openBox.query('.open_list__');
    var time_list = openBox.query( '.time_list__ li' ); 
    time_list.on
    (
        'click',
        function( $evt )
        {
            time_list.removeClass('on');
            W(this).addClass('on');
            
            var dataSec = W(this).attr('data-list');
            
            if( PRE_SECTION !== dataSec )
            {
                openList.html('<li style="text-align:center; margin:80px;">正在加载数据, 请稍候...</li>');
                PRE_SECTION = dataSec;   
                
                QW.Ajax.get
                (
                    openURL
                    , { 'id': window.CIRCLE_ID+'', 'begintime': dataSec, "count": 12, "format": "html", "rnd": new Date().getTime() }
                    , function( $d )
                    {
                        if( $d.toString().trim() )
                        {
                            openList.html( $d );                          
                        }
                        else
                        {
                            openList.html('<li style="text-align:center; margin:80px;">该圈子目前没有相关数据!</li>');
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
    
    W(window).on
    (
        "resize",
        function()
        {
            updatePos();
        }
    );
    
    function hideSmall()
    {
        updatePos();
        
        smallBox.hide();
        openBox.show();
        
        if( !INITED )
        {
            W(time_list[0]).fire('click');
            
            INITED = true;
        }
        
        location.href = location.href.split('#')[0] + '#group_trend_anchor';
    }
    
    function hideOpen()
    {
        openBox.hide();
        smallBox.show();
        location.href = location.href.split('#')[0] + '#group_trend_anchor';
    }
    
    function updatePos()
    {
        var smallXy = smallBox.getXY();
        
        if( smallBox.css('display') == 'none' )
        {
            smallBox.show();
            smallXy = smallBox.getXY();
            smallBox.hide();
        }
        openBox.setXY
        (
            smallXy[0], smallXy[1]
        );
    }
}

/*
1 – 观看视频，2 – 推视频[删掉了]，3 – 评论视频，4 – 关注圈子，5 – 分享视频，6 – 心情视频，7 – 分享圈子，8 – 邀请好友加入圈子

<%if $type == 6%>
	<%$xinqing = '喜欢'%>
	<%if $data.data == 'xh'%>
		<%$xinqing = '喜欢'%>
	<%elseif $data.data == 'wg'%>
		<%$xinqing = '惊叹'%>
	<%elseif $data.data == 'dx'%>
		<%$xinqing = '大笑'%>
	<%elseif $data.data == 'fn'%>
		<%$xinqing = '愤怒'%>
	<%elseif $data.data == 'jn'%>
		<%$xinqing = '囧'%>
	<%/if%>
<%/if%>
*/ 
