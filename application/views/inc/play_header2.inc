    	<div id="hd">
        	<div class="wrap">
            	<div class="content">
                    <div id="logo">
                        <a href="<%#siteUrl#%>/" title="首页">
                            <img src="<%#resUrl#%>/img/logo.png?20120823" alt="圈乐" />
                        </a>
                    </div>
                    <ul id="top_menu">
                    	<li><a href="<%#siteUrl#%>/" class="a <%if strcasecmp($smarty.server.REQUEST_URI, '/') == 0 || strncasecmp($smarty.server.REQUEST_URI, '/?', 2) == 0%>selected<%/if%>">首页</a></li>
                        <li><a class="a discover_ <%if strncasecmp($smarty.server.REQUEST_URI, '/category/', 10) == 0%>selected<%/if%>" href="<%#siteUrl#%>/category/all">圈子</a>
                            <!--圈子-->
                            <div class="panel-look-log popup_discover_" style="display:none">
                                <div class="hd clearfix">
                                    通过下面的分类找到你中意的圈子或视频：
                                </div>
                                <div class="bd">
                                    <div class="tag_wrap cls">
                                        <a href="/category/all"><span>全部</span></a>
                                        <%$arrCatIdToKeyMap = Model_Data_Circle::$arrUrlKeyForCategorys%>
                                        <%foreach $categorys as $k => $v%>
                                        <a href="/category/<%$arrCatIdToKeyMap[$k]%>"><span><%$v|escape:"html"%></span></a>
                                        <%/foreach%>
                                    </div>
                                </div>
                                <div class="js_creatCircle mycircle-creat-btn2">创建我的圈子</div>
                            </div>
                            <!--圈子-->
                        </li>
                        <li><a class="a <%if strncasecmp($smarty.server.REQUEST_URI, '/guesslike', 10) == 0%>selected<%/if%>" href="<%#siteUrl#%>/guesslike">发现</a></li>
                        <li><a class="a <%if strncasecmp($smarty.server.REQUEST_URI, '/movie', 6) == 0%>selected<%/if%>" href="<%#siteUrl#%>/movie">电影</a></li>
                        <li><a class="a <%if strncasecmp($smarty.server.REQUEST_URI, '/tv', 3) == 0%>selected<%/if%>" href="<%#siteUrl#%>/tv">电视剧</a></li>
                    </ul>                
                           
                        <%if $login_user%>
                            <div id="logined" class="cla">  
                                <div class="s-ic logined_wrap">
                                    <a class="headp" href="<%Util::userUrl($login_user._id)%>"><img src="<%Util::userAvatarUrl($login_user.avatar.30)%>"></a>
                                    <input type="hidden" id="logined_mark" />
                                    <ul class="names logined_">
                                    	<li class="honey"><%$login_user.nick|escape:'html'%></li>
                                        <li class="item"><a href="<%Util::userUrl($login_user._id)%>"><span class="myHomepage">我的主页</span></a></li>
                                        <li class="item"><a href="<%Util::userUrl($login_user._id, 'circle')%>"><span class="myCircle">我的圈子</span></a></li>
                                        <li class="item"><a href="<%Util::userUrl($login_user._id, 'video', ['type'=>'watch_later'])%>"><span class="myVideo">我的视频</span></a></li>
                                        <li class="item"><a href="<%Util::userUrl($login_user._id, 'follow')%>"><span class="myFollow">我的关注</span></a></li>
                                        <li class="item"><a href="<%Util::userUrl($login_user._id, 'fans')%>"><span class="myFans">我的粉丝</span></a></li>
                                        <li class="item setting"><a href="<%#siteUrl#%>/user/setting"><span class="mySetting">个人设置</span></a></li>
                                        <li class="item exting"><a href="<%#siteUrl#%>/user/logout?f=<%$smarty.server.REQUEST_URI|escape:"url"%>"><span class="ext">登出</span></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="msg_hand" id="hd_new_info_cnt">
                            <%$intUnreadMsgNumTmp=Model_Logic_User::getUserMsgCount($login_user._id, Model_Logic_User::$USER_UNREAD_MSG_COUNT_KEY)%>
                                <div class="list_ico" id="hd_new_info_tip_"><%if $intUnreadMsgNumTmp > 0%><span class="num"><%$intUnreadMsgNumTmp%></span><%else%><span class="num" style="display:none">0</span><%/if%></div>
                                <div class="news_handler" id="hd_new_info_box_" style="display:none;"></div>
                            </div>
                        <%else%>
                            <div id="login" class="cla" style="width:162px;">
                            <a class="s-ic loginLink marmot js_fast-login" title="登录" href="<%#siteUrl#%>/user/login" id="fast_login" data--marmot="{
                page_id:'click_login',position:'top_header' }"><span></span></a>
                            <a class="reg" href="<%#siteUrl#%>/user/register?p=top_header&f=<%preg_replace('/^\//', '', strip_tags($smarty.server.REQUEST_URI))|escape:'encode'%>"><span>免费注册</span></a>
                            </div>
                        <%/if%>         
                        <div id="search" style="<%if $login_user%> <%else%>position: absolute;right: 154px;top:0;<%/if%>">
                            <form action="<%#siteUrl#%>/search" id="search-form2">
                                <fieldset>
                                    <legend>搜索</legend>
                                    <!-- 搜索框区域 -->
                                    <div id="search-aera">
                                        <span class="search-tips">请输入你感兴趣的主题、关键词</span>   
                                        <p class="search-wd">
                                        <%if $search_result.query%>
                                            <%$queryStr = join('', $search_result.query)|escape:'html'%>
                                        <%/if%>
                                            <input name="q" class="search-frame2" placeholder="请输入你感兴趣的主题、关键词" id="search-text2" maxlength="100" <%if $queryStr%> value="<%$queryStr%>"<%/if%>><button type="submit"><span>搜索</span></button>                                        
                                        </p>
                                    </div>
                                    <!--//搜索框区域 -->
                                </fieldset>
                            </form>                        
                        </div>          
                </div>
            </div>
        </div>
        <div class="black-tips"><span>提示</span><em></em></i></div>
        
        <%include file="inc/_header_login.inc" inline%>
        <%include file="inc/brick_tmpl.inc"  inline%>

    <a href="###" id="feed_back" class="marmot" data--marmot="{page_id:'click_feedback'}"><img src="<%#resUrl#%>/img/feedback.png"></a>

