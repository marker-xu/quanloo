<%extends file="common/base.tpl"%>

<%block name="title" prepend%><%$smarty.get.q|escape:'htmlall'%><%/block%>

<%block name="view_conf"%>    
<%/block%>

<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/search.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>
<%/block%>

<%block name="bd"%>
<%foreach $smarty.get as $each_get%>
<%$query_arr.$each_get@key = $each_get|escape:'html'%>
<%/foreach%>
<div id="bd">
    <div id="search-pg">
        <%if $circle%>
            <%include file="inc/search.inc" has_circle=1 is_so_page=1%>
        <%else%>
            <%include file="inc/search.inc" is_so_page=1%>
        <%/if%>
        <%*
        <p id="hot-search">
            <strong>热搜关键词：</strong>            
            <%foreach $hot_keywords as $hot%>
            <a href="<%#siteUrl#%>/search/?q=<%$hot|escape:'url'%>" title="查看&quot;<%$hot|escape:'html'%>&quot;相关视频搜索结果"><%$hot|escape:'html'%></a>
            <%/foreach%>
        </p>
        *%>     
        <div id="search-ret" class="cls">
            <div class="main-top">
            	<div class="y-search-tag-box">
	                <!-- 搜索视频和圈子切换 -->									
        			<div class="y-box-titile">
            			<div class="y-search-tag-tab<%if $circle_result.total < 1%> y-search-tag-tab-no<%/if%>">
                        	<ul class="y-inline-ul cfix">
                            	<li class="y-select"><a href="<%#siteUrl#%>/search?q=<%$query_arr.q|escape:'url'%>" >搜索到的视频</a></li>
                            	<li><a href="<%#siteUrl#%>/search/circle?q=<%$smarty.get.q|escape:'url'%>"<%if $circle_result.total < 1%> onclick="return false"<%/if%>>搜索到的圈子(<%$circle_result.total%>)</a></li>
                        	</ul>
                 		</div>
            		</div>	            
	                <!--//搜索视频和圈子切换 -->
	                <!-- 搜索选项 -->
	                <div class="y-box-main">
	                	<a href="<%#siteUrl#%>/search?q=<%$query_arr.q|escape:'url'%>" class="y-search-tag-rest">重置选项</a>
	                    <%function name="searchQuery" replace_arr=[]%>
	                        <%strip%>
	                        <%$param_arr = $smarty.get%>
	                        <%foreach $replace_arr as $get%>
	                        <%$param_arr.$get@key = $get%>
	                        <%/foreach%>
	
	                        <%$param_arr.offset = 0%>
	                        <%#siteUrl#%>/search?<%http_build_query($param_arr)%>
	
	                        <%/strip%>
	                    <%/function%>
	                    <%function name="defaultFliter" type='' data=[] q='' notlast=1%>
	                        <li<%if ! $notlast%> class="y-last"<%/if%>>
	                        	<dl class="cfix tag_filter">
	                            <dt class="<%$q%>"><%$type%>：</dt>
	                            <dd><a href="<%searchQuery replace_arr=[$q => '']%>"<%if !$query_arr.$q && $query_arr.$q !== '0'%> class="y-select"<%/if%>>不限</a></dd>
	                            <%foreach $data as $item%>
	                            <%if $item.enable%>
	                            <dd><a href="<%searchQuery replace_arr=[$q => $item@key]%>"<%if isset($query_arr.$q) && $query_arr.$q === (string) $item@key%> class="y-select"<%/if%>><%$item.name|escape:'html'%></a></dd>
	                            <%else%>
	                            <dd><a href="###" class="disable <%if isset($query_arr.$q) && $query_arr.$q === (string) $item@key%>y-select<%/if%>" onclick="return false"><%$item.name|escape:'html'%></a></dd>
	                            <%/if%>
	                            <%/foreach%>
	                            </dl>
	                        </li>
	                    <%/function%>
	                        <ul class="y-search-tag-list y-inline-dl">
	                            <%defaultFliter type='清晰度' data=$quality q='quality'%>
	                            <%defaultFliter type='时长' data=$length q='length'%>
	                            <%defaultFliter type='类型' data=$category q='category'%>
	                            <%defaultFliter type='来源' data=$domain q='domain' notlast=$tag%>
	                        <%if $tag%>
	                            <li class="y-last">
                        			<dl class="cfix tag_filter">
                            			<dt>标签过滤：</dt>
                            				<dd><a href="<%searchQuery replace_arr=['tag' => '']%>"<%if !$query_arr.tag%> class="y-select"<%/if%>>不限</a></dd>
                            			<%foreach $tag as $item%>	
                            				<dd><a href="<%searchQuery replace_arr=['tag' => $item.md5]%>"<%if $query_arr.tag == $item.md5%> class="y-select"<%/if%>><%$item.tag|escape:'html'%></a></dd>
                            			<%/foreach%>
                        			</dl>                    
                    			</li>
	                        <%/if%>
	                        </ul>
	                </div>
	                <!--//搜索选项 -->
                </div>
            </div>
            <div class="ret-corret">
                <p id="search-ret-tips">
                    <%if $search_result.total%>
                    经过过滤和去重，为你找到以下“<%Util::utf8SubStr($smarty.get.q, 50)%>”的搜索结果。
                    <%/if%>
                    <%if $search_result.query_correct%>你要找的是不是：<a href="<%#siteUrl#%>/search/?q=<%$search_result.query_correct|escape:'url'%>"><%$search_result.query_correct|escape:'html'%></a><%/if%>
                    <%if $search_result.real_total%>
                </p>
                <p class="orderby-sort">
                    排序：
                    <%if $query_arr.sort == 'time'%>
                    <a href="<%searchQuery replace_arr=['sort' => 'relevance']%>">按相关性</a>
                    <span>按时间</span>                    
                    <%else%>                    
                    <span>按相关性</span>
                    <a href="<%searchQuery replace_arr=['sort' => 'time']%>">按时间</a>
                    <%/if%>
                </p>
                <%/if%>
            </div>
            <div class="main<%if !$search_result.entitys%> main-w<%/if%>">                
                <!-- 搜索结果 -->
                <div id="video-ret">                    
                    <div class="video-ret-list">
                        <!--视频瀑布-->
                    <div id="waterfall" class="bd-c cls" style="position:relative;">
                    	<%foreach $search_result.videos as $video%>
                    	<div class="BoardBrick">
                    		<div class="circleName s-ic"></div>
                    		<div class="vod m-pic tp-a cls">
                    			<a class="a marmot" href="<%Util::videoPlayUrl($video._id)%>" target="_blank" data--marmot="{page_id: 'click_search', item_src: '<%$video.domain|escape:'html'%>', item_id: '<%$video._id%>', item_pos: '<%$video@index+1%>', url:'http://<%$smarty.server.HTTP_HOST|escape:'url'%><%$smarty.server.REQUEST_URI|escape:'url'%>', item_list:''}">
                    				<img class="h brick_img" alt="<%$video.title|escape:'html'%>" src="<%Util::videoThumbnailUrl($video.thumbnail)%>">
                    				<div class="action m-pic-s" style="display: none;">
                                        <span data-action="{id:'<%$video._id%>'}" class="newadd" title="添加到我的收藏"></span>
                                        <span class="sharer video_share_" data-sns="{id:'<%$video._id%>', title:'<%$video.title|escape:'html'%>', image: '<%Util::videoThumbnailUrl($video.thumbnail)%>'}" title="分享给我的好友"></span>
                                        <span video-id="<%$video._id%>" class="circle_down addCircle forgroup" title="添加到我创建的圈子">圈一下</span> 
                                     </div>
                    				<span class="ico"></span>
                    			</a>
                    			<span class="time"><%Util::sec2time($video.length)%></span>
                    			<div class="tit">
                    				<a class="marmot" href="<%Util::videoPlayUrl($video._id)%>" target="_blank" title="<%$video.title|escape:'html'%>" data--marmot="{page_id: 'click_search', item_src: '<%$video.domain|escape:'html'%>', item_id: '<%$video._id%>', item_pos: '<%$video@index+1%>', url:'http://<%$smarty.server.HTTP_HOST|escape:'url'%><%$smarty.server.REQUEST_URI|escape:'url'%>', item_list:''}"><%$video.highlight_title%> </a>
                    			</div>
                    			<div class="from"><span>来源：</span><%$video.domain|escape:"html"%></div>
                    		</div>
                    	</div>
                    	<%/foreach%>
                    </div>
                    <!--视频瀑布-->
                    <%*include file="search/pager.inc"*%>
                    <%include file="inc/pager.inc" count=20 offset=$query_arr.offset total=$search_result.real_total%>

                    <%*无结果*%>
                    <%if !$search_result.real_total%>
                    <div id="fliter-noresult">
                        <h2>抱歉，没有符合条件的视频</h2>
                        <div class="search-suggest">
                            <p>建议你：</p>
                            <ul>
                                <li>调整搜索选项，放宽筛选条件</li>
                            </ul>
                        </div>
                        <div class="return-links">
                            <p>
                                <a href="<%#siteUrl#%>" onclick="window.history.go(-1);return false">&lt;&lt;返回上一步</a>
                            </p>
                        </div>
                    </div>
                    <%/if%>
                    <%*/无结果*%>

                    <!-- 相关搜索 -->
            		<%if $relation_querys%>
                    <div class="search-related">
                        <h3>相关搜索</h3>
                        <ul class="sr-items" id="sr-list">
                        	<%foreach $relation_querys as $query%>
                        	<li class="sr-item"><a title="<%$query|escape:'html'%>" href="<%#siteUrl#%>/search/?q=<%$query|escape:'url'%>"><%$query|escape:'html'%></a></li>
                        	<%/foreach%>
                        </ul>
                    </div>
            		<%/if%>
                    <!--//相关搜索 -->
                </div>
            </div>
            <!--//搜索结果 -->
        </div>
        <%if $search_result.entitys%>
        <div class="side">
            <%$entitys = $search_result.entitys%>
            <ul id="special-ret">
                <%foreach $entitys as $v%>
                <%if $v@index < 2%>
                <li class="s-ret-item<%if !$v@index%> first<%/if%>">
                    <%$items = [
                    'gender'        => '性别',
                    'birthday'      =>'生日',
                    'director'      => '导演',
                    'cast'          => '主演',
                    'region'        => '地区',
                    'genre'         => '类型',
                    'released_date' => '上映时间',
                    'length'        => '时长'
                    ]%>
                    <%function name="info"%>
                    <ul class="info">
                        <%foreach $items as $item%>
                        <%if $v.$item@key%>
                        <li><strong><%$item%>：</strong><%($v.$item@key)|escape:'html'%></li>
                        <%/if%>
                        <%/foreach%>
                    </ul>
                    <%/function%>
                    <%function name="playlink" data=[]%>
                    <%if $data.episode%>
                    <ol class="s-episodes cls">
                        <%foreach $data.episode as $ei%>
                        <%if $ei@index < 23%>
                        <li><a href="<%$ei.play_url%>" target="_blank"><%$ei.order%></a></li>
                        <%/if%>
                        <%/foreach%>
                        <%if $data.episode|count >= 25%>
                        <li><a href="<%$data.episode[23].play_url%>" target="_blank">...</a></li>
                        <li><a href="<%$data.episode[($data.episode|count) -1].play_url%>" target="_blank"><%$data.episode[($data.episode|count)-1].order%></a></li>
                        <%/if%>
                    </ol>
                    <%else%>
                    <a href="<%$data.play_url%>" target="_blank" class="special-play">播放</a>
                    <%/if%>
                    <%/function%>
                    <%if $v.entity_type != 'star'%>
                    <%*视频实体库*%>
                    <div class="special-video">
                        <h3><a href="<%Util::entityInfoUrl($v)%>" target="_blank"><%$v.title|escape:'html'%></a></h3>
                        <a href="<%Util::entityInfoUrl($v)%>" target="_blank" class="s-cover">
                            <span class="s-video-type s-<%$v.entity_type%>"></span>
                            <img src="<%Util::videoThumbnailUrl($v.thumbnail)%>" alt="<%$v.title|escape:'html'%>">
                        </a>
                        <%playlink data = $v%>
                        <%info%>
                        <div class="summary">
                            <p>
                            	<strong>剧情简介：</strong><%Util::utf8SubStr($v.desc, 140)%>
                            	<a href="<%Util::entityInfoUrl($v)%>" target="_blank">详细信息&gt;&gt;</a>
                            </p>
                        </div>
                        <%if $v.domain%><p class="from">来源：tudou.com</p><%/if%>
                    </div>
                    <%*//视频实体库*%>
                    <%else%>
                    <%*人物实体库*%>
                    <div class="special-peple">
                        <img src="<%Util::videoThumbnailUrl($v.thumbnail)%>" alt="<%$v.name|escape:'html'%>">
                        <h3><%$v.name|escape:'html'%></h3>
                        <%info%>
                        <div class="summary">
                            <p><%Util::utf8SubStr($v.desc, 150)%></p>
                        </div>
                        <%if $v.works%>
                        <div class="s-related-video">
                            <h4>相关作品</h4>
                            <%foreach $v.works as $work%>
                            <%if $work@index < 3%>
                            <!-- 视频实体库 -->
                            <div class="special-video">
                                <a href="<%Util::entityInfoUrl($work)%>" target="_blank"><h3><%$work.title|escape:'html'%></h3></a>
                                <a href="<%Util::entityInfoUrl($work)%>" target="_blank" class="s-cover">
                                    <span class="s-video-type s-<%$work.entity_type%>"></span>
                                    <img src="<%Util::videoThumbnailUrl($work.thumbnail)|escape:'html'%>" alt="<%$work.title|escape:'html'%>">
                                    <%if $work.finished == 'yes'%>
                                    <span class="howmany">共<%$work.episode_num%>集全</span><span class="howmany-bg"></span>
                                    <%/if%>
                                </a>
                                <%playlink data = $work%>
                            </div>
                            <!--//视频实体库 -->
                            <%/if%>
                            <%/foreach%>
                        </div>
                        <%/if%>
                    </div>
                    <%*//人物实体库*%>
                    <%/if%>
                </li>
                <%/if%>
                <%/foreach%>
            </ul>
        </div>
        <%/if%>
    </div>
