
            <div class="videolist-inner">
            <%if !empty($circle_list)%>
             	<ul class="list clearfix">
             	<%foreach $circle_list as $row%>
					<li class="circleitem circleitem-t2">
						<div class="hd">
							<h3><a href="<%Util::circleUrl($row._id, null, $row)%>" title="<%$row.title|escape:'html'%>"><%Util::utf8SubStr($row.title,21)%></a></h3><span class="type <%Util::circleTypeCss($row)%>"></span>
							<%if $type!=3%>
							<div class="circle_own">
		                    <img class="in-block head_img" src="<%Util::userAvatarUrl($row.user.avatar.30, 30)%>">
		                    <%if $row.user._id == 1846037590%>
		                    <span class="lele"><%$row.user.nick|escape:"html"%></span>创建
		                    <%else%>
		                    <a class="user" target="_blank" href="<%Util::userUrl($row.user._id)%>"><%$row.user.nick|escape:"html"%></a>创建
		                    <%/if%>
                			</div>
                			<%/if%>
						</div>
						<div class="bd">
							<a href="<%Util::circleUrl($row._id, null, $row)%>">
							<img src="<%Util::circlePreviewPic($row.tn_path)%>" alt="<%$row.title|escape:'html'%>">
							</a>
						</div>
						<div class="ft clearfix">
							<%if $type==3%>
								<a class="btn edit-circle" href="/user/editcircle?cid=<%$row._id%>" ><span>编辑</span></a>
								<a href="#" class="btn invite_friend" data-action="{'id':'<%$row._id%>', 'url': '<%Util::circleUrl($row._id, null, $row)%>'}" >邀请好友</a>
								<a href="#" class="btn share_group" data-action="{'id':'<%$row._id%>', 'url': '<%Util::circleUrl($row._id, null, $row)%>'}">分享Ta</a>
							<%else%>
                                <%if $row.is_focus%>
                                <%$followst = 'followed'%>
                                <%else%>
                                <%$followst = 'b-follow'%>
                                <%/if%>
                                <a class="btn <%$followst%>" href="#" data-action="{'id':'<%$row._id%>'}" is-focus="<%$row.is_focus%>"><span class="text0">关注</span><span class="text1">取消关注</span><span class="text2">取消关注</span></a>
								<a href="#" class="btn invite_friend" data-action="{'id':'<%$row._id%>', 'url': '<%Util::circleUrl($row._id, null, $row)%>'}" >邀请好友</a>
								<a href="#" class="btn share_group" data-action="{'id':'<%$row._id%>', 'url': '<%Util::circleUrl($row._id, null, $row)%>'}">分享Ta</a>
							<%/if%>
						</div>
                                    <input type="hidden" value="<%$row._id%>" />
					</li>
				<%/foreach%>
				</ul>
				<%else%>
					<div class="empty">暂无数据</div>
				<%/if%>
            </div>

