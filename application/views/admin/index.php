<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1>圈乐CMS</h1>
	<p>欢迎你，<%$login_user.nick|escape:'html'%>！</p>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">

</script>
<%/block%>