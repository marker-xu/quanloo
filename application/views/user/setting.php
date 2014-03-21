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
        <div class="setting_content person" id="setting_">
            <div class="wrap">
                <p>以下信息将显示在<a href="/user/index">个人资料</a>页方便大家了解你</p>
                <form id="register_form" method="post" action="javascript:void(0)"><!--/user/setting-->
                    <%Form::hidden('csrf_token', Security::token())%>
                    <dl class="dl cls">
                        <dt class="headpic">
                            <img src="<%Util::userAvatarUrl($user_info.avatar.160, 160)%>">
                            <a class="a" href="/user/modifyavatar">修改头像</a>
                        </dt>
                        <dd id="loginer">
                            <div class="form_module">
                                <div class="tit">你的昵称</div>
                                <div class="formPlace cls">
                                	<div class="l">
                                		<input type="text" tabindex="1" name="name" value="<%$user_info.nick%>" id="name" class="text" />
                                    </div>
                                    <div id="nicktips" class="l tips"><!--该昵称已经被其他用户使用，换个别的吧--></div></div>
                                <div class="tipTxt">2-10个字，支持中英文、数字、_和-</div>
                            </div>
                            <div class="form_module">
                            	<div class="tit">电子邮件</div>
                            	<div class="formPlace cls">
                            		<div class="l">
                            			<input type="text" tabindex="2" name="email" value="<%$user_info.email|escape:"html"%>" id="email" class="text" />
                                    </div>
                                    <div id="emailtips" class="l tips"></div>
                                </div>
                            </div>
                            <div class="form_module">
                                <div class="tit">个性签名</div>
                                <div class="formPlace cls">
                                    <textarea tabindex="3" id="intro" class="textara" rows="4" name="intro"><%$user_info.intro%></textarea>
                                </div>
                            </div>
                            <!--tage-->
                            <div class="form_module clearBg editeTag">
                                <div class="tit">添加兴趣标签有助于获得更符合口味的推荐视频</div>
                                <div class="input_tag_wrap">
                                    <div class="reg_input_tag">
                                        <input type="text" class="text" id="input_tag" name="input_tag" tabindex="3">
                                        <span id="submit_tag"></span>
                                    </div>
                                    <div class="l tipss"><em id="err-input_tag"></em></div>
                                    <div class="tipTxt">标签长度不超过10个字</div>
                                    <input type="hidden" name="tags" id="tags" value="<%if isset($user_info['tags'])%><%implode(",", $user_info['tags'])|escape:"html"%><%/if%>" />
                                </div>

                                <div class="formPlace cls">
                                    <div class="l tag_l">
                                        <h3>我已经添加的标签<span class="tipTxt">最多可以添加20个兴趣标签</span></h3>
                                        <ul id="owned_tag" class="owned_tag">
                                        <%if isset($user_info['tags'])%>
                                        	<%foreach $user_info['tags'] as $strTag%>
                                        	<li><a href="###"><%$strTag|escape:"html"%></a></li>
                                        	<%/foreach%>
                                        <%/if%>
                                        </ul>
                                    </div>
                                    <div class="r tag_r">
                                        <div class="r_wrap">
                                            <h3><a class="r" href="###" id="change_tag">换一换</a>可能感兴趣的标签：</h3>
                                            <ul id="guess_tag" class="guess_tag">
												<%foreach $select_tags as $strTag%>
													
					                            	<li><a href="###" class="<%if isset($user_info['tags']) && in_array($strTag,$user_info['tags'])%>tag_added<%/if%>"><%$strTag%></a></li>
					                            <%/foreach%>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form_module">
                                <div class="formPlace cls">
                                    <input type="checkbox" name="accept_subscribe" id="accept_subscribe" value="1" <%if !isset($user_info['accept_subscribe_email']) || $user_info['accept_subscribe_email']%>checked="checked"<%/if%> /> <label for="accept_subscribe">订阅圈乐热门内容精选</label>
                                </div>
                            </div>
                            <!--//tag-->
                            <div class="form_module clearBg" style="margin-top: 10px;"><button class="btn btn-complete s-ic-reg" type="submit"></button></div>
                        </dd>
                    </dl>
                </form>
            </div>
        </div>
    </div>
    
    <!--个人设置-->
</div>
<%/block%>

<%block name="foot_js"%>

<%/block%>
