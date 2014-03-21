<%extends file="common/adminbase.tpl"%>
<%block name="custom_css"%><%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1>添加关键词</h1>
	<%if $err_msg%><p style="color:red"><%$err_msg|escape:"html"%></p><%/if%>
	<form action="#" method="post" enctype="multipart/form-data" id="form_addword">
		<table>
            <tbody>
            	<tr>
                    <td>词表：</td>
                    <td><%html_options name='tbname' options=$wordlist_map%></td>
                </tr>
            	<tr>
                    <td>关键词(1)：</td>
                    <td>
                    	<textarea rows="5" cols="50" name="words"></textarea>
                    	<br>一行一个关键词
                    </td>
                </tr>
            	<tr>
                    <td>关键词(2)：</td>
                    <td>
                    	<input type="file" name="wordsfile">
                    	<br>从txt文件导入关键词，一行一个
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

});
</script>
<%/block%>