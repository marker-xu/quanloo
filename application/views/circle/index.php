<%extends file="common/base.tpl"%>
<%block name="seo_meta"%>
<%$circle_title_escape=HTML::chars($circle.title)%>
<%$circle_creator_nick_escape=HTML::chars($circle.user.nick)%>
<%if $cur_tag != ''%>
<%$cur_select_tag_escape=HTML::chars($cur_tag)%>
<meta name="keywords" content="<%$cur_select_tag_escape%>,<%$cur_select_tag_escape%>视频,<%$circle_title_escape%>,<%$circle_title_escape%>视频" />
<meta name="description" content="最全、最新、最热的<%$cur_select_tag_escape%>视频，就在圈乐<%$circle_title_escape%>视频圈子。" />
<%elseif ! $circle.official%>
<meta name="keywords" content="<%$circle_title_escape%>,<%$circle_title_escape%>视频,<%$circle_creator_nick_escape%>" />
<meta name="description" content="<%$circle_creator_nick_escape%>创建的<%$circle_title_escape%>视频圈子汇集<%$circle_creator_nick_escape%>最喜欢的<%$circle_title_escape%>相关视频，在这里，你能和<%$circle_creator_nick_escape%>一起分享视频观看心得，并邀请好友关注<%$circle_title_escape%>视频圈子或者将<%$circle_title_escape%>视频分享给新浪、腾讯等微博好友。" />
<%else%>
<meta name="keywords" content="<%$circle_title_escape%>,<%$circle_title_escape%>视频" />
<meta name="description" content="视频圈子汇集最全、最新、最热的<%$circle_title_escape%>相关视频，在这里，你能和圈友一起分享视频观看心得，并邀请好友关注<%$circle_title_escape%>视频圈子或者将<%$circle_title_escape%>视频分享给新浪、腾讯等微博好友。" />
<%/if%>
<link rel="canonical" href="<%Util::circleUrl($circle._id, ['tag' => $cur_tag], $circle)%>" />
<%/block%>
<%block name="title" prepend%><%$circle_title_escape%><%$cur_select_tag_escape%>视频<%if ! $circle.official%>-<%$circle_creator_nick_escape%><%/if%><%/block%>

<%block name="custom_css"%>
<%Util::concatCss(['circle_user.css'], $smarty.config.v, 'video/')%>
<%Util::concatCss(['y-circle.css'], $smarty.config.v, 'video/')%>

<style type="text/css">
    .abs_user-guide{
        height: 84px;
        position: absolute;
        width: 258px;
        z-index: 999;
        display: none;
    }
    .abs_user-guide .wrap{
        background-image: url("<%#resUrl#%>/img/introduce/logo_pop.png")!important;
        background-image:url(www.quanloo.com);
        background-repeat:no-repeat;
        filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<%#resUrl#%>/img/introduce/logo_pop.png');
        width: 258px;
        height: 84px;
    }
    .abs_user-guide .close{
        cursor: pointer;
        display: block;
        height: 16px;
        left: 229px;
        overflow: hidden;
        position: absolute;
        text-indent: 999em;
        top: 53px;
        width: 16px;
    }
