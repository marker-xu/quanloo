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
            	<!--个人设置-->
            	<div id="user_setting">
                    <%include file="user/user_setting_panel.inc"%>
                    <div class="setting_content medals">
                    	<div class="wrap">
                            <div class="tit">已获得的勋章</div>
                            <div class="medalPlace">
                            	<ul>
                            	<%foreach $user_info.medal as $v%>
                            		<%if $v == Model_Data_User::MEDAL_SUBSCRIBE_CIRCLE%>
                                	<li>
                                    	<div><img src="<%#resUrl#%>/img/medal0.jpg" alt="初来乍到"></div>
                                        <div class="medal_tit"><img src="<%#resUrl#%>/img/medal0_t.jpg"></div>
                                    </li>
                                    <%/if%>
                                    <%if $v == Model_Data_User::MEDAL_INVITE_FRIEND%>
                                	<li>
                                    	<div><img src="<%#resUrl#%>/img/medal1.jpg" alt="宾客盈门"></div>
                                        <div class="medal_tit"><img src="<%#resUrl#%>/img/medal1_t.jpg"></div>
                                    </li>
                                    <%/if%>
                                    <%if $v == Model_Data_User::MEDAL_CREATE_CIRCLE%>
                                	<li>
                                    	<div><img src="<%#resUrl#%>/img/medal2.jpg" alt="繁花似锦"></div>
                                        <div class="medal_tit"><img src="<%#resUrl#%>/img/medal2_t.jpg"></div>
                                    </li>
                                    <%/if%>
                                <%/foreach%>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!--个人设置-->
        </div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
    (function(){

    })();
</script>
<%/block%>