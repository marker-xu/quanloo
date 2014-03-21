<!doctype html>
<%strip%>
<%config_load file='site.conf'%>
<%block name="view_conf"%>
<%/block%>
<html>
<head>
    <meta charset="utf-8">
    <title><%block name="title"%><%$smarty.block.child%>_圈乐CMS<%/block%></title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>    
    <%function name="resource" type="" files=""%>
		<%include file="inc/resource.inc" files=$files type=$type group=$group%>
	<%/function%>    
	<%block name="sb"%>
		<%resource files=[
			"res_inc/css/bc.inc"
		]%>
		<%$smarty.block.child%>
	<%/block%>
    <style type="text/css">
        #bd {font-size:14px}
        #bd h1,#bd h2,#bd h3,#bd table,#bd ul,#bd form,#bd dl,#bd p {margin: 5px 0;}
        #bd h1 {font-size:16px;}
        #bd table {width:100%;border-collapse:collapse;border-spacing:0;font-size:12px;}
        #bd th,#bd td {padding:5px 2px;border:1px solid #ccc}
        #bd th {font-weight:bold;text-align:center;}
        #bd table.hover tr:hover td {background:#ddd}
        #bd li {margin: 3px 0pt;}
        #bd button {border: 2px outset #D4D0C8;}        
        #bd .pagination {margin:10px auto;text-align: center;}
        #bd .nav {margin: 5px 0pt; font-weight: bold;}
        #bd .nav .current {background: #ccc;}
        #bd .box {border: 1px solid #ccc; padding: 5px;}
    </style>
	<%block name="custom_css"%><%/block%>
</head>
<%/strip%>
<body>
    <div id="<%block name="doc"%>doc1<%/block%>">
        <%block name="hd"%>
            <%include file="inc/header.inc" h1=$h1%>
        <%/block%>
		<div id="bd">
            <%block name="nav"%>
            <div id="nav" class="nav">
            	<a href="/admin">首页</a> |
<!--            	<a href="/admin_topiccircle">主题圈管理</a> | -->
            	<a href="/admin_promotecircle">推广圈子管理</a> | 
            	<a href="/admin_circlecandidate">候选圈子管理</a> | 
            	<a href="/admin_circle">圈子管理</a> | 
            	<a href="/admin_hotquery">热搜词管理</a> | 
            	<a href="/admin_feedback">用户反馈</a> |
            	<a href="/admin_link">友情链接管理</a> |
            	<a href="/admin_wordlist">敏感词管理</a> |
            	<a href="/admin_appversion">移动APP发布</a> |
            	<a href="/admin_ad">广告管理</a> |
            	<a href="/admin_acl">权限管理</a>
            </div>
            <%/block%>
            <%block name="main"%>
            <%/block%>
		</div>
        <%block name="ft"%>
            <%include file="inc/footer.inc"%>
        <%/block%>
    </div>
    <script type="text/javascript" src="<%#resUrl#%>/js/third/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="<%#resUrl#%>/js/third/jquery/jquery-url-2.0.js"></script>
    <%block name="custom_js"%><%/block%>
    <script type="text/javascript">
    $(function () {
    	$('#feed_back').hide();

    	$('.nav a').each(function (index, ele) {
        	if (window.location.pathname.split('/')[1] == $(ele).attr('href').split('/')[1]) {
            	$(ele).addClass('current');
            	return false;
        	}
        });
    });
    </script>
    <%block name="foot_js"%><%/block%>
</body>
</htm>