</div>
</div>
<%/block%>

<%block name="foot_html"%>
<div class="panel panel-t1" id="panel-follow" style="width:760px;top:130px">
    <div class="panel-content">
        <div class="hd">
            <h3 class="panel-title">加关注</h3>
        </div>
        <div class="bd">
            <div class="pop_wrap cls">
                <p class="follow-circle-tips">你已成功关注“<strong id="circlename"></strong>”圈，在“<a href="<%#siteUrl#%>/user">个人主页</a>”你可以方便管理你关注的所有圈子</p>
                <div class="add-feed-circle">                    
                    <div class="circleitem circleitem-t1">
                        <div class="hd">
                            <h3><a href="#" id="circletitle" target="_blank"></a></h3><span class="type type-t0"></span>
                        </div>
                            <div class="bd">
                            <a href="#" id="circleUrl" target="_blank">
                            <img src="" id="tn_path">
                            </a>
                        </div>
                        <div class="ft clearfix">
                            <a class="btn followed" href="#" id="circle-follow-btn">
                                <span class="text0">关注</span>
                                <span class="text1" style="padding-left:1em">已关注</span>
                                <span class="text2">取消关注</span>
                            </a>
                        </div>                        
                    </div>
                </div>
                <div class="guess-like">
                    <h4 id="guess-title"></h4>
                    <ol id="guess-list">
                    </ol>
                </div>
            </div>
        </div>
        <div class="ft clearfix">
            <span class="x-close"></span>
        </div>
    </div>
    <span class="co1"><span></span></span>
    <span class="co2"><span></span></span>
    <span class="sd"></span>
    <span class="close"></span>
