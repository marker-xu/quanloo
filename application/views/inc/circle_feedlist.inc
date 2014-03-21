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
		<img width="16px" class="ico-play" src="<%#resUrl#%>/img/b.png" />观看了视频<a target="_blank" href="<%Util::videoPlayUrl($data.video._id, NULL, $circleParam)%>"><%$data.video.title|escape:"html"%></a>
	<%/if%>
	<%if $type == 2%>
		<img width="16px" class="ico-share" src="<%#resUrl#%>/img/b.png" />推荐了视频<a target="_blank" href="<%Util::videoPlayUrl($data.video._id, NULL, $circleParam)%>"><%$data.video.title|escape:"html"%></a>
	<%/if%>
	<%if $type == 3%>
		<img width="16px" class="ico-common" src="<%#resUrl#%>/img/b.png" />评论了视频<a target="_blank" href="<%Util::videoPlayUrl($data.video._id, NULL, $circleParam)%>"><%$data.video.title|escape:"html"%></a>
	<%/if%>
	<%if $type == 4%>
		关注了<%if $is_circle%><%$data.circle.title|escape:"html"%><%else%><img width="16px" class="ico-circle" src="<%#resUrl#%>/img/b.png" /><a target="_blank" href="<%Util::circleUrl($data.circle._id, null, $data.circle)%>"><%$data.circle.title|escape:"html"%></a><%/if%>圈
	<%/if%>
	<%if $type == 5%>
		<img width="16px" class="ico-share" src="<%#resUrl#%>/img/b.png" />分享了视频<a target="_blank" target="_blank" href="<%Util::videoPlayUrl($data.video._id, NULL, $circleParam)%>"><%$data.video.title|escape:"html"%></a>
	<%/if%>
	<%if $type == 6%>
		<%$xinqingcss = 'ico-xq-xh'%>
		<%if $data.data == 'xh'%>
			<%$xinqingcss = 'ico-xq-xh'%>
		<%elseif $data.data == 'wg'%>
			<%$xinqingcss = 'ico-xq-wg'%>
		<%elseif $data.data == 'dx'%>
			<%$xinqingcss = 'ico-xq-dx'%>
		<%elseif $data.data == 'fn'%>
			<%$xinqingcss = 'ico-xq-fn'%>
		<%elseif $data.data == 'jn'%>
			<%$xinqingcss = 'ico-xq-jn'%>
		<%/if%>
		<%if $xinqingcss!='ico-xq-xh'%>
		<img width="16px" class="<%$xinqingcss%>" src="<%#resUrl#%>/img/b.png" />
		<%/if%>
		对视频<a target="_blank" href="<%Util::videoPlayUrl($data.video._id, NULL, $circleParam)%>"><%$data.video.title|escape:"html"%></a>标识心情
	<%/if%>
	<%if ($is_circle && $type == 8) || (! $is_circle && $type == 7)%>
		<img width="16px" class="ico-share" src="<%#resUrl#%>/img/b.png" />分享了圈子<img width="16px" class="ico-circle" src="<%#resUrl#%>/img/b.png" /><a target="_blank" href="<%Util::circleUrl($data.circle._id, null, $data.circle)%>"><%$data.circle.title|escape:"html"%></a>圈
	<%/if%>
	<%if ($is_circle && $type == 9) || (! $is_circle && $type == 8)%>
		<img width="16px" class="ico-Invitation" src="<%#resUrl#%>/img/b.png" />邀请了好友关注<img width="16px" class="ico-circle" src="<%#resUrl#%>/img/b.png" /><a target="_blank" href="<%Util::circleUrl($data.circle._id, null, $data.circle)%>"><%$data.circle.title|escape:"html"%></a>圈
	<%/if%>
	<%/strip%>
<%/function%>

