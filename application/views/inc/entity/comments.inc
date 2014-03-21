<%if $comments%>
<div class="y-videoinfo-comment">
	<div class="y-box-title"><h2>豆瓣影评：</h2></div>
	<div class="y-box-main">
        <ul class="y-comment-list">
        	<%foreach $comments as $comment%>
        	<li>
        		<dl class="y-comment-box">
                	<dt class="y-comment-title"><%$comment.title|escape:'html'%></dt>
                    <dd class="y-comment-text">
                    	<span><%Util::utf8SubStr($comment.comment, 194)%></span>
                    	<span style="display:none;" class="js-hidden-comment-part"><%$comment.comment|escape:'html'%></span>
                    	<a href="<%$comment.url%>" target="_blank">全部&gt;&gt;</a>
                    </dd>
                    <dd class="y-comment-info"><%$comment.time%></dd>
                </dl>
            </li>
            <%/foreach%>
        </ul>
    </div>
</div>
<%/if%>