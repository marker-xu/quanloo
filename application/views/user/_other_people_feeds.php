<%extends file="common/base_user.tpl"%>
<%block name="seo_meta"%>
<%$user_nick_escape=HTML::chars($user_info.nick)%>
    <meta name="keywords" content="<%$user_nick_escape%>,动态" />
    <meta name="description" content="<%$user_nick_escape%>的动态" />
    <link rel="canonical" href="<%Util::userUrl($user_info._id)%>" />
<%/block%>

<%block name="title" prepend%><%$user_nick_escape%>的动态<%/block%>

<%block name="custom_js"%>
<script type="text/javascript">
    PARAM_LASTTIME = <%$feeds_lasttime%>;
    PARAM_COUNT = <%$feeds_page_count%>;   
    PARAM_SUBTYPE = <%Model_Logic_Feed2::SUBTYPE_SELF%>; 
    PARAM_FORWARD_TEXT_MAX_LEN = <%$forward_text_max_len%>; 
</script>
<%/block%>

<%block name="user_nav"%>
<%include file="inc/user_nav.inc" pagetype=0%>
<%/block%>

<%block name="article"%>
            <!-- 动态 -->
			<div id="feedslist">
				<div class="inner">
					<div class="list" id="feed_list_">
                    <%include file="user/_feeds_list.php" inline%>
                    <%if empty($feeds.data)%>
                        <%if $is_admin%>
                        <div class="init-status nodata_">
							 <div class="inner_" style="position:relative;">
			                	  你最近没有新的动态，赶快去发现有趣的<a href="/category/all">圈子</a>或<a href="/">视频</a>吧
			                	<a class="map" href="/guesslike"></a>
			                </div>
						</div>
                       
                        <%else%>
                        	<div style="text-align:center;">Ta最近没有新的动态</div>
                        <%/if%>
                    <%/if%>
					</div>
				</div>
				<!-- 加载 -->
				<div id="loader" style="display:none"><div class="s-ic"><em></em></div></div>
				<!-- /加载 -->
				<%if $feeds.has_more%>
				<!-- 加载更多 -->
				<div class="bt-load-more"><a href="#" class="loading_more_info_">加载更多</a></div>
				<!-- /加载更多 -->
				<%/if%>
			</div>
			<!-- /动态 -->
<%/block%>

<%block name="foot_js"%>
<%/block%>
<%block name="custom_foot"%>    
<script type="text/marmot">
{
    "page_id"   : "userfeeds"
}
</script>
<%/block%> 
