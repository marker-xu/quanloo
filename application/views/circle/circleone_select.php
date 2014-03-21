<ul class="circle_list <%if !$circle_list%>noscroll<%/if%>">
    <li class="<%if !$circle_list%>selectItem<%/if%>"><a href="###" cid="0">请选择</a></li>
    <%if $circle_list%>
    <%foreach $circle_list as $row%>
    <li class="<%if $circle_one._id==$row._id%>selectItem<%/if%>"><a href="###" cid="<%$row._id%>" title="<%$row.title|escape:"html"%>"><%Util::utf8SubStr($row.title, 21)%></a></li>
    <%/foreach%>
    <%/if%>
</ul>
