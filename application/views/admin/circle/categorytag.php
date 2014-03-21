<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>

<%block name="custom_css"%>
<style>
<!--
#bd ul li.current {background: #ccc;}
#bd dl {overflow: auto;}
#bd dl dt {float: left; text-align: right; width: 100px;}
#bd dl dd {float: left;}
#bd dl dd input {margin-left: 5px;}
-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1>圈子分类Tag管理</h1>
	<div class="box categorys" style="margin: 10px 0pt;">
		<p>选择分类：</p>
    	<ul style="overflow: hidden;">
    		<?php foreach ($categorys as $category) { ?>
    		<li class="<?php echo $category['id'] == $current['id'] ? 'current' : '' ?>" style="float: left; margin: 5px;">
    			<a href="/admin_circle/categorytag?id=<?php echo $category['id'] ?>"><?php echo HTML::chars($category['name']) ?></a>
    		</li>
    		<?php } ?>
    	</ul>
	</div>
	<div class="box tags" style="margin: 10px 0pt;">
		<p>
			当前分类Tag：
		</p>
    	<ul style="overflow: hidden;">
    	<%$arrCatIdToKeyMap = Model_Data_Circle::$arrUrlKeyForCategorys%>
    		<?php foreach ($current['tags'] as $tag) { ?>
    		<li style="float: left; margin: 5px;">
    			<input type="checkbox" value="<?php echo HTML::chars($tag) ?>" />
    			    <a href="/category/<?php echo $arrCatIdToKeyMap[$current['id']];?>/<?php echo urlencode($tag);?>" target="_blank"><?php echo HTML::chars($tag) ?></a> (<?php echo $circlesCountOfTag[$tag] ?>)
    		</li>
    		<?php } ?>
    	</ul>
		<p>
        	<input type="checkbox" value="" />全选
        	<button type="button" class="delete-tag">删除Tag</button>
		</p>
	</div>
	<div class="box candidate-tags" style="margin: 10px 0pt;">
		<p>
			候选分类Tag：
		</p>
    	<ul style="overflow: hidden;">
    	<%$arrCatIdToKeyMap = Model_Data_Circle::$arrUrlKeyForCategorys%>
    		<?php foreach ($current['candidate_tags'] as $tag) { ?>
    		<li style="float: left; margin: 5px;">
    			<input type="checkbox" value="<?php echo HTML::chars($tag) ?>" />
    			<a href="/category/<?php echo $arrCatIdToKeyMap[$current['id']];?>/<?php echo urlencode($tag);?>" target="_blank"><?php echo HTML::chars($tag) ?></a>
    		</li>
    		<?php } ?>
    	</ul>
		<p>
			手工输入：<input type="text" name="manualInputTag" class="manual-input-tag" /> 多个Tag间以“,”号分隔。
		</p>
		<p>
    		<input type="checkbox" value="" />全选
    		<button type="button" class="add-tag">添加Tag</button>
		</p>
	</div>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
$(function () {	
	$('input:checked').prop('checked', false);
	$('.tags p input:checkbox, .candidate-tags p input:checkbox').change(function () {
		$(this).parent().siblings('ul').find('input:checkbox').prop('checked', $(this).prop('checked'));
	});
	
	$('.delete-tag').click(function () {
		var tags = [];
		$('.tags ul li input:checked').each(function (index, ele) {
			tags.push($(ele).val());
		});
		if (tags.length == 0) {
			window.alert('请选择Tag');
			return false;
		}
		$.post('/admin_circle/deletecategorytag', {
			id: $.url().param('id'),
			tags: tags
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
		$('.candidate-tags ul li input:checked').each(function (index, ele) {
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
		$.post('/admin_circle/addcategorytag', {
			id: $.url().param('id'),
			tags: tags
		}, function (res) {
			if (res.errno == 0) {
				$('.manual-input-tag').val('');
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		}, 'json');
	});
});
</script>
<%/block%>