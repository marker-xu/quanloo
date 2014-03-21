
<%if $circle.official == 1%>
<div id="userside" class="BoardBrick first_brick"><!-- 第一块砖头-->
    <div class="circletags">
        <div class="hd">
            <div id="xplus" class="xplus marmot xplus_tag" data--marmot="{page_id:'click_addtag'}"></div><h3>标签筛选</h3>
        </div>
        <div class="bd" id="tag_box" url_base="<%Util::circleUrl($circle._id, null, $circle)%>">
            <div class="taglist clearfix">
                <div class="tagadd clearfix" style="display: none; ">
                    <form id="formaddtag">
                        <input class="ipt-text" placeholder="输入我的tag">
                        <input class="ipt-submit" type="button" style="cursor:pointer;">
                    </form>
                </div>
                <div class="tag-item tag-sys<%if empty($cur_tag)%> tag-item-on<%/if%>">
                    <a href="<%Util::circleUrl($circle._id, null, $circle)%>"><span class="tag_link">不限</span></a>
                </div>
                <%$cust_tag_tmp=array_fill_keys((array)$cust_tag, true)%>
                <%foreach $circle.filter_tag as $v=>$w%>
                <%if ! isset($cust_tag_tmp[$v])%>
                <%*如果系统tag和用户自定义的相同，则只显示用户的*%>
                <div class="tag-item <%if $circle.creator == $login_user._id%>tag-cus<%else%>tag-sys<%/if%><%if $cur_tag == $v%> tag-item-on<%/if%>">
                    <a href="<%Util::circleUrl($circle._id, ['tag' => $v], $circle)%>">
                    	<span class="tag_link">
                        	<%if $w>30%>
                        	<img width="13px" height="12px" class="y-ico y-ico-hot" src="<%#resUrl#%>/img/b.png">
                        	<%/if%>
                        	<%$v|escape:"html"%>
                    	</span>
                    </a>
                    <%if $circle.creator == $login_user._id%>
                    <span class="del" data="<%urlencode($v)%>"></span>
                    <%/if%>
                </div>
                <%/if%>
                <%/foreach%>
                <%foreach $cust_tag as $v%>
                <div class="tag-item tag-cus<%if $cur_tag == $v%> tag-item-on<%/if%>">
                    <a href="<%Util::circleUrl($circle._id, ['tag' => $v], $circle)%>"><span class="con tag_link"><%$v|escape:"html"%></span></a>
                    <span class="del" data="<%urlencode($v)%>"></span>
                </div>
                <%/foreach%>
            </div>
        </div>
        <div class="ft"></div>
    </div>
</div>
<%/if%>
<%if $circle.user._id != 1846037590 and ($circle.video_count < 1)%>
<div style="text-align: center; margin-top: 30px; left: 268px;">
	<%if $circle.user._id==$login_user._id%>
	该圈子还没有任何视频，赶快按照提示去添加视频吧！
	<%else%>
	这里还没有任何视频，先关注Ta， 精彩视频稍后马上送上！
	<%/if%>
	<%if $circle.user._id==$login_user._id%>
	<br/><br/>
    <img src="<%#resUrl#%>/img/circle_no_video.jpg"  />
    <%/if%>
</div>
<%/if%>
<div id="waterfall" class="bd-c cls" style="position:relative; <%if $circle.user._id==$login_user._id and ($circle.video_count < 1)%>height:500px;<%/if%>">
    <!--对来自Spider的访问同步输出页面-->
    <%if Util::isSpider()%>
    <%include file="inc/spider_waterfall_4_cols.inc"%>
    <%/if%>
</div>
<div id="loader"><div class="s-ic"><em></em></div></div>
