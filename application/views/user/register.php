<%extends file="common/base.tpl"%>

<%block name="title" prepend%>注册<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
	<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/reg2.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>    
    <%*<script type="text/javascript" src="<%#resUrl#%>/js/video/register.js?v=<%#v#%>"></script>*%>
<%/block%>
<!--- 测试 -->
<%block name="bd"%>  
        <div id="bd">
			<div class="wrap" id="register">
                <!--第三方登录-->
                <div class="oauth-login clearfix">
                    <h3>你可以选择下方合作网站账号直接登录圈乐，一分钟完成注册。</h3>
						<div class="bt-ologins clearfix">
                       <a href="/connect?type=2" onclick="sinaLogin();return false;" class="bt-ologin bt-ologin-sina">新浪</a>
                    		<a href="/connect?type=3" onclick="tqqLogin();return false;" class="bt-ologin bt-ologin-tqq">腾讯微博</a>
                            <a href="/connect?type=6" onclick="qqLogin();return false;" class="bt-ologin bt-ologin-qzone">Qzone</a>
                    		<a href="/connect?type=5" onclick="doubanLogin();return false;" class="bt-ologin bt-ologin-douban">豆瓣</a>
<!--                            <a href="/connect?type=4" onclick="renrenLogin();return false;" class="bt-ologin bt-ologin-renren">人人</a>-->
                    </div>
                </div>
                <!--第三方登录-->     
            	<!--登录-->
            	<div class="snda-login">
                	<h3 title="注册" class="s-ic-reg regTit">你也可以快速注册盛大通行证！</h3>
                	<iframe id="regframe" width="800" height="450"  frameborder="0" allowtransparency="true" scrolling="no" src="<%$register_iframe_url%>"></iframe>
                </div>
                <!--登录-->
            </div>
        </div>
<%/block%>

<%block name="foot_js"%>
<%*
<script type="text/javascript">
Dom.ready(function(){
    /*密码正确设置提示*/
    W('#register_form input').on('focus', function(){
        W(this).parentNode('div').nextSibling('.tips').removeClass('okey');
    });
    W('#register_form input[type=password]').on('blur', function(){
        var we = W(this),
            tipse = we.parentNode('div').nextSibling('.tips');
        if(Valid.check(this) && we.val()){
            tipse.addClass('okey');
        } else {
            tipse.removeClass('okey');
        }
    }).on('focus', function(){
        W(this).parentNode('div').nextSibling('.tips').removeClass('okey');
    });
    /*检测用户名和邮箱是否已注册*/
    QW.valid_reg_diy_lock = {};
    var u_map = {
        'email': ['email','is_email_reged', '该email帐号已经注册'],
        'name' : ['nick', 'is_nick_reged', '该昵称已经注册']
    }
    W('#register_form input[type=text]').on('blur', function(){
        if(Valid.check(this) && W(this).val()){            
            var _name = W(this).attr('name');
            if(u_map[_name]){
                var _this = this;
                var url = QW.Config.get('host') + '/user/' + u_map[_name][1];
                var param = {};
                param[u_map[_name][0]] = W(this).val();               
                Ajax.get(url, param, function(d){
                    if(!d.err){
                        Valid.fail(_this, u_map[_name][2]);
                        QW.valid_reg_diy_lock[_name] = true;
                        W(_this).parentNode('div').nextSibling('.tips').removeClass('okey');
                    } else {
                        QW.valid_reg_diy_lock[_name] = false;
                        W(_this).parentNode('div').nextSibling('.tips').addClass('okey');
                    }                    
                });
            }
        }
    }).on('focus', function(){
    });
    /*提交注册*/
    QW.use('Ajax,Valid', function(){
        function check_diy(el){
            var _name = W(el).attr('name');
            if(u_map[_name] && QW.valid_reg_diy_lock[_name]){
                Valid.fail(el, u_map[_name][2]);
                return false;
            }
            return true;
        }
        W('#register_form').on('submit',function(e){
            e.preventDefault();
            if(QW.Valid.checkAll(this, false, {myValidator: check_diy})){
                Ajax.PageLogic.request(this, null, {
                    cooldown: 3000,
                    validate: true
                });
            }
        });        
    });
});
</script>
*%>
<%/block%>
