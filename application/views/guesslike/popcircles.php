	<!--圈子弹出框-->
    <div class="popup_group" id="popup_group">
        <div class="pg_header">
            <span>挑选一批你感兴趣的圈子，关注它们，收获更多有趣的视频</span>
            <span class="pg_right">
                <label for="closely_all">关注全部</label><input type="checkbox" id="closely_all" class="closely_all" />
            </span>
        </div>
        
        <div class="pg_mid">
            <ul class="list clearfix">
            <%foreach $circle_list as $row%>
				<li class="circleitem circleitem-t2">
					<div class="hd">
						<h3>
                            <label><a href="<%Util::circleUrl($row._id, null, $row)%>" title="<%$row.title|escape:'html'%>"><%Util::utf8SubStr($row.title,21)%></a></label>
                            <input type="checkbox" value="<%$row._id%>"  />
                        </h3>
					</div>
					<div class="bd">
						<a href="<%Util::circleUrl($row._id, null, $row)%>">
						<img src="<%Util::circlePreviewPic($row.tn_path)%>" alt="<%$row.title|escape:"html"%>" />
						</a>
					</div>
				</li>
			 <%/foreach%>
			</ul>
        </div>
        <div class="pg_footer">
            <div class="pg_subbox"> 
                <button type="button" class="pg_done submit__"></button> <a href="#" class="pg_later cancel__">以后再说</a>
            </div>
        </div>
    </div>    
    <!--end 圈子弹出框-->
