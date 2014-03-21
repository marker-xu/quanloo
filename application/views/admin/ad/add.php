<%extends file="common/adminbase.tpl"%>
<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/js/third/jquery/jquery-ui/ui-lightness/jquery-ui-1.8.20.custom.css">
<style type="text/css">
/* css for timepicker start*/
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
/* css for timepicker end*/
</style>
<%/block%>

<%block name="main"%>        
<div id="main"> 
	<h1>添加广告</h1>
	<%if $err_msg%><p style="color:red"><%$err_msg|escape:"html"%></p><%/if%>
	<form action="#" method="post" enctype="multipart/form-data" id="form_ad">
		<table>
            <tbody>
            	<tr>
                    <td>广告位：</td>
                    <td><%html_options name='ad_pos' options=$adpos_map selected=$cur_adpos_id%></td>
                </tr>
            	<tr>
                    <td>广告标题：</td>
                    <td>
                    	<input name="ad_title" type="text" size="100">
                    </td>
                </tr>
            	<tr>
                    <td>点击链接：</td>
                    <td>
                    	<input name="ad_url" type="text" size="100">
                    </td>
                </tr>
            	<tr>
                    <td>开始时间：</td>
                    <td>
                    	<input name="ad_starttime" type="text" size="30" id="ad_starttime">
                    </td>
                </tr>
            	<tr>
                    <td>结束时间：</td>
                    <td>
                    	<input name="ad_endtime" type="text" size="30" id="ad_endtime">
                    </td>
                </tr>                 
            	<tr>
                    <td>广告图片：</td>
                    <td>
                    	<input type="file" name="ad_pic">
                    </td>
                </tr>
            	<tr>
                    <td></td>
                    <td><input type="submit" value="提交"></td>
                </tr>
        	</tbody>
        </table>
        <input type="hidden" name="ad_type" value="1"><%*目前只支持图片广告一种类型，以后扩展的话要用下拉菜单*%>
        <input type="hidden" name="csrf_token" value="<?php echo Security::token() ?>">
	</form>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript" src="<%#resUrl#%>/js/third/jquery/jquery-ui/jquery-ui-1.8.20.custom.min.js"></script>
<script type="text/javascript" src="<%#resUrl#%>/js/third/jquery/jquery-ui/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript">
$(function () {	
	$('#ad_starttime').datetimepicker({
		hourGrid: 4,
		minuteGrid: 10,
		secondGrid: 10,
		showSecond: true,
		timeFormat: 'hh:mm:ss',
	    onClose: function(dateText, inst) {
	        var endDateTextBox = $('#ad_endtime');
	        if (endDateTextBox.val() != '') {
	            var testStartDate = new Date(dateText);
	            var testEndDate = new Date(endDateTextBox.val());
	            if (testStartDate > testEndDate)
	                endDateTextBox.val(dateText);
	        }
	        else {
	            endDateTextBox.val(dateText);
	        }
	    },
	    onSelect: function (selectedDateTime){
	        var start = $(this).datetimepicker('getDate');
	        $('#ad_endtime').datetimepicker('option', 'minDate', new Date(start.getTime()));
	    }
	});
	$('#ad_endtime').datetimepicker({
		hourGrid: 4,
		minuteGrid: 10,
		secondGrid: 10,
		showSecond: true,
		timeFormat: 'hh:mm:ss',
	    onClose: function(dateText, inst) {
	        var startDateTextBox = $('#ad_starttime');
	        if (startDateTextBox.val() != '') {
	            var testStartDate = new Date(startDateTextBox.val());
	            var testEndDate = new Date(dateText);
	            if (testStartDate > testEndDate)
	                startDateTextBox.val(dateText);
	        }
	        else {
	            startDateTextBox.val(dateText);
	        }
	    },
	    onSelect: function (selectedDateTime){
	        var end = $(this).datetimepicker('getDate');
	        $('#ad_starttime').datetimepicker('option', 'maxDate', new Date(end.getTime()) );
	    }
	});

	//避免日期弹出层被header覆盖的补丁，这是非常ugly的做法
    $('#form_ad').delegate("#ad_endtime", "focus", function() {
        var o = $('#ui-datepicker-div');
        if (! o) return;
        var offset = o.offset();
        if (offset.top < 20) {
                o.css('top','100px');
        }
	});
    $('#form_ad').delegate("#ad_starttime", "focus", function() {
        var o = $('#ui-datepicker-div');
        if (! o) return;
        var offset = o.offset();
        if (offset.top < 20) {
                o.css('top','100px');
        }
	});
});
</script>
<%/block%>