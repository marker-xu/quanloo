<%for $col=0 to 2%>
<div class="cols index_<%$col%>" style="float: left; margin-left: <%if $col == 0%>0<%else%>13<%/if%>px; width: 228px;">
	<%foreach $videos as $video%>
	<%if $video@index % 4 == $col%>
	<div style="opacity: 1;" class="BoardBrick">
		<%if $video.circle%>
		<div class="circleName s-ic"><span><a href="<%Util::circleUrl($video.circle._id, null, $video.circle)%>"><%$video.circle.title|escape:'html'%></a></span></div>
		<%else%>
		<div class="hidenHead"></div>
		<%/if%>
		<div class="vod m-pic tp-a cls marmot">
			<a style="width:200px;height:151px" class="a" href="<%Util::videoPlayUrl($video._id)%>" target="_blank">
				<img style="width:200px;height:151px" class="h brick_img" alt="<%$video.title|escape:'html'%>" src="<%Util::videoThumbnailUrl($video.thumbnail)%>">
				<span class="ico"></span>
			</a>
			<span class="time"><%Util::sec2time($video.length)%></span>
			<div class="tit">
				<a href="<%Util::videoPlayUrl($video._id)%>" target="_blank" title="<%$video.title|escape:'html'%>"><%$video.title|escape:'html'%> </a>
			</div>
			<div class="count">
				<%if $video.shared_count > 0%>
				<span title="分享" class="share"><%$video.shared_count%></span>
				<%/if%>
				<%if $video.mooded_count.total > 0%>
				<span title="心情" class="heart _mood"><%$video.mooded_count.total%></span>
				<%/if%>
				<%if $video.watched_count > 0%>
				<span title="播放" class="playn"><%$video.watched_count%></span>
				<%/if%>
			</div>
		</div>
		<div class="commentBox">
            <%foreach $video.comments as $comment%>
			<dl class="comment cls">
                <%if $comment.user_id > 0%>
                <dt>
                	<a href="<%Util::userUrl($comment.user_id)%>"><img src="<%$comment.avatar%>"></a><span class=""></span>
                </dt>
                <dd>
                    <a href="<%Util::userUrl($comment.user_id)%>"><%$comment.nick|escape:'html'%></a>：<%Util::formatUserLinkText($comment.data, $comment.users)%>
                </dd>
                <%else%>
                <dt>
                	<img src="<%$comment.avatar%>"><span class=""></span></dt>
                <dd>
                    <%$comment.nick|escape:'html'%>：<%$comment.data|escape:'html'%>
                </dd>
                <%/if%>
			</dl>
            <%/foreach%>
		</div>
	</div>
	<%/if%>
	<%/foreach%>
</div>
<%/for%>
