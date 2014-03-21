<%extends file="common/base.tpl"%>

<%block name="seo_meta"%>
<meta name="keywords" content="电影,电影在线观看,圈乐" />
<meta name="description" content="圈乐电影页，汇聚来自各网站各类最新最热华语电影、好莱坞电影、日韩电影和欧洲电影尽收眼底。" />
<%/block%>

<%block name="title" prepend%>全网最新、最热电影尽在圈乐电影页<%/block%>

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
    
    <div class="y-content cfix">
    	<div class="y-main l">
        	<div class="y-box-border">
        	
                <div class="y-scorlltab-box-long">
                    <div class="y-box-title">
                        <div class="y-scorlltab-box cfix r">
                            <b class="y-select">1</b>
                            <b>2</b>
                            <b>3</b>
                        </div>
                        <h2>当前热映</h2>
                    </div>
                    <div class="y-box-main">
                          <div class="y-scorlllong-box"> 
                          <a href="#" class="y-scorlllong-left y-ico">向左</a> 
                          <a href="#" class="y-scorlllong-right y-ico ">向右</a>
                           <div class="y-scorlllong-middle">
                              <ul class="y-v-list y-inline-ul cfix js-big-carousel">
                                <%foreach $hotMovies as $movie%>
                                <li>
                                  <dl class="y-v-box y-vbox-2">
                                    <dt>
                                    	<a href="<%Util::entityInfoUrl($movie)%>" target="_blank"><img src="<%Util::videoThumbnailUrl($movie.thumbnail)%>"></a>
                                    </dt>
                                    <dd class="vbox-text">
                                    	<cite><%$movie.score%></cite>
                                    	<h3><a href="<%Util::entityInfoUrl($movie)%>" target="_blank" title="<%$movie.title|escape:'html'%>"><%$movie.title|escape:'html'%></a></h3>
                                    </dd>
                                    <dd>类型：<%Util::utf8SubStr($movie.genre, 12)%></dd>
                                  </dl>
                                </li>
                                <%/foreach%>
                              </ul>
                            </div>
                          </div>
       				 </div>
                </div>
                
                <div class="y-videoinfo-content">
                    <div class="y-videoinfo-tab">
                        <ul class="y-inline-ul cfix">
                            <li class="<%if $sort == 'new'%>y-select<%/if%>"><a href="/movie">最新影片</a></li>
                            <li class="<%if $sort == 'hot'%>y-select<%/if%>"><a href="/movie?sort=hot">最热影片</a></li>
                        </ul>
                    </div>
                    <%if $movies.data%>
                    <ul>
                        <%foreach $movies.data as $movie%>
                    	<li>
                        	<div class="y-videoinfo-box cfix">
                                <div class="l">
                                    <a href="<%Util::entityInfoUrl($movie)%>" target="_blank"><img src="<%Util::videoThumbnailUrl($movie.thumbnail)%>"></a>
                                </div>
                                <div class="r">
                                    <h3><a href="<%Util::entityInfoUrl($movie)%>" target="_blank" title="<%$movie.title|escape:'html'%>"><%$movie.title|escape:'html'%></a></h3>
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
                                        <dd class="y-videoinfo-protagonist">
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
                                        <dd>
                                        	<%if $movie.desc%>简介：<%Util::utf8SubStr($movie.desc, 136)%><%/if%>
                                        	<a href="<%Util::entityInfoUrl($movie)%>" target="_blank">详细信息&gt;&gt;</a>
                                        </dd>
                                    </dl>
                                    <a class="y-ico y-ico-play" href="<%$movie.play_url%>" target="_blank">立即播放</a>
                                </div>
                            </div>
                    	</li>
                        <%/foreach%>
                    </ul>
                    <%Util::pager($movies.total, $count, 5)%>
                    <%else%>
                    <p style="margin: 20px 35px;">很抱歉，没有找到符合条件的结果。</p>
                    <%/if%>
                </div>
            </div>
        </div>
        <div class="y-sidebar r">
        	
            <div class="y-box-border">
            	<div class="y-box-title">
                	<a class="r" href="/movie?sort=<%$sort|escape:'url'%>">重置</a>
                    <h2>电影检索</h2>
                </div>
                <div class="y-c-box">
                	<ul class="y-list-retrieval filter">
                    	<li data-filter-name="genre">
                        	<dl>
                            	<dt><b>类型：</b></dt>
                                <dd>
                                	<a href="">全部</a>
                                	<a href="剧情">剧情</a>
                                	<a href="动作">动作</a>
                                	<a href="喜剧">喜剧</a>
                                	<a href="言情">爱情</a>
                                	<a href="惊悚">惊悚</a>
                                	<a href="犯罪">犯罪</a>
                                	<a href="奇幻">奇幻</a>
                                	<a href="科幻">科幻</a>
                                	<a href="战争">战争</a>
                                	<a href="冒险">冒险</a>
                                	<a href="动漫">动画</a>
                                	<a href="悬疑">悬疑</a>
                                	<a href="历史">历史</a>
                                	<a href="家庭">家庭</a>
                                	<a href="其他">其他</a>
                                </dd>
                        	</dl>
                    	</li>
                        <li data-filter-name="region">
                            <dl>
                            	<dt><b>国家/地区：</b></dt>
                                <dd>
                                	<a href="">全部</a>
                                	<a href="欧美">欧美</a>
                                	<a href="香港">香港</a>
                                	<a href="大陆">大陆</a>
                                	<a href="日本">日本</a>
                                	<a href="韩国">韩国</a>
                                	<a href="台湾">台湾</a>
                                	<a href="泰国">泰国</a>
                                	<a href="印度">印度</a>
                                	<a href="其他">其他</a>
                                </dd>
                        	</dl>
                    	</li>
                        <li data-filter-name="released_date">
                            <dl>
                            	<dt><b>年代：</b></dt>
                                <dd>
                                	<a href="">全部</a>
                                	<%for $year=2012 to 2005 step -1%>
                                	<a href="<%$year%>"><%$year%></a>
                                	<%/for%>
                                	<a href="其他">更早</a>
                                </dd>
                        	</dl>
                    	</li>
                    	<%if $hotStars.filter%>
                        <li class="y-last" data-filter-name="cast">
                            <dl>
                            	<dt><b>明星：</b></dt>
                                <dd>
                                	<a href="">全部</a>
                                	<%foreach $hotStars.filter as $star%>
                                	<a href="<%$star|escape:'html'%>"><%$star|escape:'html'%></a>
                                	<%/foreach%>
                                </dd>
                        	</dl>
                    	</li>
                    	<%/if%>
                    </ul>
               </div>
            </div>
            
            <div class="y-box-border">
            	<div class="y-box-title">
                	<h2>电影圈</h2>
                </div>
                <div class="y-c-box">
                	<ul class="y-list-circle y-inline-dl cfix">
                		<%foreach $hotCircles as $circle%>
                    	<li>
                        	<dl>
                            	<dd class="r">
                                	<a class="y-btn-gray y-btn-attention <%if $circle.is_focus%>followed<%else%>b-follow<%/if%>" data-action="{id:'<%$circle._id%>'}" href="#"><span></span>  
                                        <cite class="text2">已关注</cite>
                                        <cite class="text0">加关注</cite>
                                        <cite class="text1">取消关注</cite>
                                    </a>
                                </dd>
                                <dt><img src="<%#resUrl#%>/img/circle.png"></dt>
                                <dd><a href="<%Util::circleUrl($circle._id, null, $circle)%>"><%Util::utf8SubStr($circle.title, 26)%></a></dd>
                            </dl>
                        </li>
                        <%/foreach%>
                    </ul>
               </div>
            </div>
            
            <div class="y-box-border">
            	<div class="y-box-title">
                	<h2>电影明星榜</h2>
                </div>
                <div class="y-c-box">
                	<ul class="y-toplist y-dl-actor y-inline-dl cfix">
                		<%foreach $hotStars.hot as $star%>
                    	<li class="<%if $star@first%>y-toplist-one<%/if%> <%if $star@last%>y-last<%/if%>">
                        	<em class="<%if $star@index < 3%>y-f-color<%/if%>"><%$star@index+1%>.</em>
                        	<%if $star@first%>
                            <dl>
                                <dt>
                                	<a href="<%Util::starInfoUrl($star)%>" target="_blank">
                                		<img src="<%Util::videoThumbnailUrl($star.thumbnail)%>">
                                	</a>
                                </dt>
                                <dd>
                                	<h3><a href="<%Util::starInfoUrl($star)%>" target="_blank" title="<%$star.name|escape:'html'%>"><%Util::utf8SubStr($star.name, 22)%></a></h3>
                                	<p>
                                		简介：<%Util::utf8SubStr($star.desc, 46)%><br>
                                    	代表作：<%foreach $star.works as $work%><%if !$work@first%><%break%><%/if%><a href="<%Util::entityInfoUrl($work)%>" target="_blank" title="<%$work.title|escape:'html'%>"><%Util::utf8SubStr($work.title, 18)%></a><%/foreach%>
                                    </p>
                                </dd>
                            </dl>
                            <%else%>
                            <a href="<%Util::starInfoUrl($star)%>" target="_blank" title="<%$star.name|escape:'html'%>"><%Util::utf8SubStr($star.name, 38)%></a>
                            <%/if%>
                        </li>
                        <%/foreach%>
                    </ul>
               </div>
            </div>                        
        </div>
    </div>
</div>
<%/block%>

<%block name="foot_js"%>
<%Util::concatJs(['third/jquery-1.7.2.min.js', 'third/jquery/jquery-url-2.0.js', 'video/movie.js'], $smarty.config.v)%>	
<script type="text/javascript">
$(function () {
	$('ul.filter li').each(function () {
		var param = $.url().param();
		var name = $(this).attr('data-filter-name');
		var value = param[name];
		if (value == undefined) {
			value = '';
		}
		$(this).find('a').each(function () {
			if ($(this).attr('href') == value) {
				$(this).addClass('y-select');
			}
		});
	});
	
	$('ul.filter li a').click(function () {
		var param = $.url().param();
		var name = $(this).parents('li').attr('data-filter-name');
		var value = $(this).attr('href');
		param[name] = value;
		param['offset'] = 0;
		window.location.search = $.param(param);
		return false;
	});
})
</script>
<%/block%>

<%block name="custom_foot"%>
<%/block%>
