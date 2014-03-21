<%extends file="common/base.tpl"%>

<%block name="title" prepend%>个人设置页<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/userSetting.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>
<script type="text/javascript" src="<%#resUrl#%>/js/video/user_settings.js?v=<%#v#%>"></script>
<script type="text/javascript" src="<%#resUrl#%>/js/components/switch/switch_all.js?v=<%#v#%>"></script>
<script type="text/javascript" src="<%#resUrl#%>/js/video/individual_tag.js?v=<%#v#%>"></script>

<%/block%>
<!--- 测试 -->
<%block name="bd"%>  
<div id="bd">
    <!--个人设置-->
    <div id="user_setting">
        <%include file="user/user_setting_panel.inc"%>
        <div class="setting_content syscconnect" id="sysaccount">
            <div class="wrap">
                 <h3>帐号绑定</h3>
                 <p class="desc">绑定主流社交网站账号,可以快速地将圈乐中有趣的内容分享给相应网站上的朋友们。赶紧开始你的分享之旅吧！</p>
                 	<div class="bind-list clearfix">
                 		<div class="bind-item<%if $bindlist.sina.connect_status == 1%> bind-item-on<%/if%>">
                    		<div class="pic ico-sina">新浪</div>
                            <div class="info">
                            	<%if $bindlist.sina.connect_status == 0%>
                                <p>未绑定</p>
                                <a class="bt-bind" onclick="sinaBind();return false;" href="/connect?type=<%Model_Data_UserConnect::TYPE_SINA%>">马上绑定</a>
                                <%else%>
                                <p class="c2">已于<%$bindlist.sina.time%>绑定</p>
                                <a class="bt-unbind" href="/connect/unbind?type=<%Model_Data_UserConnect::TYPE_SINA%>">取消绑定</a>
                                <%/if%>
                            </div>
                         </div>
                         <div class="bind-item<%if $bindlist.tqq.connect_status == 1%> bind-item-on<%/if%>">
                         	<div class="pic ico-tqq">腾讯</div>
                            <div class="info">
                            	<%if $bindlist.tqq.connect_status == 0%>
                                <p>未绑定</p>
                                <a class="bt-bind" onclick="tqqBind();return false;" href="/connect?type=<%Model_Data_UserConnect::TYPE_TQQ%>">马上绑定</a>
                                <%else%>
                                <p class="c2">已于<%$bindlist.tqq.time%>绑定</p>
                                <a class="bt-unbind" href="/connect/unbind?type=<%Model_Data_UserConnect::TYPE_TQQ%>">取消绑定</a>
                                <%/if%>
                            </div>
                         </div>
                         
                     </div>
                     <!-- /bind-list -->
                </div>
                <!-- /bind-wrap -->
            </div>
            <!-- /sysaccount -->
        </div>
    
    <!--个人设置-->
</div>
<%/block%>

<%block name="foot_js"%>

<%/block%>
