Dom.ready(function(){
    var shareOpen = false;
    function showMask(){
    //W("#iframeMasks").show();
    }
    function hideMask(){
    //W("#iframeMasks").hide();
    }
    //分享菜单
    W("#share-pop").on("mouseenter",function(){
        if(!shareOpen){
            var str = "@圈乐网：【在线视频："+(document.title).replace('-视频-在线观看-圈乐','')+"】更多视频，等你发现！请点击链接观看：";
            
            var src = location.href;
            src = add_url_param( src, {
                "key": 'fuid',
                "value": window.UID
            } );
            src = add_url_param( src, {
                "key": 'stype',
                "value": 'o'
            } );
            
            str += src;
            
            W("#shareText").val(str);
            shareOpen = true;
        }
        W("#shareList").show();
        W(".share").addClass("shareOpen")
        showMask();
    }).on("mouseleave",function(){
        W("#shareList").hide();
        W(".share").removeClass("shareOpen");
        hideMask();
    });

    //播放列表的菜单
    W("#playList").on("mouseenter",function(){
        W("#videoList").show();
        W(".wp").addClass("wpOpen")
        showMask();
    }).on("mouseleave",function(){
        W("#videoList").hide();
        W(".wp").removeClass("wpOpen")
        hideMask();
    });
    //登陆的菜单
    W("#logined").on("mouseenter",function(){
        W("#userList").show();
        W(".login").addClass("loginOpen");
        showMask();
    }).on("mouseleave",function(){
        W("#userList").hide();
        W(".login").removeClass("loginOpen");
        hideMask();
    });
    //评论的菜单
    var hideComment = function(){
        W("#commentList").hide();
        W(".comments").removeClass("commentOpen")
        hideMask();
    }
    W("#comment-pop").on("mouseenter",function(){
        W("#commentList").show();
        W(".comments").addClass("commentOpen")
        showMask();
    }).on("mouseleave",hideComment);
    
    var COMMENT_LENGTH = 200;
    var comment_input_tip = W('#comment_input_tip');
    var setCommentErr = function (objContainer, strErr) {
        objContainer.html('<span style="float:none;color:red">' + strErr + '</span>');
    };
    var setCommentCharNum = function (objContainer, objInput, objSubmit, intMaxCharNum) {
        var strContent = objInput.val().trim();
        var intTmp = intMaxCharNum - strContent.length;
        if (intTmp >= 0) {
            objContainer.html('还可以输入<span style="float:none">' + intTmp + '</span>个字');
            objSubmit.attr('disabled', '');
        } else {
            objContainer.html('已经超过<span style="float:none;color:red">' + (strContent.length - intMaxCharNum) + '</span>个字');
            objSubmit.attr('disabled', 'disabled');
        }
    };
    function getParam(name){//获取参数值 by司徒正美
        var sUrl = window.location.search.substr(1);
        var r = sUrl.match(new RegExp("(^|&)" + name + "=([^&]*)(&|$)"));
        return (r == null ? null : unescape(r[2]));
    }
    var textarea = W("#commentText"),//用于取得用户的评论
    vid = textarea.attr("data-vid"), circle = getParam("circle"),
    //以前少伟在页面弄的一个标识，如果登录了，有这个<input type="hidden" id="logined_mark" />
    isSubmitingComment = false,
    isLogin = !!W("#logined_mark").length;
    onpropertychange_f(textarea, function ($evt) {
        setCommentCharNum(comment_input_tip, textarea, W("#commentSubmit .sb"), COMMENT_LENGTH);
    });
    var inputing = false;
    textarea.on("focus",function(){
        setCommentCharNum(comment_input_tip, textarea, W("#commentSubmit .sb"), COMMENT_LENGTH);
        //如果正在输入评论则去掉收入菜单的mouseleave回调
        if(!inputing){
            inputing = true;
            W("#comment-pop").un("mouseleave",hideComment);
            setTimeout(function(){
                W("#comment-pop").on("mouseleave",hideComment);
                inputing = false;
            },3500)
        }
    });
    //点击发布按钮
    W(document).delegate("#commentSubmit .sb","click", function(){
        if (isSubmitingComment) return; //防止重复提交
        if(isLogin){
            //判断有没有超出200个字，超出就警告
            var value = StringH.trim(textarea[0].value);
            var n = value.length - COMMENT_LENGTH;
            if (! value) {
                setCommentErr(comment_input_tip, '请说几句再提交内容!');
            } else if(n > 0){
                setCommentErr(comment_input_tip, '评论内容不能超过' + COMMENT_LENGTH + '字');
            } else{
                isSubmitingComment = true;
                Ajax.post("/video/comment", {
                    id: vid,
                    content:value,
                    circle:circle || ""
                }, function(json){
                    isSubmitingComment = false;
                    if(json.err == 'ok'){
                        textarea[0].value = "";
                        var val = W("#comments_total").getHtml();
                        W('#js_nocomment').removeNode();//移除显示无评论的LI节点
                        W("#comments_total").setHtml(parseInt(val,10)+1);
                        //处理IE8下插入字符串失败的BUG
                        var html = "<ul>"+json.data.data+"<ul>";
                        var div = document.createElement("div");
                        div.innerHTML = html;
                        var li = div.getElementsByTagName("li")[0];
                        window.console && console.log(li.tagName);
                        if(li){
                            W('#commentList ul').insert("afterbegin",li);
                        }
                        var lis = W('#commentList ul li')
                        if(lis.length > 3){
                            lis.item(3).removeNode();
                            //如果是超过三条才可以点击下一页
                            W(".comment_next").removeClass("unable")
                        }
                        textarea[0].value = '';
                    } else {
                        setCommentErr(comment_input_tip, getMessage(json.msg) || '发生错误，请稍候重试');
                    }
                    
                    var fuid = get_url_param( location.href, "fuid" );
                    
                    if( fuid && fuid != "0" )
                    {
                        var stype = get_url_param( location.href, "stype" );
                    
                        QW.Marmot.log({
                            "page_id": "click_comment",
                            "fuid": fuid,
                            "stype": (stype||'i')
                        } );
                    }  
                    
                })
            } 
        }
    });
    //取消评论
    W(document).delegate("#commentSubmit .cl","click", function(){
        textarea[0].value = "";
        W("#commentList").hide();
    });
    //取上一页与下一页的用户评论    
    var comment_pager_data = {
        count:3,
        offset:3,
        locking: false
    };
    W(document).delegate("#comment_prev,#comment_next", "click", function(){
        if(comment_pager_data.locking) {//锁住，只能等请求返回才能翻下一页或上一页
            return;
        }
        var curr = W(this);
        if (curr.hasClass("unable")) {
            return;
        }
        
        if (curr.hasClass("comment_prev")) {
            if (comment_pager_data['offset'] <= comment_pager_data['count']) {
                return;
            }
            var i = comment_pager_data['offset'] - (comment_pager_data['count'] * 2);
            if (i < 0) {
                return;
            }
            comment_pager_data['offset'] = i;
        } else {
            if (comment_pager_data['total'] != undefined && comment_pager_data['total'] <= comment_pager_data['offset']) {
                return;
            }
        }
       
        //这是翻页
        comment_pager_data.locking = true;
        Ajax.get('/video/comments', {
            id    : vid,
            offset: comment_pager_data['offset'],
            count  : comment_pager_data['count']
        }, function(json){
            comment_pager_data.locking = false;
            if(json.err == 'ok'){
                if (json.data.count < comment_pager_data['count']) { //到头了
                    W('#comment_next').addClass("unable");
                }
                comment_pager_data['total'] = json.data.total;
                comment_pager_data['offset'] = comment_pager_data['offset'] + comment_pager_data['count'];
                
                if (comment_pager_data['offset'] <= comment_pager_data['count']) {
                    W('#comment_prev').addClass("unable");
                } else {
                    W('#comment_prev').removeClass("unable");
                }       
                if (comment_pager_data['total'] <= comment_pager_data['offset']) {
                    W('#comment_next').addClass("unable");
                } else {
                    W('#comment_next').removeClass("unable");
                }            	
                W('#commentList ul').html(json.data.data);
            }
        });
    })


    //处理播放列表的切换
    W(document).delegate(".tit li:not(.selected)", "mouseover", function(){
        W(".tit li.selected").removeClass("selected");
        W(this).addClass("selected");
        var show = W(".lst-a:not(.selected)")
        W(".lst-a.selected").removeClass("selected").hide();
        show.addClass("selected").show();
    })


    //分享绑定 
    W('.submit__').on('click',function(){
        var type = W(this).getAttr('data-sns').trim();
        var url = ''; //box.query('input[name=url]').val().trim();
        var content = W('#shareText').val().trim();
        var dat = W('#share-pop .share').getAttr('u-data'),image=null;
        if(dat){
            image = dat.evalExp()['pu']
        }
        
        XShare.exec( {
            "type": type,
            "url": url,
            "textContent": content,
            "image":image, 
            "shareType": "video"
        } );

        
    }
    );

    //处理遮罩层的大小
    var mask = W("#iframeMasks");
    //监听窗口变化
    setResize();
    function setResize(){
        var moviesPlace = W("#moviesPlace");
        moviesPlace.setStyle("height", (W('body').getSize()["height"]-54)+"px");
    }
    var intId = null;
    W(window).on('resize', function() {
        if(intId) {
            clearTimeout(intId);
            intId = null;
        }
        intId = setTimeout(setResize, 100);
    });
	
})
