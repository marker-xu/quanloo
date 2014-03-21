<%*翻页*%>

<%foreach $smarty.get as $each_get%>
    <%$query_arr.$each_get@key = $each_get|escape:'html'%>
<%/foreach%>

<%$count = 30%>
<%$maxTotal = 3000%>
<%$total = min($search_result.real_total, $maxTotal)%>
<%$totalPn = ceil($total/$count)%>
<%$currPn = max(min(ceil((($query_arr.offset|default:0)) /$count+1), $totalPn), 1)%>

<%function name="page" pn=1%>
    <%strip%>
    <%$param_arr = $query_arr%>
    <%$param_arr.offset = ($pn -1)*$count%>
    <%*$param_arr.pn = $pn*%>
    <%#siteUrl#%><%substr($smarty.server.REQUEST_URI, 0, strpos($smarty.server.REQUEST_URI, '?'))%>?<%http_build_query($param_arr)%>
    <%/strip%>
<%/function%>

<%if $total > $count%>
    <div class="pager">
        <%if $currPn > 1%>
            <a href="<%page pn=$currPn - 1%>" class="pager-pre">&lt;上一页</a>
        <%/if%>

        <%if $currPn == 1%>
            <em>1</em>
        <%else%>
            <a href="<%page pn=1%>">1</a>
        <%/if%>
            
        <%if $currPn > 7%>
            ...
        <%/if%>

        <%section name=p start=max(($currPn - 5), 2) loop=$totalPn max = 15%>
            <%if $smarty.section.p.index == $currPn%>
                <em><%$smarty.section.p.index%></em>
            <%else%>
                <a href="<%page pn=$smarty.section.p.index%>"><%$smarty.section.p.index%></a>
            <%/if%>
        <%/section%>

        <%*
        <%if $currPn < $totalPn - 4%>
            ...
        <%/if%>
        *%>
        
        <%if $currPn >= $totalPn - 4%>
            <%if $currPn == $totalPn%>
                <%$totalPn%>
            <%else%>
                <a href="<%page pn=$totalPn%>"><%$totalPn%></a>
            <%/if%>
        <%/if%>

        <%if $currPn != $totalPn%>
            <a href="<%page pn=$currPn + 1%>" class="pager-next">下一页&gt;</a>
        <%/if%>
    </div>
<%/if%>