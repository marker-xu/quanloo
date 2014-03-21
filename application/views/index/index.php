<%extends file="common/base.tpl"%>

<%block name="title"%>圈乐：发现精彩视频，圈住无限快乐<%/block%>
 
<%block name="seo_meta"%>
<meta name="keywords" content="视频SNS社区,视频圈子,个性化视频,视频发现,视频推荐,视频分享,热点视频,精彩视频,最好视频" />
<meta name="description" content="圈乐是一个视频SNS社区，玩转圈子，分享视频，一网打尽全网最热、最酷、最好玩视频。凭借最快速、最全的视频热点发掘，独有的视频圈子，在这里你能找到视频同趣好友，分享热门视频、搞笑视频、美女视频等，尽享视频社交的最大乐趣。">
<link rel="canonical" href="http://<%$smarty.const.DOMAIN_SITE%>/" />
<%/block%>
<%block name="view_conf"%>
<%$h1 = 1%>
<%/block%>

<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/index.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>
<script>
    window.timestamp = "<%$recommend_tm%>" || '0';
    function getParam(name){//获取参数值 by司徒正美
        var sUrl = window.location.search.substr(1);
        var r = sUrl.match(new RegExp("(^|&)" + name + "=([^&]*)(&|$)"));
        return (r == null ? null : unescape(r[2]));
    }
    window.waterfallOpts = {
        requestUrl: "/index/hotvideos",
        requestOpts:{
            firstData:{
                err:"ok",
                data:<%json_encode($videos)%>
            },
            tm: window.timestamp,
            count: 16,
            type: getParam("type") || "0",
            playlist: "home_page"
        },
        initOpts: {
            target:"#waterfall",
            col: 4,
            colWidth:228,
            imgExpr: ".brick_img"
        },
        requestInBottom: true,
        requestCallback:function(){
            if(window.waterfallOpts.target.bricks.length >= 200){
                window.waterfallOpts.requestInBottom = false;
            }
        }
    }
    Dom.ready(function(){
        var promotion = W("#circle_popularize li a").on("mouseover",function(){
            promotion.removeClass("opaque")
            W(this).addClass("opaque")
        });
       // promotion.item(0).fire("mouseover")
        W(".sprit_bg.fast_login").on("click",function(e){
            e.preventDefault();
            //弹出大的登陆页面，LOGIN_POPUP定义在common.js中
            LOGIN_POPUP();
        })

    })
</script>
<%strip%>
<%/strip%>
<%/block%>
<!--- 测试 -->
<%block name="bd"%>
<div id="bd">

    <%include file="inc/search.inc"%>
    <!--推广圈found_circle_login-->
    <div class="cls" id="circle_popularize">
        <ul class="l circle_item">
	        <%foreach $promoteCircles as $circle%>
            <li>
            	<a data-lks="from=home_banner" class="item <%if $circle@first%>opaque<%else%>,<%/if%>" href="<%Util::circleUrl($circle._id, null, $circle)%>"><!--在第一个A标签中添加一个叫opaque的属性-->
                    <div class="mask"></div>
                    <img class="pic" src="<%Util::circlePreviewPic($circle.tn_path)%>" alt="<%$circle.title|escape:'html'%>" />
                        <div class="spread"><img src="<%Util::circlePreviewPic($circle.recommend_image)%>" alt="<%$circle.title|escape:'html'%>" /></div>
                        <span class="tit"><%$circle.title|escape:'html'%></span>
                       
            	</a>
            </li>
	        <%/foreach%>
        </ul>
        <div class="r user_place">
        	<%if $login_user%>
            <a class="sprit_bg found_circle_login" href="/category/all" data-lks="from=home_banner"></a>
        	<%else%>
            <a class="sprit_bg found_circle" href="/category/all" data-lks="from=home_banner"></a>
            <a class="sprit_bg fast_reg" href="/user/register?p=index_circle"></a>
            <a class="sprit_bg fast_login marmot" href="" data--marmot="{page_id:'click_login', position:'index_circle'}"></a>
        	<%/if%>
        </div>
    </div>
    <!--//推广圈-->
    <!--视频分类-->
    <div id="video_type" class="bd-b">
        <ul class="wrap cls">
            <li class="lele"><a class="marmot" data--marmot="{page_id:'click_guide_home'}"  href="###"></a></li>
            <li <%if !$type%>class="selected"<%/if%>><a href="/">全部</a></li>
            <li <%if $type==1%>class="selected"<%/if%>><a href="/?type=1">最新</a></li>
            <li <%if $type==2%>class="selected"<%/if%>><a href="/?type=2">最热</a></li>
            <li <%if $type==3%>class="selected"<%/if%>><a href="/?type=3">最受关注</a></li>
        </ul>
    </div>
    <!--//视频分类-->
    <!--视频瀑布-->
    <div id="waterfall" class="bd-c cls" style="position:relative;">
		<!--对来自Spider的访问同步输出页面-->
        <%if Util::isSpider()%>
    	<%include file="inc/spider_waterfall_4_cols.inc"%>
        <%/if%>
    </div>
    <!--//视频瀑布-->
    <!--loading-->
    <div id="loader"><div class="s-ic"><em></em></div></div>
    <!--loading-->
</div>
<%/block%>

<%block name="foot_js"%>
<%Util::concatJs(['waterfall.js', 'ejs.js', 'intro.js'], $smarty.config.v, 'video/')%>
<%if !Util::isSpider()%>
<script type="text/javascript" src="<%#resUrl#%>/js/video/create_waterfall.js?v=<%#v#%>"></script>
<%/if%>
<%/block%>
