<div id="searchBar">
            <div id="search"<%if $has_circle%> class="has-circle"<%/if%>>
                <form id="search-form" action="<%#siteUrl#%>/search" method="get">
                    <fieldset>
                        <legend>搜索</legend>
                        <!-- 搜索框区域 -->                            
                            <span class="search-tips">请输入你感兴趣的主题、关键词</span>                          
                            <span class="search-wd">
                                <input name="q" id="search-text" class="text" maxlength="100" placeholder="请输入你感兴趣的主题、关键词" x-webkit-speech x-webkit-grammar="builtin:search"<%if $is_so_page%> value="<%$smarty.get.q|escape:'html'%>"<%/if%>>
                            </span>
                            <button type="submit" class="submiters"><span>搜索</span></button>                            
                        <!--//搜索框区域 -->
                    </fieldset>
                </form>                
            </div>
            <%if $has_circle%>
                <div class="cc-tips">
                    <span id="video-ret-total"><%$search_result.total%></span>
                    <a href="###" id="btn-guanzhu" class="marmot" data--marmot="{page_id:'click_searchconcern',query:'<%$smarty.get.q|escape:'html'%>'}" data-kw="<%$circle.title|escape:'html'%>"></a> 
                </div>                
            <%/if%>
            <div class="hotSearch" id="hotsearchkeys"></div>
</div>
<script type="text/javascript">
(function(){
    /*
    QW.use('Ajax', function(){
        QW.Ajax.get(QW.Config.get('host') + '/search/hotkeywords', {
            count : 5
        }, function(d){
            if(d.err == 'ok'){
                var keywords = [];
                d.data.forEach(function(k){
                    keywords.push('<a href="' + QW.Config.get('host') + '/search?q=' + encodeURIComponent(k) + '">' + QW.StringH.encode4Html(k) + '</a>');
                });
                W('#hotsearchkeys').html('热门搜索：' + keywords.join('<span>|</span>'));            
            }
        });
    });
    */       
})();
</script>