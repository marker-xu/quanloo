<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>
<%block name="custom_js"%>
<script type="text/javascript" 
src="<%#resUrl#%>/js/third/jquery/jquery-ui/jquery-ui-1.8.20.custom.min.js?v=<%#v#%>"></script>
<%/block%>
<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" 
href="<%#resUrl#%>/js/third/jquery/jquery-ui/ui-lightness/jquery-ui-1.8.20.custom.css?v=<%#v#%>">
<style>
<!--
#main ul{
	margin-top:10px;
	margin-bottom:10px;
	width:400px;
	position:relative;
	overflow: hidden;
}
#main ul li{
	list-style:none;
	float:left;
	height:80px auto;
	margin:0px 13px;
	display:block;line-height:28px
}
a.baowei{
	display: block;
	background: url(<%#resUrl#%>/css/video/images/sp-icon-reg2.png?v=<%#v#%>) no-repeat right -152px;
	padding: 0 17px 0 11px;
	_display: inline;
	zoom: 1;
	left: 20px;
	position: relative;
	top: -25px;
}
a.songzong{
	display: block;
	background: url(<%#resUrl#%>/css/video/images/sp-icon-reg2.png?v=<%#v#%>) no-repeat right -152px;
	padding: 0 17px 0 11px;
	_display: inline;
	zoom: 1;
	left: 20px;
	position: relative;
	top: -45px;
}
-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main">
	<div> 
		<h1>图片友情链接</h1>
		<div style="display:block">
			<ul>
			<%foreach $image_list as $row%>
				<li>
				<p style="height:30px">
				<a href="<%$row.url%>" target="__blank"><img src="<%Util::webStorageClusterFileUrl($row.logo)%>" width="83px" height="31px" /></a>
				<a class="songzong" href="link-remove" link-id="<%$row._id%>">&nbsp;</a>
				</p>
				<p><a href="link-edit" link-id="<%$row._id%>" link-url="<%$row.url%>">编辑</a></p>
				</li>
			<%foreachelse%>
				<li>还没有数据，肿么办</li>
			<%/foreach%>
			</ul>
		</div>
		<div style="display:block">
			<form action="/admin_link/image" method="post" enctype="multipart/form-data" id="image-link">
				<%Form::hidden('csrf_token', Security::token())%>
				<p>导入图片：<input type="file" name="image" /> </p>
				<p>输入URL：  <input type="text" name="url" /> </p>
				<input type="submit" value="提交" />
			</form>
		</div>
	</div>
	
	<div>
		<h1>文字友情链接</h1>
		<div>
			<ul>
			<%foreach $text_list as $row%>
				<li>
				<p style="height:30px"><a href="<%$row.url%>" target="__blank"><%$row.title|escape:"html"%></a>
				<a class="baowei" href="link-remove" link-id="<%$row._id%>">&nbsp;</a></p>
				<p><a href="link-edit" link-id="<%$row._id%>" link-url="<%$row.url%>" link-text="<%$row.title|escape:"html"%>">编辑</a></p>
				</li>
				
			<%foreachelse%>
				<li>还没有数据，肿么办</li>
			<%/foreach%>
			</ul>
		</div>
		<div>
			<form action="/admin_link/text" method="post" id="text-link">
				<%Form::hidden('csrf_token', Security::token())%>
				<p>输入文字：<input type="text" name="title" /></p> 
				<p>输入URL：  <input type="text" name="dest_url" /></p>
				<input type="submit" value="提交" />
			</form>
		</div>
	</div>
	<div id="dialog" style="display:none;">
		<form action="/admin_link/edit" method="post" id="edit-link" enctype="multipart/form-data" >
				<%Form::hidden('csrf_token', Security::token())%>
				<%Form::hidden('link_type', 0)%>
				<%Form::hidden('id', 0)%>
				<p style="display:none;">导入图片：<input type="file" name="image"  /> </p>
				<p>输入文字：<input type="text" name="title" /></p> 
				<p>输入URL：  <input type="text" name="url" /></p>
				<input type="submit" value="提交" />
		</form>
	</div>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
$(document).ready(function(){
	function init_dialog() {
		$( "#dialog" ).dialog({
			close:function(event, ui) {
					$("#edit-link")[0].reset(); 
					$("#edit-link p").show();
					$("#edit-link").children("p").eq(0).hide();
				},
			width: 360,
			height: 150,
			}
		);
	}
	$("a[href='link-remove']").click(function(){
		if(!confirm("确定要删除数据吗？")) {
			return false;
		}
		var link_id = $(this).attr("link-id");
		$.get("/admin_link/remove", {id:link_id},function(data){
			if(data.err=="ok") {
				alert("删除成功");
				window.location.reload();
				return;
			}
			alert("删除失败");
		},'json');
		return false;
	});
	$("a[href='link-edit']").click(function(){
		var link_id = $(this).attr("link-id");
		var dest_url = $(this).attr("link-url");
		var isText = typeof($(this).attr("link-text"))!="undefined";
		$("#edit-link").children("p").show();
		$("#edit-link").children("p").children("input[name='url']").val(dest_url); 
		$("#edit-link").children("input[name='id']").val(link_id); 
		if(isText) {
			$("#edit-link").children("p").eq(0).hide();
			$("#edit-link").children("input[name='link_type']").val(0); 
			$("#edit-link").children("p").children("input[name='title']").val($(this).attr("link-text"));
		} else {
			$("#edit-link").children("p").eq(1).hide();
			$("#edit-link").children("input[name='link_type']").val(1); 
		}
		init_dialog();
		return false;
	});
	$("#image-link").submit(function(){
		var dest_url = $.trim( $(this).children("p").children("input[name='url']").val() );
		if(dest_url.length==0) {
			alert("URL不能为空");
			return false;
		}
		return true;
	});
	$("#edit-link").submit(function(){
		var dest_url = $.trim( $(this).children("p").children("input[name='url']").val() );
		var link_type = $(this).children("input[name='link_type']").val();
		if(dest_url.length==0) {
			alert("URL不能为空");
			return false;
		}
		if(!link_type) {
			if($.trim( $(this).children("p").children("input[name='title']").val() ).length==0) {
				alert("标题不能为空");
				return false;
			}
		}
		return true;
	});
	$("#text-link").submit(function(){
		var url = $(this).attr("action");
		var dest_url = $.trim( $(this).children("p").children("input[name='dest_url']").val() );
		var title = $.trim( $(this).children("p").children("input[name='title']").val());
		if(title.length==0) {
			alert("文字不能为空");
			return false;
		} 
		if(dest_url.length==0) {
			alert("URL不能为空");
			return false;
		}
		
		$.post(url, {title:title, url:dest_url, csrf_token:$(this).children("input[name='csrf_token']").val()}, function(data){
			if(data.err=="ok") {
				alert("添加成功");
				window.location.reload();
				return;
			} 
			if(typeof data.msg == 'object') {
				if(data.msg.title) {
					alert("文字"+data.msg.title);
				} else {
					alert("URL"+data.msg.url);
				}
			} else {
				alert(data.msg||"添加失败");
			}
			
			return false;
		}, 'json');
		return false;
	});
})
</script>
<%/block%>