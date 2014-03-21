<%extends file="common/base_user.tpl"%>
<%block name="seo_meta"%>
<%$user_nick_escape=HTML::chars($user_info.nick)%>
    <meta name="keywords" content="<%$user_nick_escape%>,<%$user_nick_escape%>关注的圈子,<%$user_nick_escape%>分享的圈子,<%$user_nick_escape%>评论的视频,<%$user_nick_escape%>标注心情的视频" />
    <meta name="description" content="<%$user_nick_escape%>在圈乐关注和分享的视频圈子，以及评论和标注过心情的视频信息汇总。">
<%/block%>

<%block name="title" prepend%><%$user_nick_escape%>关注和分享的精彩视频和视频圈子信息<%/block%>

<%block name="custom_js"%>
<script type="text/javascript">
    PARAM_LASTTIME = <%$feeds_lasttime%>;
    PARAM_COUNT = <%$feeds_page_count%>;    
    PARAM_SUBTYPE = "<%$cur_subtype%>";
    PARAM_FORWARD_TEXT_MAX_LEN = <%$forward_text_max_len%>;
</script>
<%/block%>

<%block name="user_nav"%>
<%include file="inc/user_nav.inc" pagetype=0%>
<%/block%>

<%block name="article"%>
            <!-- 动态 -->
			<div id="feedslist">
		        <div class="feed-nav">
					<ul class="clearfix">
						<li<%if $cur_subtype == 0%> class="on"<%/if%>><a href="<%Util::userUrl($user_info._id)%>">全部</a></li>
						<%foreach $subtype_list as $k => $v%>
						    <%$tmp=null%>
						    <%if $cur_subtype == $k%>
						        <%$tmp=" on"%>
						    <%/if%>
						    <%if $v@last%>
						        <%$tmp="last$tmp"%>
						    <%/if%>
						<li<%if $tmp !== null%> class="<%$tmp%>"<%/if%>><a href="<%Util::userUrl($user_info._id, 'feeds', ['type' => $k])%>"><%$v|escape:"html"%></a></li>
						<%/foreach%>
					</ul>
				</div>
				<div class="inner">
					<div class="updatetips" id="new_info_" style="display:none;" data-url="<%Util::userUrl($user_info._id)%>"><a href="#">有3个更新，点击查看</a></div>
					<div class="list" id="feed_list_">
                    <%include file="user/_feeds_list.php" inline%>
                    <%if empty($feeds.data)%>
                    <div class="init-status nodata_">
						 <div class="inner_" style="position:relative;">
						 <%if $cur_subtype == Model_Logic_Feed2::SUBTYPE_MEMTION_ME%>						 
		                	 没有任何@消息提到你
		                <%else%>
		                	 你最近没有收到新的动态，赶快去发现和关注有趣的<a href="/category/all">圈子</a>吧
		                	<a class="map" href="/guesslike"></a>		                
		                <%/if%>
		                </div>
					</div>
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
