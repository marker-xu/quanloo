<%extends file="common/base.tpl"%>

<%block name="seo_meta"%>
<meta name="keywords" content="电视剧,电视剧在线观看,内地电视剧,日韩电视剧,欧美电视剧,港台电视剧,电视剧全集,电视剧排行榜" />
<meta name="description" content="圈乐电视剧页，汇聚来自各网站的各类热播电视剧，精彩电视剧尽收眼底。" />
<%/block%>

<%block name="title" prepend%>全网热播、优质电视剧尽在圈乐电视剧页<%/block%>

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
                                <%foreach $hotTvs as $tv%>
                                <li >
                                  <dl class="y-v-box y-vbox-2">
                                    <dt>
                                    	<a href="<%Util::entityInfoUrl($tv)%>" target="_blank"><img src="<%Util::videoThumbnailUrl($tv.thumbnail)%>"></a>
                                    </dt>
                                    <dd class="vbox-text">
                                    	<cite><%$tv.score%></cite>
                                    	<h3><a href="<%Util::entityInfoUrl($tv)%>" target="_blank" title="<%$tv.title|escape:'html'%>"><%$tv.title|escape:'html'%></a></h3>
                                    </dd>
                                    <dd>类型：<%Util::utf8SubStr($tv.genre, 12)%></dd>
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
                            <li class="<%if $sort == 'new'%>y-select<%/if%>"><a href="/tv">最新剧集</a></li>
                            <li class="<%if $sort == 'hot'%>y-select<%/if%>"><a href="/tv?sort=hot">最热剧集</a></li>
                        </ul>
                    </div>
                    <%if $tvs.data%>
                    <ul>
                        <%foreach $tvs.data as $tv%>
                    	<li>
                        	<div class="y-videoinfo-box cfix">
                                <div class="l">
                                    <a href="<%Util::entityInfoUrl($tv)%>" target="_blank"><img src="<%Util::videoThumbnailUrl($tv.thumbnail)%>"></a>
                                </div>
                                <div class="r js-tv-msg">
                                    <h3><a href="<%Util::entityInfoUrl($tv)%>" target="_blank" title="<%$tv.title|escape:'html'%>"><%$tv.title|escape:'html'%></a></h3>
                                    <%if $tv.score!=''%>
                                    <dl class="y-score-box y-inline-dl cfix">
                                        <dd><p style="width:<%Util::scoreToStar($tv.score)%>%;"></p></dd>
                                        <dt><%$tv.score%></dt>
                                    </dl>
                                    <%/if%>
                                    <dl class="y-videoinfo-text">
                                        <%if $tv.genre%><dt>类型：<%$tv.genre%></dt><%/if%>
                                        <%if $tv.cast_list%>
                                          <dd class="y-videoinfo-protagonist">
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
                                        <dd>
                                        	<%if $tv.desc%>剧情：<%Util::utf8SubStr($tv.desc, 136)%><%/if%>
                                        	<a href="<%Util::entityInfoUrl($tv)%>" target="_blank">详细信息&gt;&gt;</a>
                                        </dd>
                                    </dl>
                                    <dl class="y-videoinfo-warehouse y-inline-dl cfix">
                                        <dd>
                                            <%Util::episodesPager($tv.episode, 4, $tv.finished)%>
                                        </dd>
                                        <%if count($tv.episode) > 9%>
                                        <dt><a href="#" class="js-show-all-episode">显示全部&gt;&gt;</a></dt>
                                        <%/if%>
                                    </dl>
                                </div>
                                <dl class="y-videoinfo-warehouse-width-min y-inline-dl cfix js-tv-hidden-msg" style="display:none;">
                                    <dd>
                                        <%Util::episodesPager($tv.episode, 100, $tv.finished)%>
                                    </dd>
                                </dl>
                            </div>
                    	</li>
                        <%/foreach%>
                    </ul>
                    <div class="episode-pager-all">
                    <%Util::pager($tvs.total, $count, 5)%>
                    </div>
                    <%else%>
                    <p style="margin: 20px 35px;">很抱歉，没有找到符合条件的结果。</p>
                    <%/if%>
            	</div>
            </div>
        </div>
        <div class="y-sidebar r">
        	
            <div class="y-box-border">
            	<div class="y-box-title">
                	<a class="r" href="/tv?sort=<%$sort|escape:'url'%>">重置</a>
                    <h2>电视剧检索</h2>
                </div>
                <div class="y-c-box">
                	<ul class="y-list-retrieval filter">
                    	<li data-filter-name="genre">
                        	<dl>
                            	<dt><b>类型：</b></dt>
                                <dd>
                                	<a href="">全部</a>
                                	<a href="言情">言情</a>
                                	<a href="都市">都市</a>
                                	<a href="古装">古装</a>
                                	<a href="偶像">偶像</a>
                                	<a href="剧情">剧情</a>
                                	<a href="悬疑">悬疑</a>
                                	<a href="喜剧">喜剧</a>
                                	<a href="历史">历史</a>
                                	<a href="警匪">警匪</a>
                                	<a href="家庭">家庭</a>
                                	<a href="动作">动作</a>
                                	<a href="其他">其他</a>
                                </dd>
                        	</dl>
                    	</li>
                        <li data-filter-name="region">
                            <dl>
                            	<dt><b>国家/地区：</b></dt>
                                <dd>
                                	<a href="">全部</a>
                                	<a href="大陆">大陆</a>
                                	<a href="日本">日本</a>
                                	<a href="韩国">韩国</a>
                                	<a href="香港">香港</a>
                                	<a href="美国">美国</a>
                                	<a href="台湾">台湾</a>
                                	<a href="英国">英国</a>
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
                	<h2>电视剧圈</h2>
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
                	<h2>电视剧明星榜</h2>
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
