<%extends file="common/widget.tpl"%>

<%block name="title" prepend%>widget制定<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/widget.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>
<script type="text/javascript" src="<%#resUrl#%>/js/video/widget.js?v=<%#v#%>"></script>
<%/block%>
<!--- 测试 -->
<%block name="bd"%>
<div id="bd" >
  <!--  <center>请先<a href="" class="fast_login">登录</a>系统，未注册用户请<a href="/user/register?f=<%urlencode("/widget/index")%>">注册</a>圈乐帐号。</center>-->
</div>

<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">

    window.onload = function(){
        W("#fast_login").on("click",LOGIN_POPUP)
       LOGIN_POPUP();
    }

    //})
</script>
<%/block%>