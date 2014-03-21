<%if $circle_list%>
<ul class="list clearfix">
    <%foreach $circle_list as $row%>
    <li class="circleitem circleitem-t1">
        <div class="hd">
            <h3>
                <label><a href="<%Util::circleUrl($row._id)%>" title="<%$row.title|escape:'html'%>"><%Util::utf8SubStr($row.title,21)%></a></label>
                <input type="checkbox" value="<%$row._id%>" style="display:none;" name="circle_ids[]" checked />
            </h3>
        </div>
        <div class="bd">
            <a href="<%Util::circleUrl($row._id)%>">
                <img src="<%Util::circlePreviewPic($row.tn_path)%>" alt="<%$row.title|escape:"html"%>" />
            </a>
        </div>
        <div class="ft clearfix">
            <a class="btn b-follow user_complete_watch" style="width:215px;" is-focus="" data-action="{'id':'<%$row._id%>'}" href="#">
                <span class="text0">关注</span>
                <span class="text1">取消关注</span>
                <span class="text2">取消关注</span>
            </a>
        </div>
    </li>
    <%/foreach%>
</ul>
<%else%>
<center><a href="/guesslike" class="watchmore"></a></center>
<style>
    #circle_parent{
        height:360px;
    }
    .page-nav{
        display:none;
    }
</style>
<%/if%>