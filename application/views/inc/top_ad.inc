<%if !$login_user%>
<div id="circle-login" class="y-circle-login-box cfix">
    <dl class="y-circle-login-other r">
    	<dt class="y-font-999">使用合作网站账号直接登录：</dt>
        <dd>
        	<a class="y-ico y-ico-sina" href="/connect?type=2" onclick="sinaLogin();return false;">新浪微博</a>
            <a class="y-ico y-ico-qq" href="/connect?type=3" onclick="tqqLogin();return false;">腾讯微博</a>
        </dd>
    </dl>
    <dl class="y-circle-login cfix y-inline-dl">
    	<dt class="y-title-20">登录后就可以创建自己的喜欢的圈子</dt>
        <dd>
        	<a class="y-ico y-ico-register" href="<%#siteUrl#%>/user/register?p=circle">注册</a><a class="marmot y-ico y-ico-login js_fast-login" 
          data--marmot="{page_id:'click_login',position:'circle',status:''}" href="javascript:void(0)">登录</a>
        </dd>
    </dl>

    <a href="#" class="y-ico y-ico-close js_close-adbar">关闭</a>
</div>
<%elseif $topAd%>
<div id="circle-login" class="y-circle-login-in">
	<a href="<%$topAd.ad_mat.ad_url%>" target="_blank">
		<img alt="<%$topAd.ad_mat.ad_title|escape:'html'%>" src="<%Util::webStorageClusterFileUrl($topAd.ad_mat.ad_pic)%>" height="60" width="960">
	</a>
    <a class="y-ico y-ico-close js_close-adbar" href="#" >关闭</a> 
</div>
<%/if%>