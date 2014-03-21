<%extends file="common/adminbase.tpl"%>

<%block name="view_conf"%>
    <%$h1 = 1%>
<%/block%>

<%block name="custom_css"%>
<style>
<!--
#bd form ul li {
	float: left;
	margin: 5px;
}
-->
</style>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1><a href="/admin_topiccircle">主题圈管理</a></h1>
	<form action="" method="post" enctype="multipart/form-data">
		<table>
            <tbody>
            	<tr>
                    <td style="width: 70px;">圈子：</td>
                    <td><a href="/circle?id=<?php echo $circle['_id'] ?>" target="_blank"><?php echo HTML::chars($circle['title']) ?></td>
                </tr>
            	<tr>
                    <td>大图：</td>
                    <td><input type="file" name="big_picture"> 替换当前图片</td>
                </tr>
            	<tr>
                    <td>小图：</td>
                    <td><input type="file" name="small_picture"> 替换当前图片</td>
                </tr>
            	<tr>
                    <td>视频：</td>
                    <td>
        				<ul class="cls">
                			<?php foreach ($circle['videos'] as $index => $video) { ?>
                			<li>
                				<a href="<?php echo Util::videoPlayUrl($video['_id']) ?>" target="_blank">
                					<img src="<?php echo Util::videoThumbnailUrl($video['thumbnail']) ?>" width="120" height="90" />
                				</a>
                				<p><?php echo $index+1 ?>. <?php echo HTML::chars(mb_substr($video['title'], 0, 10)) ?></p>
                				<p>
                					<a href="" class="move-forward">前移</a>
                					<a href="" class="move-back">后移</a>
                					<a href="" class="del">删除</a>
                				</p>
                				<input type="hidden" name="videos[]" value="<?php echo $video['_id'] ?>">
                			</li>
                			<?php } ?>
                		</ul>
        			</td>
                </tr>
            	<tr>
                    <td>手动输入：</td>
                    <td>
                		<input type="text" name="videos[]">
                		<input type="text" name="videos[]">
                		<input type="text" name="videos[]">
                		<input type="text" name="videos[]">
                		<input type="text" name="videos[]"><br />
                		请输入视频ID或站内播放地址
        			</td>
                </tr>
            	<tr>
                    <td>推荐视频：</td>
                    <td>
        				<ul class="cls recommend">
                			<?php foreach ($recommendVideos as $video) { ?>
                			<li>
                				<a href="<?php echo Util::videoPlayUrl($video['_id']) ?>" target="_blank">
                					<img src="<?php echo Util::videoThumbnailUrl($video['thumbnail']) ?>" width="120" height="90" />
                				</a>
                				<p><?php echo HTML::chars(mb_substr($video['title'], 0, 10)) ?></p>
                				<p>
                					<input type="checkbox" name="videos[]" value="<?php echo $video['_id'] ?>"> 添加
                				</p>
                			</li>
                			<?php } ?>
                		</ul>
        			</td>
                </tr>
            	<tr>
                    <td></td>
                    <td><input type="submit" value="提交"></td>
                </tr>
        	</tbody>
        </table>
        <input type="hidden" name="csrf_token" value="<?php echo Security::token() ?>">
	</form>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
$(function () {
	$('.recommend').find('input[type=checkbox]').attr('checked', false);
	
	$('.move-forward').click(function () {
		var li = $(this).parents('li');
		var prev = li.prev();
		if (prev) {
			prev.before(li);
		}
		return false;
	});
	
	$('.move-back').click(function () {
		var li = $(this).parents('li');
		var next = li.next();
		if (next) {
			next.after(li);
		}
		return false;
	});

	$('.del').click(function () {
		var li = $(this).parents('li');
		li.remove();
		return false;
	});
});
</script>
<%/block%>