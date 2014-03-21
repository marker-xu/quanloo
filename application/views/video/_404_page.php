<%extends file="video/index.php"%>

<%block name="seo_meta"%>
<meta name="keywords" content="视频,在线观看,视频类Pinterest" />
<meta name="description" content="在线观看视频，发表评论或和好友分享你的观看心情。">
<link rel="canonical" href="<%Util::videoPlayUrl($video._id)%>" />
<%/block%>

<%block name="title"%>圈乐为你推荐更多精彩视频-在线观看<%/block%>

<%block name="bd"%>
<div id="local-data" data-jss="res:'<%$video.domain|escape:'html'%>',vid:'<%$video._id%>'"></div>
<div id="bd">
	<!--主体容器begin-->
	<div id="container">
	        <!--视频begin-->
	        <div id="videoWrap" class="lt_play lt_player-narr" style="width:660px;">
			    <div class="box-inner" style="padding-bottom: 25px;">
				    <!--位置导航begin-->
					<h2 class="pos-nav">
						<a href="">首页</a><span class="sp-stand">&gt;</span>
						<strong class="video-title">视频不见了，看看圈乐为你推荐的更多精彩内容吧</strong>
					</h2>
					<!--位置导航end--
>					<!--播放器begin-->
					<div id="player" class="player">
						<div class="player-hand js_player-hand">
		                    <div class="video-recomm clearfix">
		                        <div class="logo"></div>
		                        <div class="recomm-vodeo">
		                            <h3>推荐视频</h3>
		                            <div class="v-list">
		                                <ul id="end_list_" class="end_list_" data-rec-zome="<%$rec_videos.rec_zone%>" style="height:330px;overflow:hidden;">                   
		                                <%foreach $rec_videos.video as $v%>
		                                    <li class="v-item" data-id="<%$v._id%>">
		                                       <a class="clearfix marmot" href="<%Util::videoPlayUrl($v._id)%>" title="<%$v.title|escape:'html'%>" data--marmot="{'page_id': 'click_recommendation', 'item_id': '<%$v._id%>', 'item_pos': '<%$v@index+1%>', 'rec_zone': '<%$rec_videos.rec_zone%>', 'item_list': ''}" target="_blank">
		                                            <div class="v-pic"><img src="<%Util::videoThumbnailUrl($v.thumbnail)%>" alt="<%$v.title|escape:'html'%>"></div>
		                                            <div class="v-info">
		                                                <p class="title"><%Util::utf8SubStr($v.title, 40)%></p>
		                                                <%*<p class="circle">某圈子</p>*%>
		                                                <p class="num">
		                                                    <span class="played"><%$v.watched_count|number_format%></span>
		                                                    <span class="mood"><%$v.mooded_count.total|number_format%></span>
		                                                </p>
		                                            </div>
		                                        </a>
		                                    </li>
		                                <%/foreach%>
		                                </ul>                               
		                            </div>
		                            <div class="ft-btn clearfix">
		                                <a href="#" class="bt-pre pre_page_" id="end_list_pre"></a>
		                                <a href="#" class="bt-next next_page_" id="end_list_next"></a>
		                            </div>
		                        </div>                       
		                        <div class="curr-video">
		                        <%if $search_word%>
								    <div class="v-se">   
											<p class="title"> 
												<a href="javascript:void(0)" hidefocus>	
													<a href="/search?from=player&q=<%urlencode($search_word)%>" target="_blank" title="<%$search_word|escape:"html"%>"><em class="ico"></em>更多&nbsp;&nbsp;<%$search_word|escape:"html"%></a>
												</a>
											</p>
								    </div>
								<%/if%>
		                            <div class="no_video"></div>
	                            	<div class="no_video_text">该视频已被删除，推荐观看相关视频。</div> 
		                        </div>                        
		                    </div>
        				</div>
					</div>
					<!--播放器end-->
				</div>	
	        </div>
			<!--视频播放器end-->
			<!--视频相关信息begin-->
			<div id="videoInfo" class="lt_play-out cls">
			    <!--用户交互区[心情,短评,评论]begin-->
				<div id="videoAction" class="l lt_action v-action" style="padding: 0;width: 660px;">
				<%if ! empty($rand_reco_video[Model_Data_Recommend::HOMEPAGE_REC_HOT])%>
					<div class="more_video clearfix" id="more_hot_video">
						<div class="label clearfix">
							<span class="sleft">最热视频</span> <span class="sright"><a href="/?type=2">更多&gt;&gt;</a></span>
						</div>
						<ul class="slist clearfix">
						<%$tmp_rec_zone=$rand_reco_video[Model_Data_Recommend::HOMEPAGE_REC_HOT].rec_zone%>
			            <%foreach $rand_reco_video[Model_Data_Recommend::HOMEPAGE_REC_HOT].video as $v%>
			                <li class="marmot" data--marmot="{page_id:'click_recommendation',item_id:'<%$v._id|escape:"javascript"%>',item_pos:'<%$v@index%>',rec_zone:'<%$tmp_rec_zone%>',item_list:''}"><dl><dd>
			                    <a href="<%Util::videoPlayUrl($v._id)%>" title="<%$v.title|escape:'html'%>" target="_blank">
			                        <img src="<%Util::videoThumbnailUrl($v.thumbnail)%>" alt="<%$v.title|escape:'html'%>">
			                    </a></dd>
			                    <dt><a href="<%Util::videoPlayUrl($v._id)%>" title="<%$v.title|escape:'html'%>" target="_blank">
			                        <%Util::utf8SubStr($v.title, 200)%>
			                    </a></dt>
			                </dl></li>
			            <%/foreach%>
						</ul>
					</div>
				<%/if%>
				<%if ! empty($rand_reco_video[Model_Data_Recommend::HOMEPAGE_REC_NEW])%>
					<div class="more_video clearfix" id="more_new_video">
						<div class="label clearfix">
							<span class="sleft">最新视频</span> <span class="sright"><a href="/?type=1">更多&gt;&gt;</a></span>
						</div>
						<ul class="slist clearfix">
						<%$tmp_rec_zone=$rand_reco_video[Model_Data_Recommend::HOMEPAGE_REC_NEW].rec_zone%>
			            <%foreach $rand_reco_video[Model_Data_Recommend::HOMEPAGE_REC_NEW].video as $v%>
			                <li class="marmot" data--marmot="{page_id:'click_recommendation',item_id:'<%$v._id|escape:"javascript"%>',item_pos:'<%$v@index%>',rec_zone:'<%$tmp_rec_zone%>',item_list:''}"><dl><dd>
			                    <a href="<%Util::videoPlayUrl($v._id)%>" title="<%$v.title|escape:'html'%>" target="_blank">
			                        <img src="<%Util::videoThumbnailUrl($v.thumbnail)%>" alt="<%$v.title|escape:'html'%>">
			                    </a></dd>
			                    <dt><a href="<%Util::videoPlayUrl($v._id)%>" title="<%$v.title|escape:'html'%>" target="_blank">
			                        <%Util::utf8SubStr($v.title, 200)%>
			                    </a></dt>
			                </dl></li>
			            <%/foreach%>
						</ul>
					</div>
				<%/if%>
				</div>
				<!--用户交互区end-->
				<!--视频相关推荐区begin-->
				<div id="recArea" class="r lt_recommend v-rec">
					<div id="rightArea" class="box-inner" style="margin-top:-552px;">
						<%if ! empty($ad_right_top_1)%>
						<!--右侧广告-->
						<div id="ad-vp-1" class="m_recommend">
							<a href="<%$ad_right_top_1.ad_mat.ad_url%>" data-lks="from=ad" target="_blank"><img class="v-ad" src="<%Util::webStorageClusterFileUrl($ad_right_top_1.ad_mat.ad_pic)%>" /></a>
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
											<a href="<%Util::videoPlayUrl($v._id)%>" title="<%$v.title|escape:'html'%>" target="_blank">
												<img class="thumb" src="<%Util::videoThumbnailUrl($v.thumbnail)%>" alt="<%$v.title|escape:'html'%>" />
											</a>
											<p class="title">
											   <a href="<%Util::videoPlayUrl($v._id)%>" title="<%$v.title|escape:'html'%>" target="_blank"><%Util::utf8SubStr($v.title, 200)%></a>
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

