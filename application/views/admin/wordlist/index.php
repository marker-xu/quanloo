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
<div id="main"> 
	<h1>词表管理-<%$cur_tbname_nick|escape:"html"%>(共<%$pager.total%>个词)</h1>
	<p id="msg_err" style="color:red"></p>
	<div style="overflow: hidden;">
    	<form name="search" style="float: left;" method="get" id="form_search">
    	    词表名：<%html_options name='tbname' options=$wordlist_map selected=$cur_tbname%>
        	词长度<input name="word_len" type="text" value="<%$smarty.get.word_len|default:0%>" style="color:#999;">
        	关键词<input name="word" type="text" value="<%$smarty.get.word|default:''|escape:"html"%>" style="color:#999;">
        	<input id="input_orderby" name="orderby" type="hidden" value="<%$orderby%>">
        	<input id="input_orderseq" name="orderseq" type="hidden" value="<%$orderseq%>">
        	<input type="submit" value="搜索">
    	</form>
    	<p class="batch-op" style="float: right; height: 26px; line-height: 26px;">
    	    <a href="/admin_wordlist/add">添加关键词</a>
			<input type="button" id="publish_link" data-url="/admin_wordlist/publish?tbname=<%urlencode($cur_tbname)%>" value="发布词表">
    	</p>
	</div>
	<table class="hover" id="table_wordlist">
		<thead>
			<tr>
    			<th class="th-order" data-param="word"><a href="#">关键词</a></th>
    			<th class="th-order" data-param="word_len"><a href="#">长度</a></th>
    			<th>添加者</th>
    			<th class="th-order" data-param="addtime"><a href="#">添加时间</a></th>
    			<th>操作</th>
			</tr>
		</thead>
		<tbody>
			<%foreach $words as $v%>
			<tr>
				<td><%$v['word']|escape:"html"%></td>
				<td><%$v['word_len']%></td>
				<td><%$v['adder_nick']|escape:"html"%></td>
				<td><%date('Y-m-d H:i:s', $v['addtime'])%></td>
				<td class="op">
				    <input class="buttonop" type="button" value="删除" data-url="/admin_wordlist/delete?id=<%urlencode($v['_id'])%>">
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
		var url = w.attr('data-url');
		var param = w.attr('data-param');
		if (param) {
			param = evalExp(param);
		}
		$.getJSON(url, param, function (res) {
			if (res.err == 'ok') {
				w.attr('disabled', 'disabled');
			} else {
				window.alert(res.msg);
			}
		});
	});
	
	$('#publish_link').click(function () {
		var w = $(this);
		var url = w.attr('data-url');
		w.attr('disabled', 'disabled');
		$.getJSON(url, function (res) {
			if (res.err == 'ok') {
				$('#msg_err').html("词表发布成功");				
			} else {
				$('#msg_err').html(res.msg);
			}
			w.removeAttr('disabled');
		});
		return false;
	});
	
	$('.th-order').click(function () {
		var w = $(this);
		var objOrderby = $('#input_orderby');
		var objOrderseq = $('#input_orderseq');
		var strOrderby = w.attr('data-param');
		if (objOrderby.val() == strOrderby) {
			//同一个排序字段，每次点击只是切换排序方向
			if (objOrderseq.val() == 1) {
				objOrderseq.val('-1');
			} else {
				objOrderseq.val('1');
			}
		} else {
			objOrderby.val(strOrderby);
		}
		$('#form_search').submit();
	});
});
</script>
<%/block%>