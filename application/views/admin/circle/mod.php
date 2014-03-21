<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>

<%block name="custom_css"%>
<style>
<!--

-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1>修改圈子</h1>
	<form name="search">
    	<input type="text" value="请输入圈子名称" style="color:#999;">
    	<input type="submit" value="搜索">
	</form>
	<form action="" method="post" enctype="multipart/form-data">
		<table>
            <tbody>
            	<tr>
                    <td style="width: 70px;">ID：</td>
                    <td><?php echo $circle['_id'] ?></td>
                </tr>
            	<tr>
                    <td>名称：</td>
                    <td><input type="text" name="title" value="<?php echo HTML::chars($circle['title']) ?>"></td>
                </tr>
            	<tr>
                    <td>类别：</td>
                    <td>
                    	<select name="category[]" multiple="multiple" style="height: 120px; width: 100px;">
                    		<?php foreach (Model_Data_Circle::$adminCategorys as $key => $value) { ?>
							<option value="<?php echo $key ?>" <?php echo in_array($key, $circle['category']) ? 'selected="selected"' : '' ?>><?php echo $value ?>
							<?php } ?>
                    	</select>
					</td>
                </tr>
            	<tr>
                    <td>Tag：</td>
                    <td>
                    	<textarea rows="5" cols="80" name="tag"><?php echo HTML::chars(implode(', ', $circle['tag'])) ?></textarea>
                    	多个Tag间以“,”号分隔。
                    </td>
                </tr>
            	<tr>
                    <td>Logo：</td>
                    <td>
                    	<?php if (isset($circle['logo'])) { ?>
                    	<img src="<?php echo Util::webStorageClusterFileUrl($circle['logo']) ?>">
                    	<?php } ?>
                    	<input type="file" name="logo">
                    </td>
                </tr>
            	<tr>
                    <td>是否认证：</td>
                    <td>
                    	<select name="certified">
							<option value="1" <?php echo $circle['certified'] == 1 ? 'selected="selected"' : '' ?>>是
							<option value="0" <?php echo $circle['certified'] == 0 ? 'selected="selected"' : '' ?>>否
                    	</select>
                    </td>
                </tr>
            	<tr>
                    <td>是否官方：</td>
                    <td>
                    	<select name="official">
							<option value="1" <?php echo $circle['official'] == 1 ? 'selected="selected"' : '' ?>>是
							<option value="0" <?php echo $circle['official'] == 0 ? 'selected="selected"' : '' ?>>否
                    	</select>
                    </td>
                </tr>
            	<tr>
                    <td>状态：</td>
                    <td>
                    	<select name="status">
                    		<?php foreach (Model_Data_Circle::$statuses as $key => $value) { ?>
							<option value="<?php echo $key ?>" <?php echo $circle['status'] == $key ? 'selected="selected"' : '' ?>><?php echo $value?>
							<?php } ?>
                    	</select>
					</td>
                </tr>
            	<tr>
                    <td>创建者：</td>
                    <td><?php echo $circle['creator'] ?></td>
                </tr>
            	<tr>
                    <td>创建时间：</td>
                    <td><?php echo date('Y-m-d H:i:s', $circle['create_time']->sec) ?></td>
                </tr>
            	<tr>
                    <td></td>
                    <td><input type="submit" value="提交"></td>
                </tr>
        	</tbody>
        </table>
        <input type="hidden" name="csrf_token" value="<?php echo Security::token() ?>">
	</form>
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
			window.location.href = '/admin_circle?keyword='+encodeURIComponent($(this).find('input[type=text]').val());
		}
		return false;
	});
	
	$('.recommend').find('input[type=checkbox]').attr('checked', false);
	
	$('.move-forward').click(function () {
		var li = $(this).parents('li');
		var prev = li.prev();
		if (prev) {
			prev.before(li);
		}
		return false;
	});
	
	$('.move-back').click(function () {
		var li = $(this).parents('li');
		var next = li.next();
		if (next) {
			next.after(li);
		}
		return false;
	});

	$('.del').click(function () {
		var li = $(this).parents('li');
		li.remove();
		return false;
	});
});
</script>
<%/block%>