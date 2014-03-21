<%extends file="common/share_panel2.tpl"%>
<%block name="title"%>分享视频<%/block%>
<%block name="content"%>
我在圈乐网看过【 <%$video_info.title|escape:"html"%>】，点击链接就可以观看： <%Util::videoPlayUrl($video_info._id)%>?fuid=<%$login_user._id%>，更多视频等你发现！
<%/block%>
<%block name="picurl"%>
<%Util::videoThumbnailUrl($video_info.thumbnail)%>
<%/block%>