<%extends file="common/base.tpl"%>

<%block name="title" prepend%>个人设置——制定主页<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
	<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/userSetting.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>
<script type="text/javascript" src="<%#resUrl#%>/js/components/xclonedrag/XCloneDrag.js?v=<%#v#%>"></script>
<script type="text/javascript" src="<%#resUrl#%>/js/video/user_settings.js?v=<%#v#%>"></script>
<script type="text/javascript" src="<%#resUrl#%>/js/components/switch/switch_all.js?v=<%#v#%>"></script> 
<%/block%>
<!--- 测试 -->
<%block name="bd"%>  
        <div id="bd">
            	<!--个人设置-->
            	<div id="user_setting">
                    <%include file="user/user_setting_panel.inc"%>
                    <div class="setting_content customPage" id="custom_homepage">
                    	<div class="wrap">
                            <div class="tip">鼠标拖拽可调整排序</div>
                            <ul class="currentTag cls selectedList">
                            	<%foreach $setting_circles as $row%>
                            	   <li class="has_tag" circle-id="<%$row._id%>">
                                        <label title="<%$row.title|escape:"html"%>"><%Util::utf8SubStr($row.title, 11)%></label>
                                        <em title="删除"></em>
                                   </li>
                            	<%/foreach%>
                                <?php
                                    for( $i = 8 - count($setting_circles); $i > 0; $i-- )
                                    {
                                        echo "<li></li>";
                                    }
                                ?>
                            </ul>
                            <div class="followCircle">
                            	<div class="tit">  
                                    <a href="<%Util::userUrl($login_user._id)%>">
                                        <span class="edit">管理圈子</span>
                                    </a>
                                    你已经关注的圈子
                                </div>
                                <ul class="cls list">
                                <%foreach $subscribe_circles as $row%>
                                <%if !isset($setting_circles[$row._id])%>
                                <li circle-id="<%$row._id%>" ><span title="<%$row.title|escape:"html"%>"><%Util::utf8SubStr($row.title, 11)%></span></li>
                                <%/if%>
                                <%/foreach%>
                                </ul>
                            </div>
                            <form id="set_form" action="/user/sethomepage" method="post">
                    		<%Form::hidden('csrf_token', Security::token())%>
                    		<input type="hidden" name="circles_set" value="<%implode(",", array_keys($setting_circles))%>" />
                            <div class="w_submit"><button class="btn btn-complete s-ic-reg" type="submit"></button></div>
                            </form>
                        </div>
                    </div>
                </div>
                <!--个人设置-->
        </div>
        
        <ul id="temp_box" class="temp_box"></ul>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
    (function(){

    })();
</script>
<%/block%>
