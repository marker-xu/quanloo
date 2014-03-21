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
	<h1>热门圈子（TOP 100）</h1>
	<div style="overflow: hidden;">
    	<form name="search" style="float: left;">
        	<input type="text" value="请输入圈子名称" style="color:#999;">
        	<input type="submit" value="搜索">
    	</form>
	</div>
	<table class="hover">
		<thead>
			<tr>
    			<th>ID</th>
    			<th>名称</th>
    			<th>
    				<select name="category">
    					<option value="-1">类别
    					<?php foreach (Model_Data_Circle::$adminCategorys as $key => $value) { ?>
						<option value="<?php echo $key ?>" <?php echo Request::initial()->param('category', -1) == $key ? 'selected' : '' ?>><?php echo $value ?>
						<?php } ?>
    				</select>
				</th>
    			<th width="30%">
    				<select name="filter_tag">
    					<option value="-1">视频过滤Tag
    					<option value="1" <?php echo Request::initial()->param('filter_tag', -1) == 1 ? 'selected="selected"' : '' ?>>有
						<option value="0" <?php echo Request::initial()->param('filter_tag', -1) == 0 ? 'selected="selected"' : '' ?>>无
    				</select>
    			</th>
    			<th><a href="" name="popularity">热度</a></th>
    			<th><a href="" name="user_count">关注人数</a></th>
    			<th>创建时间</th>
    			<th>
    				<select name="status">
    					<option value="-1">状态
    					<?php foreach (Model_Data_Circle::$statuses as $key => $value) { ?>
						<option value="<?php echo $key ?>" <?php echo Request::initial()->param('status', -1) == $key ? 'selected' : '' ?>><?php echo $value ?>
						<?php } ?>
    				</select>
    			</th>
    			<th>操作</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($circles as $circle) { ?>
			<tr>
				<td><?php echo $circle['_id'] ?></td>
				<td><a href="<?php echo Util::circleUrl($circle['_id'], null, $circle) ?>" target="_blank"><?php echo HTML::chars($circle['title']) ?></a></td>
				<td>
            		<?php foreach ($circle['category'] as $category) { ?>
            		<span><?php echo Model_Data_Circle::$adminCategorys[$category].' ' ?></span>
					<?php } ?>
				</td>
				<td><?php echo HTML::chars(Circle::filterTagToString(isset($circle['filter_tag']) ? $circle['filter_tag'] : array())) ?></td>
				<td><?php echo $circle['popularity'] ?></td>
				<td><?php echo $circle['user_count'] ?></td>
				<td>
				    <?php echo is_object($circle['create_time']) ? date('Y-m-d H:i:s', $circle['create_time']->sec) : '' ?>
				</td>
				<td><?php echo Model_Data_Circle::$statuses[$circle['status']] ?></td>
				<td>
					<a href="/admin_circle/mod?id=<?php echo $circle['_id'] ?>" class="mod">修改</a>
					<a href="/admin_circle/filtertag?id=<?php echo $circle['_id'] ?>" class="filter-tag">视频过滤Tag管理</a>
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
});
</script>
<%/block%>