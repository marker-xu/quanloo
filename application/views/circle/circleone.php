<div class="panel panel-t1 login_popup_big" style="width:760px;position: relative;" id="login_popup_creatcircle">
    <div class="panel-content">

        <div class="hd"><h3>圈一下</h3></div>
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
                    <form>
                        <%Form::hidden('csrf_token', Security::token())%>
                        <%Form::hidden('vid', $video_info._id)%>
                        <%Form::hidden('cid', $circle_one._id)%>
                        <textarea name="note" rows="4" class="textara circle_placeholder" >请添加描述</textarea>
                    </form>
                    <div class="submit">
                        <a title="圈下来" class="in-block btn_circle_down" href="###"></a>
                        <a title="取消" class="in-block btn_cannel_circle" href="###"></a>
                    </div>
                </div>
            </div>

        </div>
        <div class="ft">

        </div>
        <span class="co1"><span></span></span><span class="co2"><span></span></span>
        <span class="cue"></span><span class="sd"></span><span class="close close__"></span><span class="resize"></span>
    </div>
</div>