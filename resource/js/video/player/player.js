Dom.ready(function(){
    var vid = W('#video').attr('data-videoid');    

    //显示切换卡           
    var tabview = new Switch.TabView(g('video_tabs'), {		
        events : ['mouseover'],
        delayTime : 500,
        immediateEvents : ['click']
    });
    tabview.render();
    //显示上面的面板
    W(".switch-nav li:first-child").fire("mouseover");
    //当点击三角形时可以移动视频列表
    var panels ={
        top:0,
        bottom:0
    }
    //大视频下的视频列表切换卡
    W(".go_to_left, .go_to_right").click(function(){
        var left = /left/.test(this.className);//判定是向左移还是向右移
        var parent = this.parentNode;//取得当前要操作的切换卡
        var whichPanel = /top/.test(parent.className) ? "top" : "bottom";//判定是点了上面的还是下面的面板
        var number = panels[whichPanel]
        var videoNumbers = W(parent).query(".video_room").length;//这个面板总共有多少个视频
        //如果往左移动， which 不能为零， 否则不能等于视频总数组
        //console.log( videoNumbers - number > 4)
        if(left ? number  : videoNumbers - number > 4){
            number = left ? number - 4: number + 4;
            panels[whichPanel] = number;
            var container = W(parent).query(".video_list_container")
            container.animate({
                marginLeft:{//每次移动一个视频的宽度
                    by: (left ? 140 * 4 :-140 * 4)
                }
            },{
                duration: 700
            });
            //控件两边按钮的可点击情况（不可点显示为灰）
            if(parseFloat(container.getStyle("marginLeft")) < 0){
                W(parent).query(".go_to_left div").removeClass("disabled");
            }
            if(left ? !number  : videoNumbers - number <= 4){
                W(this).query("div").addClass("disabled");
                var anotherBtn = W(parent).query(".go_to_"+(/left/.test(this.className) ? "right div" : "left div"));
                anotherBtn.removeClass("disabled")
            }
        }
    });

    W("body").delegate("#tucao_btn","click",function(){
        W("#tucao_panel").toggle();
    })
    /*展示评论*/
    var _tucaoHtml = function(d){
        var str = '<div class="tucao_comment"><table><tr><td class="user_portrait">';        
        str += QW.StringH.format('<img src="{0}" /></td><td><p><a href="{1}">{2}：</a>{3}</p><p class="time">{4}</p>',
            PageUtil.userAvatarUrl(d.user.avatar['24'], 24),
            PageUtil.userUrl(d.user._id),
            StringH.encode4Html(d.user.nick),
            StringH.encode4Html(d.data),
            PageUtil.sec2time(d.create_time.sec)
        );
        str += '</td></tr></table></div>';
        return str;
    }
    function getComments(offset, size){
        Ajax.get('/video/comments', {
                id    : vid,
                offset: offset || 0,
                size  : size || 10
            }, function(d){
            var retStr = '';
            if(d.err == 'ok'){
                for(data in d.data.data){
                    retStr += _tucaoHtml(d.data.data[data]);
                }
            } else {
                retStr += '<p>' + d.msg + '</p>';
            }
            W('.tucao_list').html(retStr);
        });
    };
    getComments();     

    //提交吐槽
    W("body").delegate("#submit_tocao","click",function(){
        var content = W("#tucao_panel textarea")[0].value;
        Ajax.get("/video/comment", {
            id: vid,
            content:content
            /*circle:1*/
        }, function(d){
            if(d.err == 'ok'){
                //收起面板
                //增加吐槽数
                W('#tucao_btn em').html(W('#tucao_btn em').html()|0 +1);
                setTimeout(getComments, 1000);
            } else {
                alert(d.msg || '发生错误，请稍候重试');
            }
        })
    });

    W("body").delegate(".video_room","click",function(){
        //刷新页面
        });
   
    setTimeout(function(){
        var video_width = W("#player_area embed").getCurrentStyle("width");
        W("#push_and_tocao").setStyle("width",video_width);
        W("h2.big_title").setStyle("width",video_width);
        W("#tucao_panel_triangle").setStyle("left",parseFloat(video_width)-30)
    },500)
});
