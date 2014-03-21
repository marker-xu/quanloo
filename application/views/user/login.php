<%extends file="common/base.tpl"%>

<%block name="title" prepend%>登录<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
	<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/reg.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>    

<%/block%>
<%block name="bd"%>
        <div id="bd">
            <div class="wrap login_popup_big" >
            	<!--登录-->
            	<div class="pbox" style="height:347px!important;padding-left: 560px;padding-top:55px;">
                	<iframe id="login_popup" src="<%$login_url%>" style="width:230px;height:220px;overflow:hidden;background:#fff;" scrolling="no" frameborder="0"></iframe>
                	<%*<div>
                		<a href="/connect?type=<%Model_Data_UserConnect::TYPE_DOUBAN%>">豆</a> 
						| <a href="/connect?type=<%Model_Data_UserConnect::TYPE_SINA%>">浪</a> 
						| <a href="/connect?type=<%Model_Data_UserConnect::TYPE_TQQ%>">腾</a> 
						| <a href="/connect?type=<%Model_Data_UserConnect::TYPE_RENREN%>">人</a>
                	</div>  *%>
                	<%*<div class="oauth-login">
                    	<p>只需6秒，通过以下合作网站帐号直接登录！</p>
                    	<div class="bt-ologins clearfix">
                    		<a class="bt-ologin bt-ologin-sina" href="/connect?type=<%Model_Data_UserConnect::TYPE_SINA%>">新浪</a>
                    		<a class="bt-ologin bt-ologin-tqq" href="/connect?type=<%Model_Data_UserConnect::TYPE_TQQ%>">腾讯</a>
                    	</div>
                    </div>*%>
                </div>
            </div>
        </div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
QW.use('Ajax,Valid', function(){
    W('#login_form').ajaxOnSubmit();
});
</script>
<%/block%>
