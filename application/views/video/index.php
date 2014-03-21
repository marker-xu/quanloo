<%extends file="common/base.tpl"%>

<%block name="seo_meta"%><%$video_title_escape=HTML::chars($video.title)%>
<meta name="keywords" content="<%$video_title_escape%>,视频,在线观看,视频类Pinterest" />
<meta name="description" content="在线观看<%$video_title_escape%>视频和其他相关视频，发表关于<%$video_title_escape%>的评论或和好友分享你的观看心情。">
<link rel="canonical" href="<%Util::videoPlayUrl($video._id)%>" />
<%/block%>

<%block name="title" prepend%><%$video_title_escape%>-视频-在线观看<%/block%>

<%block name="custom_css"%>
<link href="<%#resUrl#%>/css/video/play_v5.css?v=<%#v#%>" type="text/css" rel="stylesheet" />
<%/block%> 

<%block name="custom_js"%>
<script type="text/javascript" src="<%#resUrl#%>/js/video/player_v5.js?v=<%#v#%>"></script>

<script>
    PAGE_TYPE = "play";
    VIDEO_ID = "<%$video._id%>" || '';
    CIRCLE_ID = "<%$smarty.get.circle|escape:'javascript'%>"||"";
    VIDEO_THUMB = "<%Util::videoThumbnailUrl($video.thumbnail)%>";
    REFERER = "<%$smarty.server.HTTP_REFERER|escape:'javascript'%>";
    (function(){
        var flashplay_param = {
            src:"<%$video.player_url|escape:'javascript'%>"
        };
        QW.provide('flashplay_param', flashplay_param);
    })();
</script>
<%/block%>

<%block name="hd"%>
<%include file="inc/play_header2.inc"%>
<%/block%>

