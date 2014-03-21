<%extends file="common/base.tpl"%>
<%block name="seo_meta"%>
<%$video_title_escape=HTML::chars($video.title)%>
    <meta name="keywords" content="<%$video_title_escape%>,视频,在线观看,视频类Pinterest" />
    <meta name="description" content="在线观看<%$video_title_escape%>视频和其他相关视频，发表关于<%$video_title_escape%>的评论或和好友分享你的观看心情。">
    <link rel="canonical" href="<%Util::videoPlayUrl($video._id)%>" />
<%/block%>
<%block name="title" prepend%><%$video_title_escape%>-视频-在线观看<%/block%>

<%block name="custom_css"%>
<link href="<%#resUrl#%>/css/video/play2.css?v=<%#v#%>" type="text/css" rel="stylesheet" />
<%/block%>

<%block name="custom_js"%> 
<script>
    XLogin.forceRefresh = true;
</script>
 
<script type="text/javascript" src="<%#resUrl#%>/js/video/player/player2.js?v=<%#v#%>"></script>
<script type="text/javascript" src="<%#resUrl#%>/js/components/switch/switch_all.js?v=<%#v#%>"></script>   
<%/block%>

<%block name="hd"%>
<%include file="inc/play_header.inc"%>
<%/block%>

<%block name="bd"%>
<div id="bd" class="cls">
    <div id="moviesPlace">
        <iframe id="iframePlace" src="<%$video.play_url|escape:'javascript'%>" frameborder="0"></iframe>
        <iframe allowTransparency="true" frameborder="0" id="iframeMasks" class="iframeMasks" src="about:blank"></iframe>
    </div>
</div>
<%/block%>

<%block name="ft"%><%/block%>

<%block name="foot_js"%>
<%if $is_admin_user%>
<script type="text/javascript">
Dom.ready(function() {	
    W('#commentList .comment-del').on('click', function(e) {
    	e.preventDefault();
    	e.stopPropagation();
        QW.Ajax.post(
            '/video/delComment', 
            {"id": W(this).attr('data-action')},
            function( $d ) {
                $d = $d || {};                
                                            
                if( $d.err == 'ok' ) {
                    alert("删除成功");
                } else {
                	var msg = getMessage($d.msg);
                    alert( msg || '失败，请重试!' );  
                }
            }, {
                onerror:
                function()
                {
                    alert('网络连接中断，请稍后重试!');
                }
            }
        );
    	return false;
    });	
});
</script>
<%/if%>
<%/block%>

<%block name="custom_foot"%>
<script type="text/marmot">
{
    "feedid"   : "<%$smarty.get.feedid|escape:"html"|default:0%>",
    "fuid":"<%$smarty.get.fuid|escape:"html"|default:0%>",
    "stype":"<%$smarty.get.stype|escape:"html"|default:i%>",
	"page_id":"viewvideo",
	"video_id":"<%$video._id%>",
	"video_time":"<%$video.length|escape:"html"|default:-1%>",
	"out_url":"<%$video.play_url|escape:"url"%>"
}
</script>
<script type="text/javascript">
Dom.ready(function() {
<%foreach $left_playlist as $v%>
    <%$item_list=null%>
    <%foreach $v.videos.data as $v2%>
        <%$item_list[]=$v2._id%>
    <%/foreach%>
QW.Marmot.log({
	page_id        : "recommendation",
    rec_zone        : "<%$v.rec_zone%>",
    item_list      : "<%join(',', $item_list)%>"
});
<%/foreach%>
});
</script>
<%/block%>
