<%extends file="common/share_panel.tpl"%>

<%block name="textarea"%>
这里有一个很好玩的圈子, <%$circle_info.title|escape:"html"%> <%Util::circleUrl($circle_info._id, null, $circle_info)%>?fuid=<%if $login_user%><%$login_user._id%><%else%>0<%/if%>&stype=o 快来一起玩
<%/block%>

<%block name="url"%>
    <%Util::circleUrl($circle_info._id, null, $circle_info)%>
<%/block%>
