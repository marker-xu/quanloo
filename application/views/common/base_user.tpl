<%extends file="common/base.tpl"%>
<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/user2.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>	
<script>
	GROUP_ADD_URL = '/user/addtag';
	GROUP_DEL_URL = '/user/removetag';          
    IS_USER_TAG = 1;           
    USER_ID = "<%$user_info._id%>"||"0";
    LOGIN_USER_ID = "<%$login_user._id%>"||"0";
    
    XLogin.forceRefresh = <%if $login_user._id%>false<%else%>true<%/if%>;
    <%block name="custom_js_inline"%><%/block%>
</script>
<script type="text/javascript" src="<%#resUrl#%>/js/video/user.js?v=<%#v#%>"></script>
	<%$smarty.block.child%>
<%/block%>
<%block name="bd"%>
<div id="bd">
    <%*搜索框*%>
    <%include file="inc/search.inc"%>
    <%*//搜索框*%>
    <!--用户页面-->
    <div id="sns-page" class="clearfix">
        <!--用户边栏-->
		<div id="aside">
            <div id="userinfo">
                <h2 class="name"<%if $is_admin%> style="width:140px;"<%/if%>><%$user_info.nick|escape:'html'%></h2>
                <%if $is_admin%>
                <a class="lnk-admin" href="<%Url::site('user/setting', null, false)%>">个人设置</a>
                <%/if%>
                <div class="pic">
                <%if $user_info.avatar.200%><%$user_info_avatar=$user_info.avatar.200%><%else%><%$user_info_avatar=$user_info.avatar.160%><%/if%>               
                    <img width="200" src="<%Util::userAvatarUrl($user_info_avatar,160)%>" alt="<%$user_info.nick|escape:'html'%>" />
                </div>
                <div class="desc">
                	<%$user_info.intro|escape:"html"%>
                </div>
                <%*1为关注圈子   初来乍到   2为邀请好友    宾客盈门 3为成功创建圈子   繁花似锦*%>
                <%if !empty($medals)%>
                <div class="icons">
                    <ul class="clearfix">
                        <%foreach $medals as $item%>
                        <%if $item == 1%><%$alt = '初来乍到'%>
                        <%elseif $item == 2%><%$alt = '宾客盈门'%>
                        <%elseif $item == 3%><%$alt = '繁花似锦'%>
                        <%else%><%continue%>
                        <%/if%>
                        <li><%if $is_admin%><a href="/user/mymedal"><%/if%><img src="<%#resUrl#%>/img/user/<%$item%>.png" alt="<%$alt%>" title="<%$alt%>"/><%if $is_admin%></a><%/if%></li>
                        <%/foreach%>
                    </ul>
                </div>
                <%/if%>
                <div class="follows">
					<ul>
						<li class="first"><a href="<%Util::userUrl($user_info._id, 'follow')%>">关注:<b><%$user_info.followings_count|number_format%></b></a></li>
						<li><a href="<%Util::userUrl($user_info._id, 'fans')%>">粉丝:<b><%$user_info.followers_count|number_format%></b></a></li>
					</ul>
				</div>				
				<%if ! $is_admin%>
				<a class="btn<%if $user_info.is_fans%> bt-f-done<%else%> bt-f-add<%/if%>" data-action="{following:<%$user_info._id%>}" href="###"></a>
                <%/if%>
            </div>
            <div class="usertags">
            <%if $is_admin || ! empty($user_info.tags)%>
				<div class="hd"><%if $is_admin%><div id="xplus" class="xplus marmot xplus_tag" data--marmot="{page_id:'click_addtag'}"></div><%/if%><h3>兴趣标签<%*<span>(13)</span>*%></h3></div>
			<%/if%>
				<div class="bd" id="tag_box">
					<div class="tagadd clearfix" style="display:none;">
						<form id="formaddtag">
							<input class="ipt-text" placeholder="输入我的tag">
							<input class="ipt-submit" type="submit">
						</form>
					</div>
					<div class="taglist clearfix">
					<%if $is_admin%>
						<%$tag_str_tmp='tag-cus'%>
					<%else%>
						<%$tag_str_tmp='tag-sys'%>
					<%/if%>
						<%if isset($user_info.tags)%>
							<%foreach $user_info.tags as $tagTmp%>
							<div class="tag-item <%$tag_str_tmp%>">
								<span class="con tag_link"><a href="/search?q=<%urlencode($tagTmp)%>" target="_blank"><%$tagTmp|escape:"html"%></a></span>
								<span class="del"></span>
							</div>
							<%/foreach%>
						<%/if%>
					</div>
				</div>
				<div class="ft"></div>
			</div>
        </div>
        <!--/用户边栏-->
        <div id="article">
            <div id="sns-nav"><%block name="user_nav"%><%/block%></div>
			<!--内容-->
			<%block name="article"%><%/block%>
			<!--/内容-->
        </div>
    </div>
</div>
<%/block%>
<%block name="foot_js"%>

<%/block%>