</style>
<%/block%>
<%block name="custom_js"%>
<%Util::concatJs(['video/mycircle.js'],$smarty.config.v)%>
<%Util::concatJs(['video/cycle_feeds.js'],$smarty.config.v)%>
<script>
	PARAM_LASTTIME = "<%$feeds_lasttime%>"||0;
	PARAM_COUNT = "<%$feeds_page_count%>" ||20;
	PARAM_FORWARD_TEXT_MAX_LEN = "<%$forward_text_max_len%>"||140;
    window.CIRCLE_ID = "<%$circle._id%>" || "0";
    var initData = <%json_encode($circle_video_list)%>;
    var isCurrentUser =  <%$circle.user._id%>  === parseFloat(UID);
    function getParam(name){//获取参数值 by司徒正美
        var sUrl = window.location.search.substr(1);
        var r = sUrl.match(new RegExp("(^|&)" + name + "=([^&]*)(&|$)"));
        return (r == null ? null : unescape(r[2]));
    }
    window.waterfallOpts = {
        requestUrl: "/circle/videos",
        initOpts: {
            target  : "#waterfall",
            col     : 4,
            colWidth: 228,
            imgExpr: ".brick_img"
        },
        requestOpts: {
            id:      "<%$circle._id%>",
            r:       ~~getParam("r") || 1,
            count:   12,
            offset:  ~~getParam("offset"),
            playlist: 'circle',
            tag: '<%$cur_tag|escape:"javascript"%>',
            title: '<%$circle.title|escape:"javascript"%>'
        },
        requestInBottom: true,
        requestCallback:function(){
            if(window.waterfallOpts.target.bricks.length >= 200){
                window.waterfallOpts.requestInBottom = false;
            }
            if(isCurrentUser){
                W(".circle_down").forEach(function(el){//变成删除
                  //  el.className = el.className.replace(/circle_down(\s|$)/,"circle_del");
                    el.className = el.className.replace(/circle_down(\s|$)/,"circle_edit");
                });
            }
            //外站来源出泡泡
            var isoutRes = !'<%$smarty.server.HTTP_REFERER%>'.match(/^[^.]+\.quanloo\.com/);
            if(!isoutRes || !!QW.Cookie.get('hasLogoPop'))return;
            var targetXY = W('#logo').getXY();
            W('body').insert('afterbegin', W('<div class="abs_user-guide"><div class="wrap"></div><span class="close">关闭</span></div>')[0])
            W('.abs_user-guide').css('top',40+targetXY[1]+"px").css('left',30+targetXY[0]+"px").show();
            W('.abs_user-guide .close').on('click',function(){
                W('.abs_user-guide').hide();
            });
            if(!QW.Cookie.get('hasLogoPop'))QW.Cookie.set('hasLogoPop',"1");

        }
    }
    //第一列
    var DefaultData = <%json_encode($mostPlayedVideos)%>;
