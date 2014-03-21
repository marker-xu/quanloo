<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>

<%block name="custom_css"%>
<style>
<!--
ul li label {
	display: inline-block; 
	width: 150px; 
	text-align: right;
}
-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1>增加管理员</h1>
	<form action="" method="post">
		<table>
            <tbody>
            	<tr>
                    <td>用户ID：</td>
                    <td><input type="text" name="id" value=""></td>
                </tr>
            	<tr>
                    <td>权限：</td>
                    <td>
    					<ul>
    				    <?php foreach (Controller_Admin::$resources as $resource => $resName) { ?>
    				    	<li>
    				    		<?php if ($resource == Controller_Admin::RES_CMS) { ?>
    				    		<label><?php echo $resName ?>：</label>
    				            <input type="checkbox" checked="checked" disabled="disabled"> <?php echo Controller_Admin::$privileges[Controller_Admin::PRIV_VIEW] ?>
    				    		<?php } else { ?>
    				    		<label><input type="checkbox"> <?php echo $resName ?>：</label>
    				            <?php foreach (Controller_Admin::$privileges as $privilege => $privName) { ?>
    				            <input type="checkbox" name="<?php echo $resource ?>_privileges[]" value="<?php echo $privilege ?>"> <?php echo $privName ?>
    				            <?php } ?>
    				    		<?php } ?>
    				    	</li>
    				    <?php } ?>
    				    </ul>
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
	$('ul li label input').click(function () {
		$(this).parents('li').children('input:enabled').prop('checked', $(this).prop('checked'));
	});
});
</script>
<%/block%>