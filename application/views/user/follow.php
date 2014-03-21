<%extends file="common/base_user.tpl"%>
<%block name="seo_meta"%>
<%$user_nick_escape=HTML::chars($user_info.nick)%>
    <meta name="keywords" content="<%$user_nick_escape%>,关注的人" />
    <meta name="description" content="<%$user_nick_escape%>关注的人">
<%/block%>

<%block name="title" prepend%><%$user_nick_escape%>关注的人<%/block%>

<%block name="custom_js"%>

<%/block%>

<%block name="user_nav"%>
<%include file="inc/user_nav.inc" pagetype=4%>
<%/block%>

<%block name="article"%>
			<!--关注和粉丝-->
			<div id="followlist">
				<div class="inner">
					<div class="f-l-hd">
						<h3>已关注（<%$following_total_num%>人）</h3>
					</div>
					<div class="f-l-bd">
						<ul class="clearfix">
						<%foreach $following_list as $v%>
							<li class="follow-item follow-item-ex">
								<a class="pic" href="<%Util::userUrl($v._id)%>" target="_blank"><img src="<%Util::userAvatarUrl($v.avatar.48, 48)%>" class="ava_popup_" data-id="<%$v._id%>"></a>
								<p class="name"><a href="<%Util::userUrl($v._id)%>" target="_blank" class="ava_popup_" data-id="<%$v._id%>"><%$v.nick|escape:"html"%></a></p>
								<%if $is_admin%>
								<%*<a href="###" data-action="{following:<%$v._id%>}" class="btn bt-f-del"></a>*%>
								<%/if%>
							</li>
						<%/foreach%>
						</ul>
					</div>
                    <%if $following_total_num > $following_page_count%>
						<%include file="inc/pager.inc" count=$following_page_count offset=$following_page_offset total=$following_total_num%>
					<%/if%>					
				</div>
			</div>
			<!--/关注和粉丝-->
<%/block%>

<%block name="custom_foot"%>    
<script type="text/marmot">
{
    "page_id"   : "userfollow"
}
</script>
<%/block%> 
