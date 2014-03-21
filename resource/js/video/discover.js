//发现页
;$(function(){
//    var windowY = $(window).height() - 220;//求出在loader出现页面底部时，loader到顶部的距离
//    var lock = false;
//    var loader = $("#loading");
//    var i = 0
//    $(window).on("scroll", function(){
//        var a = loader[0].getBoundingClientRect().top;
//        console.log(a - i);
//        i = a;
//       // document.title =  a +"  "+windowY
//        if(a < windowY && !lock){
//            lock = true;
//            var hide = $(".y-guess-like-hide:first");//找出隐藏的砖头
//            if(hide.length){
//                hide.removeClass("y-guess-like-hide");//显示隐藏的砖头
//                lock = false;
//            }else{
//                loader.hide();
//            }
//        }
//    });
    $("html").delegate(".i-comment-discover","click",function(){
        if( $('#logined_mark').length == 0 ){
            LOGIN_POPUP();
        }else{
            var vid =  $(this).attr("data-vid");
            location.href = PageUtil.videoPlayUrl(vid)        //"/v/" + vid +".html"
        }
    })

})
