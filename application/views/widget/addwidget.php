<%extends file="common/widget.tpl"%>

<%block name="title" prepend%>widget制定<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/widget.css?v=<%#v#%>">
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/widget/widget.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>
<script type="text/javascript" src="<%#resUrl#%>/js/video/widget.js?v=<%#v#%>"></script>
<%/block%>
<!--- 测试 -->
<%block name="bd"%>

<div id="container">
    <h2 class="subject j_subject">Widget定制页面</h2>

    <form id="add_widget_form">
        <%Form::hidden('wid', $widget_info._id|default:0, ['id'=>'js_input_wid'])%>
        <%Form::hidden('csrf_token', Security::token())%>
        <div class="info-wrap">
            <div class="info-inner">
                <div class="diy-box">
                    <fieldset>
                        <legend>调用配置</legend>
                        <div class="inp-row">
                            <label class="lbl">名称：</label>
                            <div class="conbox">
                                <input type="text" name="name" class="inp-txt name" style="width:240px" value="<%$widget_info.name|escape:"html"%>" />
                                <span class="tips"><em id="err_name"></em></span>
                            </div>
                        </div>
                        <div class="inp-row">
                            <label class="lbl">圈子链接地址：</label>
                            <div class="conbox">
                                <input type="text" value="" class="inp-txt js_circle_input" style="width:360px" />&nbsp;
                                <input type="button" class="inp-btn inp-btn-s1" id="js_add_circle" value="添加" />
                                <div class="toggle-area">
                                <%if isset($widget_info.cid_list)%>
                                	<%foreach $widget_info.cid_list as $tmpCid%>
                                        <li>
                                            <span><%$tmpCid%></span>
                                            <!-- 没区分类型 -->
                                            <button class="js_delete_item">删除</button>
                                            <input type="hidden" value="<%$tmpCid%>" name="cid_list[]">
                                        </li>
                                        <%/foreach%>
                                <%/if%>
                                </div>
                                <span class="tips"><em id="err_cid_list"></em></span>
                            </div>
                        </div>
                        <div class="inp-row">
                            <label class="lbl">视频链接地址：</label>
                            <div class="conbox">
                                <input type="text" value="" class="inp-txt js_video_input" style="width:360px" />&nbsp;
                                <input type="button" class="inp-btn inp-btn-s1" id="js_add_video" value="添加" />
                                <div class="toggle-area">
                                <%if isset($widget_info.vid_list)%>
                                	<%foreach $widget_info.vid_list as $tmpVid%>
                                        <li>
                                            <span><%$tmpVid%></span>
                                            <!-- 没区分类型 -->
                                            <button class="js_delete_item">删除</button>
                                            <input type="hidden" value="<%$tmpVid%>" name="vid_list[]">
                                        </li>
                                        <%/foreach%>
                                <%/if%>
                                </div>
                                <span class="tips"><em id="err_vid_list"></em></span>
                            </div>
                        </div>
                        <div class="inp-row">
                            <label class="lbl">视频个数：</label>
                            <div class="conbox">
                                <input type="text" value="<%if $widget_info.video_count%><%$widget_info.video_count%><%else%>3<%/if%>" class="inp-txt video_count" name="video_count" style="width:30px" />
                                <span class="tips"><em id="err_video_count"></em></span>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="diy-box per-area">
                    <fieldset>
                        <legend>框架设置</legend>
                        <div class="inp-row"><label class="lbl">区域大小：</label>
                            <div class="conbox">
                                <input type="text" name="width" class="inp-txt j-s-box-w width" style="width:60px" value="<%if $widget_info.width%><%$widget_info.width%><%else%>660<%/if%>" />&nbsp;像素&nbsp;×&nbsp;
                                <input type="text" name="height" class="inp-txt j-s-box-h height" value="<%if $widget_info.height%><%$widget_info.height%><%else%>246<%/if%>" style="width:60px" />&nbsp;像素
                                <span class="tips"><em id="err_height"></em></span>
                                <span class="tips"><em id="err_width"></em></span>
                                <span class="tip">注：填写顺序为（宽×高）</span>
                            </div>
                        </div>
                        <div class="inp-row"><label class="lbl">背景颜色：</label>
                            <div class="conbox">
                                <input type="text" name="bgcolor" value="<%if isset($widget_info.bgcolor)%><%$widget_info.bgcolor%><%else%>#f1f1f1<%/if%>" class="inp-txt j-s-box-bg bgcolor" style="width:60px" />
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="diy-box per-area">
                    <fieldset>
                        <legend>更多配置</legend>
                        <div class="inp-row">
                            <label class="lbl">视频图片尺寸：</label>
                            <div class="conbox">
                                <input type="text" name="pic_width" class="inp-txt"  style="width:60px" value="<%if $widget_info.pic_width%><%$widget_info.pic_width%><%else%>200<%/if%>" />&nbsp;像素&nbsp;×&nbsp;
                                <input type="text" name="pic_height" class="inp-txt"  style="width:60px" value="<%if $widget_info.pic_height%><%$widget_info.pic_height%><%else%>150<%/if%>" />&nbsp;像素
                                <span class="tip">注：填写顺序为（宽×高），宽高比例固定为4:3</span></div>
                        </div>
                        <div class="inp-row">
                            <label class="lbl">是否显示更多链接：</label><div class="conbox"><p class="inner"><input type="checkbox" name="is_more" value="1" id="showMoreLink" <%if $widget_info.is_more%>checked<%/if%> class="inp-chk" /><label for="showMoreLink">显示更多链接</label></p></div>
                        </div>
                        <div class="inp-row">
                            <label class="lbl">外链样式（CSS）地址：</label>
                            <div class="conbox">
                                <input type="text" name="css_url" class="inp-txt" style="width:360px" value="<%$widget_info.css_url%>" />
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="diy-box" style="padding-bottom:20px;">
                    <input type="button" class="inp-btn inp-btn-s2" value="预览" id="js_get_html" />
                    &nbsp;&nbsp;
                    <input type="button" class="inp-btn inp-btn-s2" value="保存" id="js_save_html" />&nbsp;&nbsp;
                    <input type="button" class="inp-btn inp-btn-s2" id="js_reset_form" value="重置为默认值" />
                </div>
                <div class="diy-box res-code" style="padding-bottom:50px;">
                    <input type="button" class="inp-btn inp-btn-s3" value="将代码复制到剪贴板" id="js_copy_html" />
                    <textarea name="" cols="20" rows="5" style="width:100%;resize:vertical;"  id="js_set_html"></textarea>
                </div>
            </div>
        </div>
        <div class="preview j_preview" id="preview_area">
        </div>
    </form>
</div>  

<%/block%>

<%block name="foot_js"%>
<%*
<script type="text/javascript">

</script>
*%>
<%/block%>
