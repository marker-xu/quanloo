<%extends file="common/base.tpl"%>
<%block name="seo_meta"%>
<%$circle_title_escape=HTML::chars($circle.title)%>
<%$circle_creator_nick_escape=HTML::chars($circle.user.nick)%>
<%if $cur_select_tag != ''%>
<%$cur_select_tag_escape=HTML::chars($cur_select_tag)%>
<meta name="keywords" content="<%$cur_select_tag_escape%>,<%$cur_select_tag_escape%>视频,<%$circle_title_escape%>,<%$circle_title_escape%>视频" />
<meta name="description" content="最全、最新、最热的<%$cur_select_tag_escape%>视频，就在圈乐<%$circle_title_escape%>视频圈子。" />
<%elseif ! $circle.official%>
<meta name="keywords" content="<%$circle_title_escape%>,<%$circle_title_escape%>视频,<%$circle_creator_nick_escape%>" />
<meta name="description" content="<%$circle_creator_nick_escape%>创建的<%$circle_title_escape%>视频圈子汇集<%$circle_creator_nick_escape%>最喜欢的<%$circle_title_escape%>相关视频，在这里，你能和<%$circle_creator_nick_escape%>一起分享视频观看心得，并邀请好友关注<%$circle_title_escape%>视频圈子或者将<%$circle_title_escape%>视频分享给新浪、腾讯等微博好友。" />
<%else%>
<meta name="keywords" content="<%$circle_title_escape%>,<%$circle_title_escape%>视频" />
<meta name="description" content="视频圈子汇集最全、最新、最热的<%$circle_title_escape%>相关视频，在这里，你能和圈友一起分享视频观看心得，并邀请好友关注<%$circle_title_escape%>视频圈子或者将<%$circle_title_escape%>视频分享给新浪、腾讯等微博好友。" />
<%/if%>
<link rel="canonical" href="<%Util::circleUrl($circle._id, ['tag' => $cur_select_tag], $circle)%>" />
<%/block%>
<%block name="title" prepend%><%$circle_title_escape%><%$cur_select_tag_escape%>视频<%if ! $circle.official%>-<%$circle_creator_nick_escape%><%/if%><%/block%>

<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/circle_user.css?v=<%#v#%>">
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

