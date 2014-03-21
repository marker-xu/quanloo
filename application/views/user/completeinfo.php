<%extends file="common/base.tpl"%>

<%block name="title" prepend%>填写用户信息<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/reg.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>    
<%*<script type="text/javascript" src="<%#resUrl#%>/js/video/register.js?v=<%#v#%>"></script>*%>
<script type="text/javascript" src="<%#resUrl#%>/js/video/individual_tag.js?v=<%#v#%>"></script>

<%/block%>
<!--- 测试 -->
<%block name="bd"%>
<div id="bd">
    <%include file="inc/search.inc"%>
    <div class="wrap">
        <div class="reg_tit"><span class="step1"></span><em></em><span class="step2"></span></div>
        <!--登录class="clearBg"-->
        <div id="loginer">
            <form action="javascript:void(0)" method="post" id="register_form">
                <%Form::hidden('csrf_token', Security::token())%>
                <%Form::hidden('goto_f', $goto_f)%>
                <!--<h2 class="regTit overReg">请完善你的个人信息</h2>-->
                <ul class="ul">
                    <li>
                        <div class="tit">电子邮件</div>
                        <div class="formPlace cls">
                            <div class="l"><input type="text" class="text" id="email" name="email" tabindex="1" emel="err-email" value="<%$email|escape:"html"%>"></div>
                            <div class="l tips"><em id="err-email"></em></div>
                        </div>
                    </li>
                    <li>
                        <div class="tit">你的昵称</div>
                        <div class="formPlace cls"><div class="l"><input type="text" class="text" id="honeyname" name="name" tabindex="4" emel="err-honeyname" maxlength="10"<%if isset($nickname)%> value="<%$nickname|escape:"html"%>"<%/if%>></div>
                            <div class="l tips"><em id="err-honeyname"></em></div></div>
                        <div class="tipTxt">该怎么称呼你？昵称不小于1个字符且不大于10个字符，数字、_和- </div>
                    </li>
                    <li>
                        <div class="tit">个性签名</div>
                        <div class="formPlace cls">
                            <textarea rows="4" class="l textara" id="signature" tabindex="5" name="intro" emel="err-intro" maxlength="100"></textarea>
                            <div class="l tips"><em id="err-intro"></em></div>
                        </div>
                    </li>
                </ul>
                <div class="tit">添加兴趣标签有助于获得更符合口味的推荐视频</div>
                <div class="input_tag_wrap">
                    <div class="reg_input_tag">
                        <input type="text" class="text" id="input_tag" name="input_tag" tabindex="3">
                        <span id="submit_tag" ></span>
                    </div>
                    <div class="l tipss"><em id="err-input_tag"></em></div>
                    <div class="tipTxt">标签长度不超过10个字</div>
                    <input type="hidden" name="tags" id="tags"/>
                </div>
                <ul class="ul tag_edit">
                    <li class="cls">
                        <div class="l tag_l">
                            <h3>我已经添加的标签<span class="tipTxt">最多可以添加20个兴趣标签</span></h3>
                            <ul id="owned_tag" class="owned_tag">
                            </ul>
                        </div>
                        <div class="r tag_r">
                            <div class="r_wrap">
                                <h3><a class="r" href="###" id="change_tag">换一换</a>可能感兴趣的标签：</h3>
                                <ul id="guess_tag" class="guess_tag">
                                    <%foreach $select_tags as $strTag%>
                                    <li><a href="###"><%$strTag%></a></li>
                                    <%/foreach%>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <li style="padding-top: 12px;font-size: 14px;">
                        <input type="checkbox" name="accept_subscribe" id="accept_subscribe" checked="checked" value="1" /> <label for="accept_subscribe">订阅圈乐热门内容精选</label>
                    </li>
                </ul>
                <!--下一步按钮-->
                <div class="submiter cls"><button type="button" class="btn btn-reg s-ic-reg"></button></div>
            </form>
        </div>

        <div id="popup_group" class="popup_group"style="display:none;">
            <h2 class="regTit overReg" >
                <a href="#" class="page-nav">换一换</a>
                挑选一批你感兴趣的圈子，关注它们，收获更多有趣的视频
            </h2>
            <form action="/user/batchsubscribecircles" method="post">
                <%Form::hidden('csrf_token', Security::token())%>
                <%Form::hidden('goto_f', $goto_f)%>
                <div class="pg_mid" id="circle_parent">

                </div>
                <p style="text-align:right" id="js_watch_count"></p>
                <div class="pg_footer" style="position:relative">
                    <div class="pg_subbox">
                        <button class="watchall" id="js_watchall" type="button"></button>
                        <button class="pg_done" onclick="location.href='<%$goto_f%>'" type="button" ></button>
                    </div>
                </div>
            </form>
        </div>

        <!--//登录-->
        <!--第三方登录-->
        <div id="login3" >
            <div class="wrap">
                <h3><a href="/user/changeaccount">换个帐号登录</a></h3>
                <%*<!--                        <h3>用其他网站帐号登录：</h3>-->
                <!--                        <ul>-->
                <!--                            <li class="logo_snda"><a href="#">盛大通行证登录</a></li>-->
                <!--                            <li class="logo_sina"><a href="#">新浪微博账号登录</a></li>-->
                <!--                            <li class="logo_qq"><a href="#">腾讯微博账号登录</a></li>-->
                <!--                        </ul>-->*%>
            </div>
        </div>
        <!--第三方登录-->
    </div>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
    Dom.ready(function(){
        /*密码正确设置提示*/
        W('#register_form input').on('focus', function(){
            var el = W(this).parentNode('div').nextSibling('[class^=tips]')
            if(el.length){
                el.removeClass('okey');
            }
        });

        W('#register_form input[type=password]').on('blur', function(){
            var we = W(this),
            tipse = we.parentNode('div').nextSibling('[class^=tips]');
            if(Valid.check(this) && we.val()){
                tipse.addClass('okey');
            } else {
                tipse.removeClass('okey');
            }
        }).on('focus', function(){
            var el = W(this).parentNode('div').nextSibling('[class^=tips]')
            if(el.length){
                el.removeClass('okey');
            }
        });
        W("#honeyname").focus();



        //第二步

        //用于显示错误提示
        //HTML 格式 <div class="l tips"><em id="err-intro"></em></div>
        //@param id 元素的CSS表达式
        //@param status
        //0 表示失败，这时第三个参数有效，显示这红色字 
        //1表示成功，会在此元素的父节点上添加一个叫okey的类名，显示绿色的勾号
        //2表示隐藏，去掉元素的innerHTML与父节点上的okey的类名
        //@param msg 错误消息
        function showTip(id, status, msg){
            var el = W(id), parent = el.parentNode();
            switch(status){
                case 0 :
                    parent.removeClass("okey");
                    el[0] && (el[0].innerHTML = msg);
                    break;
                case 1:
                    parent.addClass("okey");
                    el[0] && (el[0].innerHTML = "");
                    break
                case 2:
                    parent.removeClass("okey");
                    el[0] && (el[0].innerHTML = "");
                    break
            }
        }

        /*检测用户名和邮箱是否已注册*/
        QW.valid_reg_diy_lock = {};
        var u_map = {
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
                        if(d.err!="ok"){
                            var msg = d.err=="sys.forbidden.blackword" ? d.msg : u_map[_name][2]
                            u_map[_name][2] = msg
                            Valid.fail(_this, msg);
                            QW.valid_reg_diy_lock[_name] = true;
                            var el = W(_this).parentNode('div').nextSibling('[class^=tips]')
                            if(el.length){
                                el.removeClass('okey');
                            }
                        } else {
                            QW.valid_reg_diy_lock[_name] = false;
                            var el =W(_this).parentNode('div').nextSibling('[class^=tips]')
                            if(el.length){
                                el.addClass('okey');
                            }
                        }
                    });
                }
            }
        }).on('focus', function(){
            //  showTip("#err-"+this.id, 2)
        });