</div>
<%/block%>

<%block name="foot_js"%>
<%Util::concatJs(['ejs.js', 'search.js'], $smarty.config.v, 'video/')%>
<script type="text/javascript">
Dom.ready(function(){ 
    /*记录 搜索项tag过滤log*/
    W(".tag_filter a").on("click",function(){
        var tag = W(this).html();
        QW.Marmot.log({page_id:"click_searchtag",tag:tag,query:W("#search-text").val()});
    });
});
</script>

<script type="text/template" id="share_panel_html">
<div class="panel panel-t1" style="width:600px;" id="share_video_popup"> 
    <div class="panel-content">
        <div class="hd">
            <h3>分享视频</h3>
        </div>
        <div class="bd">            
            <!-- content -->
            <style>
            .panel-temp-0806285482 {color:#666;margin:1em;}
            .panel-temp-0806285482 li {list-style:decimal;margin:0 0 .5em 2em;}
            </style>
            <div class="panel-temp-0806285482">
                <div class="pop_wrap">
                    <div class="poptit">把这个视频分享给站外好友</div>
                    <div class="pop-module">
                        <div class="inputPlace">
                        <textarea tabindex="1" id="share_text" class="textara" rows="3">//@圈乐网：【在线视频：{0}】更多视频，等你发现！请点击链接观看：{1}</textarea>
                        </div>
                        <input type="hidden" name="url" value="http://www.quanloo.com" />
                    </div>
                    
                    <div class="submit_btn cls">
                        <span class="share_sina submit__" title="分享到新浪微博" data-sns="sina_weibo"></span>
                        <span class="share_qq submit__" title="分享到腾讯微博" data-sns="tencent_weibo"></span>
                    </div>
                </div>
            </div>
            <!-- //content -->
        </div>
        <div class="ft"></div>
    </div>
    <span class="co1"><span></span></span>
    <span class="co2"><span></span></span>
    <span class="cue"></span>
    <span class="sd"></span>
    <span class="close close__"></span>
    <span class="resize"></span>
</div>
</script>

<%/block%>

<%block name="custom_foot"%>
<script type="text/marmot">
<%$item_list = []%>
<%foreach $search_result.videos as $item%>
    <%$_a = array_push($item_list, $item._id|cat:':0')%>
<%/foreach%>

<%if $smarty.get.quality%>
    <%$quality_stat = ($quality[$smarty.get.quality|escape:'html'])%>
<%else%>
    <%$quality_stat = ''%>
<%/if%>

<%if $smarty.get.length%>
    <%$length_stat = ($length[$smarty.get.length|escape:'html'])%>
<%else%>
    <%$length_stat = ''%>
<%/if%>

<%if $smarty.get.domain%>
    <%$domain_stat = ($domain[$smarty.get.domain|escape:'html'])%>
<%else%>
    <%$domain_stat = ''%>
<%/if%>

<%$category_id = $smarty.get.category|escape:'html'%>
{
"page_id"        : "search",
"query"          : "<%$smarty.get.q|escape:'html'%>",
"query_src"      : "<%$smarty.get.query_src|escape:'html'|default:'0'%>",
"result_set_size": "<%$search_result.real_total%>",
"page_num"       : "<%(ceil(($smarty.get.offset|escape:'html')/30)) + 1%>",
"page_size"      : "<%count($search_result.videos)%>",
"category"       : "<%$category.$category_id%>",
"aspect"         : "清晰度:<%$quality_stat%>,时长:<%$length_stat%>,来源:<%$domain_stat%>,tag:<%$smarty.get.tag|escape:'html'|default:''%>",
"sort_id"        : "<%$smarty.get.sort|default:'relevance'|escape:'html'%>",
"item_list"      : "<%join(',', $item_list)%>"
}
</script>
<%/block%>