<script>
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
            tag: '<%$cur_select_tag|escape:"javascript"%>',
            title: '<%$circle.title|escape:"javascript"%>'
        },
        requestInBottom: true,
        requestCallback:function(){
            if(window.waterfallOpts.target.bricks.length >= 200){
                window.waterfallOpts.requestInBottom = false;
            }
            if(isCurrentUser){
                W(".circle_down").forEach(function(el){//变成删除
                    el.className = el.className.replace(/circle_down(\s|$)/,"circle_del")
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

</script>
<%/block%>
<%block name="bd"%>
<div id="bd" style="position: relative;">
    <%*搜索框*%>
    <%include file="inc/search.inc"%>
    <%*//搜索框 *%>
    <div id="userside" class="BoardBrick first_brick"><!-- 第一块砖头-->
        <div id="circleinfo" class="circleitem circleitem-t2">
            <h2 class="name"><%$circle.title|escape:'html'%></h2>
            <div class="circle_own">
                <img class="in-block head_img" src="<%Util::userAvatarUrl($circle.user.avatar.30, 30)%>">
                <%if $circle.user._id == 1846037590%>
                <span class="lele"><%$circle.user.nick|escape:"html"%></span>创建
                <%else%>
                <a class="user" target="_blank" href="<%Util::userUrl($circle.user._id)%>"><%$circle.user.nick|escape:"html"%></a>创建
                <%/if%>
            </div>
            <div class="head_pic">
                <a href="<%Util::circleUrl($circle._id, null, $circle)%>">
                    <img src="<%Util::circlePreviewPic($circle.tn_path)%>" alt="<%$circle.title|escape:'html'%>">
                </a>
            </div>
            <div class="circle_count">
                <p class="in-block first">视频:<span><%$circle.video_count|number_format%></span></p>
                <p class="in-block">圈友:<span><%$circle.user_count|number_format%></span></p>
            </div>
            <div class="ft clearfix">
            	<%if $circle.user._id==$login_user._id%>
            	<a class="btn edit-circle" href="/user/editcircle?cid=<%$circle._id%>" ><span>编辑</span></a>
            	<%else%>
                <%if $is_subscribed%>
                <a href="###" data-action="{id:'<%$circle._id%>'}" class="btn btn-follow followed">
                    <span class="text0">关注</span><span class="text1">取消关注</span><span class="text2">取消关注</span>
                </a>
                <!--<div class="follow"><a href="###" data-action="{id:'<%$circle._id%>'}" class="followed">取消关注</a></div>-->
                <%else%>
                  <a href="###" data-action="{id:'<%$circle._id%>'}" class="btn btn-follow b-follow">
                    <span class="text0">关注</span><span class="text1">取消关注</span><span class="text2">取消关注</span>
                </a>
              <!--<div class="follow"><a href="###" data-action="{id:'<%$circle._id%>'}" class="b-follow">关注</a></div>-->
                <%/if%>
				<%/if%>	
                <a href="###" data-action="{'id':'<%$circle._id%>', 'url': '<%Util::circleUrl($circle._id, null, $circle)%>'}" class="btn bt-invite invite_friend">邀请好友</a>
                <a href="###" data-action="{'id':'<%$circle._id%>','image':'<%Util::circlePreviewPic($circle.tn_path)%>','circleName':'<%$circle.title|escape:'html'%>', 'url': '<%Util::circleUrl($circle._id, null, $circle)%>'}" class="btn bt-share share_group">分享Ta</a>
            </div>


        </div>
        <%if $circle.official == 1%>
        <div class="circletags">
            <div class="hd">
                <div id="xplus" class="xplus marmot xplus_tag" data--marmot="{page_id:'click_addtag'}"></div><h3>标签筛选</h3>
            </div>
            <div class="bd" id="tag_box" url_base="<%Util::circleUrl($circle._id, null, $circle)%>">
                <div class="taglist clearfix">
                    <div class="tagadd clearfix" style="display: none; ">
                        <form id="formaddtag">
                            <input class="ipt-text" placeholder="输入我的tag">
                            <input class="ipt-submit" type="button" style="cursor:pointer;">
                        </form>
                    </div>
                    <div class="tag-item tag-sys<%if empty($cur_select_tag)%> tag-item-on<%/if%>">
                        <a href="<%Util::circleUrl($circle._id, null, $circle)%>"><span class="tag_link">不限</span></a>
                    </div>
                    <%$cust_tag_tmp=array_fill_keys((array)$cust_tag, true)%>
                    <%foreach $circle.filter_tag as $v=>$w%>
                    <%if ! isset($cust_tag_tmp[$v])%>
                    <%*如果系统tag和用户自定义的相同，则只显示用户的*%>
                    <div class="tag-item <%if $circle.creator == $login_user._id%>tag-cus<%else%>tag-sys<%/if%><%if $cur_select_tag == $v%> tag-item-on<%/if%>">
                        <a href="<%Util::circleUrl($circle._id, ['tag' => $v], $circle)%>"><span class="tag_link"><%$v|escape:"html"%></span></a>
                        <%if $circle.creator == $login_user._id%>
                        <span class="del" data="<%urlencode($v)%>"></span>
                        <%/if%>
                    </div>
                    <%/if%>
                    <%/foreach%>
                    <%foreach $cust_tag as $v%>
                    <div class="tag-item tag-cus<%if $cur_select_tag == $v%> tag-item-on<%/if%>">
                        <a href="<%Util::circleUrl($circle._id, ['tag' => $v], $circle)%>"><span class="con tag_link"><%$v|escape:"html"%></span></a>
                        <span class="del" data="<%urlencode($v)%>"></span>
                    </div>
                    <%/foreach%>
                </div>
            </div>
            <div class="ft"></div>
        </div>
        <%/if%>
        <%if $circle_feedlist%>
        <%include file="inc/circle_feedlist.inc" user_circle_feedlist=$circle_feedlist feed_title="圈子动态" is_circle=true circle_id=$circle._id inline%>
        <%/if%>
    </div>
    <%if $circle.user._id != 1846037590 and ($circle.video_count < 1)%>
    <div style="position: absolute; text-align: center; margin-top: 30px; left: 268px;">
    	<%if $circle.user._id==$login_user._id%>
    	该圈子还没有任何视频，赶快按照提示去添加视频吧！
    	<%else%>
    	这里还没有任何视频，先关注Ta， 精彩视频稍后马上送上！
    	<%/if%>
    	<%if $circle.user._id==$login_user._id%>
    	<br/><br/>
	    <img src="<%#resUrl#%>/img/circle_no_video.jpg"  />
	    <%/if%>
    </div>
    	
    <%/if%>
    <div id="waterfall" class="bd-c cls" style="position:relative; <%if $circle.user._id==$login_user._id and ($circle.video_count < 1)%>height:500px;<%/if%>">
        <!--对来自Spider的访问同步输出页面-->
        <%if Util::isSpider()%>
        <%include file="inc/spider_waterfall_4_cols.inc"%>
        <%/if%>
    </div>
    <div id="loader"><div class="s-ic"><em></em></div></div>
</div>

<%/block%>

<%block name="foot_js"%>
<%Util::concatJs(['pager/pager.js'], $smarty.config.v, 'components/')%>
<%Util::concatJs(['waterfall.js', 'ejs.js', 'group_trend.js', 'group.js'], $smarty.config.v, 'video/')%>
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
        "page_id"   : "circle"
    }
</script>
<%/block%>
