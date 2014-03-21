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
    
    
	<%block name="sb"%>
	    <link rel="stylesheet" type="text/css" href="<%#apiResUrl#%>/css/video/bc.css?v=<%#v#%>">
		<%$smarty.block.child%>
	<%/block%>
	<%block name="custom_css"%><%/block%>
	<%block name="2b"%>
	
		<%$smarty.block.child%>
	<%/block%>
	<%block name="custom_js"%><%/block%>
</head>
<%/strip%>
<body>
    <div id="<%block name="doc"%>doc1<%/block%>">
        <%block name="hd"%>
        <%/block%>
        <%block name="bd"%>
        <%/block%>
        <%block name="ft"%>
        <%/block%>
    </div>
    <%block name="foot_html"%>
    <%/block%>

    <%*其他尾部js*%>
    <%block name="foot_js"%>
    	<script type="text/javascript" src="<%#apiResUrl#%>/js/apps/qwrap.src<%#combo#%>.js?v=<%#v#%>"></script>
		<script type="text/javascript" src="<%#apiResUrl#%>/js/components/??marmot/src/marmot<%#combo#%>.js" data--opts="{server:'<%#statUrl#%>'}"></script>
    	<%$smarty.block.child%>
    <%/block%>

    <%*其他自定义尾部数据*%>
    <%block name="custom_foot"%>
    <%/block%>    
</body>
</html>
