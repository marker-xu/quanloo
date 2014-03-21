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

ul li label {
	display: inline-block; 
	width: 120px; 
	text-align: right;
}
-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1>管理员列表</h1>
	<a href="/admin_acl/addadmin">增加管理员</a>
	<p>
		权限管理以模块为单位，每个模块都支持查看、增加、修改和删除四种操作。超级管理员可进行任意操作，包括分配和收回普通管理员的权限。CMS查看权限为进入CMS的最基本权限，每个管理员都俱备。
	</p>
	<table class="hover">
		<thead>
			<tr>
    			<th>管理员</th>
    			<th>权限</th>
    			<th>操作</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($acl as $id => $resources) { ?>
			<tr>
				<td><a href="<?php echo Util::userUrl($id) ?>" target="_blank"><?php echo htmlspecialchars($users[$id]['nick']) ?></a></td>
				<td>
					<ul>
				    <?php foreach ($resources as $resource => $privileges) { ?>
				    	<li>
				    		<label><?php echo Controller_Admin::$resources[$resource] ?>：</label>
				    	    <?php echo implode(' ', array_map(function ($value) { return Controller_Admin::$privileges[$value]; }, array_keys(array_filter($privileges))))?></li>
				    <?php } ?>
				    </ul>
				</td>
				<td class="op">
					<a href="/admin_acl/modifyadmin?id=<?php echo $id ?>">修改</a>
					<a href="/admin_acl/deleteadmin?id=<?php echo $id ?>" class="del">删除</a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
$(function () {
	$('.op .del').click(function () {
		var href = $(this).attr('href');
		if (window.confirm('确认删除？')) {
			$.getJSON(href, function (res) {
				if (res.errno == 0) {
					window.location.reload();
				} else {
					window.alert(res.error);
				}
			});
		}
		return false;
	});
});
</script>
<%/block%>