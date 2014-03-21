<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>

<%block name="custom_css"%>
<style>
<!--
#bd table tbody tr td:last-child a {
	margin: 0 5px;
}
td{
	word-break:break-all;
	word-wrap:break-word;
	white-space: -moz-pre-wrap;
}
-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1>用户反馈</h1>
	<div>
		点击数：<%$feedback_click_count%> &nbsp;&nbsp;
		反馈总数：<%$total%> &nbsp;&nbsp;
		<a href="/admin_feedback/clear">清空数据</a>
	</div>
	<table class="hover">
		<thead>
			<tr>
    			<th width="50%">
    				反馈内容
				</th>
    			<th>
    				IP
    			</th>
    			<th>创建时间</th>
    			<th width="15%">反馈用户</th>
    			<th width="7%">操作</th>
			</tr>
		</thead>
		<tbody>
			<%foreach $list as $row%>
				<tr>
					<td style="max-width:500px"><%$row.content|escape:html%></td>
					<td><%$row.client_ip%></td>
					<td><%date("Y-m-d H:i:s", $row.create_time->sec)%></td>
					<td><%if isset($user_list[$row['user_id']])%><%$user_list[$row['user_id']].nick%>（<%$row.user_id%>）
					<%else%>路人甲<%/if%></td>
					<td>&nbsp;<a href="<%$row._id%>" op="remove">删</a></td>
				</tr>
			<%/foreach%>
		</tbody>
	</table>
	<%$pagination%>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
$(document).ready(function(){

	$("a[href='/admin_feedback/clear']").click(function(){
		if(!confirm("确定要清空数据吗？")) {
			return false;
		}
		var href=$(this).attr("href");
		$.get(href, {}, function(data){
			if(data.err=="ok") {
				alert("操作成功");
				window.location.reload();
				return;
			}
			alert("操作失败");
		}, 'json')
		return false;
	});

	$("a[op='remove']").click(function(){
		var _id=$(this).attr("href");
		$.get("/admin_feedback/remove", {id:_id}, function(data){
			if(data.err=="ok") {
				alert("操作成功");
				window.location.reload();
				return;
			}
			alert("操作失败");
		}, 'json')
		return false;
	});
})
</script>
<%/block%>