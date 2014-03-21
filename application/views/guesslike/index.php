<%extends file="common/base.tpl"%>

<%block name="title"%>猜你喜欢页<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/index.css?v=<%#v#%>">
<%/block%>

<%block name="custom_js"%>
<script type="text/javascript" src="http://dev.static.quanloo.sii.sdo.com:8181/js/jquery/jquery1.72.js?v=1.0.5"></script>
<script type="text/javascript" src="<%#resUrl#%>/js/video/discover.js?v=<%#v#%>"></script>
<%/block%>
<%block name="bd"%>
<div id="bd">
	<%include file="guesslike/inc/video_show.inc"%>
    <%*搜索框*%>
    <%include file="inc/search.inc"%>

    <%*//搜索框*%>
    <!--视频分类-->
    <style type="text/css">
        .cfix:after{content:".";display:block;visibility:hidden;height:0;clear:both;}
        .cfix{zoom:1}
        .y-ico{overflow:hidden; text-indent:-100px; display:inline-block; +display:block;  float:none; +float:left; background-image:url(<%#resUrl#%>/img/y-ico.png);width:19px; height:19px; margin:4px 4px 0 0;}
        .y-ico-circle{ background-position:0 0;}
        .y-ico-video{ background-position:0 -22px;}

        .y-inline-dl dt,.y-inline-dl dd,.y-inline-ul li{ float:left;}
		.y-guess-like-title {border-bottom: 1px solid #C5C5C5;}
        .y-guess-like-title h2{ background:none; line-height:30px; height:30px;font-size:14px;}
        .y-guess-like .circleitem,.y-guess-like .BoardBrick{ float:left; margin-right:15px; margin-top:20px;}
        .y-guess-like .list,.y-guess-like-box{width:1000px;}
     /*   .y-guess-like-hide{display: none;}*/
    </style>

    <%$item_pos=-4%>
    <%$item_pos_circle=-4%>
    <%foreach $guess_result as $result%>
    <%$circle_list=$result['circle_list']%>
    <%$video_list=$result['video_list']%>
    <%$item_pos_circle=$item_pos_circle+4%>
    <div class="y-guess-like <%if $result@index>0%>y-guess-like-hide<%/if%>" round="<%$result@index%>">
        <div id="video_type" class="bd-b">
            <dl class="y-guess-like-title y-inline-dl cfix">
                <dt class="y-ico y-ico-circle"></dt>
                <dd>
                    <h2>这些圈子你可能很感兴趣</h2></dd>
            </dl>
        </div>
        <div class="y-guess-like-box cfix">
            <ul class="list y-inline-ul cfix">
                <%foreach $circle_list as $row%>
                <li class="circleitem circleitem-t2">
                    <div class="hd marmot" <%strip%>data--marmot="{
                         page_id:'click_recommendation',
                         item_list:'',
                         item_id:'<%$row._id|escape:"javascript"%>',
                         item_pos:'<%$row@index%>',
                         rec_zone:'circle_rec'
                         }"<%/strip%>>
                         <h3>
                            <a href="<%Util::circleUrl($row._id, null, $row)%>" title="<%$row.title|escape:'html'%>"><%Util::utf8SubStr($row.title,21)|escape:"html"%></a>
                        </h3>
                        <span class="type type-t0"></span>
                        <div class="circle_own">
                            <img class="in-block head_img" src="<%Util::userAvatarUrl($row.user.avatar.30, 30)%>" />
                            <%if $row.user._id == 1846037590%>
                            <span class="lele"><%$row.user.nick|escape:"html"%></span>创建
                            <%else%>
                            <a class="user" target="_blank" href="<%Util::userUrl($row.user._id)%>"><%$row.user.nick|escape:"html"%></a>创建
                            <%/if%>
                        </div>
                    </div>
                    <div class="bd">
                        <a href="<%Util::circleUrl($row._id, null, $row)%>">
                            <img src="<%Util::circlePreviewPic($row.tn_path)%>" alt="<%$row.title|escape:'html'%>" />
                        </a>
                    </div>
                    <div class="ft clearfix">
                        <%if $row.is_focus%>
                        <%$followst = 'followed'%>
                        <%else%>
                        <%$followst = 'b-follow'%>
                        <%/if%>
                        <a class="btn <%$followst%>" href="#" data-action="{'id':'<%$row._id%>'}" is-focus="<%$row.is_focus%>"><span class="text0">关注</span><span class="text1">取消关注</span><span class="text2">取消关注</span></a>

                        <a href="#" class="btn invite_friend" data-action="{'id':'<%$row._id%>', 'url': '<%Util::circleUrl($row._id, null, $row)%>'}" >邀请好友</a>
                        <a href="#" class="btn share_group" data-action="{'id':'<%$row._id%>','image':'<%Util::circlePreviewPic($row.tn_path)%>','circleName':'<%Util::utf8SubStr($row.title,21)|escape:"html"%>','url': '<%Util::circleUrl($row._id, null, $row)%>'}">分享Ta</a>
                    </div>
                    <input type="hidden" value="<%$row._id%>" />
                </li>
                <%/foreach%>
            </ul>
        </div>
    </div>

    <%foreach $video_list as $key=>$clusterTmp%>
    <%$item_pos=$item_pos+4%>
    <%$reason_desc=$recommend_type_map[$key]%>
    <div class="y-guess-like <%if $result@index>0%>y-guess-like-hide<%/if%>" round="<%$result@index%>">    
        <div id="video_type" class="bd-b">
            <dl class="y-guess-like-title y-inline-dl cfix">
                <dt class="y-ico y-ico-video"></dt>
                <dd>
                    <h2><%$reason_desc%></h2></dd>
            </dl>
        </div>
        <div class="y-guess-like-box cfix">
            <%foreach $clusterTmp as $row%>
            <%$videoPlayUrl=Util::videoPlayUrl($row._id)%>
            <%$tmpCircleId = 0%>
            <%if isset($row.circle._id)%>
            <%$tmpCircleId = $row.circle._id%>
            <%/if%>
            <!----------------这是砖头----------------->
            <div style="opacity: 1;" class="BoardBrick">
                <%if $row.circle and 0%>
                <div class="circleName s-ic"><span><a href="<%Util::circleUrl($row.circle._id, null, $row.circle)%>"><%$row.circle.title|escape:'html'%></a></span></div>
                <%else%>
                <div class="hidenHead"></div>
                <%/if%>
                <div class="hidenHead"></div>
                <div class="cls vod m-pic tp-a">
                    <a style="width:200px;height:150px" class="a playurl" href="<%$videoPlayUrl%>" target="_blank" data--marmot="{page_id:'click_recommendation',item_pos:'<%$row@index+$item_pos%>',rec_zone:'video_rec',item_id: '<%$row._id%>'}">
                        <img style="width:200px;height:150px" class="h brick_img" alt="<%$row.title|escape:'html'%>" src="<%Util::videoThumbnailUrl($row.thumbnail)%>" />
                        <div class="action">
                            <span class="circle_down" title="添加到我创建的圈子" video-id="<%$row._id%>" circle-id="<%$tmpCircleId%>" ></span>
                            <span data-action="{id:'<%$row._id%>'}" class="newadd marmot"
                                  data--marmot="{page_id:'click_addtowatchlater',video_id: '<%$row._id%>'}" title="添加到我的收藏" ></span>
                            <span class="sharer" data-sns="{id:'<%$row._id%>', title:'<%$row.title|escape:'html'%>', image: '<%Util::videoThumbnailUrl($row.thumbnail)%>'}"></span>
                        </div>
                        <span class="ico"></span>
                    </a>
                    <span class="time"><%Util::sec2time($row.length)%></span>
                    <div class="tit" style="height:3em;overflow-y:hidden;">
                        <a href="<%$videoPlayUrl%>" target="_blank" title="<%$row.title|escape:'html'%>"><%$row.title|escape:'html'%> </a>
                    </div>
                    <div class="count">
                        <%if $row.shared_count > 0%>
                        <span title="分享" class="share"><%$row.shared_count%></span>
                        <%else%>
                        <span title="分享" class="share" style="display:none;">false;</span>
                        <%/if%>
                        <%if $row.mooded_count.total > 0%>
                        <span title="心情" class="heart _mood"><%$row.mooded_count.total%></span>
                        <%else%>
                        <span title="心情" class="heart _mood" style="display:none;">false</span>
                        <%/if%>
                        <%if $row.watched_count > 0%>
                        <span title="播放" class="playn"><%$row.watched_count%></span>
                        <%else%>
                        <span title="播放" class="playn" style="display:none;">false</span>
                        <%/if%>
                    </div>
                </div>
                <!--表情-->
                <%guesslike_moods row=$row tmpCircleId=$tmpCircleId%>
                <!--评论-->
                <%guesslike_comments data=$row.comments%>

            </div>
            <%/foreach%>
        </div>
    </div>
    <%/foreach%>
    <%/foreach%>
    <!--//视频瀑布-->
    <!--loading-->
    <div id="loading" style="height:10px;width:300px;"></div>
    <!--loading-->
</div>
<%/block%>

<%block name="foot_js"%>

<%/block%>