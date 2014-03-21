<%extends file="common/base.tpl"%>

<%block name="title" prepend%>个人设置——修改密码<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
	<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/userSetting.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>
<script type="text/javascript" src="<%#resUrl#%>/js/video/user_settings.js?v=<%#v#%>"></script>
<script type="text/javascript" src="<%#resUrl#%>/js/components/switch/switch_all.js?v=<%#v#%>"></script>  
<%/block%>
<!--- 测试 -->
<%block name="bd"%>  
        <div id="bd">
            	<!--个人设置-->
            	<div id="user_setting">
                    <%include file="user/user_setting_panel.inc"%>
                    <div class="setting_content editPwd" id="password_">
                    <form action="/user/modifypassword" method="post" id="modifypassword_form">
                    <%Form::hidden('csrf_token', Security::token())%>
                    	<div class="wrap">
                            <div class="form_module">
                                <div class="tit">当前密码</div>
                                <div class="formPlace cls"><div class="l"><input type="password" class="text" id="pass_old" name="pass_old" tabindex="1"></div>
                                <div class="l tips"></div></div>
                                
                            </div>
                            <div class="form_module">
                                <div class="tit">新密码</div>
                                <div class="formPlace cls"><div class="l"><input type="password" class="text" id="password" name="password" tabindex="2"></div>
                                <div class="l tips"></div></div>
                                <div class="tipTxt">4-12位，可以使用英文（区分大小写），数字和符号</div>
                            </div>
                            <div class="form_module clearBg">
                                <div class="tit">确认密码</div>
                                <div class="formPlace cls"><div class="l"><input type="password" class="text" id="pass_again" name="pass_again" tabindex="3"></div>
                                <div class="l tips final_tips"></div></div>
                            </div>
                            <div class="form_module clearBg"><button type="submit" class="btn btn-complete s-ic-reg"></button></div>
                        </div>
                    </div>
                </div>
                <!--个人设置-->
        </div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
    (function(){

    })();
</script>
<%/block%>
