<%extends file="common/base.tpl"%>

<%block name="title" prepend%>找回密码<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
	<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/reg.css?v=<%#v#%>">
<%/block%>
<!--- 测试 -->
<%block name="bd"%>  
        <div id="bd">
			<div class="wrap">
            	<!--忘记密码-->
            	<div id="loginer">
                	<h2 title="忘记密码" class="s-ic-reg forgetTit"></h2>
                	<form action="/user/findpassword" method="post" id="register_form">
	                	<%Form::hidden('csrf_token', Security::token())%>
	                    <ul>
	                    	<li>
	                        	<div class="tit">输入你注册<span>圈乐</span>时的电子邮件</div>
	                        	<div class="formPlace cls"><div class="l"><input type="text" class="text" id="email" name="email" tabindex="1"></div><div class="l tips"></div></div>
	                        </li>
	                        
	                        <li>
	                        	<div class="tit">输入验证码</div>
	                        	<div class="formPlace cls"><div class="l">
	                       		<input type="text" class="text" name="checkcode" tabindex="3" id="checkcode">
	                    		<img class="code-pic" src="../resource/img/check-code.jpg"> <span class="change">看不清换一张</span>
	                        	</div><div class="l tips"></div></div>
	                        </li>
	                    </ul>
	                    <div class="submiter cls"><div class="l"><button type="submit" class="btn btn-forget s-ic-reg"></button></div></div>
                    </form>
                </div>
                <!--忘记密码-->
            </div>
        </div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
    (function(){

    })();
</script>
<%/block%>