<%block name="brfore_html"%>
    <%$no_brick = 1%>
<%/block%>
	<div class="panel-content">
		<div class="hd">
			<h3><%block name="title"%><%/block%></h3>
		</div>
		<div class="bd">
			<div class="panel-sns-share">
                <form name="openidshare" id="openidshare" action="/connect/share" method="post">
                	<div class="bind-list clearfix">
						<div class="bind-list-hd">分享到：</div>
						<ul class="bind-list-bd">
							<li>
								<a id="bind-sina" class="<%if $bindlist.sina.connect_status == 1 and ($bindlist.sina.expire_time>$smarty.server.REQUEST_TIME)%>bt-bind-slt<%else%>bt-bind-err<%/if%>" href="###" data-title="新浪微博"></a>
								<input type="checkbox" class="ipt-checkbox" name="type[]" value="<%Model_Data_UserConnect::TYPE_SINA%>" <%if $bindlist.sina.connect_status == 1 and ($bindlist.sina.expire_time>$smarty.server.REQUEST_TIME)%>checked="checked"<%/if%> />
							</li>
							<li>
								<a id="bind-tqq" class="<%if $bindlist.tqq.connect_status == 1%>bt-bind-slt<%else%>bt-bind-err<%/if%>" href="###" data-title="腾讯微博"></a>
								<input type="checkbox" class="ipt-checkbox" name="type[]" value="<%Model_Data_UserConnect::TYPE_TQQ%>" <%if $bindlist.tqq.connect_status == 1%>checked="checked"<%/if%> />
							</li>
						</ul>
						<div class="bind-list-ft"><a href="/user/syncconnect" target="_blank">同步设置</a></div>
					</div>
					<div class="bind-share-fm">
                        <div class="bind-share-fm-bd">
                        	<textarea name="content" class="content" rows="3"><%block name="content"%><%/block%></textarea>
                        	<input type="hidden" name="picurl" value="<%block name="picurl"%><%/block%>" />
                        	<p class="tips">你还可以输入<b id="textlength">140</b>字</p>
                        </div>
                        
                        <div class="bind-share-fm-ft cls">
	                    	<button class="submit" type="submit">分享</button>
	                    	<button class="cancel close__" type="button">取消</button>
	                    </div>
	                </div>
                </form>
			</div>
			<!-- //content -->

		</div>
		<div class="ft"></div>
	</div>
	<span class="close close__"></span>