<%block name="bd"%>
<div id="local-data" data-jss="res:'<%$video.domain|escape:'html'%>',vid:'<%$video._id%>'"></div>
<div id="bd">
	<!--主体容器begin-->
	<div id="container">
	        <!--视频begin-->
	        <div id="videoWrap" class="lt_play lt_player-narr" style="width:<%if $smarty.cookies.isMin==0%>660<%else%>960<%/if%>px;">
			    <div class="box-inner">
				    <!--位置导航begin-->
					<h2 class="pos-nav">
						<a href="/">首页</a><span class="sp-stand">&gt;</span>
						<%if ! empty($circle)%><a href="<%Util::circleUrl($circle._id, null, $circle)%>"><%Util::utf8SubStr($circle.title, 30)%>圈</a><span class="sp-stand">&gt;</span>
						<%/if%>
						<strong class="video-title"><%$video_title_escape%></strong>
					</h2>
					<!--位置导航end-->
					<!--圈子分享,微博分享begin-->
					<div class="share-bar j_align-reference cls">
					    <div class="b_play_fn">
							<a href="javascript:void(0)" video-id="<%$video._id%>" class="b_link b_add-circle j_add-circle circle_down marmot" data--marmot="{page_id:'click_viewvideo_quan'}" hidefocus>
								<em class="ico"></em>
								圈一下
							</a>
						</div>
						<div class="r share-to">
						    <div class="tip">
							    <em class="ico"></em>
							    分享到
							</div>
							<div class="sites wrap">
							    <div class="inner cls">
								    <ul class="list">
									    <li class="item"><a class="video_share_" data-sns="{id:'<%$video._id%>', cid:'<%$circle._id%>', title:'<%$video_title_escape%>',image:'<%Util::videoThumbnailUrl($video.thumbnail)%>'}" href="javascript:void(0)"><span><em class="ico ico-l-sina"></em></span></a></li>
									    <li class="item"><a class="video_share_" data-sns="{id:'<%$video._id%>', cid:'<%$circle._id%>', title:'<%$video_title_escape%>',image:'<%Util::videoThumbnailUrl($video.thumbnail)%>'}" href="javascript:void(0)"><span><em class="ico ico-l-tt"></em></span></a></li>
									    <li class="item" ><a class="video_share_" data-sns="{id:'<%$video._id%>', cid:'<%$circle._id%>', title:'<%$video_title_escape%>',image:'<%Util::videoThumbnailUrl($video.thumbnail)%>'}" href="javascript:void(0)"><span><em class="ico ico-l-renren"></em></span></a></li>
									    <li class="item"><a href="javascript:void(0)"><span><em class="ico ico-l-tianya"></em></span></a></li>
									</ul>
								</div>
								<a class="collapse" style="display:none;" href="javascript:void(0)">
								    <em class="ico-tri"></em>
								</a>
							</div>
						</div>
					</div>
					<!--圈子分享,微博分享end-->
					<!--播放器begin-->
					<div id="player" class="player">
						<div class="player-hand js_player-hand">
						<%if $video.is_html5%>
        						<%include file="video/_index_html5_player.php" inline%>
        					<%else%>
        						<script type="text/javascript">
        			        		(function(){
        			        		    function FLASH(u,w,h,i,wm,v){
        				        			var g="";
        				        			if(window.ActiveXObject){
        				            			g='<OBJECT classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0" id="'+i+'" name="'+i+'" border="0" width="'+w+'" height="'+h+'"><param name="movie" value="'+u+'" /><param name="quality" value="high" /><param name="allowScriptAccess" value="always" /><param name="menu" value="false" /><param name="allowFullScreen" value="true" /><param name="wmode" value="'+wm+'" /><param name="flashvars" value="'+v+'" /><param name="BGColor" value="#000000" /></OBJECT>';
        				            		}else{
        				                		g='<embed name="'+i+'" width="'+w+'" height="'+h+'" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" allowScriptAccess="always" allowFullScreen="true" quality="high" BGColor="#000000" menu="false" wmode="'+wm+'" flashvars="'+v+'" src="'+u+'"></embed>';
        				                	}
        				                	return g;
        			        		    }
        			        		    var flashStr = FLASH(flashplay_param.src,'100%','100%',"videoPlayer","Opaque","autoPlay=true&isAutoPlay=true&auto=1&playMovie=true&adss=0&api=1");
        			        		    document.write(flashStr);
        			        		})();
                				</script>
        					<%/if%>
        				</div>
					</div>
					<!--播放器end-->
					<!--视频播放功能按钮条begin-->
					<div class="play-fn-bar cls">
					    <div class="l l-group">
							<div class="b_play_fn">
									<a href="javascript:void(0)" class="b_link b_screen-min j_toggle-screen" hidefocus>
										<em class="ico"></em>
										<%if $smarty.cookies.isMin==0%>宽屏观看<%else%>普屏观看<%/if%>
									</a>
							</div>
							<div class="b_play_fn">
									<a href="javascript:void(0)" class="b_link b_justify j_justify" hidefocus>
										<em class="ico"></em>
										对齐观看
									</a>
							</div>
							<div class="b_play_fn" style="display:none;">
							    <a href="javascript:void(0)" class="b_link b_play-win j_play-win" hidefocus>
								    <em class="ico"></em>
									弹出播放
								</a>
							</div>
						</div>	
						<div class="r r-group">
						    <div class="b_play_fn">
							    <a href="javascript:void(0)" class="b_link b_change-skin j_change-skin" hidefocus>
								    <em class="ico"></em>
									换场景
								</a>
							</div>
							<div class="b_play_fn j_light">
							    <a href="javascript:void(0)" class="b_link b_light-up j_sec-light" hidefocus>
								    <em class="ico"></em>
									关灯
								</a>
							</div>
						</div>
					</div>
					<!--视频播放功能按钮条end-->
					<!--视频来源及更多搜索begin-->
					<div class="video-tips">
					    <span class="v-coming">
						    来自：<a href="<%$video.play_url|escape:'html'%>" target="_blank"><%$video.domain|escape:'html'%><em class="ico ico-outlink"></em></a>
						</span>
						<%if $search_word%>
						<span class="v-more-se">
						    搜索更多&nbsp;&nbsp;<a href="/search?from=player&q=<%urlencode($search_word)%>" target="_blank" title="<%$search_word|escape:"html"%>"><%Util::utf8SubStr($search_word, 60)%></a>
						</span>
						<%/if%>
					</div>
					<!--视频来源及更多搜索end-->
				</div>	
	        </div>
			<!--视频播放器end-->
			<!--视频相关信息begin-->
			<div id="videoInfo" class="lt_play-out cls">
			    <!--用户交互区[心情,短评,评论]begin-->
				<div id="videoAction" class="l lt_action v-action">
				        <!--心情模块-->
						<div class="cla mood m_action">
						    <h3 class="tit-bar">看完视频您的心情是<a id="j_select-mood" href="javascript:void(0)" class="face-default"><em class="ico-face"></em></a>
						    	<span class="f_tips j_mood-tips" style="margin-left:39px;"></span>
						    </h3>
						    <div style="height:102px;overflow: hidden;" class="j_mood-face-wrap">
								<div class="wrap mood-face j_mood-face-place">
								    <!--js动态生成部分-->
								</div>
							</div>
							<div class="wrap mood-user j_mood-user-place">
                                <!--js动态生成部分-->
							</div>
						</div>
						<!--短评墙-->
						<div class="s-view m_action">
						    <h3 class="tit-bar">共有<strong class="j_tag_total">0</strong>人发表了观点 <span class="f_tips j_tag-tips"></span></h3>
							<div class="view-wall">
							    <div class="inner">
								    <ul class="cls list view-list j_init-wall">
								    	<!--js动态生成部分-->
									</ul>
								</div>
								<div class="form">
								    <div class="inp-area j_postag-place" style="display:none;"><input type="text" class="j_wall-input inp-txt" maxlength="40" />
								    <input type="button" class="inp-btn j_post-tag" value="提交" /></div>
									<div class="inp-tip j_wall-num"><strong class="cur-num">0</strong>/15</div>
								</div>
								<!--当短评墙超过排数时有更多按钮-->
								<div class="bar" style="display:none;">
								    <a class="more" href="">更多</a>
								</div>
							</div>
						</div>
						<!--评论模块-->
						<div class="comment m_action">
						    <h3 class="tit-bar">发表你的评论（<strong class="j_comment-total"><%$comments.total%></strong>）<span class="f_tips j_comms-tips"></span></h3>
						    <div class="comment-form">
							    <div class="face-box">
								    <img class="face" src="<%Util::userAvatarUrl($login_user.avatar.48, 48)%>"/>
								</div>
								<div class="inp-area">
								    <textarea class="j_comment-input xat_control_ comment-inp" cols="" rows=""></textarea>
								</div>
								<a class="sub-comment j_sub_comment" href="javascript:void(0)">
								提交
								</a>
							</div>
							<div class="j_remNum char-tip">
							    还可以输入<span class="num">200</span>个字符
							</div>
							<div class="comment-box">
							    <div class="tab-menu cls" style="display:none">
								    <ul>
									    <li class="selected">
										    最新评论 
										</li>
                                        <li>
										    热门评论 
										</li>										
									</ul>
								</div>
								<div class="tab-con-wrap">
								    <div class="tab-con" style="display:block;">
									    <div class="m_comment">
										    <p class="push-tip" style="display:none;">
											    <a href="#">有新的评论，点击查看</a> 
											</p>
											<ul class="list" id="comment_list">
											<%if empty($comments.data)%>
												<li class='no_comment' id="no_comment">暂无评论</li>
											<%else%>
												<%include file="video/comments2.php" inline%>
											<%/if%>
											</ul>
										</div>
									</div>
									<div class="tab-con">
									</div>
								</div>
							</div>
						</div>
				</div>
				<!--用户交互区end-->
				<!--视频相关推荐区begin-->
				<div id="recArea" class="r lt_recommend v-rec">
					<div id="rightArea" class="box-inner" style="margin-top:<%if $smarty.cookies.isMin==0%>-628<%else%>0<%/if%>px;">
						<%if ! empty($ad_right_top_1)%>
						<!--右侧广告-->
						<div id="ad-vp-1" class="m_recommend">
							<a href="<%$ad_right_top_1.ad_mat.ad_url%>" data-lks="from=ad" target="_blank"><img class="v-ad" src="<%Util::webStorageClusterFileUrl($ad_right_top_1.ad_mat.ad_pic)%>" /></a>
						</div>
						<%/if%>
						<%$tmp_related_video=$left_playlist.0%>
						<%if ! empty($tmp_related_video)%>
						<!--相关视频-->
						<div id="relaVideos" class="m_recommend">
							<h3 class="tit-bar"><%$tmp_related_video.name|escape:"html"%></h3>
							<div class="wrap">
							    <div class="inner">
									<ul class="list cls">
										<%$tmp_related_video.videos.data=array_slice($tmp_related_video.videos.data, 0, 6)%>
										<%include file="video/playlist_inner.php" videos=$tmp_related_video.videos playlist=null playUrlParam=null rec_zone=$tmp_related_video.rec_zone%>
									</ul>
								</div>	
							</div>
						</div>
						<%/if%>
						<%*
						<!--推荐圈子-->
						<div id="recCircles" class="m_recommend rec-circles">
							<h3 class="tit-bar">圈子推荐</h3>
							<div class="wrap cls">
								<div class="l circle-pics">
									<a href=""><img src="<%#resUrl#%>/css/video/images/circle-thumb.jpg" alt="" title="" /></a>
								</div>
								<div class="r circle-info">
									<h4 class="name"><a href="" target="_blank">轩辕剑之天痕</a><em class="ico-vali"></em></h4>
									<p class="info">
										<em class="">圈主：</em><a href="" target="_blank"></a><br/>
										<em class="">粉丝数：</em>2322<br/>
										<em class="">视频数：</em>102<br/>
									</p>
								</div>
							</div>
						</div>*%>
						<%if ! empty($people_watched_video)%>
						<!--大家都在看-->
						<div id="popVideos" class="m_recommend pop-videos">
							<h3 class="tit-bar">大家都在看</h3>
							<div class="wrap">
							    <div class="inner">
									<ul class="list cls">
									<%foreach $people_watched_video as $v%>
										<li class="item m_v-item marmot" data--marmot="{page_id:'click_recommendation',item_id:'<%$v._id|escape:"javascript"%>',item_pos:'<%$v@index+1%>',rec_zone:'viewvideo_righthot_rec',item_list:''}">
											<a href="<%Util::videoPlayUrl($v._id)%>" title="<%$v.title|escape:'html'%>">
												<img class="thumb" src="<%Util::videoThumbnailUrl($v.thumbnail)%>" alt="<%$v.title|escape:'html'%>" />
											</a>
											<p class="title">
											   <a href="<%Util::videoPlayUrl($v._id)%>" title="<%$v.title|escape:'html'%>"><%Util::utf8SubStr($v.title, 200)%></a>
											</p>
										</li>
									<%/foreach%>
									</ul>
								</div>	
							</div>
						</div>
						<%/if%>
					</div>					
				</div>
				<!--视频相关推荐区end-->
			</div>
			<!--视频相关信息end-->
	</div>
	<!--主体容器end-->
