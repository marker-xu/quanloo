                           
                        <%if $login_user%>
                            <div id="logined" class="cla">  
                                <div class="s-ic logined_wrap">
                                    <a class="headp" href="<%Util::userUrl($login_user._id)%>"><img src="<%Util::userAvatarUrl($login_user.avatar.30)%>"></a>
                                    <input type="hidden" id="logined_mark" />
                                    <ul class="names logined_">
                                    	<li class="honey"><%$login_user.nick|escape:'html'%></li>
                                        <li class="item"><a href="<%#siteUrl#%>/widget"><span class="myHomepage">我的Widget</span></a></li>
                                        <li class="item setting"><a href="<%#siteUrl#%>/user/setting"><span class="mySetting">个人设置</span></a></li>
                                        <li class="item exting"><a href="<%#siteUrl#%>/user/logout?f=<%$smarty.server.REQUEST_URI|escape:"url"%>"><span class="ext">登出</span></a></li>
                                    </ul>
                                </div>
                            </div>
                        <%else%>
                            <div id="login" class="cla">
                            <a class="s-ic loginLink marmot" title="登录" href="<%#siteUrl#%>/user/login" id="fast_login" data--marmot="{
                page_id:'click_login',position:'top_header' }"><span></span></a>
                            <a class="reg" href="<%#siteUrl#%>/user/register?p=top_header&f=<%preg_replace('/^\//', '', strip_tags($smarty.server.REQUEST_URI))|escape:'encode'%>"><span>免费注册</span></a>
                            </div>
                        <%/if%> 
