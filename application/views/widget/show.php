<%extends file="common/widget_show.tpl"%>

<%block name="title" prepend%>Widget展现<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#apiResUrl#%>/css/video/widget-diy/v-widget-basic.css?v=<%#v#%>">
<%/block%>


<!--- 测试 -->
<%block name="bd"%>
<style>
	html,body, #doc1{
        width: <%$widget_info.width%>px !important;
        <%if $widget_info.bgcolor%>
        background:<%$widget_info.bgcolor%>;
        <%/if%>
    }
	.v-box-wrap {
		width: <%$widget_info.width%>px;
		height: auto;
		<%if $widget_info.bgcolor%>
        background:<%$widget_info.bgcolor%>;
        <%/if%>
	}
	.v-box-inner {
		padding-left:<%$margin_left.right%>px;
		padding-top: <%$margin_top.bottom%>px;
		padding-bottom: <%$margin_top.bottom%>px;
	}
	.v-box-inner .list li {
		margin-right: <%$margin_left.right%>px;
	}
	<%if $widget_info.pic_width%>
	.v-box-inner .list img {
		width: <%$widget_info.pic_width%>px;
		height: <%$widget_info.pic_height%>px;
	}
	<%/if%>
	
</style>
<%if $widget_info.css_url%>
<link rel="stylesheet" type="text/css" href="<%$widget_info.css_url%>">
<%/if%>
<div class="v-box-wrap j_view_wrap">
    <div class="v-box-inner j_view_inner">
		<ul class="list clearfix">
		<%foreach $video_list as $row%>
		<%$videoPlayUrl=Util::videoPlayUrl($row._id,null,['from_id'=>'widget','widget_id'=>$widget_info['wid']])%>
			<li>
				<a href="<%$videoPlayUrl%>" target="_blank">
					<img src="<%Util::videoThumbnailUrl($row.thumbnail)%>" alt="<%$row.title|escape:"html"%>" />
					<%if $row.length%>
		            <span class="time"><%Util::sec2time($row.length)%></span>
		            <%/if%>
				</a>
				<p class="title" title="<%$row.title|escape:"html"%>">
					<a href="<%$videoPlayUrl%>" target="_blank"><%$row.title|escape:"html"%></a>	
				</p>
			</li>
		<%/foreach%>
		</ul>
		<%if $widget_info.is_more%>
		<p class="more"><a href="<%$more_url%>" target="_blank">更多</a></p>
		<%/if%>
	</div>	
</div>	
<%/block%>
<%block name="custom_foot"%>
<script type="text/marmot">
    {
        "site_id"   : "videosearch",
        "page_id"   : "recommendation",
        "user_id"   : "<%$login_user._id%>",
        "cookie_id": "<%Session::instance()->id()%>",
        "url"       : "http://<%$smarty.server.HTTP_HOST|escape:'url'%><%$smarty.server.REQUEST_URI|escape:'url'%>",
        "referrer"  : "<%$smarty.server.HTTP_REFERER|escape:'url'%>",
        "bucket"    : "1",
        "sort_id"	: "<%$widget_info.wid%>",
        "rec_zone"  : "widget",
        "item_list"	: "<%implode(",", $video_id_list)%>"
    }
</script>               
<%/block%>  
<%block name="foot_js"%>
<%*
<script type="text/javascript">

</script>
*%>
<%/block%>