<%*        //  切换页面
        //        var numberDIV = W(".page-nav .progress")[0], cur = 1
        //        function switchButtonAndPanel(n, m, prev, next){
        //            prev.className =  n==1 ? "bt-pre-dis" : "bt-pre"
        //            next.className =  m==5 ?  "bt-next-dis" : "bt-next";
        //            var start = (n -1) * 6, end = start + 6;
        //            var els =  W(".circleitem").hide();
        //            var array = Array.prototype.slice.call(els,start, end);
        //            W(array).show()
        //
        //            numberDIV.innerHTML = n;
        //        }
        //        var prevBtn = W("#bt-pre")[0]
        //        var nextBtn = W("#bt-next")[0]
        //        W("body").delegate(".bt-pre","click", function(){
        //            --cur
        //            switchButtonAndPanel(cur, cur+1, prevBtn, nextBtn)
        //        });
        //        W("body").delegate(".bt-next","click", function(){
        //            ++cur
        //            switchButtonAndPanel(cur, cur+1, prevBtn, nextBtn)
        //        });
        //
        //        //全选与非全选
        //        W("body").delegate("#closely_all","click",function(){
        //            var checked = this.checked;
        //            W(".circleitem input[type=checkbox]").set("checked",checked)
        //        });

        /*
        获取注册时推荐的圈子
URI：/user/ getregistercirclelist
请求类型：GET
请求参数：
offset– 起始位置，默认0
        count– 数量，默认6个
        format– 类型，json/html，默认html
返回数据：
        {“err”:”ok”, “data”:array(
                total : 圈子总数,
                data: 圈子列表(format=json时),
                html: 页面模板(format=html时)
)}
         */*%>
        function addYourInterestedCircle(){
            QW.Ajax.get("/user/getregistercirclelist",{
                offset:0,
                count:6,
                format:"html"
            },function(json){
                if(json.err == "ok"){
                    W("#circle_parent").setHtml(json.data.html)
                }
            })
        }
        W("#js_watchall").on("click",function(){
            Ajax.post("/user/batchsubscribecircles", QW.NodeH.encodeURIForm(this.form),function(json){
                if(json.err == "ok"){
                    var n = json.data.subscribed_circle_count;
                    refreshCount(n);
                    W(".b-follow").addClass("followed").removeClass("b-follow");
                    setTimeout(addYourInterestedCircle,50);
                }
            });
        });
        W("body").delegate(".page-nav","click",function(){
            addYourInterestedCircle()
        })
        function refreshCount(n){
            W("#js_watch_count").setHtml( n+"个圈子已关注了");
        }

        //快速通道，不用每次都注册新用户：http://dev.quanloo.com:8181/user/completeinfo?test=123
        W("body").delegate(".b-follow,.followed", "click", function(){
            setTimeout(function(){
                Ajax.get("/user/getsubscribedcount",function(json){
                    if(json.err == "ok"){
                        var n = json.data.subscribed_circle_count;
                        refreshCount(n);
                    }
                });
            },300)
        })

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
            /* 这是点下一步按钮 */
            W('.btn-reg').on('click',function(e){
                e.preventDefault();

                if(QW.Valid.checkAll(this.form, false, {myValidator: check_diy})){
                    Ajax.post("/user/completeinfo", QW.NodeH.encodeURIForm(this.form),function(json){
                        if(json.err == "ok"){
                            W("#loginer").hide();
                            W("#popup_group").show();
                            W(".step2").addClass("over")//关注热门圈子变绿
                            addYourInterestedCircle()
                        }else{
                            var map ={
                                name:"#err-honeyname",
                                input_tag:"#err-input_tag"
                            }
                            //  "name intro input_tag"
                            //显示错误提示
                            for(var name in json.msg){
                                if(map[name]){
                                    showTip(map[name], 0 , json.msg[name])
                                }
                            }
                        }
                    });
                }

            });
        });
    });
</script>
<%/block%>