<%block name="custom_foot"%>
<script type="text/marmot">
{
    "feedid"   : "<%$smarty.get.feedid|escape:"html"|default:0%>",
    "fuid":"<%$smarty.get.fuid|escape:"html"|default:0%>",
    "stype":"<%$smarty.get.stype|escape:"html"|default:i%>",
	"page_id":"viewvideo",
	"video_id":"<%$video._id%>",
	"video_time":"<%$video.length|escape:"html"|default:-1%>",
	<%if ! empty($ad_right_top_1)%>"ad_show":1,<%/if%>
	"page_not_found":1,
	"out_url":"<%$video.play_url|escape:"url"%>"
}
</script>
<script type="text/javascript">
Dom.ready(function() {   
    <%foreach $rand_reco_video as $k => $v%>
    	<%$item_list=null%>
    	<%foreach $v.video as $v2%>
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

	//播放器内视频的事件
    (function init_event() {
        var end_list_ = W('#end_list_');
        if( !end_list_.length ) return;
        
        var pre_page_ = W('a.pre_page_');
        var next_page_ = W('a.next_page_');
        
        if( !(pre_page_.length || next_page_.length ) ) return;
    
        var items = end_list_.query('> li');
        var list_len = items.length;
        var page_len = 3;
        
        var ids = [];
        
        items.forEach
        (
            function($ele)
            {
                if( W($ele).attr('data-id') )
                {
                    ids.push( W($ele).attr('data-id') );
                }
            }
        );
            
        QW.Marmot.log({
            "page_id": "recommendation"
            , "rec_zone": end_list_.attr('data-rec-zome') 
            , "item_list": ids.join()
            //, "user_id": window.UID || ''
        });
        
        if( list_len <= page_len ) 
        {
            pre_page_.hide();
            next_page_.hide();
            return
        }
        
        /**
         * 最受欢迎 - 垂直划动
         */         
        XItemSlider.vexec
        (
            {
                prev: 'end_list_pre',
                next: 'end_list_next',
                list: 'end_list_',
                
                "fixPosition": true,
                "itemHeight": 100,
                "mainHeight": 330
            }
        );
        
    })();
});
</script>
<%/block%>
