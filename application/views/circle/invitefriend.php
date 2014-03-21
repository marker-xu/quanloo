<div class="panel panel-t1" style="width:600px;" id="invite_friend_popup">
	
	<div class="panel-content">
		<div class="hd">
			<h3>邀请好友加入
			<a href="<%Util::circleUrl($circle_info._id, null, $circle_info)%>"><%$circle_info.title|escape:"html"%>圈</a>
			</h3>
		</div>
		<div class="bd">
			
			<!-- content -->
			<style>
			.panel-temp-0806285482 {color:#666;margin:1em;}
			.panel-temp-0806285482 li {list-style:decimal;margin:0 0 .5em 2em;}
			</style>
			<div class="panel-temp-0806285482">
                <div class="pop_wrap">
                    <div class="pop-module">
                        <div class="tit">链接邀请</div>
                        <div class="tips">通过QQ、MSN、电子邮件发送邀请链接给你的朋友</div>
                        <div class="inputPlace" style="height:35px;">
                            <input type="text" tabindex="1" name="link_url" class="text link_url" value="<%$invite_code%>"> 
                            <a class="popBtn_a copy_link" href="#"><span>复制链接</span></a>
                        </div>
                    </div>
                    
                    <div class="pop-module">
                        <div class="tit">邀请邮箱联系人</div>
                        <div class="inputPlace itemList">
                            <div class="item"><input type="text" tabindex="2" class="text email_url"></div>
                            <div class="item"><input type="text" tabindex="3" class="text email_url"></div>
                            <div class="item"><input type="text" tabindex="4" class="text email_url"><span class="addOne">加一个</span></div>
                        </div>
                    </div>
                    
                    <div class="submit_btn cls"><a href="#" class="popBtn_a done submit__"><span>发送邀请</span></a></div>
                    <input type="hidden" name="id" value="<%$circle_info._id%>" />
                </div>
			</div>
			<!-- //content -->

		</div>
		<div class="ft"></div>
	</div>

	<span class="co1"><span></span></span>
	<span class="co2"><span></span></span>
	<span class="cue"></span>
	<span class="sd"></span>
	<span class="close close__"></span>
	<span class="resize"></span>

</div>
