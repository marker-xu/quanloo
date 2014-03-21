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
	<h1>新建候选圈子</h1>
	<form name="search">
    	<input type="text" value="请输入圈子名称" style="color:#999;">
    	<input type="submit" value="搜索">
	</form>
	<form action="" method="post">
		<table>
            <tbody>
            	<tr>
                    <td>名称：</td>
                    <td><input type="text" name="title" value=""></td>
                </tr>
            	<tr>
                    <td>类别：</td>
                    <td>
                    	<select name="category[]" multiple="multiple" style="height: 120px; width: 100px;">
                    		<?php foreach (Model_Data_Circle::$adminCategorys as $key => $value) { ?>
							<option value="<?php echo $key ?>"><?php echo $value ?>
							<?php } ?>
                    	</select>
					</td>
                </tr>
            	<tr>
                    <td>Tag：</td>
                    <td>
                    	<textarea rows="5" cols="80" name="tag"></textarea>
                    	多个Tag间以“,”号分隔，名称默认会作为Tag。
                    </td>
                </tr>
            	<tr>
                    <td>状态：</td>
                    <td>
                    	<select name="status">
                    		<?php foreach (Model_Data_CircleCandidate::$statuses as $key => $value) { ?>
                    		<?php if ($key != Model_Data_CircleCandidate::STATUS_PROCESSED) { ?>
							<option value="<?php echo $key ?>" <?php echo $key == Model_Data_CircleCandidate::STATUS_APPROVED ? 'selected="selected"' : '' ?>><?php echo $value ?>
							<?php } ?>
							<?php } ?>
                    	</select>
					</td>
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
			param['keyword'] = $(this).find('input[type=text]').val();
			window.location.href = '/admin_circlecandidate?keyword='+encodeURIComponent($(this).find('input[type=text]').val());
		}
		return false;
	});
});
</script>
<%/block%>