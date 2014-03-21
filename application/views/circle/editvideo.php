<%extends file="common/base.tpl"%>

<%block name="title" prepend%>个人设置——我的勋章<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/userSetting.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>
<%/block%>
<!--- 测试 -->
<%block name="bd"%>  
<div id="bd">
    <%include file="inc/search.inc"%>
    <div id="edit_video" style="margin-top: 15px; position: relative;" class="panel panel-t1 login_popup_big">
        <div class="panel-content">
            <div class="hd"><h3>编辑圈内视频</h3></div>
            <div class="bd">
                <div id="to_my_circle_box" class="cls">
                    <div class="lot_l">
                        <a class="a" href=""><img class="img" src="<%Util::videoThumbnailUrl($video_info.thumbnail)%>"></a>
                    </div>
                    <div class="lot_r">
                        <div class="tit"><%$video_info.title|escape:"html"%></div>
                        <div class="select_circle">

                            <a class="circle_first" href="###"><i class="i_arrow show-select"></i>
                                <span class="show-select" title="<%$circle_one.title|escape:"html"%>"><%if $circle_one%><%Util::utf8SubStr($circle_one.title, 21)%><%else%>请选择圈子<%/if%></span>
                            </a>
                            <div class="options">
                                <%include file="circle/circleone_select.php"%>
                                <div class="circle_list_ft">
                                    <span class="layout_submit">
                                        <a class="i_submit" href="javascript:void(0)">
                                        </a>
                                    </span>
                                    <input type="text" id="new-circle" placeholder="创建新圈子" class="text" name="title">
                                </div>
                            </div>
                            <div class="tips" id="title-msg"></div>
                        </div>
                        <form id="edit_video_form">
                            <%Form::hidden('csrf_token', Security::token())%>
                            <%Form::hidden('vid', $video_info._id)%>
                            <%Form::hidden('cid', $circle_one._id)%>
                            <%Form::hidden('old_cid', $circle_one._id)%>
                            <textarea name="note" rows="4"  class="textara <%if !$circle_video.note%>circle_placeholder<%/if%>"><%if $circle_video.note%><%$circle_video.note%><%else%>请输入描述<%/if%></textarea>
                        </form>
                        <div class="submit">
                            <a href="#" class="in-block s-ic-reg btn-complete" id="edit_video_btn" title="保存"></a>
                            <a href="#" class="in-block btn_circle_back" onclick="window.history.go(-1);return false;" title="返回"></a>
                        </div>
                    </div>
                </div>

            </div>
            <span class="sd"></span>
            <span class="btn-del-circle js_del_video"
                  data-cid="<%$circle_one._id%>"
                  data-vid="<%$video_info._id%>"
                  data-forward="<%Util::circleUrl($circle_one._id, null, $circle_one)%>">
            </span>
            <span class="resize"></span>
        </div>
    </div>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">

</script>
<%/block%>