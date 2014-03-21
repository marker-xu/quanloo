<div class="panel panel-t1 login_popup_big" style="width:900px;" id="login_popup_big">
	
	<div class="panel-content">
		<div class="hd">
			<h3>你还未登录</h3>
		</div>
		<div class="bd">
			
			<!-- content -->
			<style>
			.panel-temp-0806285482 {color:#666;margin:1em;}
			.panel-temp-0806285482 li {list-style:decimal;margin:0 0 .5em 2em;}
			</style>
			<div class="panel-temp-0806285482">
                <div class="pop_wrap">
                    <!--登录-->
            	<div class="loginer">
                	<h2 title="登录" class="s-ic-reg">登录</h2>
                    <ul>
                    	<li class="parent_">
                        	<div class="tit">电子邮件</div>
                        	<div class="formPlace cls">
                                <div class="l">
                                    <input type="text" class="text email" name="lp_user" tabindex="1">
                                    <input type="hidden" name="csrf_token" value="<%Security::token()%>" /> 
                                </div>
                                <div class="l tips lp_tip"></div>
                            </div>
                        </li>
                        <li class="parent_">
                        	<div class="tit">登录密码</div>
                        	<div class="formPlace cls">
                                <div class="l">
                                    <input type="password" class="text password" name="lp_pwd" tabindex="2"></div>
                                    <div class="l tips lp_tip"></div>
                                </div>
                        </li>
                        <%*
                        <li class="parent_">
                        	<div class="tit">输入验证码</div>
                        	<div class="formPlace cls"><div class="l">
                       		<input type="text" class="text checkcode" name="checkcode" tabindex="3" id="checkcode">
                    		<img class="code-pic" src="../resource/img/check-code.jpg"> <span class="change">看不清换一张</span>
                        	</div><div class="l tips lp_tip"></div></div>
                        </li>
                        *%>
                        <li>
                       		<div class="tit">
                                <input id="rememberme" type="checkbox" name="lp_remeber rememberme" />
                                <label class="remeb" for="rememberme">记住你</label>
                            </div>
                        </li>
                    </ul>
                    <div class="submiter cls">
                        <div class="l">
                            <button type="submit" class="btn btn-login s-ic-reg lp_login"></button>
                        </div>
                        <div class="l rmblink">
                            <span><a href="/user/findpassword" >忘记密码？</a></span>
                        </div>
                    </div>
                </div>
                <!--登录-->
                <!--第三方登录-->
                <div class="login3">
                	<div class="wrap">
                        <h3>用其他网站帐号登录：</h3>
                        <ul>
                            <li class="logo_snda"><a href="#">盛大通行证登录</a></li>
                            <li class="logo_sina"><a href="#">新浪微博账号登录</a></li>
                            <li class="logo_qq"><a href="#">腾讯微博账号登录</a></li>
                        </ul>
                    </div>
                    <div class="reg_new"><span class="t">还没有注册？</span><a href="/user/register" class="popBtn_a regid"><span>注册圈乐帐号</span></a></div>
                </div>
                <!--第三方登录-->
                </div>
			</div>
			<!-- //content -->

		</div>
		<div class="ft"></div>
	</div>

	<span class="co1"><span></span></span>
	<span class="co2"><span></span></span>
	<span class="cue"></span>
	<span class="sd"></span>
	<span class="close"></span>
	<span class="resize"></span>

</div>
