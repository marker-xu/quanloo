<%*
@param array $data 评论列表
*%>
<%function name=guesslike_comments%>
	<%strip%>
				<!--评论-->
                <div class="commentBox">
                    <%foreach $data as $comment%>
                    <dl class="comment cls">
                        <%if $comment.user_id > 0%>
                        <dt>
                            <a href="<%Util::userUrl($comment.user_id)%>"><img src="<%$comment.avatar%>"></a><span class=""></span>
                        </dt>
                        <dd style="height:3em;overflow-y:hidden;">
                            <a href="<%Util::userUrl($comment.user_id)%>"><%$comment.nick|escape:'html'%></a>：<%Util::formatUserLinkText($comment.data, $comment.users)%>
                        </dd>
                        <%else%>
                        <dt>
                            <img src="<%$comment.avatar%>"><span class=""></span></dt>
                        <dd style="height:3em;overflow-y:hidden;">
                            <%$comment.nick|escape:'html'%>：<%$comment.data|escape:'html'%>
                        </dd>
                        <%/if%>
                    </dl>
                    <%/foreach%>
                </div>
	<%/strip%>
<%/function%>
<%*
@param $row 视频内容
@param $tmpCircleId 对应圈子ID
*%>
<%function name=guesslike_moods%>
	<%strip%>
		<!--表情-->
				<div class="interactive s-rx cls">
                    <span>
                        <a href="###" class="i-mood-tips i-comment-discover marmot" title="评论" data-vid="<%$row._id%>" data--marmot="{page_id:'click_comment',video_id: '<%$row._id%>'}"></a>
                        <i></i>
                        <a href="###" class="i-mood i-love i-mood-tips marmot" title="喜欢" data-action="{id:'<%$row._id%>',mood:'xh', circle:<%$tmpCircleId%>}"
                           data--marmot="{page_id:'click_feelingicon',feeling_id:'0',video_id: '<%$row._id%>'}"></a>
                        <a href="###" class="i-mood i-happy i-mood-tips marmot" title="大笑" data-action="{id:'<%$row._id%>',mood:'dx', circle:<%$tmpCircleId%>}"
                           data--marmot="{page_id:'click_feelingicon',feeling_id:'1',video_id: '<%$row._id%>'}"></a>
                        <a href="###" class="i-mood i-nobody i-mood-tips marmot" title="围观" data-action="{id:'<%$row._id%>',mood:'wg', circle:<%$tmpCircleId%>}"
                           data--marmot="{page_id:'click_feelingicon',feeling_id:'2',video_id: '<%$row._id%>'}"></a>
                        <a href="###" class="i-mood i-angry i-mood-tips marmot" title="鄙视" data-action="{id:'<%$row._id%>',mood:'fn', circle:<%$tmpCircleId%>}"
                           data--marmot="{page_id:'click_feelingicon',feeling_id:'3',video_id: '<%$row._id%>'}"></a>
                        <a href="###" class="i-mood i-jiong i-mood-tips marmot" title="囧" data-action="{id:'<%$row._id%>',mood:'jn', circle:<%$tmpCircleId%>}"
                           data--marmot="{page_id:'click_feelingicon',feeling_id:'4',video_id: '<%$row._id%>'}"></a>
                    </span>
                    <div class="put"><img class="h" src="javascript:void(0)">
                        <textarea placeholder="添加评论" name="comment_text" autocomplete="off" rows="1" class="text comment_text xat_control_"></textarea>
                        <span class="post" data-action="{id: '<%$row._id%>', circle : '<%$tmpCircleId%>'}">确定</span></div>
                </div>
	<%/strip%>
<%/function%>