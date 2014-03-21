<%*翻页 eg*%>

<%*include file="inc/pager.inc" count=$count offset=$offset total=$user_circle_list.count*%>

<%foreach $smarty.get as $each_get%>
    <%$query_arr.$each_get@key = $each_get%>
<%/foreach%>

<%$totalPn = ceil($total/$count)%>
<%$currPn = max(min(ceil((($offset|default:0)) /$count+1), $totalPn), 1)%>

<%if strpos($smarty.server.REQUEST_URI, '?')%>
    <%$thisPguri = substr($smarty.server.REQUEST_URI, 0, strpos($smarty.server.REQUEST_URI, '?'))%>
<%else%>
    <%$thisPguri = $smarty.server.REQUEST_URI%>
<%/if%>
<%$pageUrl = $smarty.config.siteUrl|cat:$thisPguri%>

<%function name="page" pn=1%>    
    <%strip%>
    <%$param_arr = $query_arr%>
    <%$param_arr.offset = ($pn -1)*$count%>
    <%*$param_arr.pn = $pn*%>
    <%$pageUrl%>?<%http_build_query($param_arr)%>
    <%/strip%>
<%/function%>

<%if $total > $count%>
<%strip%>
    <div class="pager">
        <%if $currPn > 1%>
            <a href="<%page pn=$currPn - 1%>" class="pager-pre">&lt;上一页</a>
        <%else%>
        	<span class="pager-pre">&lt;上一页</span>
        <%/if%>

        <%if $currPn == 1%>
            <em>1</em>
        <%else%>
            <a href="<%page pn=1%>">1</a>
        <%/if%>
            
        <%if $currPn > 7%>
            ...
        <%/if%>

        <%section name=p start=max(($currPn - 5), 2) loop=$totalPn max = 10%>
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
                <em><%$totalPn%></em>
            <%else%>
                <a href="<%page pn=$totalPn%>"><%$totalPn%></a>
            <%/if%>
        <%/if%>

        <%if $currPn != $totalPn%>
            <a href="<%page pn=$currPn + 1%>" class="pager-next">下一页&gt;</a>
        <%else%>
        	<span class="pager-next">下一页&gt;</span>
        <%/if%>
    </div>
<%/strip%>
<%/if%>