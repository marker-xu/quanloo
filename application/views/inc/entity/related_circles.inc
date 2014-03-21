<div class="y-sidebar r">
	<div class="y-about-box">
    	<div class="y-box-title"><h2>与Ta有关的圈子：</h2></div>
        <div class="y-box-main">
    		<ul class="list clearfix">
    			<%foreach $relatedCircles as $circle%>
				<li class="circleitem circleitem-t2">
                    <div class="hd">
						<h3><a href="<%Util::circleUrl($circle._id, null, $circle)%>" title="<%$circle.title|escape:'html'%>"><%Util::utf8SubStr($circle.title, 21)%></a></h3>
                        <div class="circle_own">
                            <img class="in-block head_img" src="<%Util::userAvatarUrl($circle.user.avatar.30, 30)%>">
                            <%if $circle.user._id == 1846037590%>
                            <span class="lele"><%$circle.user.nick|escape:"html"%></span>创建
                            <%else%>
                            <a class="user" target="_blank" href="<%Util::userUrl($circle.user._id)%>"><%$circle.user.nick|escape:"html"%></a>创建
                            <%/if%>
                        </div>
					</div>
                    <div class="bd">
						<a href="<%Util::circleUrl($circle._id, null, $circle)%>">
							<img src="<%Util::circlePreviewPic($circle.tn_path)%>" alt="<%$circle.title|escape:'html'%>">
						</a>
					</div>
					<div class="ft clearfix">
                        <a class="btn <%if $circle.is_focus%>followed<%else%>b-follow<%/if%>" href="#" data-action="{'id':'<%$circle._id%>'}" is-focus="<%$circle.is_focus%>"><span class="text0">关注</span><span class="text1">取消关注</span><span class="text2">取消关注</span></a>
						<a href="#" class="btn invite_friend" data-action="{'id':'<%$circle._id%>', 'url': '<%Util::circleUrl($circle._id, null, $circle)%>'}" >邀请好友</a>
						<a href="#" class="btn share_group" data-action="{'id':'<%$circle._id%>','image':'<%Util::circlePreviewPic($circle.tn_path)%>','circleName':'<%Util::utf8SubStr($circle.title,21)%>','url': '<%Util::circleUrl($circle._id, null, $circle)%>'}">分享Ta</a>
					</div>
                    <input type="hidden" value="<%$circle._id%>" />
				</li>
				<%/foreach%>
			</ul>
        </div>
    </div>
</div>