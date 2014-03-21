<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>

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
	<h1>候选圈子管理</h1>
	<a href="/admin_circlecandidate/add">新建候选圈子</a>
	<div style="overflow: hidden;">
    	<form name="search" style="float: left;">
        	<input type="text" value="请输入圈子名称" style="color:#999;">
        	<input type="submit" value="搜索">
    	</form>
    	<p class="batch-op" style="float: right; height: 26px; line-height: 26px;">
    		批量操作：
			<a href="/admin_circlecandidate/batchop?op=audit&status=1" class="audit">通过</a>
			<a href="/admin_circlecandidate/batchop?op=audit&status=2" class="audit">拒绝</a>
			<a href="/admin_circlecandidate/batchop?op=delete" class="delete">删除</a>
    	</p>
	</div>
	<table class="hover">
		<thead>
			<tr>
    			<th><input type="checkbox"></th>
    			<th><a href="" name="title">名称</a></th>
    			<th>
    				<select name="category">
    					<option value="-1">类别
    					<?php foreach (Model_Data_Circle::$adminCategorys as $key => $value) { ?>
						<option value="<?php echo $key ?>" <?php echo Request::initial()->param('category', -1) == $key ? 'selected' : '' ?>><?php echo $value ?>
						<?php } ?>
    				</select>
				</th>
    			<th width="20%">Tag</th>
    			<th>
    				<select name="source">
    					<option value="-1">来源
    					<?php foreach (Model_Data_CircleCandidate::$sources as $key => $value) { ?>
						<option value="<?php echo $key ?>" <?php echo Request::initial()->param('source', -1) == $key ? 'selected' : '' ?>><?php echo $value ?>
						<?php } ?>
    				</select>
    			</th>
    			<th><a href="" name="submitted_count">提交次数</a></th>
    			<th><a href="" name="video_count">视频数</a></th>
    			<th><a href="" name="search_count">搜索次数</a></th>
    			<th>创建者</th>
    			<th><a href="" name="create_time">创建时间</a></th>
    			<th>
    				<select name="status">
    					<option value="-1">状态
    					<?php foreach (Model_Data_CircleCandidate::$statuses as $key => $value) { ?>
						<option value="<?php echo $key ?>" <?php echo Request::initial()->param('status', -1) == $key ? 'selected' : '' ?>><?php echo $value ?>
						<?php } ?>
    				</select>
    			</th>
    			<th>操作</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($circles as $circle) { ?>
			<tr>
				<td><input type="checkbox" value="<?php echo $circle['_id'] ?>"></td>
				<td>
					<a href="/search?q=<?php echo urlencode($circle['title']) ?>" target="_blank"><?php echo HTML::chars($circle['title']) ?></a>
				</td>
				<td>
            		<?php foreach ($circle['category'] as $category) { ?>
            		<span><?php echo Model_Data_Circle::$adminCategorys[$category].' ' ?></span>
					<?php } ?>
				</td>
				<td><?php echo HTML::chars(implode(', ', $circle['tag'])) ?></td>
				<td><?php echo Model_Data_CircleCandidate::$sources[$circle['source']] ?></td>
				<td><?php echo isset($circle['submitted_count']) ? $circle['submitted_count'] : '' ?></td>
				<td><?php echo isset($circle['video_count']) ? $circle['video_count'] : '' ?></td>
				<td><?php echo isset($circle['search_count']) ? $circle['search_count'] : '' ?></td>
				<td><?php echo $circle['creator'] ?></td>
				<td title="<?php echo date('Y-m-d H:i:s', $circle['create_time']->sec) ?>">
				    <?php echo date('Y-m-d', $circle['create_time']->sec) ?>
				</td>
				<td><?php echo Model_Data_CircleCandidate::$statuses[$circle['status']] ?></td>
				<td class="op">
					<a href="/admin_circlecandidate/mod?id=<?php echo $circle['_id'] ?>" class="mod">修改</a>
					<?php if (in_array($circle['status'], array(Model_Data_CircleCandidate::STATUS_PENDING, 
					    Model_Data_CircleCandidate::STATUS_REFUSED))) { ?>
					<a href="/admin_circlecandidate/batchop?op=audit&ids=<?php echo $circle['_id'] ?>&status=<?php echo Model_Data_CircleCandidate::STATUS_APPROVED ?>" class="change-status">通过</a>
					<?php } ?>
					<?php if (in_array($circle['status'], array(Model_Data_CircleCandidate::STATUS_PENDING))) { ?>
					<a href="/admin_circlecandidate/batchop?op=audit&ids=<?php echo $circle['_id'] ?>&status=<?php echo Model_Data_CircleCandidate::STATUS_REFUSED ?>" class="change-status">拒绝</a>
					<?php } ?>
					<a href="/admin_circlecandidate/del?id=<?php echo $circle['_id'] ?>" class="del">删除</a>
					<input type="hidden" name="id" value="<?php echo $circle['_id'] ?>">
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php echo $pagination ?>
</div>
<div id="search-iframe-wrap" style="position:fixed;display:none;overflow:hidden;background:#fff">
    <iframe id="search-iframe" src="about:blank" style="width:1000px;height:550px;border:2px solid #cc0"></iframe>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
$(function () {
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
			param['keyword'] = $(this).find('input[type=text]').val();
			window.location.search = $.param(param);
		}
		return false;
	});
	
	$('table th select').change(function () {
		var param = $.url().param();
		param[$(this).attr('name')] = $(this).val();
		param['page'] = 1;
		window.location.search = $.param(param);
	});
	$('table th a').click(function () {
		var param = $.url().param();
		if (!param['orderby']) {
			param['orderby'] = 'create_time';
			param['direction'] = -1;
		}
		var direction;
		if ($(this).attr('name') == param['orderby']) {
			direction = -param['direction'];
		} else {
			if ($(this).attr('name') == 'title') {
				direction = 1;
			} else {
				direction = -1;
			}
		}
		param['orderby'] = $(this).attr('name');
		param['direction'] = direction;
		param['page'] = 1;
		window.location.search = $.param(param);
		return false;
	});
	$('table th input:checkbox').change(function () {
		$('table td input:checkbox').prop('checked', $(this).prop('checked'));
	});
	
	$('.change-status').click(function () {
		var href = $(this).attr('href');
		$.getJSON(href, function (res) {
			if (res.errno == 0) {
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		});
		return false;
	});
	$('.op .del').click(function () {
		var href = $(this).attr('href');
		if (window.confirm('确认删除？')) {
			$.getJSON(href, function (res) {
				if (res.errno == 0) {
					window.location.reload();
				} else {
					window.alert(res.error);
				}
			});
		}
		return false;
	});
	$('.batch-op a').click(function () {
		if ($(this).attr('class') == 'delete' && !window.confirm('确认删除？')) {
			return false;
		}
		var ids = [];
		$('table tbody input:checked').each(function (index, ele) {
			ids.push($(ele).val());
		});
		var url = $(this).url();
		url.param()['ids'] = ids.join(',');
		$.getJSON(url.attr('path')+'?'+$.param(url.param()), function (res) {
			if (res.errno == 0) {
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		});
		return false;
	});

    $('table td:nth-child(2) a').mouseenter(function(e) {
        var _this = this;
        $('body').data('search-iframe-show-timer', setTimeout(function() {
            clearTimeout($('body').data('search-iframe-show-timer'));
            $('#search-iframe').attr('src', 'http://' + window.location.host + '/search?q=' + encodeURIComponent($(_this).text()) + '#search-ret-tips');
			var offset = $(_this).parent().offset();
			if ($(window).height() > $('#search-iframe').height()) {
				var top = ($(window).height() - $('#search-iframe').height()) / 2;
			} else {
				var top = 0;
			}
			var left = offset.left + $(_this).parent().innerWidth();
            $('#search-iframe-wrap').css({
                'top' : top + 'px', 
                'left' : left + 'px'
            }).show();
        }, 500));
    }).mouseleave(function(e){
        clearTimeout($('body').data('search-iframe-show-timer'));
        $('body').data('search-iframe-hide-timer', setTimeout(function() {
            $('#search-iframe-wrap').hide();
        }, 500));
    });
    $('#search-iframe-wrap').mouseenter(function(e) {
        clearTimeout($('body').data('search-iframe-hide-timer'));
    }).mouseleave(function(e){
        $('body').data('search-iframe-hide-timer', setTimeout(function() {
            $('#search-iframe-wrap').hide();
        }, 500));
    });
});
</script>
<%/block%>