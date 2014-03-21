<%*
生成HTML5的播放器，用于iPhone、iPad和iPod等不支持flash的设备
*%>
<%if $video.domain == 'tudou.com'%>
<script type="text/javascript">
(function () {
	var iOSType = ({ipad: 9, iphone: 8, ipod: 10})["<%$video.player_html5_param.device%>"];
	var pixelRatio = window.devicePixelRatio;
	var isBaselineProfile = ! pixelRatio || pixelRatio <= 1;
	var reqUrl = 'http://v.tudou.com/RealtimeDecodeDispatcher/DispatchServlet?jsoncallback=%callbackfun%&' + ObjectH.encodeURIJson({
        "code": "<%$video.player_html5_param.code%>",
        "type": iOSType,
        "base": isBaselineProfile
    });	
	QW.loadJsonp(reqUrl, function(data){
		var player_str = '<embed src="' + data.src + '" id="qt_player" width="100%" height="100%" type="video/quicktime" autoplay="true" BGCOLOR="#000"';
        player_str += ' CONTROLLER="true" ENABLEJAVASCRIPT="true" postdomevents="true" LOOP="false" SHOWLOGO="false" />';
		W("#embed_video").html(player_str);
	});
})();
</script>
<%elseif $video.domain == 'pptv.com'%>
<script type="text/javascript">
(function () {
	var reqUrl = 'http://api.v.pptv.com/api/ipad/play.js?cb=%callbackfun%&' + ObjectH.encodeURIJson({
        "rid": "<%$video.player_html5_param.code%>",
        "r": Math.random()
    });
	QW.loadJsonp(reqUrl, function(data){
		var player_str = '<video width="100%" height="100%" controls="controls" autoplay="autoplay" id="html5_vplayer" ><source src="'+data.data+'" type="video/mp4" /> Your browser does not support the video tag.</video>';
		W("#embed_video").html(player_str);
	});
})();
</script>
<%else%>
<video width="100%" height="100%" controls="controls" autoplay="autoplay" id="html5_vplayer" ><source src="<%$video.player_html5_param.src%>" type="video/mp4" /> Your browser does not support the video tag.</video>
<%/if%>
