<%extends file="common/base.tpl"%>

<%block name="title" prepend%>友情链接<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
<%/block%>
<%block name="custom_js"%>

<%/block%>
<!--- 测试 -->
<%block name="bd"%>  
<style type="text/css">
	#bd{border-top: 1px solid #FFFFFF;padding-top: 10px;min-height: 400px;}
	#bd h2{border-bottom: 1px solid #C5C5C5;font-size: 16px;font-weight: normal;height: 31px;line-height: 31px;margin-top: 15px;padding-left: 11px;}
	.pic_links{border-top: 1px solid #FFFFFF;padding-top: 10px;}
	.pic_links li{border: 1px solid #CCCCCC;display: block;width:94px;height:37px;overflow: hidden;margin-left: 10px;margin-top:10px;background-color: #FFF;float: left;}
	.text_links{margin-top:22px;}
	.text_links li{display: block;width:178px;height:25px;line-height:25px;overflow: hidden;margin-left: 11px;margin-top:0px;float: left;white-space:nowrap;}
    .pic_links li,.text_links li{zoom:1;display:inline;}
</style>
<div id="bd">
    <h2>友情链接</h2>
    <%if $image_list%>
    <ul class="cls pic_links">
	    <%foreach $image_list as $row%>
	    	<li><a href="<%$row.url%>" target="_blank"><img src="<%Util::webStorageClusterFileUrl($row.logo)%>" /></a></li>
	    <%/foreach%>
    </ul>
    <%/if%>
    <%if $text_list%>
    <ul class="cls text_links">
    	<%foreach $text_list as $row%>
    	<li><a href="<%$row.url%>" target="_blank" title="<%$row.title|escape:"html"%>"><%$row.title|escape:"html"%></a></li>
    	<%/foreach%>
    </ul>
    <%/if%>
</div>
<%/block%>

<%block name="foot_js"%>

<%/block%>