</script>
<%/block%>
<%block name="bd"%>
<div id="web-js-config" style="display:none" data="{circleId:'<%$circle._id%>',circleFriend:'<%$circle.user_count%>',defaulType:'<%$mostPlayedVideosType%>'}"></div>
<div id="bd" style="position: relative;">
    <%*搜索框*%>
    <%include file="inc/search.inc"%>
    <%*//搜索框 *%>
    
    <%*广告位*%>
    <%include file="inc/top_ad.inc"%>
    <%*//广告位 *%>
    
    <%*标题栏*%>
    <div class="y-circle-info-box y-circle-box">
      <div class="r"> <a class="y-btn-gray invite_friend" data-action="{'id':'<%$circle._id%>', 'url': '<%Util::circleUrl($circle._id, null, $circle)%>'}" href="#"><span></span><cite>邀请好友</cite></a> <a class="y-btn-gray share_group" data-action="{'id':'<%$circle._id%>', 'url': '<%Util::circleUrl($circle._id, null, $circle)%>'}" href="#"><span></span><cite>分享Ta</cite></a> </div>
      <dl class="y-circle-title cfix y-inline-dl">
        <dt>
        	<img src="<%if $circle.logo%><%Util::webStorageClusterFileUrl($circle.logo)%><%else%><%#resUrl#%>/img/circle.png<%/if%>">
        </dt>
        <dd>
          <h1 class="y-title-20  y-circle-name"><%$circle.title|escape:'html'%></h1>
        </dd>
        <%if $circle.certified%>
        <dd><span class="y-ico y-ico-approve"></span></dd>
        <%/if%>
        <dd>
        	<%if $circle.creator == $login_user._id%>
            <a class="y-btn-gray y-btn-edit" href="/user/editcircle?cid=<%$circle._id%>&refer=<%urlencode(Util::circleUrl($circle._id, null, $circle))%>"><span></span><cite>编辑</cite></a>
            <%else%>
        	<a class="y-btn-gray y-btn-attention <%if $is_subscribed%>followed<%else%>b-follow<%/if%>" data-action="{id:'<%$circle._id%>'}" href="#"><span></span>  
                <cite class="text2">已关注</cite>
                <cite class="text0">加关注</cite>
                <cite class="text1">取消关注</cite>
            </a>
            <%/if%>
        </dd>
      </dl>
      <dl class="y-circle-tag cfix y-inline-dl">
        <dt class="y-font-999">所属分类：</dt>
        <dd>
        <%foreach $circle.category as $category%>
        <%if $category@index < 3%>
        <a href="/category/<%Model_Data_Circle::$arrUrlKeyForCategorys[$category]%>"><%Model_Data_Circle::$categorys[$category]%></a>
        <%/if%>
        <%/foreach%>
        </dd>
      </dl>
      <div class="y-circle-cfix">
        <ul class="y-circle-other cfix y-inline-ul y-inline-dl r">
          <%if $relatedCircles%>
              <li>Ta创建的其他圈子：</li>
              <%foreach $relatedCircles as $relatedCircle%>
              <li>
                <dl>
                  <dt class="y-ico y-ico-circle2"> </dt>
                  <dd><a href="<%Util::circleUrl($relatedCircle._id, null, $relatedCircle)%>" title="<%$relatedCircle.title|escape:'html'%>"><%Util::utf8SubStr($relatedCircle.title, 16)%></a></dd>
                </dl>
              </li>
              <%/foreach%>
          <%/if%>
          <%if $circle.creator == 1846037590%>
			<li><b><a href="/category/all">[更多圈子]</a></b> </li>
          <%else%>
            <%if $relatedCircles%>
            <li><b><a href="<%Util::userUrl($circle.creator, 'circle')%>">[更多圈子]</a></b> </li>
            <%/if%>
          <%/if%>
        </ul>
        <dl class="y-circle-user cfix y-inline-dl">
          <dt><img src="<%Util::userAvatarUrl($circle.user.avatar.30, 30)%>"></dt>
          <dd>
              <%if $circle.creator == 1846037590%>
              <%$circle.user.nick|escape:"html"%>
              <%else%>
              <a class="user" target="_blank" href="<%Util::userUrl($circle.creator)%>"><%$circle.user.nick|escape:"html"%></a>
              <%/if%>
              <span class="y-font-999">创建</span>
          </dd>
        </dl>
      </div>
    </div>
    <%*//标题栏 *%>
    <%*热播视频*%>
    <%if $mostPlayedVideos || $circleEntity%>

    <div class="y-circle-box y-circle-info-box2">
        <%if $circleEntity%>
        	<%if $mostPlayedVideos%>
            <%include file="circle/index/entity.php"%>
            <%else%>
            <%include file="circle/index/entity_width.php"%>
            <%/if%>
        <%/if%>
        <%if $mostPlayedVideos%>
      <div class="<%if $circleEntity%>y-hot-video-box<%else%>y-hot-video-box-width<%/if%>">
        <div class="y-box-title">
          <ul id="hot-video" class="y-tab cfix y-inline-ul r" data="{defaulType:'<%$mostPlayedVideosType%>',<%if $circleEntity%>displayNum:5,step:116<%else%>displayNum:7,step:124<%/if%>}">
          	<%if $mostPlayedVideosType=='day'%>
            <li data-sns="{type:'day'}" class="y-tab-left y-tab-left-select">今日热播</li>
            <li data-sns="{type:'week'}" class="y-tab-center">本周热播</li>
            <li data-sns="{type:'month'}" class="y-tab-right">本月热播</li>
          	<%elseif $mostPlayedVideosType=='week'%>
            <li data-sns="{type:'week'}" class="y-tab-center y-tab-center-select">本周热播</li>
            <li data-sns="{type:'month'}" class="y-tab-right">本月热播</li>
          	<%elseif $mostPlayedVideosType=='month'%>
            <li data-sns="{type:'month'}" class="y-tab-right y-tab-right-select">本月热播</li>
            <%/if%>
          </ul>
          <h2>圈内热播</h2>
        </div>
        <div class="y-box-main">
          <div class="y-scorll-box"> <a class="y-scorll-left  y-ico y-radius" href="#">向左</a> <a class="y-scorll-right y-ico y-radius" href="#">向右</a>
            <div class="y-scorll-middle">
                <ul class="y-v-list y-inline-ul cfix">
                </ul>
                </div>
              </div>
            </div>
        </div>
        <%/if%>
    </div>
    <%/if%>
    <%*//热播视频 *%>
    <div class="y-circle-count">
        <ul class="cfix y-inline-ul">
          <li class="<%if $cur_tab=='video'%>cur<%/if%>">
          	<cite><%$circle.video_count%></cite>
          	<a href="<%Util::circleUrl($circle._id, null, $circle)%>">视频</a>
          </li>
          <li><span></span></li>
          <li class="<%if $cur_tab=='user'%>cur<%/if%>">
          	<cite><%$circle.user_count%></cite>
          	<a href="<%Util::circleUrl($circle._id, ['tab' => 'user'], $circle)%>&type=quanyou">圈友</a></li>
          <li><span></span></li>
          <li class="<%if $cur_tab=='feed'%>cur<%/if%>">
          	<cite id="feeds_num_"></cite>
          	<a href="<%Util::circleUrl($circle._id, ['tab' => 'feed'], $circle)%>&type=dongtai">动态</a></li>
        </ul>
    </div>
    <%if $cur_tab==video%>
      <%include file="circle/index/video.php"%>
    <%elseif $cur_tab==user%>
      <%include file="circle/index/user.php"%>
    <%elseif $cur_tab==feed%>
    <div class=" y-feeds-box">
    	<div id="feedslist">
			<div class="inner">
				<div id="feed_list_" class="list">
      				<%include file="user/_feeds_list.php"%>
      				<%if empty($feeds.data)%>
                    <div class="nodata_">
						 <div class="inner_" style="text-align:center;">
						 该圈子暂无动态
		                </div>
					</div>      				
      				<%/if%>
      			</div>
			</div>
			<!-- 加载 -->
			<div style="display:none" id="loader"><div class="s-ic"><em></em></div></div>
			<!-- /加载 -->
			<%if $feeds.has_more%>
			<!-- 加载更多 -->
			<div class="bt-load-more"><a class="loading_more_info_" href="#">加载更多</a></div>
			<!-- /加载更多 -->
			<%/if%>
		</div>      
    </div>
    <%/if%>