<a name="group_trend_anchor" id="group_trend_anchor"></a>
<!--圈子动态 默认-->
<div class="circlefeed" id="circlefeedmini">
	<div class="hd">
		<h3><div class="bt-open open__" title="展开详细内容">展开</div>圈子动态</h3>
	</div>
	<div class="bd">
		<ul class="feedlist">
		<%$circleParam.circle = $circle_id%>
		<%foreach $user_circle_feedlist as $item%>
			<li class="feeditem clearfix">
					<%$firstUserTmp = $item.users.0%>
					<%$totalUserTmp = count($item.users)-1%>
					<%if $item.circle_id%>
						<%if $firstUserTmp%>		
							<a class="pic ava_popup_"  href="<%Util::userUrl($firstUserTmp.user_id)%>" target="_blank">
								<img class="ava_popup_" data-id="<%$firstUserTmp.user_id%>" src="<%Util::userAvatarUrl($firstUserTmp.user.avatar.30, 30)%>" alt="<%$firstUserTmp.user.nick|escape:"html"%>">
							</a>
						<%else%>
							<div class="pic"><img src="<%Util::userAvatarUrl("", 30)%>" alt="游客" /></div>
						<%/if%>
					<%else%>
						<a class="pic" href="<%Util::videoPlayUrl($item.video._id, NULL, $circleParam)%>" target="_blank">
							<img src="<%Util::videoThumbnailUrl($item.video.thumbnail)%>" alt="<%$item.video.title|escape:'html'%>" style="width:30px" />
						</a>
					<%/if%>
						
					<div class="info">
						<%if $item.type ==1%>
						有<%$totalUserTmp+1%>位圈友
						<%else%>
							<%if $firstUserTmp%>	
							<a href="<%Util::userUrl($firstUserTmp.user_id)%>" target="_blank" class="ava_popup_" data-id="<%$firstUserTmp.user_id%>"><%$firstUserTmp.user.nick|escape:"html"%></a>
							<%else%>
							<span>游客</span>
							<%/if%>
							<%if $totalUserTmp>0%>和其他<%$totalUserTmp%>位用户
							<%/if%>
						<%/if%>
						<%usercirclefeedlist type=$item.type data=$item is_circle=$is_circle circleParam=$circleParam%>
						
					</div>
				
				
			</li>
		<%/foreach%>
		</ul>
	</div>
	<div class="ft">
		<div class="bt-open open__">展开</div>
	</div>
</div>
<!--end 圈子动态 默认-->

<!--圈子动态 展开-->
<div class="circlefeed" id="circlefeedall" style="z-index: 999; display:none;">
	<div class="hd">
		<h3><div class="bt-close close__" title="收起详细内容">收起</div>圈子动态</h3>
	</div>
	<div class="bd">
		<div class="content">
			<ul class="feedlist open_list__">
<!--
				<li class="feeditem clearfix">
					<a class="pic" href="#">
						<img src="pic/facemini.png" alt="">
					</a>
					<div class="info">
						<a href="#">可爱的猫</a>对<b>碟中谍</b>评论：真是难看啊！<img width="16" class="ico-common" src="../resource/img/b.png" /><img width="16" class="ico-xq-fn" src="../resource/img/b.png" /><img width="16" class="ico-xq-wg" src="../resource/img/b.png" /><img width="16" class="ico-xq-dx" src="../resource/img/b.png" /><img width="16" class="ico-xq-dx" src="../resource/img/b.png" /><img width="16" height="14" class="ico-share" src="../resource/img/b.png" /><img width="16" height="14" class="ico-play" src="../resource/img/b.png" />
					</div>
				</li>
				<li class="feeditem clearfix">
					<a class="pic" href="#"><img src="pic/facemini.png" alt=""></a>
					<div class="info">
						<a href="#">可爱的猫</a>对<b>碟中谍</b>评论：真是难看啊！
					</div>
					<div class="userall">
						<a href="#"><img src="pic/facemini.png" alt=""></a> <a href="#"><img src="pic/facemini.png" alt=""></a>
					</div>
				</li>
				<li class="feeditem clearfix">
					<a class="pic" href="#">
						<img src="pic/facemini.png" alt="">
					</a>
					<div class="info">
						<a href="#">可爱的猫</a>对<b>碟中谍</b>评论：真是难看啊！
					</div>
				</li>
				<li class="feeditem clearfix">
					<a class="pic" href="#">
						<img src="pic/facemini.png" alt="">
					</a>
					<div class="info">
						<a href="#">可爱的猫</a>对<b>碟中谍</b>评论：真是难看啊！
					</div>
				</li>
-->
			</ul>
		</div>
		<div class="aside">
			<ul class="time_list__">
				<li class="on" data-list="0">即刻</li>
				<li data-list="180">3分钟前</li>
                <li data-list="3600">1小时前</li>
                <li data-list="10800">3小时前</li>
                <li data-list="86400">1天前</li>
                <li data-list="259200">3天前</li>
<!--
                <li data-list="680400">7天前</li>
-->
			</ul>
		</div>
	</div>
	<div class="ft">
		<div class="bt-close close__">收起</div>
	</div>
</div>
<!--end 圈子动态 展开-->
