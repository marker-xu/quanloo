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
            	<!--修改圈子模板-->
            	<div id="edit_circle" style="position: relative;" class="panel panel-t1 login_popup_big">
				    <div class="panel-content">

				        <div class="hd"><h3>编辑圈子 <b>&gt;</b> <a href="<%Util::circleUrl($circle_info._id, null, $circle_info)%>"><%$circle_info.title|escape:"html"%>圈子</a></h3></div>
				        <div class="bd">
				            <div id="creatCircle_box" style="margin: 8px auto 15px;width: 775px;">
                                                <form id="add-circle" action="/user/editcircle">
				                    <%Form::hidden('csrf_token', Security::token())%>
				                    <%Form::hidden('cid', $circle_info._id)%>
				                    <%Form::hidden('refer', $refer)%>
				                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
				                        <tbody><tr class="form_module">
				                            <td class="name">圈子名称:</td>
				                            <td>
				                                <input type="text" tabindex="1" class="text circle_name" name="title" value="<%$circle_info.title|escape:"html"%>">
				                            </td>
				                            <td id="title-msg" class="tips"></td>
				                        </tr>
				                        <tr class="form_module">
				                            <td class="name">分类:</td>
				                            <td>
				                                <select tabindex="2" class="circleType" name="cat" id="circleType">
				                                	<option>请选择分类</option>
				                                    <%foreach $cat_list as $tmpCatId=>$tmpCatName%>
				                                    <option value="<%$tmpCatId%>" <%if $circle_info.category.0==$tmpCatId%>selected<%/if%>><%$tmpCatName|escape:"html"%></option>
				                                    <%/foreach%>
                                                </select>
				                            </td>
				                            <td id="cat-msg" class="tips"></td>
				                        </tr>
				                        <tr class="form_module">
				                            <td class="name">标签:</td>
				                            <td><input placeholder="最多10个标签，请以逗号分开" type="text" tabindex="3" class="text circle_tag" name="tags" value="<%if $circle_info.tag%><%implode(",", $circle_info.tag)|escape:"html"%><%/if%>"></td>
				                            <td id="tags-msg" class="tips"></td>
				                        </tr>
				                        <!--<tr>
				                            <td class="name">隐私:</td>
				                            <td>
				                            	<span class="radio_item">
				                            		<input name="view_pri" value="0" id="id_public" checked="checked" type="radio">
				                            		<label for="id_public">公开</label>
				                            	</span>
				                            	<span class="radio_item radio_2">
				                            		<input name="view_pri" value="1" id="id_private" type="radio">
				                            		<label for="id_private">仅自己可见</label>
				                            	</span>
				                            </td>
				                            <td class="tips"></td>
				                        </tr>
				                    -->
				                        <tr>
				                            <td class="name btn" style="height:100px;">&nbsp;</td>
				                            <td><a href="#" class="in-block s-ic-reg btn-complete" id="edit_circle_btn" title="完成"></a>
				                                <a href="#" class="in-block btn_circle_back" onclick="window.history.go(-1);return false;" title="取消"></a>
				                            </td>
				                            <td class="tips"></td>
				                        </tr>
				                    </tbody></table>
				                </form>
				            </div>
				        </div>
				        <span class="sd"></span>
				        <span class="btn-del-circle js_del_circle" data-cid="<%$circle_info._id%>" data-forward="/user/circle?type=2"></span><span class="resize"></span>
				    </div>
				</div>
                <!--修改圈子模板-->
        </div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
    (function(){

    })();
</script>
<%/block%>