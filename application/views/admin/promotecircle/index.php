<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>

<%block name="custom_css"%>
<style>
<!--
#bd div.item div {
	margin: 5px 0pt;
}

#bd ul li {
	float: left;
	margin: 0pt 5px;
}
-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1>推广圈子管理</h1>
	<a href="/admin_promotecircle/add">添加推广圈子</a>
	<a href="" class="publish">发布</a>
	<?php foreach ($promote_circles as $i => $circle) { ?>
	<div class="item">
	    <div>
	        <?php echo $i+1 ?>. <a href="<?php echo Util::circleUrl($circle['_id'], null, $circle) ?>" target="_blank"><?php echo HTML::chars($circle['title']) ?></a>
		    <a href="/admin_promotecircle/mod?id=<?php echo $circle['_id'] ?>" class="mod">修改</a>
		    <a href="" class="mov-up">上移</a>
		    <a href="" class="mov-down">下移</a>
		    <a href="" class="del">删除</a>
	    </div>
	    <div>
	    	九宫图：<img src="<?php echo Util::webStorageClusterFileUrl($circle['tn_path']) ?>">
	    	推广图：<img src="<?php echo Util::webStorageClusterFileUrl($circle['recommend_image']) ?>">
	    </div>
	    <input type="hidden" name="id", value="<?php echo $circle['_id'] ?>">
		<hr />
	</div>
	<?php } ?>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
$(function () {
	$('.publish').click(function () {
		if (window.confirm('确认发布？')) {
			$.getJSON('/admin_promotecircle/publish', function (res) {
				if (res.errno == 0) {
					window.alert('发布成功');
				} else {
					window.alert(res.error);
				}
			});
		}
		return false;
	});
	
	$('.mov-up').click(function () {
		var id = $(this).parents('.item').find('input[name=id]').val();
		$.getJSON('/admin_promotecircle/move', {id: id, amount: -1}, function (res) {
			if (res.errno == 0) {
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		});
		return false;
	});
	
	$('.mov-down').click(function () {
		var id = $(this).parents('.item').find('input[name=id]').val();
		$.getJSON('/admin_promotecircle/move', {id: id, amount: 1}, function (res) {
			if (res.errno == 0) {
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		});
		return false;
	});

	$('.del').click(function () {
		var id = $(this).parents('.item').find('input[name=id]').val();
		if (window.confirm('确认删除？')) {
			$.getJSON('/admin_promotecircle/delete', {id: id}, function (res) {
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