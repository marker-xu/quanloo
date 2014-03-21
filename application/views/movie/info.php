<%extends file="common/base.tpl"%>

<%block name="seo_meta"%>
<meta name="keywords" content="<%$movie.title|escape:'html'%>,<%foreach $movie.cast_list as $cast%><%$cast.name|escape:'html'%>,<%/foreach%>" />
<meta name="description" content="<%$movie.title|escape:'html'%>剧情简介：<%$movie.desc|escape:'html'%>" />
<%/block%>

<%block name="title" prepend%><%$movie.title|escape:'html'%>详情页-剧情简介、影评、在线播放<%/block%>

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
                	<img src="<%Util::videoThumbnailUrl($movie.thumbnail)%>">
                </div>
                <div class="r">
                    <h1><%$movie.title|escape:'html'%></h1>
                    <%if $movie.score%>
                    <dl class="y-score-box y-inline-dl cfix">
                        <dd><p style="width:<%Util::scoreToStar($movie.score)%>%;"></p></dd>
                        <dt><%$movie.score%></dt>
                    </dl>
                    <%/if%>
                    
                    <dl class="y-videoinfo-text">
                        <dd>
                        	<%if $movie.released_date%><span>上映时间：<%$movie.released_date%></span><%/if%>
                        	<%if $movie.region%><span>地区：<%$movie.region%></span><%/if%>
                        	<%if $movie.genre%><span> 类型：<%$movie.genre%></span><%/if%>
                        </dd>
                        <%if $movie.director_list%>
                        <dt>
                    		导演：
                    		<%foreach $movie.director_list as $director%>
                    			<%if $director.is_link%>
                    			<a href="<%Util::starInfoUrl($director)%>" target="_blank"><%$director.name|escape:'html'%></a>
                    			<%else%>
                    			<%$director.name|escape:'html'%>
                    			<%/if%>
                    		<%/foreach%>
                        </dt>
                        <%/if%>
                        <%if $movie.cast_list%>
                        <dd>
                    		主演：
                    		<%foreach $movie.cast_list as $cast%>
                    			<%if $cast.is_link%>
                    			<a href="<%Util::starInfoUrl($cast)%>" target="_blank"><%$cast.name|escape:'html'%></a>
                    			<%else%>
                    			<%$cast.name|escape:'html'%>
                    			<%/if%>
                    		<%/foreach%>
                        </dd>
                        <%/if%>
                        <%if $movie.language%><dd>语言：<%$movie.language|escape:'html'%></dd><%/if%>
                        <%if $movie.length%><dd>片长：<%$movie.length%></dd><%/if%>
                        <dd>
                        	<a class="y-ico y-ico-play" href="<%$movie.play_url%>" target="_blank">立即播放</a>
                        </dd>
                    </dl>
                </div> 
            </div>
            
            <div class="y-videoinfo-text-all">
				剧情简介：<%if $movie.desc%><%$movie.desc|escape:'html'%><%else%>无。<%/if%>
            </div>
            
            <%if $relatedEntitys%>
            <div class="y-videoinfo-relatedshow">
            	<div class="y-box-title"><h2>相关影片</h2></div>
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
	"page_id":"movieinfo",
	"id":"<%$movie.entity_id%>",
}
</script>
<%/block%>
