
<div class="panel panel-t1 login_popup_big" style="width:760px;position: relative;" id="login_popup_creatcircle">
    <div class="panel-content">

        <div class="hd"><h3>创建圈子</h3></div>
        <div class="bd">
            <div id="creatCircle_box">
                <form action="javascript:void(0)" id="add-circle">
                    <%Form::hidden('csrf_token', Security::token())%>
                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr class="form_module">
                            <td class="name">圈子名称：</td>
                            <td>
                                <input type="text" name="title" class="text circle_name" tabindex="1">
                            </td>
                            <td class="tips" id="title-msg"></td>
                        </tr>
                        <tr class="form_module">
                            <td class="name">分类：</td>
                            <td>
                                <select id="circleType" name="cat" class="circleType" tabindex="2">
                                	<option>请选择分类</option>
                                    <%foreach $cat_list as $tmpCatId=>$tmpCatName%>
                                    <option value="<%$tmpCatId%>"><%$tmpCatName|escape:"html"%></option>
                                    <%/foreach%>
                                </select>
                            </td>
                            <td class="tips" id="cat-msg"></td>
                        </tr>
                        <tr class="form_module">
                            <td class="name">标签：</td>
                            <td><input type="text" name="tags" placeholder="最多10个标签，请以逗号分开" class="text circle_tag" tabindex="3"></td>
                            <td class="tips" id="tags-msg"></td>
                        </tr>
                        <tr>
                            <td class="name btn">&nbsp;</td>
                            <td class="lyt_foot"><a title="创建圈子" class="in-block btn_creat_circle" href="#"></a>
                                <a title="取消" class="in-block btn_cannel_circle" href="#"></a>
                            </td>
                            <td class="tips"></td>
                        </tr>
                    </table>
                </form>
            </div>


        </div>
        <div class="ft">

        </div>
        <span class="co1"><span></span></span><span class="co2"><span></span></span>
        <span class="cue"></span><span class="sd"></span><span class="close close__"></span><span class="resize"></span>
    </div>
</div>
