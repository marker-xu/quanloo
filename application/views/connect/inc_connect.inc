<div class="bind-list clearfix">
	<div class="bind-list-hd">分享到：</div>
	<ul class="bind-list-bd">
		<li>
			<a id="bind-sina" class="<%if $bindlist.sina.connect_status == 1%>bt-bind-ok<%else%>bt-bind-err" href="###"></a>
			<input class="ipt-checkbox" name="type[]" value="<%Model_Data_UserConnect::TYPE_SINA%>" />
		</li>
		<li>
			<a id="bind-tqq" class="<%if $bindlist.tqq.connect_status == 1%>bt-bind-ok<%else%>bt-bind-err" href="###"></a>
			<input class="ipt-checkbox" name="type[]" value="<%Model_Data_UserConnect::TYPE_TQQ%>" />
		</li>
	</ul>
	<div class="bind-list-ft"><a href="/user/syncconnect">同步设置</a></div>
</div>