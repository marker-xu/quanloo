<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>

<%block name="custom_css"%>
<style>
<!--
#bd div.item div {
	margin: 5px 0pt;
}

#bd ul li {
	float: left;
	margin: 0pt 5px;
}
-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1>主题圈管理</h1>
	<a href="/admin_topiccircle/add">添加主题圈</a>
	<a href="" class="publish">发布</a>
	<?php foreach ($topic_circles as $i => $circle) { ?>
	<div class="item">
	    <div>
	        <?php echo $i+1 ?>. <a href="/circle?id=<?php echo $circle['_id'] ?>" target="_blank"><?php echo HTML::chars($circle['title']) ?></a>
		    <a href="/admin_topiccircle/mod?id=<?php echo $circle['_id'] ?>" class="mod">修改</a>
		    <a href="" class="mov-up">上移</a>
		    <a href="" class="mov-down">下移</a>
		    <a href="" class="del">删除</a>
	    </div>
	    <div>
	    	大图<br />
	    	<img src="<?php echo $circle['big_picture'] ?>">
	    </div>
	    <div>
	    	小图<br />
	    	<img src="<?php echo $circle['small_picture'] ?>">
	    </div>
	    <div>
	    	视频
    		<ul class="cls">
    			<?php foreach ($circle['videos'] as $index => $video) { ?>
    			<li>
    				<a href="<?php echo Util::videoPlayUrl($video['_id']) ?>" target="_blank">
    					<img src="<?php echo Util::videoThumbnailUrl($video['thumbnail']) ?>" width="120" height="90" />
    				</a>
    				<p><?php echo $index+1 ?>. <?php echo HTML::chars(mb_substr($video['title'], 0, 10)) ?></p>
    			</li>
    			<?php } ?>
    		</ul>
	    </div>
	    <input type="hidden" name="id", value="<?php echo $circle['_id'] ?>">
		<hr />
	</div>
	<?php } ?>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
$(function () {
	$('.publish').click(function () {
		if (window.confirm('确认发布？')) {
			$.getJSON('/admin_topiccircle/publish', function (res) {
				if (res.errno == 0) {
					window.alert('发布成功');
				} else {
					window.alert(res.error);
				}
			});
		}
		return false;
	});
	
	$('.mov-up').click(function () {
		var id = $(this).parents('.item').find('input[name=id]').val();
		$.getJSON('/admin_topiccircle/move', {id: id, amount: -1}, function (res) {
			if (res.errno == 0) {
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		});
		return false;
	});
	
	$('.mov-down').click(function () {
		var id = $(this).parents('.item').find('input[name=id]').val();
		$.getJSON('/admin_topiccircle/move', {id: id, amount: 1}, function (res) {
			if (res.errno == 0) {
				window.location.reload();
			} else {
				window.alert(res.error);
			}
		});
		return false;
	});

	$('.del').click(function () {
		var id = $(this).parents('.item').find('input[name=id]').val();
		if (window.confirm('确认删除？')) {
			$.getJSON('/admin_topiccircle/delete', {id: id}, function (res) {
				if (res.errno == 0) {
					window.location.reload();
				} else {
					window.alert(res.error);
				}
			});
		}
		return false;
	});
});
</script>
<%/block%>