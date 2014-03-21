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
#bd table tbody tr td:last-child a {
	margin: 0 5px;
}
td{
	word-break:break-all;
	word-wrap:break-word;
	white-space: -moz-pre-wrap;
}
form p{margin:5px 0px;}
-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main">
	<div> 
		<h1>版本列表</h1>
		<div style="display:block; margin-bottom:20px;">
			<table class="hover">
				<thead>
					<tr>
		    			<th width="20%">
		    				版本号（版本名称）
						</th>
						<th width="40%">
		    				版本描述
						</th>
						<th width="5%">是否强制</th>
						<th width="10%">大小</th>
		    			<th>创建时间</th>
		    			<th width="15%">操作</th>
					</tr>
				</thead>
				<tbody>
					<%foreach $app_list as $row%>
						<tr>
							<td style="max-width:500px">
								<a href="<%Util::webStorageClusterFileUrl($row.source)%>"><%$row.version%>（<%$row.version_name|escape:"html"%>）</a>
							</td>
							<td><%$row.desc|escape:"html"%></td>
							
							<td><%if $row.is_force%>是
							<%else%>否<%/if%></td>
							<td><%round($row.size/1048576, 2)%>M</td>
							<td><%date("Y-m-d H:i:s", $row.create_time->sec)%></td>
							<td>&nbsp;<a href="<%$row._id%>" op="remove">删</a> 
							&nbsp;<a href="<%$row._id%>" op="edit" data="<%json_encode($row)|escape:"html"%>">编辑</a> </td>
						</tr>
					<%foreachelse%>
						<tr><td colspan=5>还没有数据肿么办</td></tr>
					<%/foreach%>
				</tbody>
			</table>
		</div>
		<div style="display:block">
			<form action="/admin_appversion/add" method="post" enctype="multipart/form-data" id="app-version">
				<%Form::hidden('csrf_token', Security::token())%>
				<p>上传文件：<input type="file" name="source" /> </p>
				<p>版本名称：  <input type="text" name="version_name" /> </p>
				<p>版 本 号：  &nbsp;<input type="text" name="version" value="1.0" /> </p>
				<p>版本描述：  <textarea name="desc" rows=4 style="width: 410px;"></textarea> </p>
				<p>是否强制： <input type="checkbox" name="is_force" value="1" />选中，表示对所有低版本强制</p>
				<p>强制列表： <input type="text" name="force_list" value="" />","分割</p>
				<input type="submit" value="提交" />
			</form>
		</div>
	</div>
	
	<div id="dialog" style="display:none; text-align:left">
		<form action="/admin_appversion/edit" method="post" id="edit-version" enctype="multipart/form-data" >
				<%Form::hidden('csrf_token', Security::token())%>
				<%Form::hidden('id', 0)%>
				<p>上传文件：<input type="file" name="source" /> </p>
				<p>版本名称：  <input type="text" name="version_name" /> </p>
				<p>版 本 号：  &nbsp;<input type="text" name="version" value="1.0" /> </p>
				<p>版本描述：  <textarea name="desc" rows=4 style="width: 410px;"></textarea> </p>
				<p>是否强制： <input type="checkbox" name="is_force" value="1" />选中，表示对所有低版本强制</p>
				<p>强制列表： <input type="text" name="force_list" value="" />","分割</p>
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
					$("#edit-version")[0].reset(); 
				},
			width: 560,
			height: 300
			}
		);
	}
	$("a[op='remove']").click(function(){
		if(!confirm("确定要删除数据吗？")) {
			return false;
		}
		var app_id = $(this).attr("href");
		$.get("/admin_appversion/remove", {id:app_id},function(data){
			if(data.err=="ok") {
				alert("删除成功");
				window.location.reload();
				return;
			}
			alert("删除失败");
		},'json');
		return false;
	});
	$("a[op='edit']").click(function(){
		var link_id = $(this).attr("href");
		var dataMap = eval("("+$(this).attr("data")+")");
		var arrP = $("#edit-version p");
		$("#edit-version").children("input[name='id']").val(link_id); 
		$(arrP).children("input[name='version_name']").val(dataMap.version_name); 
		$(arrP).children("input[name='version']").val(dataMap.version); 
		$(arrP).children("textarea[name='desc']").val(dataMap.desc);
		if(dataMap.force_list) {
			$(arrP).children("input[name='force_list']").val(dataMap.force_list.join(","));
		}
		
		if(dataMap.is_force) {
			$(arrP).children("input[name='is_force']").attr("checked", "checked"); 
		}
		init_dialog();
		return false;
	});
	$("#edit-version").submit(function(){
		var arrP = $(this).children("p");
		var version_name = $.trim( $(arrP).children("input[name='version_name']").val() );
		var version = $(arrP).children("input[name='version']").val();
		var desc = $.trim( $(arrP).children("textarea[name='desc']").val() );
		if(version_name.length==0) {
			alert("版本名称不能为空");
			return false;
		} 
		if(version.length==0) {
			alert("版本号不能为空");
			return false;
		}
		if(desc.length==0) {
			alert("版本描述不能为空");
			return false;
		}else if(desc.length>200) {
			alert("版本描述不能超过200个字");
			return false;
		}
		
		return true;
	});
	$("#app-version").submit(function(){
		var arrP = $(this).children("p");
		var version_name = $.trim( $(arrP).children("input[name='version_name']").val() );
		var version = $(arrP).children("input[name='version']").val();
		var desc = $.trim( $(arrP).children("textarea[name='desc']").val() );
		if(version_name.length==0) {
			alert("版本名称不能为空");
			return false;
		} 
		if(version.length==0) {
			alert("版本号不能为空");
			return false;
		}
		if(desc.length==0) {
			alert("版本描述不能为空");
			return false;
		}else if(desc.length>200) {
			alert("版本描述不能超过200个字");
			return false;
		}
		
		return true;
	});
})
</script>
<%/block%>