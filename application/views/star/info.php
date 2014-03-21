<%extends file="common/base.tpl"%>

<%block name="seo_meta"%>
<meta name="keywords" content="<%$star.name|escape:'html'%>,<%$star.name|escape:'html'%>个人简介,<%$star.name|escape:'html'%>相关作品" />
<meta name="description" content="<%$star.name|escape:'html'%>个人简介：<%$star.desc|escape:'html'%>" />
<%/block%>

<%block name="title" prepend%><%$star.name|escape:'html'%>简介、代表作品<%/block%>

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
                <div class="l" style="height:120px;">
                	<img src="<%Util::videoThumbnailUrl($star.thumbnail)%>">
                </div>
                <div class="r">
                    <h1><%$star.name|escape:'html'%></h1>
                    <dl class="y-videoinfo-text">
                        <%if $star.region%><dd>地区：<%$star.region%></dd><%/if%>
                        <%if $star.gender%><dd>性别：<%$star.gender%></dd><%/if%>
                        <%if $star.birthday%><dd>出生日期：<%$star.birthday%></dd><%/if%>
                     </dl>
                </div>
            </div>
            
            <div class="y-videoinfo-text-all">
				个人简介：<%if $star.desc%><%$star.desc|escape:'html'%><%else%>无。<%/if%>
            </div>
            
            <%if $star.works%>
            <div class="y-videoinfo-relatedshow">
            	<div class="y-box-title">
                    <ul class="y-tab cfix y-inline-ul r">
                        <li class="y-tab-left y-tab-left-select js-get-all">全部</li>
                        <li class="y-tab-select js-get-movie">电影</li>
                        <li class="y-tab-right js-get-tv">电视剧</li>
                    </ul>
                	<h2>代表作品</h2>
                </div>
            	<div style="height:135px;" class="y-box-main js-works-wrap">
            	<ul class="y-v-list y-inline-ul cfix js-works-list">
                    <%foreach $star.works as $entity%>
                    <li class="js-<%$entity.entity_type%>">
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
            </div>
            <%if count($star.works) > 6%>
            <div class="y-btn-drop-box">
            	<a class="y-btn-drop-open" href="#">展开全部作品</a>
                <a class="y-btn-drop-closed" href="#" style="display:none;">收起</a>
            </div>
            <%/if%>
            <%/if%>
            
            <%if $relatedVideos%>
            <div class="y-hot-video-box">
                <div class="y-box-title">
                  <h2>相关视频</h2>
                </div>
                <div class="y-box-main">
                  <div class="y-scorll-box"> <a href="#" class="y-scorll-left  y-ico y-radius">向左</a> <a href="#" class="y-scorll-right y-ico y-radius">向右</a>
                    <div class="y-scorll-middle">
                      <ul class="y-v-list y-inline-ul cfix">
                    	<%foreach $relatedVideos as $video%>
                        <li>
                          <dl class="y-v-box y-vbox-1">
                            <dt><a href="<%Util::videoPlayUrl($video._id)%>" target="_blank"><img src="<%Util::videoThumbnailUrl($video.thumbnail)%>"></a></dt>
                            <dd><a href="<%Util::videoPlayUrl($video._id)%>" target="_blank" title="<%$video.title|escape:'html'%>"><%Util::utf8SubStr($video.title, 16)%></a></dd>
                          </dl>
                        </li>
                        <%/foreach%>
                      </ul>
                    </div>
                  </div>
                </div>
          </div>
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
	"page_id":"starinfo",
	"id":"<%$star.name|escape:'javascript'%>",
}
</script>
<%/block%>
