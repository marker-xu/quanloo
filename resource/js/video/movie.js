/* 
 * http://dev.quanloo.com:8181/movie
 * application/views/movie/index.php
 * 长视频页面的轮播功能
 */
;;;$(function(){

    //更平滑的首尾相接的轮播组件
    function carousel(opts){
        //容器的类名，为一个UL元素
        var ui_class = opts.ui_class;
        //向左移动的按钮的元素的类名
        var left_class = opts.left_class;
        //向左移动的按钮的元素的类名
        var right_class = opts.right_class;
        //每次移动多少个
        var pre_num = opts.pre_num || 1
        //容器要变宽多少，这个数值要尽量大，方便让它容纳所有LI左浮动后的总宽度
        var ui_width = opts.ui_width;
        //每次移动后的回调
        var callback = opts.callback || $.noop;
        //动画的持续时间
        var speed = opts.speed;
        //===================================
        var ui = $("."+ui_class);
        var lis = ui.find(">li");
        var w = lis.eq(0).width(), n = lis.length, cur = n, index = 0
        //左右各复制一份，防止数量不够出现空白
        ui.prepend(lis.clone())
        ui.append(lis.clone())
        ui.width(ui_width);
        ui.css("margin-left",  w * n *-1 )
        $("body").delegate("."+left_class+",."+right_class, "click", function(){
            ui.stop(true,true);
            var left = $(this).hasClass(left_class), prefix   //向左边
            if(left){
                cur = cur - pre_num;
                prefix = "+="
                index--
            }else{
                cur = cur + pre_num;
                prefix = "-="
                index++
            }
            ui.animate({
                "margin-left": prefix + w * pre_num
            }, speed, function(){
                if( cur <= 0 || cur >= n * 2  ){
                    ui.css("margin-left", w * n *-1 );
                    cur = n
                    index = 0
                }
                callback(index);
            });
        })
    }
    //当前热映的carousel
    carousel({
        ui_class: "js-big-carousel",
        left_class: "y-scorlllong-left",
        right_class: "y-scorlllong-right",
        pre_num: 4,
        ui_width: 5000,
        speed: 500,
        callback: function(index){
            $(".y-scorlltab-box b").removeClass("y-select").eq(index).addClass("y-select");
        }
    });
    //相关视频的carousel
    carousel({
        ui_class: "js-circle-carousel",
        left_class: "y-scorll-left",
        right_class: "y-scorll-right",
        pre_num: 5,
        ui_width: 10000,
        speed: 600
    });

    $(".js-close-adbar").click(function(e){
        e.preventDefault();
        $(this).parents(".js-ad-wrap").remove();
    });
    //明星消息页面，点击其作品
    new function(){
        var flag_unfold = false;//是否已经展开所有作品，展开后点击切换卡会自行调整高度
        //切换样子
        function toggleStyle(el, cls){
            $(el).addClass(cls).siblings().removeClass("y-tab-left-select y-tab-right-select y-tab-center-select")
        }
        //显示所有作品
        $(".js-get-all").click(function(){
            $(".js-works-list li").show();
            toggleStyle(this,"y-tab-left-select")
            if(flag_unfold){
                getHeight()
            }
        });
        //只显示电影
        $(".js-get-movie").click(function(){
            toggleStyle(this,"y-tab-center-select")
            $(".js-works-list li.js-tv").hide();
            $(".js-works-list li.js-movie").show();
            if(flag_unfold){
                getHeight()
            }
        });
        //只显示电视剧
        $(".js-get-tv").click(function(){
            toggleStyle(this,"y-tab-right-select")
            $(".js-works-list li.js-tv").show();
            $(".js-works-list li.js-movie").hide();
            if(flag_unfold){
                getHeight()
            }
        });
        //调整高度
        var getHeight = function(){
            var n = $(".js-works-list li:visible").length;
            var el = $(".js-works-wrap");
            var h = 135 * (Math.ceil( n / 6  ) );
            el.animate({
                height:  h
            }, 600);
        }
        //展开
        $(".y-btn-drop-open").click(function(){
            $(this).hide();
            flag_unfold = true;
            getHeight();
            $(".y-btn-drop-closed").show();
        });
        //收起
        $(".y-btn-drop-closed").click(function(){
            $(this).hide();
            flag_unfold = false;
            var el = $(".js-works-wrap");
            el.animate({
                height: 135
            }, 600, function(){
                $(".y-btn-drop-open").show();
            })
        })
    }
    $(".js-show-all-episode").click(function(){
        var el = $(this).parents(".js-tv-msg")
        el.find(".y-videoinfo-warehouse").hide();
        el.next(".js-tv-hidden-msg").show();
        $(this).hide();
    })
});


