Dom.ready(function(){
    var hideTip = function(){
        this.value = "";
        W(this).parentNode().query("span").hide();
        W(this).removeClass("error")
    }
    //验证昵称
    W("#old_password").on("blur",function(){
        QW.Ajax.get("url",{
            user_id:W("#user_id").getValue()
        },function(json){
            if(json.ok){
                W(this).nextSibling("span.ok").setStyle("display","inline-block");
            }else{
                W(this).nextSibling(".error_msg").setStyle("display","inline-block");
                W(this).addClass("error")
            }
        });
    }).on("focus",hideTip);
    //验证新密码与其安全强度
    W("#new_password").on("blur",function(){
        var value = this.value || "";
        if(!value || !/\S/.test(value) || value.length < 6 || value.length > 16 ){
            W(this).nextSibling(".error_msg").setStyle("display","inline-block");
            W(this).addClass("error")
        }else{
            var a =  /\d/.test(value)
            var b = /[a-z]/.test(value)
            var c = /[A-Z]/.test(value)
            var d = /[^\x00-\xa0]/.test(value)
            var strong = a+b+c+d, className = "common_safe";
            //将密码的字符构成分为四个类别，如果类别数少于2为弱密码，等于4为强密码
            if(strong < 2){
                className = "weak_safe"
            }
            if(strong == 4){
                className = "very_safe"
            }
            W("#safe_bar")[0].className = className
            W(this).nextSibling("span.ok").setStyle("display","inline-block");
        }
    }).on("focus",hideTip);
    
    //确认密码
    W("#comfirm_password").on("blur",function(){
        if(W("#new_password").getValue() != this.value){
            W(this).nextSibling(".error_msg").setStyle("display","inline-block");
            W(this).addClass("error")
        }else{
            W(this).nextSibling("span.ok").setStyle("display","inline-block");
        }
         
    }).on("focus",hideTip);
   
    //提交表单
    W("#register_form").on("submit",function(e){
        if(!this.elements[0].value || W(this).query(".error").length){
            e.preventDefault();
        }
    });

})
