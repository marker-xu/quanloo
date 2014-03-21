$(function(){
    //用于显示错误提示
    //HTML 格式 <div class="tips"><em id="err-intro"></em></div>
    //@param expr 元素的CSS表达式
    //@param status
    //0 表示失败，这时第三个参数有效，显示这红色字
    //1表示成功，会在此元素的父节点上添加一个叫okey的类名，显示绿色的勾号
    //2表示隐藏，去掉元素的innerHTML与父节点上的okey的类名
    //@param msg 错误消息
    function showTip(expr, status, msg){
        var el = $(expr), parent = el.parent(), node = el[0], nodes = showTip.nodes;
        switch(status){
            case 0 :
                parent.removeClass("okey");
                if(node){
                    node.innerHTML = msg
                    if($.inArray(node, nodes) == -1){
                        nodes.push(node)
                    }
                }
                break;
            case 1:
                parent.addClass("okey");
                if(node){
                    node.innerHTML = "";
                    var i = $.inArray(node, nodes);
                    nodes.splice(i,1)
                }
                break
            case 2:
                parent.removeClass("okey");
                if(node){
                    node.innerHTML = "";
                    i = $.inArray(node, nodes);
                    nodes.splice(i,1)
                }
                break
        }
    }
    showTip.nodes  = [];
    /**
     *@param root 绑定事件的元素的CSS选择器，通常是form元素
     *@param name 控件的类名，要去掉前面的点号。之所以用类名，因为checkbox是一组的，共用一个name值，不能用ID
     *@param obj 验证用的函数与错误提示，错误提示作为键名，函数为值。
     *@param checktype 触发验证用的事件名，默认为blur
     */
    function validate(root, name, obj, checktype){
        checktype = checktype || "blur"
        $(root).delegate("."+name, checktype, function(){
            var ok = true
            for(var msg in obj){
                if(!obj[ msg ](this)){
                    showTip("#err_"+name, 0 , msg );
                    ok = false;
                    break
                }
            }
            if(ok){
                showTip("#err_"+name, 1);
            }
        } ).delegate("."+name, "focus",function(){
            showTip("#err_"+name, 2);
        })
    }
    validate("#add_widget_form", "name",{
        "不能为空":function(el){
            return $.trim(el.value).length != 0
        }
    });

    var checkNumber = {
        "只能填一个正整数":function(el){
            var i = el.value;
            return  Number(i) > 0 &&  parseInt( i ) === Number(i);
        }
    }
    validate("#add_widget_form", "width", checkNumber);
    validate("#add_widget_form", "height",checkNumber);
    validate("#add_widget_form", "pic_width",checkNumber);
    validate("#add_widget_form", "pic_height",checkNumber);

    validate("#add_widget_form", "video_count",{
        "数量在1~10之间":function(el){
            var i = el.value
            return  Number(i) > 0 &&  Number(i) < 11 && parseInt( i ) === Number(i);
        }
    });
    //添加视频源
    var raddcircle = /\/circle\/(\w+)/
    var raddvideo = /\/v\/(\w+)/
    function truncate(target, length, truncation) {
        length = length || 30;
        truncation = truncation === void(0) ? '...' : truncation;
        return target.length > length ?
        target.slice(0, length - truncation.length) + truncation : String(target);
    }
    //指定视频源：
    function addSource(parent, name, val ) {
        var html = "<li><span >"+truncate(val,33)+"</span><input type='hidden' value='"+val
        +"' name='"+name+"[]'/>"+ "<button class='js_delete_item' type=button><span>删除</span></button></li>"
        parent.append(html)
    }
    $("#js_add_circle,#js_add_video").click(function(){
        var input = $(this).prev();
        var val = input.val();
        var arr = val.match(  this.id == "js_add_circle" ?  raddcircle : raddvideo);
        var name = this.id == "js_add_circle"  ? "cid_list" :"vid_list"
        input.val("")
        if(arr && arr[1]){
            alert($(this).next(".toggle-area").length)
            addSource($(this).next(".toggle-area"), name, arr[1] )
        }
    })
    
    //  移除源
    $("body").delegate(".js_delete_item","click",function(){
        $(this).parent("li").remove()
    })
    $(".js_circle_input").keydown(function(){
        showTip("#err_cid_list", 2);
    });
    $(".js_video_input").keydown(function(){
        showTip("#err_vid_list", 2);
    });

    function copyToClipboard(copyText) {
        if (window.clipboardData && clipboardData.setData)  {
            clipboardData.clearData();
            clipboardData.setData("text", copyText)
            alert("已复制到剪切板中了！")
        }  else  {
            confirm(copyText)
        }
    }
    //将代码复制到剪贴板
    $("#js_copy_html").click(function(){
        var val = $("#js_set_html").val();
        val = $.trim(val);
        if(!val.length){
            return alert("没有要复制的内容")
        }
        copyToClipboard(val)
    });
    //预览
    $("#js_get_html").click(function(){
        $.post("/widget/getjscode",$(this.form).serialize(),function(json){
            if(json.err == "ok"){
                $("#js_set_html").val(json.data.html);
                $("#preview_area").html(json.data.html);
                $("#js_input_wid").val(json.data.wid);
            }else{
                for(var i in json.msg){
                    showTip("#err_"+i, 0, json.msg[i]);
                }
            }
        });
    });

    $("#js_save_html").click( function(){
        if(showTip.nodes.length ){
            return
        }
        $.post(this.form.action, $(this.form).serialize(),function(json){
            if(json.err == "ok"){
                location.href = "/widget";
            }else{
                for(var i in json.msg){
                    showTip("#err_"+i, 0, json.msg[i]);
                }
            }
        })

    });

    $("#js_reset_form").click(function(){
        this.form.reset();
    })




    //    URI：/widget/removewidget
    //请求类型：GET
    //请求参数：
    //wid– widget的ID
    //返回数据：{“err”:”ok”}*/
    //首页删除应用
    $("body").delegate(".delete_widget", "click", function(){
        var id = this.getAttribute("wid"), a = this;
        $.get("/widget/removewidget",{
            wid:id
        },function(json){
            if(json.err == "ok"){
                $(a).parent("tr").remove();
            }
        })
    })

    $("body").delegate(".copy_widget","click",function(){
        var val =  $(this).attr("iframe-html");
        copyToClipboard(val)
    })
    //===========================认证页面==========================
    //    //completecompanyinfo的表单验证
    validate("#company_info_form", "site_name",{
        "字数错误":function(el){
            var i = el.value.length
            return i > 0 && i <= 18
        }
    });
    validate("#company_info_form", "desc",{
        "字数错误":function(el){
            var i = el.value.length
            return i > 0 && i <= 140
        }
    });
    validate("#company_info_form", "icp",{
        "不能为空":function(el){
            var val = $.trim(el.value);
            return val.length;
        }
    });

    validate("#company_info_form","email",{
        "格式不正确":function(el){
            var val = $.trim(el.value);
            if(!val.length)
                return false;
            return /^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/.test(val)
        }
    });
    validate("#company_info_form","phone",{
        "格式不正确":function(el){
            var val = $.trim(el.value);
            if(!val.length)
                return false;
            return /(^([0-9]{2,4}\-)?[1-9][0-9]{6,7}$)|(^(13|15|18|14)\d{9}$)/.test(val)
        }
    });

    //    //点击开始验证网站按钮
    /**
验证域名
URI：/widget/testdomain
请求类型：GET
请求参数：domain– 域名
返回数据：{“err”:”ok”,}
     */
    $("#check_domain_btn").click(function(){
        var val = $(".domain_input").val();
        val = $.trim(val);
        if(!val.length){
            return showTip("#err_domain", 0, "不能为空");
        }
        $(".domain").val(val);//临时

        $.get("/widget/testdomain",{
            domain:val
        },
        function(json){
            if(json.err == "ok"){
                alert("网站验证通过")
                $(".domain").val(val)
            }else{
                showTip("#err_domain", 0, json.msg);
            }
        });
    });

    $(".domain_input").focus(function(){
        showTip("#err_domain", 1);
    });
    $("#check_all").click(function(){
        var bool = this.checked;
        $(".type_list").prop("checked",bool)
    });
    $("#complete_info").slideDown(400)
    setTimeout(function(){
        $("#complete_info").slideUp(400)
    },3000);


    W("#submit_info").on("click", function(){
        if(showTip.nodes.length ){
            return
        }
        $.post("/widget/completecompanyinfo",$(this.form).serialize(), function(json){
            if(json.err == "ok"){
                location.href = "/widget"
            }else{
                for(var i in json.msg){
                    showTip("#err_"+i, 0, json.msg[i]);
                }
            }
        })
    });

})











//
//Dom.ready(function(){

////调整底部的位置
////    function adjustFooterPosition(){
////        var footer = W("#ft")
////        var curTop = footer.getXY()[1];
////        var pageHeight =  Dom.getDocRect().scrollHeight ;
////
////        window.console && console.log( pageHeight - curTop - 90 )
////        footer.css("top", ( pageHeight - curTop - 90 ) +"px")
////    }
////
////    setTimeout(adjustFooterPosition, 300);
////    W(window).on("resize",adjustFooterPosition)
//
//
//})