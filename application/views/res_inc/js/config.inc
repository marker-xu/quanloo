<script type="text/javascript">
QW.Config.set('host', "<%#siteUrl#%>");
QW.Config.set('suggest_uri', "<%#suggestUrl#%>");
QW.Config.set('utilConst', {
    domain_image_video_thumbnail : '<%$smarty.const.DOMAIN_IMAGE_VIDEO_THUMBNAIL%>',
    domain_image_thumbnail_arr : [<%for $i=1 to $smarty.const.DOMAIN_IMAGE_THUMBNAIL_NUM%>'<%sprintf($smarty.const.DOMAIN_IMAGE_THUMBNAIL_FMT, $i)%>'<%if $i < $smarty.const.DOMAIN_IMAGE_THUMBNAIL_NUM%>,<%/if%><%/for%>],
    domain_static                : '<%$smarty.const.DOMAIN_STATIC%>',
    domain_image_user_avatar     : '<%$smarty.const.DOMAIN_IMAGE_USER_AVATAR%>',
    domain_site                  : '<%$smarty.const.DOMAIN_SITE%>',
    domain_web_storage_cluster   : '<%$smarty.const.DOMAIN_WEB_STORAGE_CLUSTER%>'
});
</script>
