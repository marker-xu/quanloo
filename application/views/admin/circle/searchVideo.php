<%extends file="common/adminbase.tpl"%>

<%block name="title" prepend%><%$smarty.get.q|escape:'htmlall'%><%/block%>

<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/search.css?v=<%#v#%>">
<%/block%>

<%block name="main"%>
<%foreach $smarty.get as $each_get%>
<%$query_arr.$each_get@key = $each_get|escape:'html'%>
<%/foreach%>
    <div id="main">
    	<h1>圈子：<a href="<?php echo Util::circleUrl($circle['_id'], null, $circle) ?>"><?php echo HTML::chars($circle["title"]) ?></a> <a href="./videos?id=<%$circle['_id']%>" target="_blank">预览结果</a></h1>
		<div id="searchBar">
            <div id="search">
                <form id="search-form" action="#" method="get">
                    <fieldset>
                        <legend>搜索</legend>
                        <!-- 搜索框区域 -->                            
                            <span class="search-tips">请输入你感兴趣的主题、关键词</span>                          
                            <span class="search-wd">
                                <input name="q" id="search-text" class="text" maxlength="100" placeholder="请输入你感兴趣的主题、关键词" x-webkit-speech x-webkit-grammar="builtin:search" value="<%$smarty.get.q|escape:'html'%>">
                            </span>
                            <button type="submit" class="submiters"><span>搜索</span></button>                            
                        <!--//搜索框区域 -->
                    </fieldset>
                    <input name="id" value="<%$circle._id%>" type="hidden">
                </form>                
            </div>
		</div>
        
        <div id="search-ret" class="cls">
            <div class="main-top">
                <!-- 搜索选项 -->
                <div id="search-ctrl" class="search-ctrl-open">
                     <div class="hd">
                        <h3>搜索选项</h3>
                        <a href="?id=<%$circle._id%>&q=<%$query_arr.q|escape:'url'%>" class="reset-search">重置选项</a>
                    </div>
                    <div class="bd">
                        <%function name="searchQuery" replace_arr=[]%>
                        <%strip%>
                        <%$param_arr = $smarty.get%>
                        <%foreach $replace_arr as $get%>
                        <%$param_arr.$get@key = $get%>
                        <%/foreach%>

                        <%$param_arr.offset = 0%>
                        ?<%http_build_query($param_arr)%>

                        <%/strip%>
                        <%/function%>
                        <%function name="defaultFliter" type='' data=[] q=''%>
                        <li>
                            <em class="<%$q%>"><%$type%>：</em>
                            <a href="<%searchQuery replace_arr=[$q => '']%>"<%if !$query_arr.$q && $query_arr.$q !== '0'%> class="on"<%/if%>><em>不限</em></a>
                            <%foreach $data as $item%>
                            <%if $item.enable%>
                            <a href="<%searchQuery replace_arr=[$q => $item@key]%>"<%if isset($query_arr.$q) && $query_arr.$q === (string) $item@key%> class="on"<%/if%>><em><%$item.name|escape:'html'%></em></a>
                            <%else%>
                            <a href="###" class="disable <%if isset($query_arr.$q) && $query_arr.$q === (string) $item@key%> on<%/if%>" onclick="return false"><%$item.name%></a>
                            <%/if%>
                            <%/foreach%>
                        </li>
                        <%/function%>
                        <ul id="search-fliter-list">
                            <%defaultFliter type='清晰度' data=$quality q='quality'%>
                            <%defaultFliter type='时长' data=$length q='length'%>
                            <%defaultFliter type='类型' data=$category q='category'%>
                            <%defaultFliter type='来源' data=$domain q='domain'%>
                            <%if $tag%>
                                <li class="tag_filter">
                                    <em>标签过滤：</em>
                                    <a href="<%searchQuery replace_arr=['tag' => '']%>"<%if !$query_arr.tag%> class="on"<%/if%>><em>不限</em></a>
                                    <%foreach $tag as $item%>
                                    <a href="<%searchQuery replace_arr=['tag' => $item]%>"<%if $query_arr.tag == $item%> class="on"<%/if%>><em><%$item|escape:'html'%></em></a>
                                    <%/foreach%>
                                </li>
                            <%/if%>
                        </ul>
                    </div>
                    <a class="toggle-search-ctrl" href="###" style="display:none"></a>
                </div>
                <!--//搜索选项 -->
            </div>
            <div class="ret-corret">
                <p id="search-ret-tips">
                    <%if $search_result.total%>
                    经过过滤和去重，为你找到以下“<%Util::utf8SubStr($smarty.get.q, 50)%>”的搜索结果。
                    <%/if%>
                    <%if $search_result.query_correct%>你要找的是不是：<a href="?q=<%$search_result.query_correct|escape:'url'%>"><%$search_result.query_correct|escape:'html'%></a><%/if%>
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
            <div class="main main-w">                
                <!-- 搜索结果 -->
                <div id="video-ret">                    
                    <div class="video-ret-list">
                        <!--视频瀑布-->
                    <div id="waterfall" class="bd-c cls" style="position:relative;">
                    	<%foreach $search_result.videos as $video%>
                    	<div class="BoardBrick">
                    		<div class="circleName s-ic"></div>
                    		<div class="vod m-pic tp-a cls">
                    			<a class="a" href="<%Util::videoPlayUrl($video._id)%>" target="_blank">
                    				<img class="h brick_img" alt="<%$video.title|escape:'html'%>" src="<%Util::videoThumbnailUrl($video.thumbnail)%>">
                    				<span class="ico"></span>
                    			</a>
                    			<span class="time"><%Util::sec2time($video.length)%></span>
                    			<div class="tit">
                    				<a class="" href="<%Util::videoPlayUrl($video._id)%>" target="_blank" title="<%$video.title|escape:'html'%>"><%$video.highlight_title%> </a>
                    			</div>
                    			<div class="tit">
                    				<span style="color:#ff3c00"><%$video.recommend_reason.reason_text|escape:"html"%></span>
                    			</div>
                    			<div class="tit">
                    				播放:<%$video.watched_count%> 分享:<%$video.shared_count%>
                    			</div>
                    			<div class="tit">
                    				心情:<%$video.mooded_count.total%> 清晰度:<%$quality[$video.quality].name%>
                    			</div>                    			
                    			<div class="tit">
                    				<button type="button" class="addnew" data-vid="<%$video._id%>">添加</button>
                    			</div>                    			
                    		</div>
                    	</div>
                    	<%/foreach%>         	
                    </div>
                    <!--视频瀑布-->
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
                </div>
            </div>
            <!--//搜索结果 -->
        </div>
    </div>
</div>
<%/block%>

<%block name="foot_js"%>
<%Util::concatJs(['ejs.js', 'search.js'], $smarty.config.v, 'video/')%>
<<script type="text/javascript">
var circle_id = "<%$circle._id%>";
$(function () {
	$('#waterfall').delegate('.addnew', 'click', function(e){ 
		e.preventDefault();
		var $el = $(this);
		var param = {
			'pos':0,
			'cid':circle_id,
			'vid':$el.attr('data-vid'),
			'type':'add'
		};
		
		$.post('/admin_circle/videoOpt',param,function(res){
			if(res.err ==='ok'){
				$el.attr('disabled', 'disabled');
				$el.html('已添加');
			}else{
				alert('刷新重试！')
			}			
		});	
	});
});
</script>
<%/block%>
