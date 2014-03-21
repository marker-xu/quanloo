<div id="hd">
    <div class="wrap">
        <div class="content">
            <div id="logo">
                <span style="color:#fff;font-weight:700;font-size:30px;">圈乐widget定制系统</span>
            </div>
            <%if $current_action!="default"%>
            <ul class="widget_bar">
                <li class="<%if $current_action=="index"%>current<%/if%>"><a href="/widget/">首页</a></li>
                <li class="<%if $current_action=="addwidget" or $current_action=="modifywidget"%>current<%/if%>"><a href="/widget/addwidget">widget制定</a></li>
                <li class="<%if $current_action=="completecompanyinfo"%>current<%/if%>"><a href="/widget/completecompanyinfo">信息认证</a></li>
            </ul>
            <%/if%>
            <%include file="widget/inc/_header_search.inc" inline%>
        </div>
    </div>
</div>
<div class="black-tips"><span>提示</span><em></em></i></div>

<%include file="widget/inc/_header_login.inc" inline%>

<a href="###" id="feed_back" class="marmot" data--marmot="{page_id:'click_feedback'}"><img src="<%#resUrl#%>/img/feedback.png"></a>
<style>

    #hd{
        height: 80px;
        background:#077DB4;
    }
    #hd .wrap {
        height: 80px;
        background: #077DB4;
    }
    #hd .content{
        height: 80px;
        position:relative;
    }
    #hd .widget_bar{
        position:absolute;
        bottom:0px;
    }
    #bd{
        height:auto!important;
        height:530px;
        min-height:530px;
    }
    .widget_bar{
        list-style:none;
        height:30px;
    }
    .widget_bar li{
        float:left;
        margin-left:20px;
    }
    .widget_bar li a{
        display:block;
        padding:0px 20px;
        position:relative;
        bottom:-2px;
        height:26px;
        line-height:26px;
        border:1px solid #8dceee;
        border-bottom:0 none;
        text-align:center;
        background:#2d99cc;
        color:#fff;
        text-decoration:none;
        letter-spacing:2px;
    }
    .widget_bar .current a{
        background:#077db4;
    }
    /*改变logo*/
    #logo {
        margin:10px;
        width:400px;
    }
    /*移除登录面板两边的黑线*/
    #logined{
        border:0 none!important;
    }
    .logined_wrap  {
        border:0 none!important;
    }
    .logined_pop{
        background: #329cd0;
    }


</style>