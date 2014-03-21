        <%*登录弹出框*%>
        <div id="login_popup" style="visibility:hidden;left:-10000px;position:absolute;width:227px;height:220px;border-left:1px solid #a3acb3;border-right:1px solid #a3acb3;border-bottom:1px solid #a3acb3;overflow:hidden;background:#fff;overflow:hidden;">
	        <div style="position:relative;min-height:220px;_height:220px;">
	        <iframe id="login_popup" src="about:blank" style="position:absolute;width:230px;height:200px;overflow:hidden;background:#fff;left:-1px;" scrolling="no" frameborder="0"></iframe>
	        </div>
            <%*<div class="oauth-login">
                <!--<p>只需6秒，通过以下合作网站帐号直接登录！</p>-->
                <div class="bt-ologins clearfix">
                    <a class="bt-ologin bt-ologin-sina" href="/connect?type=<%Model_Data_UserConnect::TYPE_SINA%>">新浪</a>
                    <a class="bt-ologin bt-ologin-tqq" href="/connect?type=<%Model_Data_UserConnect::TYPE_TQQ%>">腾讯</a>
                </div>
            </div>*%>
        </div>
<%*
        <div class="login_popup" id="login_popup" style="display:none;">
            <dl>
                <dd class="parent_">
                    <span class="lp_label">邮　箱：</span>
                    <div class="lp_box">
                        <input type="text" name="lp_user" />
                        <input type="hidden" name="csrf_token" value="<%Security::token()%>" />                       
                        <span class="lp_tip " tabindex="-1"></span>
                    </div>
                </dd>
                <dd class="parent_">
                    <span class="lp_label">密　码：</span>
                    <div class="lp_box">
                        <input type="password" name="lp_pwd" />
                        <span class="lp_tip" tabindex="-1"></span>
                    </div>
                </dd>
<!--
                <dd class="parent_">
                    <span class="lp_label">验证码：</span>
                    <div class="lp_box">
                        <input type="text" name="lp_validcode" class="lp_validcode" />
                        <span class="lp_tip"><img src="pic/validcode_test.jpg" class="lp_validimg" /></span>
                    </div>
                </dd>
                <dd>
                    <span class="lp_label"></span>
                    <div class="lp_box">
                        <a href="#" class="lp_new_valid_img">看不清楚，换一个</a>
                    </div>
                </dd>
-->
            </dl>
            
            <div class="lp_marker"></div>
            
            <dl>
                <dd>
                    <span class="lp_label"></span>
                    <div class="lp_box">
                        <input type="checkbox" id="lp_remeber" class="lp_remeber" /><label for="lp_remeber">记住你</label>
                    </div>
                </dd>
                <dd class="lp_login_box">
                    <span class="lp_label"></span>
                    <div class="lp_box">
                        <button type="button" class="lp_login"></button> <a href="/user/findpassword">忘记密码?</a>
                    </div>
                </dd>
                <dd>
                    <span class="lp_label"></span>
                    <div class="lp_box">
                        <a href="/user/login">使用新浪微博、MSN等其他账号登陆</a>
                    </div>
                </dd>
            </dl>
        </div>
*%>
        <%*end 登录弹出框*%>
