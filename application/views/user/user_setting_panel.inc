<ul class="menu cls">
<%foreach $panel_list as $tmpPanel=>$row%>
<%if $tmpPanel eq $panel_action%>
<li class="selected"><span><%$row.name%></span></li>
<%else%>
<li><span><a href="<%$row.url%>"><%$row.name%></a></span></li>
<%/if%>
<%/foreach%>
                        <li class="clear_r"><span>&nbsp;</span></li>
                    </ul>