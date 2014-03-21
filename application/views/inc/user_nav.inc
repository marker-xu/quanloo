<div class="nav-main">
	<ul class="clearfix">
		<li<%if $pagetype == 0%> class="on"<%/if%>>
			<a href="<%Util::userUrl($user_info._id)%>">首页</a>
		</li>
		<li<%if $pagetype == 2%> class="on"<%/if%>>
    		<a href="<%Util::userUrl($user_info._id, 'circle')%>">圈子</a>			
		</li>
		<li<%if $pagetype == 3%> class="on"<%/if%>>
            <a href="<%Util::userUrl($user_info._id, 'video')%>">视频</a>
		</li>
		<li<%if $pagetype == 4%> class="on"<%/if%>>
			<a href="<%Util::userUrl($user_info._id, 'follow')%>">关注</a>
		</li>
		<li<%if $pagetype == 5%> class="on"<%/if%>>
			<a href="<%Util::userUrl($user_info._id, 'fans')%>">粉丝</a>
		</li>
	</ul>
</div>