</div>
<%/block%>

<%block name="foot_js"%>
<%if $is_admin_user%>
<script type="text/javascript">
Dom.ready(function() {	
    W('#comment_list .comment-del').on('click', function(e) {
    	e.preventDefault();
    	e.stopPropagation();
        QW.Ajax.post(
            '/video/delComment', 
            {"id": W(this).attr('data-action')},
            function( $d ) {
                $d = $d || {};                                            
                if( $d.err == 'ok' ) {
                    alert("删除成功");
                } else {
                	var msg = getMessage($d.msg);
                    alert( msg || '失败，请重试!' );  
                }
            }, {
                onerror:
                function()
                {
                    alert('网络连接中断，请稍后重试!');
                }
            }
        );
    	return false;
    });	
});
</script>
<%/if%>
<%/block%>

<%block name="custom_foot"%>
<script type="text/marmot">
{
    "feedid"   : "<%$smarty.get.feedid|escape:"html"|default:0%>",
    "fuid":"<%$smarty.get.fuid|escape:"html"|default:0%>",
    "stype":"<%$smarty.get.stype|escape:"html"|default:i%>",
	"page_id":"viewvideo",
	"video_id":"<%$video._id%>",
	<%if ! empty($ad_right_top_1)%>"ad_show":1,<%/if%>
	"video_time":"<%$video.length|escape:"html"|default:-1%>",
	"out_url":"<%$video.play_url|escape:"url"%>"
}
</script>
<script type="text/javascript">
Dom.ready(function() {
    QW.Marmot.log = QW.Marmot.log || function(){};    
    <%foreach $left_playlist as $v%>
        <%$item_list=null%>
        <%foreach $v.videos.data as $v2%>
            <%$item_list[]=$v2._id%>
        <%/foreach%>
    QW.Marmot.log({
    	page_id: "recommendation",
        rec_zone: "<%$v.rec_zone%>",
        item_list: "<%join(',', $item_list)%>"
    });
    <%/foreach%>

	<%$item_list=null%>
	<%foreach $people_watched_video as $v2%>
    	<%$item_list[]=$v2._id%>
	<%/foreach%>
	QW.Marmot.log({
		page_id: "recommendation",
	    rec_zone: "viewvideo_righthot_rec",
	    item_list: "<%join(',', $item_list)%>"
	});	
});
</script>
<%/block%>
