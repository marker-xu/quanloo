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
	<h1><a href="/admin_topiccircle">主题圈管理</a></h1>
	<form action="" method="post" enctype="multipart/form-data">
		<table>
            <tbody>
            	<tr>
                    <td>圈子ID：</td>
                    <td><input name="id" value="<?php echo $id > 0 ? $id : '' ?>"></td>
                </tr>
            	<tr>
                    <td>大图：</td>
                    <td><input type="file" name="big_picture"></td>
                </tr>
            	<tr>
                    <td>小图：</td>
                    <td><input type="file" name="small_picture"></td>
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

</script>
<%/block%>