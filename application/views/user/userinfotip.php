
<div class="avatar_inner">
    <div class="avatar_tips">
    	<div class="ava_barrow"></div>
    	<div class="ava_sbox">
    		<div class="ava_l">
    			<a href="<%Util::userUrl($user_info._id)%>" target="_blank">
    			<%if $user_info.avatar.200%><%$user_info_avatar=$user_info.avatar.200%><%else%><%$user_info_avatar=$user_info.avatar.160%><%/if%> 
    			<img src="<%Util::userAvatarUrl($user_info_avatar,160)%>" width="100" height="100" class="ava_img" alt="<%$user_info.nick|escape:'html'%>"></a>
    		</div>
    		<div class="ava_r">
    			<dl class="ava_r_item">
    				<dd>
    					<div class="float_l">
    						<a href="<%Util::userUrl($user_info._id)%>" class="ava_name" target="_blank"><%$user_info.nick|escape:'html'%></a>
    					</div>
    					<%if ! $is_admin%>
    					<div class="float_r">
    						<a class="user_focus_ btn<%if $user_info.is_fans%> bt-f-done<%else%> bt-f-add<%/if%>" data-action="{following:<%$user_info._id%>}"
    							href="###">关注/取消关注</a>
    					</div>
    					<%/if%>
    				</dd>
    				<dd>
    					<a href="<%Util::userUrl($user_info._id, 'follow')%>" target="_blank">关注：<%$user_info.followings_count|number_format%></a> <span class="spr">|</span><a
    						href="<%Util::userUrl($user_info._id, 'fans')%>" target="_blank">粉丝：<%$user_info.followers_count|number_format%></a>
    				</dd>
    			</dl>		
                <%if ! empty($user_info.tags)%>
    			<div class="dot_line"></div>
    			<dl class="ava_r_item">
    				<dt>兴趣标签</dt>
    				<dd>
    					<div class="taglist clearfix">
    					<%foreach $user_info.tags as $tagTmp%>
    						<%if $tagTmp@index > 2%><%break%><%/if%>
    						<div class="tag-item tag-cus">
    							<span class="con tag_link"><a href="/search?q=<%urlencode($tagTmp)%>" target="_blank"><%$tagTmp|escape:"html"%></a></span>
    						</div>
    					<%/foreach%>
    					</div>
    				</dd>
    			</dl>
                <%/if%>  
    		</div>
    	</div>
    </div>
</div>