</div>

<%/block%>

<%block name="foot_js"%>
<%Util::concatJs(['pager/pager.js'], $smarty.config.v, 'components/')%>
<%Util::concatJs(['waterfall.js', 'group.js', 'ejs.js'], $smarty.config.v, 'video/')%><%*, 'group_trend.js', 'group.js'*%>
<%if !Util::isSpider()%>
<script type="text/javascript" src="<%#resUrl#%>/js/video/create_waterfall.js?v=<%#v#%>"></script>
<script type="text/javascript">
    Dom.ready(function () {
        var firstBrick = W(".first_brick");
        if (firstBrick.length) {
            var col = W("#waterfall .cols")[0];
            window.console && console.log(col);
            col && col.insertBefore(firstBrick[0],col.firstChild);
        }
        W('.circleName').removeNode();
    });
<%/if%>
</script>
<%/block%>

<%block name="custom_foot"%>    
<script type="text/marmot">
    {
        "page_id"   : "circle",
        "circle_id" : "<%$circle._id%>",
        "status":"<%if $showAd && !$login_user%>1<%else%>0<%/if%><%if $circle.category%>1<%else%>0<%/if%><%if $relatedCircles%>1<%else%>0<%/if%><%if $circleEntity%>1<%else%>0<%/if%>0"
    }
</script>
<%/block%>
