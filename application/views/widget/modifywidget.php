<%extends file="common/widget.tpl"%>

<%block name="title" prepend%>编辑Widget<%/block%>
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
<div id="bd">

    <form id="add_widget_form" action="/widget/modifywidget">
        <%Form::hidden('csrf_token', Security::token())%>
        <%Form::hidden('wid', $widget_info._id, ['id'=>'js_input_wid'])%>
        <table  id="add_widget_table" >
            <tbody>
                <tr>
                    <td align="right">名称：</td>
                    <td>
                        <input name="name" class="name" style="width:200px;" value="<%$widget_info.name|escape:"html"%>" />
                               <span class="tips"><em id="err_name"></em></span>
                    </td>
                </tr>
                <tr>
                    <td align="right">尺寸：</td>
                    <td>高<input name="height" class="height" style="width:100px;" value="<%$widget_info.data.height%>" /> px
                        宽<input name="width" class="width"  style="width:100px;" value="<%$widget_info.data.width%>" /> px
                        <span class="tips"><em id="err_height"></em></span>
                        <span class="tips"><em id="err_width"></em></span>
                    </td>
                </tr>
                <tr>
                    <td align="right">视频数：</td>
                    <td>
                        <input name="video_count" class="video_count" style="width:200px;" value="<%$widget_info.data.video_count%>" />个
                        <span class="tips"><em id="err_video_count"></em></span>
                    </td>
                </tr>
                <tr>
                    <td align="right">是否显示更多的链接：</td>
                    <td>
                        <input type="checkbox" name="is_more" class="is_more" value=1 <%if $widget_info.is_more%>checked<%/if%> />
                        <span  class="tips"><em id="err_is_more"></em></span>
                    </td>
                </tr>
                <tr>
                    <td align="right" valign="top">背景颜色：</td>
                    <td>
                        <input class="bgcolor" style="width:200px;" name="bgcolor" value="<%$widget_info.data.bgcolor%>" />
                    </td>
                </tr>
                 <tr>
                    <td align="right" valign="top">CSS链接地址：</td>
                    <td>
                        <input class="css_url" style="width:200px;" name="css_url" value="<%$widget_info.data.css_url%>" />
                    </td>
                </tr>
                <tr>
                    <td align="right" valign="top">视频样式：</td>
                    <td>
                        <table>
                            <tr>
                                <td>
                                    <input type="radio" name="skin_type" class="skin_type" value=0  <%if !$widget_info.skin_type%>checked<%/if%> />简洁式
                                </td>
                                <td>
                                    <input type="radio" name="skin_type" class="skin_type" value=1 <%if $widget_info.skin_type%>checked<%/if%> />交互式
                                           <span class="tips"><em id="err_skin_type"></em></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="skin1"></div>
                                </td>
                                <td>
                                    <div class="skin2"></div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="right"  valign="top">指定视频源：</td><td>
                        <table>
                            <tr>
                                <td>
                                    圈子<input id="circle_input"/>
                                    <button type="button" class="widget_btn" id="add_circle"><span>添加</span></button><br/>
                                    <span class="tips"><em id="err_cid_list"></em></span>
                                </td>
                                <td>
                                    视频<input id="video_input"/>
                                    <button type="button" class="widget_btn" id="add_video"><span>添加</span></button><br/>
                                    <span class="tips"><em id="err_vid_list"></em></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <ul id="circle_list">
                                        <%foreach $widget_info.data.cid_list as $tmpCid%>
                                        <li>
                                            <span><%$tmpCid%></span>
                                            <!-- 没区分类型 -->
                                            <button class="js_delete_item">删除</button>
                                            <input type="hidden" value="<%$tmpCid%>" name="cid_list[]">
                                        </li>
                                        <%/foreach%>
                                    </ul>

                                </td>
                                <td>
                                    <ul id="video_list">
                                        <%foreach $widget_info.data.vid_list as $tmpVid%>
                                        <li>
                                            <span><%$tmpVid%></span>
                                            <!-- 没区分类型 -->
                                            <button class="js_delete_item">删除</button>
                                            <input type="hidden" value="<%$tmpVid%>" name="vid_list[]">
                                        </li>
                                        <%/foreach%>
                                    </ul>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="right"  valign="top">获取JS代码：</td>
                    <td>
                        <div>
                            <button type="button" class="widget_btn"  id="js_get_html"><span>获取</span></button>
                            <button type="button" class="widget_btn"  id="js_copy_html"><span>复制</span></button>
                            <button type="button" class="widget_btn"  id="js_save_html"><span>保存</span></button>
                        </div>
                        <textarea style="width:400px;height:140px;"  id="js_set_html">

                        </textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
    <fieldset><legend>预览效果</legend>
        <span id="preview_area">
        </span>
    </fieldset>
</div>

<%/block%>

<%block name="foot_js"%>
<%*
<script type="text/javascript">

</script>
*%>
<%/block%>
