Dom.ready(function(){
    var hideTip = function(){
        this.value = "";
        W(this).parentNode().query("span").hide();
        W(this).removeClass("error")
    }
    //验证昵称
    W("#register_form input[name=name]").on("blur",function(){
        QW.Ajax.get("/user/is_nick_reged",{
            nick:this.value
        },function(json){
            if(json.ok){
                W(this).nextSibling("span.ok").setStyle("display","inline-block");
            }else{
                W(this).nextSibling(".error_msg").setStyle("display","inline-block");
                W(this).addClass("error")
            }
        });
    }).on("focus",hideTip);
    //验证email
    W("input[name=email]").on("blur",function(){
        if(!/\S/.test(this.value)){
            W(this).nextSibling(".error_msg").setStyle("display","inline-block").setHtml("不能为空");
            W(this).addClass("error")
        }else if(!/^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/.test(this.value)){
            W(this).nextSibling(".error_msg").setStyle("display","inline-block").setHtml("格式不对");
            W(this).addClass("error")
        }else{
            QW.Ajax.get("/user/is_email_reged",{
                email:this.value
            },function(json){
                if(json.ok){
                    W(this).nextSibling("span.ok").setStyle("display","inline-block");
                }else{
                    W(this).nextSibling(".error_msg").setStyle("display","inline-block").setHtml("邮箱已存在");
                    W(this).addClass("error")
                }
            });
           
        }
    }).on("focus",hideTip);
    //验证密码
    W("#password").on("blur",function(){
        if(!/\S/.test(this.value)){
            W(this).nextSibling(".error_msg").setStyle("display","inline-block").setHtml("不能为空");
            W(this).addClass("error")
        }else{ 
            W(this).nextSibling("span.ok").setStyle("display","inline-block")
        }
    }).on("focus",hideTip);
    //确认密码
    W("#comfirm_password").on("blur",function(){
        if(W("#password").getValue() != this.value){
            W(this).nextSibling(".error_msg").setStyle("display","inline-block");
            W(this).addClass("error")
        }else{
            W(this).nextSibling("span.ok").setStyle("display","inline-block");
        }

    }).on("focus",hideTip);
    //验证密码长度
    function checkLen(){
        var max = 100;
        var len = this.value.length;
        var el = W(this).nextSibling("div");
        if(max - len >= 0){
            el.setHtml('您还可以输入<span class="bolder_black">'+ (max - len) +'</span>个字')
        }else{
            el.setHtml('您已超出<span class="bolder_black">'+ (max - len) * -1 +'</span>个字')
            W(this).addClass("error")
        }
    }
    W("input[name=password]").setStyle("width", W("input[name=email]").getCurrentStyle("width"))
    if(window.VBArray){//如果是IE
        W("#register_form textarea").on("propertychange",function(e){
            if(e.propertyName == "value"){
                checkLen.call(this)
            }
        });
    }else{
        W("#register_form textarea").on("input",function(e){
            checkLen.call(this)
        });
    }
    //提交表单
    W("#register_form,#login_form").on("submit",function(e){
        if(!this.elements[0].value || W(this).query(".error").length){
            e.preventDefault();
        }
    })
})
