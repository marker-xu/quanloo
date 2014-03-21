
<%if $topAd%>
<div id="circle-login" class="y-circle-login-in js-ad-wrap">
	<a href="<%$topAd.ad_mat.ad_url%>" target="_blank">
		<img alt="<%$topAd.ad_mat.ad_title|escape:'html'%>" src="<%Util::webStorageClusterFileUrl($topAd.ad_mat.ad_pic)%>" height="60" width="960">
	</a>
    <a class="y-ico y-ico-close js-close-adbar" href="#" >关闭</a>
</div>
<%/if%>