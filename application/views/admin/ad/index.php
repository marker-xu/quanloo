<%extends file="common/adminbase.tpl"%>

<%block name="custom_css"%>
<style>
<!--
#bd table tbody tr td:last-child a {
	margin: 0 5px;
}
-->
</style>
<%/block%>

<%block name="main"%> 
<%function name=_ad_list_part_status intCurTime=0%>
    <%if $v.ad_endtime <= $intCurTime%>过期
    <%else%><%Model_Logic_Adconst::$AD_STATUS_LIST[$v.ad_status]%><%/if%>
<%/function%>       
<div id="main"> 
	<h1>广告管理-<%$cur_adpos_nick|escape:"html"%></h1>
	<p id="msg_err" style="color:red"></p>
	<div style="overflow: hidden;">
    	<form name="search" style="float: left;" method="get" id="form_search">
    	    广告位：<%html_options name='cur_adpos_id' options=$adpos_map selected=$cur_adpos_id%>
        	<input type="submit" value="搜索">
    	</form>
    	<p class="batch-op" style="float: right; height: 26px; line-height: 26px;">
    	    <a href="/admin_ad/add?cur_adpos_id=<%$cur_adpos_id%>">添加广告</a>
    	</p>
	</div>
	<table class="hover" id="table_list">
		<thead>
			<tr>
    			<th>编号</th>
    			<%if $cur_adpos_id < 1%><th>广告位</th><%/if%>
    			<th>广告内容</th>
    			<th>生效时间</th>
    			<th>到期时间</th>
    			<th>状态</th>
    			<th>操作</th>
			</tr>
		</thead>
		<tbody>
			<%foreach $ad_list as $v%>
			<tr>
				<td><%$v@index + 1%></td>
				<%if $cur_adpos_id < 1%><td><%$adpos_map[$v.ad_pos]%></td><%/if%>
				<td>
					标题：<%Util::utf8SubStr($v.ad_mat.ad_title, 150)%><br>
					图片：<img src="<%Util::webStorageClusterFileUrl($v.ad_mat.ad_pic)%>" width="200" height="146"><a href="<%Util::webStorageClusterFileUrl($v.ad_mat.ad_pic)%>" target="_blank">查看原始图</a><br>
					跳转链接：<a href="<%$v.ad_mat.ad_url%>" target="_blank"><%Util::utf8SubStr($v.ad_mat.ad_url, 100)%></a>
				</td>
				<td><%date('Y-m-d H:i:s', $v['ad_starttime'])%></td>
				<td><%date('Y-m-d H:i:s', $v['ad_endtime'])%></td>
				<td><%call name=_ad_list_part_status intCurTime=$smarty.server.REQUEST_TIME%></td>
				<td class="op">
				    <input class="buttonop" type="button" value="审核通过" data-url="/admin_ad/modStatus?id=<%urlencode($v['_id'])%>&ad_status=<%Model_Logic_Adconst::AD_STATUS_VALID%>">
				    <input class="buttonop" type="button" value="暂停广告" data-url="/admin_ad/modStatus?id=<%urlencode($v['_id'])%>&ad_status=<%Model_Logic_Adconst::AD_STATUS_PAUSE%>">
				    <a href="/admin_ad/mod?id=<%urlencode($v['_id'])%>" target="_blank">编辑</a>
				    <input class="buttonop" type="button" value="删除" data-confirm="确定要删除这条广告信息吗" data-url="/admin_ad/delete?id=<%urlencode($v['_id'])%>">
				</td>
			</tr>
			<%/foreach%>
		</tbody>
	</table>
	<%include file="inc/pager.inc" count=$pager.count offset=$pager.offset total=$pager.total%>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
var evalExp = function(s, opts) {
	return new Function("opts", "return (" + s + ");")(opts);
};
$(function () {
	$('.buttonop').click(function () {
		var w = $(this);
		var strConfirm = w.attr('data-confirm');
		if (strConfirm) {
			var bolAnswer = confirm(strConfirm);
			if (! bolAnswer) {
				return;
			}
		}
		w.attr('disabled', 'disabled');
		var url = w.attr('data-url');
		var param = w.attr('data-param');
		if (param) {
			param = evalExp(param);
		}
		$.getJSON(url, param, function (res) {
			w.removeAttr('disabled');
			if (res.err == 'ok') {
				w.attr('disabled', 'disabled');
				w.attr('value', '操作成功');
			} else {
				window.alert(res.msg);
			}
		});
	});
});
</script>
<%/block%>