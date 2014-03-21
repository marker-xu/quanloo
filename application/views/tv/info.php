<%extends file="common/base.tpl"%>

<%block name="seo_meta"%>
<meta name="keywords" content="<%$tv.title|escape:'html'%>,<%$tv.title|escape:'html'%>在线观看,<%$tv.title|escape:'html'%>全集,<%foreach $tv.cast_list as $cast%><%$cast.name|escape:'html'%>,<%/foreach%>" />
<meta name="description" content="<%$tv.title|escape:'html'%>剧情简介：<%$tv.desc|escape:'html'%>" />
<%/block%>

<%block name="title" prepend%><%$tv.title|escape:'html'%>详情页-剧情简介、影评、在线播放<%/block%>

<%block name="custom_css"%>
<%Util::concatCss(['y-circle.css'], $smarty.config.v, 'video/')%>
<%/block%>

<%block name="custom_js"%>
<%/block%>

<%block name="bd"%>
<div id="bd" style="position: relative;">
    <%*搜索框*%>
    <%include file="inc/search.inc"%>
    <%*//搜索框 *%>
    
    <%*广告位*%>
    <%include file="inc/entity/top_ad.inc"%>
    <%*//广告位 *%>
    
    <div class="y-content cfix y-content-bg">
    	<div class="y-main l">
            <div class="y-videoinfo-box cfix">
                <div class="l">
                	<img src="<%Util::videoThumbnailUrl($tv.thumbnail)%>">
                </div>
                <div class="r">
                    <h1><%$tv.title|escape:'html'%></h1>
                    <%if $tv.score%>
                    <dl class="y-score-box y-inline-dl cfix">
                        <dd><p style="width:<%Util::scoreToStar($tv.score)%>%;"></p></dd>
                        <dt><%$tv.score%></dt>
                    </dl>
                    <%/if%>
                    <dl class="y-videoinfo-text">
                        <dd>
                        	<%if $tv.released_date%><span>上映时间：<%$tv.released_date%></span><%/if%>
                        	<%if $tv.region%><span>地区：<%$tv.region%></span><%/if%>
                        	<%if $tv.genre%><span> 类型：<%$tv.genre%></span><%/if%>
                        </dd>
                        <%if $tv.director_list%>
                        <dt>
                    		导演：
                    		<%foreach $tv.director_list as $director%>
                    			<%if $director.is_link%>
                    			<a href="<%Util::starInfoUrl($director)%>" target="_blank"><%$director.name|escape:'html'%></a>
                    			<%else%>
                    			<%$director.name|escape:'html'%>
                    			<%/if%>
                    		<%/foreach%>
                        </dt>
                        <%/if%>
                        <%if $tv.cast_list%>
                        <dd>
                        	主演：
                    		<%foreach $tv.cast_list as $cast%>
                    			<%if $cast.is_link%>
                    			<a href="<%Util::starInfoUrl($cast)%>" target="_blank"><%$cast.name|escape:'html'%></a>
                    			<%else%>
                    			<%$cast.name|escape:'html'%>
                    			<%/if%>
                    		<%/foreach%>
                        </dd>
                        <%/if%>
                        <%if $tv.language%><dd>语言：<%$tv.language|escape:'html'%></dd><%/if%>
                        <%if $tv.length%><dd>片长：<%$tv.length%></dd><%/if%>
                    </dl>
                </div> 
                <dl class="y-videoinfo-warehouse-width-min y-inline-dl cfix">
                    <dd>
                        <%Util::episodesPager($tv.episode, 100, $tv.finished)%>
                    </dd>
                </dl>
            </div>
            
            <div class="y-videoinfo-text-all">
				剧情简介：<%if $tv.desc%><%$tv.desc|escape:'html'%><%else%>无。<%/if%>
            </div>
            
            <%if $relatedEntitys%>
            <div class="y-videoinfo-relatedshow">
            	<div class="y-box-title"><h2>相关剧集</h2></div>
            	<ul class="y-v-list y-inline-ul cfix">
            		<%foreach $relatedEntitys as $entity%>
                    <li>
                      <dl class="y-v-box y-vbox-3">
                        <dt>
                          <a href="<%Util::entityInfoUrl($entity)%>" target="_blank">
                            <img src="<%Util::videoThumbnailUrl($entity.thumbnail)%>">
                          </a>
                        </dt>
                        <dd>
                          <a href="<%Util::entityInfoUrl($entity)%>" target="_blank" title="<%$entity.title|escape:'html'%>"><%Util::utf8SubStr($entity.title, 12)%></a>
                        </dd>
                      </dl>
                    </li>
                    <%/foreach%>
                </ul>
            </div>
            <%/if%>
            
            <%if $relatedVideos%>
            <%*相关圈子*%>
            <%include file="inc/entity/related_videos.inc"%>
            <%*//相关圈子 *%>
            <%/if%>

            <%*评论*%>
        	<%include file="inc/entity/comments.inc"%>
            <%*//评论 *%>
        	
    	</div>
    	
        <%*相关圈子*%>
        <%include file="inc/entity/related_circles.inc"%>
        <%*//相关圈子 *%>
        
    </div>
</div>
<%/block%>

<%block name="foot_js"%>
<%Util::concatJs(['third/jquery-1.7.2.min.js', 'third/jquery/jquery-url-2.0.js', 'video/movie.js'], $smarty.config.v)%>	
<script type="text/javascript">

</script>
<%/block%>

<%block name="custom_foot"%>
<script type="text/marmot">
{
	"page_id":"tvinfo",
	"id":"<%$tv.entity_id%>",
}
</script>
<%/block%>
