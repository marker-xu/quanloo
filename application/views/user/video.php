<%extends file="common/base_user.tpl"%>
<%block name="seo_meta"%>
<%$user_nick_escape=HTML::chars($user_info.nick)%>
<meta name="keywords" content="<%$user_nick_escape%>,<%$tabNameMap[$cur_selected_tab]|escape:"html"%>" />
<meta name="description" content="<%$user_nick_escape%><%$tabNameMap[$cur_selected_tab]|escape:"html"%>">
<%/block%>
<%block name="title" prepend%><%$user_nick_escape%><%$tabNameMap[$cur_selected_tab]|escape:"html"%><%/block%>

<%block name="custom_js"%>
<script type="text/javascript" src="<%#resUrl#%>/js/video/waterfall.js?v=<%#v#%>"></script>
<script type="text/javascript" src="<%#resUrl#%>/js/video/ejs.js?v=<%#v#%>"></script>
<%if !Util::isSpider()%>
<script type="text/javascript" src="<%#resUrl#%>/js/video/create_waterfall.js?v=<%#v#%>"></script>
<%/if%>
<script type="text/javascript">
    var defaultTab = '<%$cur_selected_tab%>';
    window.waterfallOpts = {
        requestUrl: "/video/playlist",
        initOpts: {
            target:"#waterfall",
            col: 3,
            width: 710,
            colWidth: 228,
            imgExpr: ".brick_img"
        },
        requestOpts:{
            count: 24,
            name:  defaultTab, //watched , watch_later
            playlist: defaultTab,
            user:  "<%$user_info._id%>",
            _r : Math.random()
        },
        requestInBottom: true,
        requestCallback:function(json){
            if(defaultTab == "watch_later"){
                W(".newadd").forEach(function(el){
                    el.className = el.className.replace(/newadd(\s|$)/,"newadded ")
                });
            }
            if(defaultTab == "circled"){
                 W(".circle_down").forEach(function(el){//变成删除
                    el.className = el.className.replace(/circle_down(\s|$)/,"circle_del");
                    el.title = ""
                });

            }
            W("#loader").hide();
        }

    }
</script>
<%/block%>
<%block name="user_nav"%>
<%include file="inc/user_nav.inc" pagetype=3%>
<div class="nav-sub">
    <ul class="clearfix">
        <%if $is_admin%>
        <li class="js_switch_name watch_later"><a href="<%Util::userUrl($user_info._id, 'video', ['type' => 'watch_later'])%>">收藏的视频</a></li>
        <li class="js_switch_name watched"><a href="<%Util::userUrl($user_info._id, 'video', ['type' => 'watched'])%>">看过的视频 </a></li>
        <%/if%>
        <li class="js_switch_name commented"><a href="<%Util::userUrl($user_info._id, 'video', ['type' => 'commented'])%>">评论过的视频</a></li>
        <li class="js_switch_name mooded"><a href="<%Util::userUrl($user_info._id, 'video', ['type' => 'mooded'])%>">标过心情的视频</a></li>
        <li class="js_switch_name circled"><a href="<%Util::userUrl($user_info._id, 'video', ['type' => 'circled'])%>">圈过的视频</a></li>
    </ul>
</div>
<%/block%>     
<%block name="article"%>
<%if $notice_msg%>
<%if $is_admin%>
<div class="init-status nodata_">
    <div class="inner_" style="position:relative;">
        <%$notice_msg%>
        <a class="map" href="/guesslike"></a>
    </div>
</div>
<%else%>
<div style="padding:30px 0;text-align:center;font-size:14px;"><%$notice_msg%></div>
<%/if%>
<%/if%>
<%if !$notice_msg%>
<div id="waterfall" class="bd-c cls" style="position:relative;">
    <!--对来自Spider的访问同步输出页面-->
    <%if Util::isSpider()%>
    <%include file="inc/spider_waterfall_3_cols.inc"%>
    <%/if%>
</div>
<div id="loader"><div class="s-ic"><em></em></div></div>
<%/if%>
<%/block%>
<%block name="foot_js"%>
<style>
    .hideAdd span.add
    {
        display:none!important;
        width:0px;
        height:0px;
        overflow:hidden;
    }
</style>
<script type="text/javascript">
    Dom.ready(function(){
        W('.js_switch_name').removeClass('on');
        W('li.'+defaultTab).addClass('on');

        if( defaultTab == 'watch_later' )
        {
            W('#waterfall').addClass('hideAdd');
        }
    });
</script>
<%/block%>

<%block name="custom_foot"%>    
<script type="text/marmot">
    {
        "page_id"   : "uservideo"
    }
</script>
<%/block%> 