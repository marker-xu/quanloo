<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>

<%block name="custom_css"%>
<style>
<!--
#bd dl {overflow: auto;}
#bd dl dt {float: left; text-align: right; width: 100px;}
#bd dl dd {float: left;}
#bd dl dd input {margin-left: 5px;}
-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1>圈内视频过滤Tag管理</h1>
	<form name="search">
    	<input type="text" value="请输入圈子名称" style="color:#999;">
    	<input type="submit" value="搜索">
	</form>
	<h2>圈子：<a href="<?php echo Util::circleUrl($circle['_id'], null, $circle) ?>" target="_blank"><?php echo HTML::chars($circle['title']) ?></a></h2>
	<div class="box" style="margin: 10px 0pt;">
		<p>当前视频过滤Tag：</p>
		<dl style="height: 200px;" class="tag">
		    <?php foreach ($circle['filter_tag'] as $dimension) { ?>
			<dt>
			    <input type="checkbox" value="<?php echo HTML::chars($dimension['name']) ?>" /> <?php echo HTML::chars($dimension['name']) ?>：
			</dt>
			<dd style="width: 830px;">
				<?php foreach ($dimension['tag'] as $tag) { ?>
				<input type="checkbox" value="<?php echo HTML::chars($tag) ?>" /> <?php echo HTML::chars($tag) ?>
				<?php } ?>
			</dd>
		    <?php } ?>
		</dl>
    	<button type="button" class="delete-tag">删除所选Tag</button>
    	<button type="button" class="move-tag-left">前移所选Tag</button> <input type="text" value="1" size="2" /> 位
    	<button type="button" class="move-tag-right">后移所选Tag</button> <input type="text" value="1" size="2" /> 位
	</div>
	<div style="overflow: hidden;">
		<div class="box" style="width: 468px; float: left;">
    		<p>候选视频过滤Tag：</p>
    		<dl style="height: 200px;" class="candidate-tag">
    		    <?php foreach ($circle['filter_tag_candidate'] as $dimension => $tags) { ?>
    			<dt>
    			    <input type="checkbox" value="<?php echo HTML::chars($dimension) ?>" /> <?php echo HTML::chars($dimension) ?>：
    			</dt>
    			<dd style="width: 350px;">
    				<?php foreach ($tags as $tag) { ?>
    				<input type="checkbox" value="<?php echo HTML::chars($tag) ?>" /> <?php echo HTML::chars($tag) ?>
    				<?php } ?>
    			</dd>
    		    <?php } ?>
    		</dl>
    		<p>
    			手工输入：<input type="text" name="manualInputTag" class="manual-input-tag" /> 多个Tag间以“,”号分隔。
    		</p>
		</div>
		<div style="float: left; width: 150px; margin: 110px 0pt 0pt; text-align: center;">
			<button type="button" class="add-tag">将选中Tag添加到</button>
		</div>
		<div class="box" style="float: left; width: 318px; height: 263px;">
			<p>当前维度：</p>
			<div>
    			<div style="float: left;">
            		<select size="11" style="width: 135px;" class="select-dimension">
            			<?php foreach ($circle['filter_tag'] as $dimension) { ?>
            			<option value="<?php echo HTML::chars($dimension['name']) ?>" <?php echo (isset($dimension['default']) && $dimension['default']) ? 'selected="selected"' : '' ?>>
            			<?php echo HTML::chars($dimension['name']) ?>
            			<?php echo (isset($dimension['default']) && $dimension['default']) ? '(默认)' : '' ?>
            			<?php } ?>
            		</select>
    			</div>
    			<div style="float: left; margin-left: 5px;">
        			<p style="margin: 10px 0pt;">
        				<button type="button" class="set-default-dimension">设为默认</button>
        			</p>
        			<p style="margin: 10px 0pt;">
        				<button type="button" class="delete-dimension">删除维度</button>
        			</p>
        			<p><button type="button" class="move-dimension-up">上移维度</button> <input type="text" name="moveUpAmount" value="1" size="2" /> 位</p>
        			<p><button type="button" class="move-dimension-down">下移维度</button> <input type="text" name="moveDownAmount" value="1" size="2" /> 位</p>
        			<p style="margin: 10px 0pt;">
        				<input type="text" name="addDimensionName" size="8" /> <button type="button" class="add-dimension">新增维度</button>
        			</p>
    			</div>
			</div>
		</div>
	</div>
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
			window.location.href = '/admin_circle?keyword='+encodeURIComponent($(this).find('input[type=text]').val());
		}
		return false;
	});
	
	$('input:checked').prop('checked', false);
	$('dl dt input:checkbox').change(function () {
		$(this).parent().next().find('input:checkbox').prop('checked', $(this).prop('checked'));
	});
	
	$('.add-dimension').click(function () {
		var name = $(this).prev().val();
		$.post('/admin_circle/adddimension', {
			id: $.url().param('id'), 
			name: name
		}, function (res) {
			if (res.errno == 0) {
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		}, 'json');
	});
	$('.set-default-dimension').click(function () {
		var name = $('.select-dimension').val();
		if (!name) {
			window.alert('请选择维度');
			return false;
		}
		$.post('/admin_circle/setdefaultdimension', {
			id: $.url().param('id'), 
			name: name
		}, function (res) {
			if (res.errno == 0) {
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		}, 'json');
	});
	$('.delete-dimension').click(function () {
		var name = $('.select-dimension').val();
		if (!name) {
			window.alert('请选择维度');
			return false;
		}
		$.post('/admin_circle/deletedimension', {
			id: $.url().param('id'), 
			name: name
		}, function (res) {
			if (res.errno == 0) {
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		}, 'json');
	});
	$('.add-tag').click(function () {
		var tags = [];
		$('.candidate-tag dd input:checked').each(function (index, ele) {
			tags.push($(ele).val());
		});
		var input = $('.manual-input-tag').val();
		if (input) {
			$.each($.trim(input).split(','), function (index, value) {
				value = $.trim(value);
				if (value) {
					tags.push(value);
				}
			});
		}
		if (tags.length == 0) {
			window.alert('请选择候选Tag或手工输入');
			return false;
		}
		var name = $('.select-dimension').val();
		if (!name) {
			window.alert('请选择维度');
			return false;
		}
		$.post('/admin_circle/addtag', {
			id: $.url().param('id'), 
			tags: tags,
			name: name
		}, function (res) {
			if (res.errno == 0) {
				$('.manual-input-tag').val('');
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		}, 'json');
	});
	$('.delete-tag').click(function () {
		var dimensions = [];
		var tags = [];
		$('.tag dt').each(function (index, ele) {
			var dimension = $(this).find('input:checkbox').val(); 
			var tag = [];
			$(this).next().find('input:checked').each(function (index, ele) {
				tag.push($(ele).val());
			});
			if (tag.length > 0) {
				dimensions.push(dimension);
				tags.push(tag.join(','));
			}
		});
		if (dimensions.length > 0) {
			$.post('/admin_circle/deletetag', {
    			id: $.url().param('id'), 
    			dimensions: dimensions,
    			tags: tags
    		}, function (res) {
    			if (res.errno == 0) {
    				window.location.reload();
    			} else {
    				window.alert(res.error);
    			}
    		}, 'json');
		}
	});
	$('.move-dimension-up, .move-dimension-down').click(function () {
		var name = $('.select-dimension').val();
		if (!name) {
			window.alert('请选择维度');
			return false;
		}
		var amount = $(this).next().val();
		if (amount <= 0) {
			window.alert('移动位数需大于0');
			return false;
		}
		if ($(this).hasClass('move-dimension-up')) {
			amount = -amount;
		}
		$.post('/admin_circle/movedimension', {
			id: $.url().param('id'), 
			name: name,
			amount: amount
		}, function (res) {
			if (res.errno == 0) {
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		}, 'json');
	});
	$('.move-tag-left, .move-tag-right').click(function () {
		var checks = $('dl dd input:checked');
		if (checks.length == 0) {
			window.alert('请选择Tag');
			return false;
		}
		if (checks.length > 1) {
			window.alert('一次只能移动一个Tag');
			return false;
		}
		var dimension = checks.parents('dd').prev().find('input').val();
		var tag = checks.val();
		var amount = $(this).next().val();
		if (amount <= 0) {
			window.alert('移动位数需大于0');
			return false;
		}
		if ($(this).hasClass('move-tag-left')) {
			amount = -amount;
		}
		$.post('/admin_circle/movetag', {
			id: $.url().param('id'), 
			dimension: dimension,
			tag: tag,
			amount: amount
		}, function (res) {
			if (res.errno == 0) {
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		}, 'json');
	});
});
</script>
<%/block%>