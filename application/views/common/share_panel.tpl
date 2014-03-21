<%block name="brfore_html"%>
    <%$no_brick = 1%>
<%/block%>

<!--弹出框-->
<%if !$no_brick%>
<script type="text/template" id="share_panel_html">
<%/if%>
<div class="panel panel-t1" style="width:600px;" id="<%block name="panel_id"%>{2}<%/block%>">	
	<div class="panel-content">
		<div class="hd">
			<h3><%block name="panel_title"%>{0}<%/block%></h3>
		</div>
		<div class="bd">
			<!-- content -->
			<style>
			.panel-temp-0806285482 {color:#666;margin:1em;}
			.panel-temp-0806285482 li {list-style:decimal;margin:0 0 .5em 2em;}
			</style>
			<div class="panel-temp-0806285482">
                <div class="pop_wrap">
                	<div class="poptit"><%block name="poptit"%>{1}<%/block%></div>
                    <div class="pop-module">
                        <div class="inputPlace">
                        <textarea tabindex="1" id="share_text" class="share_text textara" rows="3"><%block name="textarea"%><%/block%></textarea>
                        </div>
                        <input type="hidden" name="url" value="<%block name="url"%><%/block%>" />
                    </div>
                    <div class="submit_btn cls">
                    	<span class="share_sina submit__" title="分享到新浪微博" data-sns="sina_weibo"></span>
                        <span class="share_qq submit__" title="分享到腾讯微博" data-sns="tencent_weibo"></span>
                    </div>
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

<%if !$no_brick%>
    </script>
<%/if%>

<!--end 弹出框-->
