
<div class="y-circle-box-friends">
	<div class="y-box-title"><h2>圈主</h2></div>
    <div class="y-box-main">
    	<ul class="y-friends-list cfix y-inline-ul">
        	<li>
            	<dl>
            	<%if $creator._id == 1846037590%>
                	<dt><span>
                        <img data-id="<%$creator._id%>" src="<%Util::userAvatarUrl($creator.avatar.48, 48)%>"></span></dt>
                    <dd><span><%$creator.nick|escape:"html"%></span></dd>
                <%else%>
                	<dt><a href="<%Util::userUrl($creator._id)%>" target="_blank">
                        <img data-id="<%$creator._id%>" class="ava_popup_" src="<%Util::userAvatarUrl($creator.avatar.48, 48)%>"></a></dt>
                    <dd><a href="<%Util::userUrl($creator._id)%>" target="_blank"><%$creator.nick|escape:"html"%></a></dd>                
                <%/if%>
                </dl>
            </li>
        </ul>
    </div>
    <%if $activeFollowers%>
    <div class="y-box-title"><h2>活跃圈友</h2></div>
    <div class="y-box-main">
    	<ul class="y-friends-list cfix y-inline-ul">
        	<%foreach $activeFollowers as $activeFollower%>
        	<li>
            	<dl>
                	<dt><a href="<%Util::userUrl($activeFollower._id)%>" target="_blank">
                        <img data-id="<%$activeFollower._id%>" class="ava_popup_" src="<%Util::userAvatarUrl($activeFollower.avatar.48, 48)%>"></a></dt>
                    <dd><a href="<%Util::userUrl($activeFollower._id)%>" target="_blank"><%$activeFollower.nick|escape:"html"%></a></dd>
                </dl>
            </li>
            <%/foreach%>
        </ul>
    </div>
    <%/if%>
    <%if $followers%>
	<div class="y-box-title"><h2>所有圈友</h2></div>
    <div class="y-box-main">
    	<ul id="friends-list-all" class="y-friends-list y-friends-list-all cfix y-inline-ul">
        	<%foreach $followers as $follower%>
        	<li>
            	<dl>
                	<dt><a href="<%Util::userUrl($follower._id)%>" target="_blank">
                        <img data-id="<%$follower._id%>" class="ava_popup_" src="<%Util::userAvatarUrl($follower.avatar.48, 48)%>"></a>
                        <%if $follower.is_new%>
                        <span class="y-ico y-ico-newfriend"></span>
                        <%/if%>
                    </dt>
                    <dd><a href="<%Util::userUrl($follower._id)%>" target="_blank"><%$follower.nick|escape:"html"%></a></dd>
                </dl>
            </li>
            <%/foreach%>
        </ul>
        
        <div id="circlePager" class="pager">
            

</div>   
    </div>
    <%/if%>
</div>