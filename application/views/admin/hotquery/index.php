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
	<h1>热搜词管理</h1>
	<form action="" method="post">
    	<dl>
    		<dt><strong>白名单</strong>（白名单里的词汇将放在热搜词前面）</dt>
    		<dd>
    			<?php for ($i = 0; $i < 5; $i++) { ?>
    			<input type="text" name="whitelist[]" value="<?php echo isset($whitelist[$i]) ? $whitelist[$i] : '' ?>" />
    			<?php } ?>
    		</dd>
    	</dl>
    	<dl>
    		<dt><strong>黑名单</strong>（黑名单里的词汇将从热搜词里剔除）</dt>
    		<dd>
    			<?php for ($i = 0; $i < 5; $i++) { ?>
    			<input type="text" name="blacklist[]" value="<?php echo isset($blacklist[$i]) ? $blacklist[$i] : '' ?>" />
    			<?php } ?>
    		</dd>
    	</dl>
    	<input type="submit" value="提交">
	</form>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
$(function () {
	
});
</script>
<%/block%>