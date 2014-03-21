<%*
$comments: array(total => 记录总数, data =>当前页视频信息列表)
*%>
<%foreach $comments.data as $v%>
<li class="wp cls">
<%if $v.user_id%>
    <a href="<%Util::userUrl($v.user_id)%>" target="_blank" class="thumb"><img src="<%$v.avatar%>" class="img"></a>
<%else%>
    <span class="thumb"><img src="<%$v.avatar%>" class="img"></span>
<%/if%>
    <dl>
        <dt><%Util::formatUserLinkText($v.data, $v.users)%></dt>
        <dd><%$v.create_time_str%><%if $is_admin_user%> <a href="###" data-action="<%$v._id%>" class="comment-del">删除</a><%/if%></dd>
    </dl>
</li>
<%/foreach%>
