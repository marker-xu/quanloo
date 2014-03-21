<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>

<%block name="custom_css"%>
<style>
<!--
#bd form ul li {
	float: left;
	margin: 5px;
}
-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1><a href="/admin_promotecircle">推广圈子管理</a></h1>
	<form action="" method="post" enctype="multipart/form-data">
		<table>
            <tbody>
            	<tr>
                    <td>圈子：</td>
                    <td><a href="<?php echo Util::circleUrl($circle['_id'], null, $circle) ?>" target="_blank"><?php echo HTML::chars($circle['title']) ?></td>
                </tr>
            	<tr>
                    <td>九宫图：</td>
                    <td><input type="file" name="thumbnail"></td>
                </tr>
            	<tr>
                    <td>下线后是否保留九宫图：</td>
                    <td>
                    	<input type="radio" name="keep_thumbnail" value="1" <?php echo $circle['keep_thumbnail'] ? 'checked="checked"' : '' ?> />是
                    	<input type="radio" name="keep_thumbnail" value="0" <?php echo $circle['keep_thumbnail'] ? '' : 'checked="checked"' ?> />否
                    </td>
                </tr>
            	<tr>
                    <td>推广图：</td>
                    <td><input type="file" name="recommend_image"></td>
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

});
</script>
<%/block%>