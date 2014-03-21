<%extends file="common/base.tpl"%>

<%block name="title" prepend%>未找到<%$smarty.get.q|escape:'htmlall'%>相关结果<%/block%>

<%block name="view_conf"%>    
<%/block%>

<%block name="custom_css"%>
    <link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/search.css?v=<%#v#%>">
    <style type="text/css">
        #searchBar .cc-tips, #searchBar .btn-guanzhu {display:none;}
        #search-aera .search-words .search-tips {display:block;}
        #search-aera input {left:215px;}
        #search-aera button {left:577px;}
    </style>
<%/block%>

<%block name="bd"%>
<%foreach $smarty.get as $each_get%>
    <%$query_arr.$each_get@key = $each_get|escape:'html'%>
<%/foreach%>
<div id="bd">
    <%include file="inc/search.inc" is_so_page=1%>
    <div id="search-noresult">
        <div id="noresult-tips">
            <div class="tips-text">
                <h1>抱歉，没有找到与<em><%Util::utf8SubStr($smarty.get.q, 50)%></em>相关的视频<%if $search_result.query_correct%>，你要找的是不是：<a href="<%#siteUrl#%>/search/?q=<%$search_result.query_correct|escape:'url'%>"><%$search_result.query_correct|escape:'html'%></a><%/if%></h1>
                <p>建议你：</p>
                <ul>
                    <li>检查输入的文字是否有误。</li>
                    <li>缩短关键词。</li>
                    <li>使用相近、相同或其他语义的关键词。</li>
                    <li>放宽搜索条件。</li>
                </ul>                
            </div>
            <%if $relation_querys%>
            <div class="related">
                <div class="hd">
                    <h2>相关推荐</h2>
                </div>
                <div class="bd">
                    <ul class="sr-items" id="sr-list">
                    	<%foreach $relation_querys as $query%>
                    	<li class="sr-item"><a title="<%$query|escape:'html'%>" href="<%#siteUrl#%>/search/?q=<%$query|escape:'url'%>"><%$query|escape:'html'%></a></li>
                    	<%/foreach%>
                    </ul>
                </div>
            </div>
            <%/if%>
        </div>
        <div class="nav">
            <ul class="cls">
                <li class="on"><a href="<%#siteUrl#%>/guesslike">你可能喜欢的圈子</a></li>
                <li class="change"><a href="<%#siteUrl#%>/guesslike" id="guesslike">（换一批）</a></li>
            </ul>
            <div class="more">
                <a href="<%#siteUrl#%>/guesslike">更多&gt;&gt;</a>
            </div>
        </div>
        <div id="likecircles" data-jss="offset:0" class="video-ret-list">
        </div>
    </div>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
Dom.ready(function(){
    QW.use('Ajax', function(){
        function getCircles(){
            var offset = W('#likecircles').jss('offset');
            Ajax.get('/guesslike/likecircles',{offset : offset, size : 4}, function(d){
                if(d.err == 'ok'){
                    W('#likecircles').html(d.data.html);
                    W('#likecircles').jss('offset', offset+=4);
					//INVITE_FRIEND( W('#likecircles') );        
                    //SHARE_GROUP( W('#likecircles') );
                }
            });
        }
        getCircles();
        W('#guesslike').click(function(e){
            e.preventDefault();
            getCircles();
        });
    });
});
</script>
<%/block%>