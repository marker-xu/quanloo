<%*
1 – 观看视频，2 – 推视频[删掉了]，3 – 评论视频，4 – 关注圈子，5 – 分享视频，6 – 心情视频，7 – 分享圈子，8 – 邀请好友加入圈子
$user_circle_feedlist：动态信息的二维数组
$feed_title: 动态列表的标题
$is_circle: 是否是圈子动态

观看：     小A看了视频12345
评论：     小A评论了视频12345   ——不展示评论具体内容
关注：     小A关注了qq圈
分享：     小A分享了视频12345    ——分享功能这个版本可能不会上
分享：     小A分享了圈子12345    ——分享功能这个版本可能不会上
邀请：     小A邀请了小B关注qq圈
心情：     小A对视频12345表示喜欢
小A围观了视频12345
小A对视频12345开怀大笑
小A对视频12345皱了眉头

*%>
<%function name=usercirclefeedlist%>
	<%strip%>
	<%if $type == 1%>
		<img width="16px" height="14" class="ico-play" src="<%#resUrl#%>/img/b.png" />看了视频<a target="_blank" href="<%Util::videoPlayUrl($data.video._id)%>"><%$data.video.title|escape:"html"%></a>
	<%/if%>
	<%if $type == 2%>
		<img width="16px" height="14" class="ico-share" src="<%#resUrl#%>/img/b.png" />推荐了视频<a target="_blank" href="<%Util::videoPlayUrl($data.video._id)%>"><%$data.video.title|escape:"html"%></a>
	<%/if%>
	<%if $type == 3%>
		<img width="16px" class="ico-common" src="<%#resUrl#%>/img/b.png" />评论了视频<a target="_blank" href="<%Util::videoPlayUrl($data.video._id)%>"><%$data.video.title|escape:"html"%></a>
	<%/if%>
	<%if $type == 4%>
		关注了<%if $is_circle%><%$data.circle.title|escape:"html"%><%else%><img width="16px" class="ico-circle" src="<%#resUrl#%>/img/b.png" /><a target="_blank" href="<%Util::circleUrl($data.circle._id, null, $data.circle)%>"><%$data.circle.title|escape:"html"%></a><%/if%>圈
	<%/if%>
	<%if $type == 5%>
		<img width="16px" height="14" class="ico-share" src="<%#resUrl#%>/img/b.png" />分享了视频<a target="_blank" target="_blank" href="<%Util::videoPlayUrl($data.video._id)%>"><%$data.video.title|escape:"html"%></a>
	<%/if%>
	<%if $type == 6%>
		<%$xinqing = '喜欢'%>
		<%if $data.data == 'xh'%>
			<%$xinqing = '喜欢'%>
		<%elseif $data.data == 'wg'%>
			<%$xinqing = '围观'%>
		<%elseif $data.data == 'dx'%>
			<%$xinqing = '大笑'%>
		<%elseif $data.data == 'fn'%>
			<%$xinqing = '鄙视'%>
		<%elseif $data.data == 'jn'%>
			<%$xinqing = '囧'%>
		<%/if%>
		对视频<a target="_blank" href="<%Util::videoPlayUrl($data.video._id)%>"><%$data.video.title|escape:"html"%></a>表示<%$xinqing%><img width="16px" class="ico-xq-<%$data.data%>" src="<%#resUrl#%>/img/b.png" alt="<%$xinqing%>" />
	<%/if%>
	<%if ($is_circle && $type == 8) || (! $is_circle && $type == 7)%>
		<img width="16px" class="ico-share" src="<%#resUrl#%>/img/b.png" />分享了圈子<img width="16px" class="ico-circle" src="<%#resUrl#%>/img/b.png" /><a target="_blank" href="<%Util::circleUrl($data.circle._id, null, $data.circle)%>"><%$data.circle.title|escape:"html"%></a>圈
	<%/if%>
	<%if ($is_circle && $type == 9) || (! $is_circle && $type == 8)%>
		<img width="16px" class="ico-Invitation" src="<%#resUrl#%>/img/b.png" />邀请了好友关注<img width="16px" class="ico-circle" src="<%#resUrl#%>/img/b.png" /><a target="_blank" href="<%Util::circleUrl($data.circle._id, null, $data.circle)%>"><%$data.circle.title|escape:"html"%></a>圈
	<%/if%>
	<%/strip%>
<%/function%>
<div id="circlefeed">
	<div class="hd">
		<h3><%if ! empty($feed_title)%><%$feed_title|escape:"html"%><%else%>圈友动态<%/if%></h3>
	</div>
	<div class="bd">
		<ul>
		<%foreach $user_circle_feedlist as $item%>
			<li class="feeditem clearfix">
			
			<%if ! empty($item.user_id)%>
				<%if $item.type==1%>
					<a class="pic" href="<%Util::videoPlayUrl($item.video._id)%>" target="_blank">
						<img src="<%Util::videoThumbnailUrl($item.video.thumbnail)%>" alt="<%$item.video.title|escape:'html'%>" style="width:30px" />
					</a>
				<%else%>
					<a target="_blank" class="pic" href="<%Util::userUrl($item.user_id)%>">
					<img src="<%Util::userAvatarUrl($item.user.avatar.30, 30)%>" alt="<%$data.user.nick|escape:"html"%>" />
					</a>
				<%/if%>	
			<%else%>
				<div class="pic"><img src="<%Util::userAvatarUrl($item.user.avatar.30, 30)%>" alt="游客" /></div>
			<%/if%>			
				<div class="info">
					<%if ! empty($item.user_id)%>
						<%if $item.type==1%>
						有1位圈友
						<%else%>
						<a target="_blank" href="<%Util::userUrl($item.user_id)%>"><%$item.user.nick|escape:"html"%></a>
						<%/if%>
					<%else%>
					游客
					<%/if%>
					<%usercirclefeedlist type=$item.type data=$item is_circle=$is_circle%>
				</div>
			</li>
		<%/foreach%>
		</ul>
	</div>
</div>