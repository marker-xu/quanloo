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
-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1>圈子管理</h1>
	<div style="overflow: hidden;">
    	<form name="search" style="float: left;">
        	<input type="text" value="请输入圈子名称" style="color:#999;">
        	<input type="submit" value="搜索">
    	</form>
    	<p style="float: right; height: 26px; line-height: 26px;">
			<a href="/admin_circle/categorytag">圈子分类Tag管理</a>
			<a href="/admin_circle/hot">热门圈子</a>
    	</p>
	</div>
	<table class="hover">
		<thead>
			<tr>
    			<th><a href="" name="_id">ID</a></th>
    			<th><a href="" name="title">名称</a></th>
    			<th>
    				<select name="category">
    					<option value="-1">类别
    					<?php foreach (Model_Data_Circle::$adminCategorys as $key => $value) { ?>
						<option value="<?php echo $key ?>" <?php echo Request::initial()->param('category', -1) == $key ? 'selected' : '' ?>><?php echo $value ?>
						<?php } ?>
    				</select>
				</th>
    			<th width="20%">视频生成Tag</th>
    			<th width="20%">
    				<select name="filter_tag">
    					<option value="-1">视频过滤Tag
    					<option value="1" <?php echo Request::initial()->param('filter_tag', -1) == 1 ? 'selected="selected"' : '' ?>>是
						<option value="0" <?php echo Request::initial()->param('filter_tag', -1) == 0 ? 'selected="selected"' : '' ?>>否
    				</select>
    			</th>
    			<th>
    				<select name="certified">
    					<option value="-1">是否认证
    					<option value="1" <?php echo Request::initial()->param('certified', -1) == 1 ? 'selected="selected"' : '' ?>>是
						<option value="0" <?php echo Request::initial()->param('certified', -1) == 0 ? 'selected="selected"' : '' ?>>否
    				</select>
				</th>
    			<th>
    				<select name="official">
    					<option value="-1">是否官方
    					<option value="1" <?php echo Request::initial()->param('official', -1) == 1 ? 'selected="selected"' : '' ?>>是
						<option value="0" <?php echo Request::initial()->param('official', -1) == 0 ? 'selected="selected"' : '' ?>>否
    				</select>
    			</th>
    			<th><a href="" name="create_time">创建时间</a></th>
    			<th>
    				<select name="status">
    					<option value="-1">状态
    					<?php foreach (Model_Data_Circle::$statuses as $key => $value) { ?>
						<option value="<?php echo $key ?>" <?php echo Request::initial()->param('status', -1) == $key ? 'selected' : '' ?>><?php echo $value ?>
						<?php } ?>
    				</select>
    			</th>
    			<th width="12%">操作</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($circles as $circle) { ?>
			<tr>
				<td><?php echo $circle['_id'] ?></td>
				<td><a href="<?php echo Util::circleUrl($circle['_id'], null, $circle) ?>" target="_blank"><?php echo HTML::chars($circle['title']) ?></a></td>
				<td>
            		<?php foreach ($circle['category'] as $category) { ?>
            		<span><?php echo isset(Model_Data_Circle::$adminCategorys[$category]) ? Model_Data_Circle::$adminCategorys[$category].' ' : '' ?></span>
					<?php } ?>
				</td>
				<td><?php echo HTML::chars(implode(', ', $circle['tag'])) ?></td>
				<td><?php echo HTML::chars(Circle::filterTagToString(isset($circle['filter_tag']) ? $circle['filter_tag'] : array())) ?></td>
				<td><?php echo $circle['certified'] ? '是' : '否' ?></td>
				<td><?php echo $circle['official'] ? '是' : '否' ?></td>
				<td title="<?php echo is_object($circle['create_time']) ? date('Y-m-d H:i:s', $circle['create_time']->sec) : '' ?>">
				    <?php echo is_object($circle['create_time']) ? date('Y-m-d', $circle['create_time']->sec) : '' ?>
				</td>
				<td><?php echo Model_Data_Circle::$statuses[$circle['status']] ?></td>
				<td>
					<a href="/admin_circle/mod?id=<?php echo $circle['_id'] ?>" class="mod">修改</a>
					<?php if (!in_array($circle['status'], array(Model_Data_Circle::STATUS_PUBLIC))) { ?>
					<a href="/admin_circle/changestatus?id=<?php echo $circle['_id'] ?>&status=<?php echo Model_Data_Circle::STATUS_PUBLIC ?>" class="change-status">公开</a>
					<?php } ?>
					<?php if (!in_array($circle['status'], array(Model_Data_Circle::STATUS_PRIVATE))) { ?>
					<a href="/admin_circle/changestatus?id=<?php echo $circle['_id'] ?>&status=<?php echo Model_Data_Circle::STATUS_PRIVATE ?>" class="change-status">私有</a>
					<?php } ?>
					<?php if (!in_array($circle['status'], array(Model_Data_Circle::STATUS_DELETED))) { ?>
					<a href="/admin_circle/changestatus?id=<?php echo $circle['_id'] ?>&status=<?php echo Model_Data_Circle::STATUS_DELETED ?>" class="change-status">删除</a>
					<?php } ?>
					<a href="/admin_circle/videos?id=<?php echo $circle['_id'] ?>" class="videos">圈内视频管理</a>
					<a href="/admin_circle/filtertag?id=<?php echo $circle['_id'] ?>" class="filter-tag">视频过滤Tag管理</a>
					<input type="hidden" name="id" value="<?php echo $circle['_id'] ?>">
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php echo $pagination ?>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
$(function () {
	$('form[name=search] input[type=text]').click(function () {
		if (!$(this).data('clicked')) {
			$(this).val('').css('color', '').data('clicked', true);
		}
	});
	var keyword = $.url().param('keyword');
	if (keyword) {
		$('form[name=search] input[type=text]').click().val(keyword);
	}
	$('form[name=search]').submit(function () {
		var param = $.url().param();
		if ($(this).find('input[type=text]').data('clicked')) {
			param['keyword'] = $(this).find('input[type=text]').val();
			window.location.search = $.param(param);
		}
		return false;
	});
	
	$('select').change(function () {
		var param = $.url().param();
		param[$(this).attr('name')] = $(this).val();
		param['page'] = 1;
		window.location.search = $.param(param);
	});
	$('table th a').click(function () {
		var param = $.url().param();
		if (!param['orderby']) {
			param['orderby'] = '_id';
			param['direction'] = -1;
		}
		var direction;
		if ($(this).attr('name') == param['orderby']) {
			direction = -param['direction'];
		} else {
			if ($(this).attr('name') == 'title') {
				direction = 1;
			} else {
				direction = -1;
			}
		}
		param['orderby'] = $(this).attr('name');
		param['direction'] = direction;
		param['page'] = 1;
		window.location.search = $.param(param);
		return false;
	});
	
	$('.change-status').click(function () {
		var href = $(this).attr('href');
		$.getJSON(href, function (res) {
			if (res.errno == 0) {
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		});
		return false;
	});
});
</script>
<%/block%>