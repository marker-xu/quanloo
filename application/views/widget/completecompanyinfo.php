<%extends file="common/widget.tpl"%>

<%block name="title" prepend%>完善商户信息<%/block%>
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
	<%if !$website_info%>
    <div id="complete_info">请先完成商户信息！</div>
    <%/if%>
    <form id="company_info_form">
        <table  id="add_widget_table" >
            <tbody>
                <tr>
                    <td align="right" valign="top" style="width:150px;">您的网站域名：</td>
                    <td>
                        <input class="domain_input" style="width:300px;" value="<%$website_info.domain%>"  /><button type="button" class="widget_btn" id="check_domain_btn"><span>开始认证网站</span></button>
                        <input name="domain" class="domain" type="hidden" value="<%$website_info.domain%>" />
                        <p>为保护您的权益，请先对您的网站进行验证，才能继续注册<span class="tips"><em id="err_domain"></em></span></p>
                    </td>
                </tr>
                <tr>
                    <td align="right" valign="top">网站名称：</td>
                    <td>
                        <input name="site_name" class="site_name" style="width:300px;" value="<%$website_info.name%>" />
                        <p> 允许最多输入18个字<span class="tips"><em id="err_site_name"></em></span></p>
                        
                    </td>
                </tr>
                <tr>
                    <td align="right" valign="top">网站描述：</td>
                    <td>
                        <textarea name="desc" class="desc" style="width:300px;height:120px;"  ><%$website_info.desc%></textarea>
                        <p> 允许最多输入140个字<span class="tips"><em id="err_desc"></em></span></p>
                    </td>
                </tr>
                <tr>
                    <td align="right" valign="top">网站类别：</td>
                    <td>
                        <p><input type="checkbox" id="check_all" />全选</p>
                        <ul class="cls">
                            <%foreach $domain_type_list as $tmpTypeVal=>$tmpTypeName%>
                            <li style="float:left;width:200px;"><input type="checkbox" name="type_list[]" class="type_list" value="<%$tmpTypeVal%>" <%if $website_info.type&&in_array($tmpTypeVal, $website_info.type)%>checked<%/if%> /><%$tmpTypeName%></li>
                            <%/foreach%>
                        </ul>
                        <span class="tips"><em id="err_type_list"></em></span>
                    </td>
                </tr>
                <tr>
                    <td align="right"  valign="top">网站备案信息：</td>
                    <td>
                        <input name="icp" class="icp" style="width:300px;" value="<%$website_info.icp%>" />
                        <p>例如：京ICP证010234号</p>
                        <span class="tips"><em id="err_icp"></em></span>
                    </td>
                </tr>
                <tr>
                    <td align="right"  valign="top">联系人邮箱：</td>
                    <td>
                        <input name="email" class="email" style="width:300px;" value="<%$website_info.email%>" />
                        <span class="tips"><em id="err_email"></em></span>
                    </td>
                </tr>
                <tr>
                    <td align="right"  valign="top">联系人电话：</td>
                    <td>
                        <input name="phone" class="phone" style="width:300px;" value="<%$website_info.phone%>" />
                        <span class="tips"><em id="err_phone"></em></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <center><button type="button" class="widget_btn" id="submit_info"><span>完善信息</span></button></center>
    </form>

</div>

<%/block%>

<%block name="foot_js"%>
<%*
<script type="text/javascript">

</script>
*%>
<%/block%>
