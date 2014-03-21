<!doctype html>
<%strip%>
<%config_load file='site.conf'%>
<%block name="view_conf"%>
<%/block%>
<html>
<head>
    <meta charset="utf-8">
    <%block name="seo_meta"%><%/block%>  
    <title><%block name="title"%>-圈乐<%/block%></title> 
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>    
    <script type="text/javascript">
            var LOG_headToloaded = new Date();
    </script>
    <%function name="resource" type="" files=""%>        
		<%include file="inc/resource.inc" files=$files type=$type group=$group%>
	<%/function%>
    <script>        
        <%if $login_iframe_url%>var LOGIN_IFRAME_URL = "<%$login_iframe_url|escape:'javascript'%>";<%/if%>
        <%if $login_user%>
        var UID = "<%$login_user._id%>";
        <%else%>
        var UID = "";
        window.COMPLETE_SDID = <%if $complete_sdid%><%$complete_sdid%><%else%>0<%/if%>;
		function checkstat(stat)
        {
			var f = location.href.split('#')[0].split('/').slice(3).join('/');
	    	if(f.indexOf("?")>-1) {
	        	f+="&middle=1";
	        } else {
	        	f+="?middle=1"
	        }
            if( stat.CAS_LOGIN_STATE=="1" && !UID)
            {
                window.location.href="http://cas.sdo.com/cas/login?service="+encodeURIComponent('<%#siteUrl#%>/user/login?f='+encodeURIComponent( f ));
            }
        }        
        <%/if%>
        window.XAT_REQUEST_URL = '<%#atUserSugUrl#%>/su?query=&qid=<%$login_user._id%>&cb=XAtComplete.updateQuanloo';
        var SITE_URL = '<%#siteUrl#%>';
    </script>
	<%block name="sb"%>
	    <%include file="res_inc/css/bc.inc"%>
		<%$smarty.block.child%>
	<%/block%>
	<%block name="custom_css"%><%/block%>
	<%block name="2b"%>
		<%resource files=[
            "res_inc/js/apps.inc",
            "res_inc/js/config.inc",
            "res_inc/js/video.inc"
		]%>
		<%$smarty.block.child%>
	<%/block%>
	<%block name="custom_js"%><%/block%>
    <%block name="log_loadTime"%>
        <%$intTmp=strpos($smarty.server.REQUEST_URI, '?')%>
        <%if $intTmp !== false%>
            <%$thisPguri = substr($smarty.server.REQUEST_URI, 0, $intTmp)%>
        <%else%>
            <%$thisPguri = $smarty.server.REQUEST_URI%>
        <%/if%>
        <script type="text/javascript">
            window.onload = function(){
                var t = new Date();
                LOG_headToloaded = t.getTime() - LOG_headToloaded.getTime();
                var randomer = function(src){
                    var islog = 0;
                    var key = 'hasLoged'+src;
                    if(!QW.Cookie.get(key)){
                        islog = 1;
                        QW.Cookie.set(key,'1',{ expires:24*60*60*1000});
                    }else{
                        if(Math.random()*10<=1)islog = 1;
                    }
                    return islog;
                };
                var _src = QW.Marmot.pageId || "null";
                if(!randomer(_src))return;
                var render_time = LOG_headToloaded;
                QW.use("Ajax,Marmot",function(){
                    setTimeout(function(){
                        var LOG_loadindex = new Date(),all,theHost = document.location.href;
                        QW.Ajax.get(theHost,{t:Math.random()},function(){
                            var t = new Date(); 
                            LOG_loadindex = t - LOG_loadindex;
                            var obj = {page_id:"time_stat",html_time:LOG_loadindex,render_time:render_time,src:_src};
                            QW.Marmot.log(obj);
                        });
                    },1000);
                });
            };
        </script>
    <%/block%> 
</head>
<%/strip%>
<body>
    <div id="<%block name="doc"%>doc1<%/block%>">
        <%block name="hd"%>
            <%include file="inc/header.inc"%>
        <%/block%>
        <%block name="bd"%>
        <%/block%>
        <%block name="ft"%>
            <%include file="inc/footer.inc"%>
        <%/block%>
    </div>
    <%block name="foot_html"%>
    <%/block%>
    <%include file="inc/share.inc"%>
    <a class="go-top" href="#" id="gotop"></a>
    <%*自动添加前后端验证规则*%>
    <%strip%>
    <%if $valid_rules%>
    <script type="text/javascript">
        QW.use('Valid', function(){
            Valid.CheckRules = (<%$valid_rules%>);
        });
    </script>
    <%/if%>
    <%/strip%>

    <%*其他尾部js*%>
    <%block name="foot_js"%>
        <script type="text/javascript" src="<%#resUrl#%>/js/components/??marmot/src/marmot<%#combo#%>.js?v=1.0.1" data--opts="{server:'<%#statUrl#%>'}"></script>
        <%$smarty.block.child%>
        <script type="text/javascript" src="http://ipic.staticsdo.com/external/install_beacon.js"></script>
<%*<!--        <script type="text/javascript">-->
<!--        var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");-->
<!--        document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F26589a9a85e84ce26c08b8bb5403c6be' type='text/javascript'%3E%3C/script%3E"));-->
<!--        </script>-->*%>
    <%/block%>

    <%*其他自定义尾部数据*%>
    <%block name="custom_foot"%>
        <script type="text/marmot">
            {
                "site_id"   : "videosearch",
                <%if strncasecmp($thisPguri, '/v/', 3)%>
                "page_id"   : "<%$thisPguri|replace:'/':''|default:'index'|escape:"url"%>",
                <%/if%>
                "user_id"   : "<%$login_user._id%>",
                "cookie_id": "<%Session::instance()->id()%>",
                "url"       : "http://<%$smarty.server.HTTP_HOST|escape:'url'%><%$smarty.server.REQUEST_URI|escape:'url'%>",
                "referrer"  : "<%$smarty.server.HTTP_REFERER|escape:'url'%>",
                "bucket"    : "1"
            }
        </script>               
        <%$smarty.block.child%>
    <%/block%>    
</body>
</html>