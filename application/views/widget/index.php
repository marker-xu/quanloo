<%extends file="common/widget.tpl"%>

<%block name="title" prepend%>注册<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/widget.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>
<script type="text/javascript" src="<%#resUrl#%>/js/video/widget.js?v=<%#v#%>"></script>

<%/block%>
<!--- 测试 -->
<%block name="bd"%>
<!--<div id="widget_bd_center">-->
<div id="bd">

    <table id="widget_table">
        <thead>
            <tr>
                <th>时间</th> <th>名称</th> <th colspan="3">操作</th>
            </tr>
        </thead>
        <tbody>
            <%foreach $widget_list as $row%>
            <tr>
                <td align="center"><%date("Y-m-d H:i:s",$row.create_time->sec)%></td>
                <td><%$row.name|escape:"html"%></td>
                <td align="center">
                    <a href="/widget/modifywidget?wid=<%$row._id%>" wid="<%$row._id%>">编辑</a>
                </td>
                <td align="center">
                    <a href="#copy" class="copy_widget" iframe-html="<%$row.iframe|escape:"html"%>">复制</a>
                </td>
                <td align="center">
                    <a href="###" wid="<%$row._id%>" class="delete_widget">删除</a>
                </td>
            </tr>
            <%foreachelse%>
            <tr><td colspan="6">亲，还没数据呀，肿么办</td></tr>
            <%/foreach%>
        <style>

        </style>
        </tbody>
    </table>
</div>

<%/block%>

<%block name="foot_js"%>
<%*
<script type="text/javascript">

</script>
*%>
<%/block%>
