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
		<%include file="inc/search.inc" is_so_page=1%>
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
                		<div class="y-search-tag-tab">                		
                    	<ul class="y-inline-ul cfix">
                        	<li><a href="<%#siteUrl#%>/search?q=<%$query_arr.q|escape:'url'%>" style="color:#000">搜索到的视频</a></li>
                       		<li class="y-select"><a href="<%#siteUrl#%>/search/circle?q=<%$query_arr.q|escape:'url'%>" style="color:#000">搜索到的圈子(<%$search_result.total%>)</a></li>                                
                   		</ul>
                   		</div>
                	</div>
                <!--//搜索视频和圈子切换 -->
                </div>
                <!-- 搜索结果 -->
                     <%include file="guesslike/topcircles.php" circle_list=$search_result.circles circle_ul_class="list clearfix y-inline-ul cfix"%>
                     <%include file="inc/pager.inc" count=16 offset=$query_arr.offset total=$search_result.total%>
                <!--//搜索结果 -->
            </div>
        </div>
    </div>
</div>
<%/block%>

<%block name="foot_js"%>
<%Util::concatJs(['waterfall.js', 'ejs.js'], $smarty.config.v, 'video/')%>
<script type="text/javascript">
Dom.ready(function(){

});
</script>
<%/block%>