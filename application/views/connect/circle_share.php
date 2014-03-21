<%extends file="common/share_panel2.tpl"%>
<%block name="title"%>分享圈子<%/block%>
<%block name="content"%>
我在圈乐网发现了一个有意思的视频圈子—— <%$circle_info.title|escape:"html"%>，点击链接一起来看看吧：<%Util::circleUrl($circle_info._id, null, $circle_info)%>?fuid=<%$login_user._id%>
<%/block%>
<%block name="picurl"%>
<%Util::circlePreviewPic($circle_info.tn_path)%>
<%/block%>