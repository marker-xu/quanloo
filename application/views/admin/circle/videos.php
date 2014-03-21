<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>
<%block name="title" prepend%>圈内视频管理<%/block%>
<%block name="custom_css"%>
<style>
<!--
#bd .cls {overflow:hidden;zoom:1;}
#bd .videolist,#bd .videooplist {background:#fff;font-size:12px;}
#bd .videolist .video-item {float:left;width:24%;border:1px #ccc solid;padding:2px;margin:0 -1px 5px 0;height:250px;}

#bd td {vertical-align:top;}
#bd .searchbox {width:250px;background:#fff;font-size:12px;}
#bd #searchres ul li {float:none;margin:0 0 5px;border-bottom:1px #ccc dotted;}
-->
</style>
<%/block%>

<%block name="main"%>
<div id="main">
	<h1>圈子：<a href="<?php echo Util::circleUrl($circle['_id'], null, $circle) ?>"><?php echo HTML::chars($circle["title"]) ?></a></h1>
	<form name="search">
    	<input type="text" value="请输入圈子名称" style="color:#999;">
    	<input type="submit" value="搜索">
	</form>
	<form name="addVideoForm" id="addVideoForm">
    	视频url或id<input type="text" name="url" value="">
    	位置<input type="text" name="pos" value="0">
    	<input type="submit" value="添加视频到圈子">
	</form>
	<p> 当前自然排序有<span style="color:red"><?php echo $vcount ?></span>个视频
	    <a href="searchVideo?id=<%$circle['_id']%>&q=<%urlencode($circle["title"])%>">添加视频</a>
	    <a href="delVideos?id=<%$circle['_id']%>">已删除视频</a>
	    <a href="">刷新本页</a>
	</p>
	<table>
		<tr>
			<td>
	<div class="videolist">
		<ul class="cls">
			<%foreach $c_videos as $v%>
			<li class="video-item">
				<p>序号:<b><%$v@index + 1%></b>
				<%if $v.is_cms%>
				<span style="color:red">（已添加）</span>
				<button type="button" class="opt-cancel" data-param="{id:'<%$v.cms_id%>',type:'cancel'}">取消</button>
				<%/if%>
				<button type="button" class="opt-del" data-param="{vid:'<%$v._id%>',type:'remove',pos:-1}">删除</button>
				</p>
				<p class="sortData">
					位置:<br/>
					<input name="sortnum" size="5" type="text" value="<%$v.position|default:0%>"/>
					<button class="opt-add" type="button" data-param="{vid:'<%$v._id%>',type:'add'}">添加</button>
				</p>
				<p><%HTML::chars($v["domain"])%></p>
				<p><img src="<%Util::videoThumbnailUrl($v["thumbnail"])%>" height="100" width="150"></img></p>
				<p><a target="_blank" href="<%Util::videoPlayUrl($v["_id"])%>"><%HTML::chars($v["title"])%></a></p>				
			</li>
			<%/foreach%>
		</ul>
	</div>	
			</td>
		</tr>
	</table>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
$(function () {
	var evalExp = function(s, opts) {
		return new Function("opts", "return (" + s + ");")(opts);
	};

	var cid = "<%$circle._id%>";
	
	$('form[name=search] input[type=text]').click(function () {
		if (!$(this).data('clicked')) {
			$(this).val('').css('color', '').data('clicked', true);
		}
	});
	var keyword = $.url().param('keyword');
	if (keyword) {
		$('form[name=search] input[type=text]').click().val(keyword);
	}
	$('form[name=search]').submit(function () {
		var param = $.url().param();
		if ($(this).find('input[type=text]').data('clicked')) {
			window.location.href = '/admin_circle?keyword='+encodeURIComponent($(this).find('input[type=text]').val());
		}
		return false;
	});

	$('#addVideoForm').submit(function () {
		var objForm = $('#addVideoForm');
		var url = $.trim(objForm.find('input[name=url]').val());
		var pos = objForm.find('input[name=pos]').val();
		var vid = '';
		if (! url) {
			return false;
		} else if (url.indexOf('http://') > -1) {
			var objPattern = /\/v\/(.*)\.html/;
			var arrMatch = url.match(objPattern);
			if (arrMatch[1]) {
				vid = arrMatch[1];
			}
		} else if (url.indexOf('http://') == -1) {
			vid = url;
		}
		if (! vid) {
			return false;
		} 
		
		var param = {pos: pos, cid: cid, vid: vid, type:'add'};
		$.post('/admin_circle/videoOpt',param,function(res){
			if(res.err ==='ok'){
				alert('添加成功')
				location.reload();
			}else{
				alert('刷新重试！')
			}
		});
		
		return false;
	});

	$('.videolist').delegate('.opt-del', 'click', function(e){ 
		e.preventDefault();
		var cfm = window.confirm('确定禁止该视频在圈子中出现？');
		var $el = $(this);
		var param = evalExp($el.attr('data-param'));
		param['cid'] = cid;
		if(cfm){
			$.post('/admin_circle/videoOpt',param,function(res){
				if(res.err ==='ok'){
					$el.attr('disabled', 'disabled');
					$el.html('删除成功');
				}else{
					alert('刷新重试！')
				}
			});
		}
	});

	$('.videolist').delegate('.opt-cancel', 'click', function(e){ 
		e.preventDefault();
		var cfm = window.confirm('确定从CMS删除？');
		var $el = $(this);
		var param = evalExp($el.attr('data-param'));
		if(cfm){
			$.post('/admin_circle/videoOpt',param,function(res){
				if(res.err ==='ok'){
					$el.attr('disabled', 'disabled');
					$el.html('取消成功');
				}else{
					alert('刷新重试！')
				}
			});
		}
	});

	$('.videolist').delegate('.opt-add', 'click', function(e){ 
		e.preventDefault();
		var $el = $(this);
		var param = evalExp($el.attr('data-param'));
		param['pos'] = $el.parents('.sortData').find('input[name=sortnum]').val();
		param['cid'] = cid;
		$.post('/admin_circle/videoOpt',param,function(res){
			if(res.err ==='ok'){
				$el.attr('disabled', 'disabled');
				$el.html('添加成功');
			}else{
				alert('刷新重试！')
			}
		});
	});
});
</script>
<%/block%>
