/* 
 * 带你玩圈子
 */
Dom.ready(function(){
    var staticUrl = "http://"+ QW.Config.get('utilConst').domain_static;
    var introStr = '<div class="intro_bd">';
        introStr +='<img src="'+staticUrl+'/img/help01.jpg"><img src="'+staticUrl+'/img/help02.jpg">';
        introStr +='<img src="'+staticUrl+'/img/help03.jpg"><img src="'+staticUrl+'/img/help04.jpg"><img src="'+staticUrl+'/img/help05.jpg"></div>';
        introStr +='<div class="intro_foot"><div class="intro_over over_no"></div><div class="foot_cnt"><span class="l intro_pre pre_no"></span><span class="r intro_next"></span></div></div>';
    var _html = '<div class="intro_hand panel panel-t1 login_popup_big" style="width:892px;position: relative;" id="login_popup_big"><div class="panel-content">';
        _html += '<div class="hd intro_hd"></div><div class="bd">'+introStr+'</div>';
        _html += '<div class="ft"></div></div><span class="co1"><span></span></span><span class="co2"><span></span></span>';
        _html += '<span class="cue"></span><span class="sd"></span><span class="close close__ intro_close"></span><span class="resize"></span></div>';
    var currentHelp,currentTarget;
    W("li.lele a").on("click",function(){
        showIntro(false);
    });
    firstIntro();
    function showIntro(bool){
        var introDialog = XDialog.exec({
                "tpl":_html
                , closeCallback: function(){ 
                    closeIntroHandler();
                    if(!bool)return;
                    var currentCloseNum = parseInt(QW.Cookie.get('hasIntro'));
                    currentCloseNum++
                    QW.Cookie.set('hasIntro',currentCloseNum+"");
                }
                , "autoVertical": true
            });
        currentHelp = 0;
        currentTarget = W(".intro_bd img").first();
        currentTarget.show();
        setResize();

        /*bind event*/
        //pre
        W(".intro_foot .intro_pre").on("mouseover",function(){ 
            var o = W(this);
            if(o.hasClass("pre_no"))return;
            o.addClass("pre_hover");
        }).on("mouseout",function(){
            var o = W(this);
            o.removeClass("pre_hover");
        }).on("click",function(){
            var o = W(this);
            if(o.hasClass("pre_no"))return;
             viewLele(o,-1);
        });
        //next
        W(".intro_foot .intro_next").on("mouseover",function(){
            var o = W(this);
            if(o.hasClass("next_no"))return;
            o.addClass("next_hover");
        }).on("mouseout",function(){
            var o = W(this);
            o.removeClass("next_hover");
        }).on("click",function(){
            var o = W(this);
            if(o.hasClass("next_no"))return;
            viewLele(o,1);
        });
        //over button
        W(".intro_foot .intro_over").on("mouseover",function(){ 
            var o = W(this);
            if(o.hasClass("over_no"))return;
            o.addClass("over_hover");
        }).on("mouseout",function(){
            var o = W(this);
            o.removeClass("over_hover");
        }).on("click",function(){
            var o = W(this);
            if(o.hasClass("over_no"))return;
            introDialog.view.close();
            QW.Cookie.set('hasIntro',"1000");
            //闭关回调
            closeIntroHandler();
        });
        //window resize
        function setResize(){
            var _h,_w,
                _paddingTop = 20,
                _foot = W(".intro_foot"),
                _body = W(".intro_bd"),
                _box = W(".intro_hand"),
                _img = W(".intro_bd img"),
                _scale = (890+2)/(560+2),
                _bsize = Dom.getDocRect();
            if(!_body.length)return;
            //获取body大小
            var _bw = _bsize["width"],
                _bh = _bsize["height"];
            
            if(_bw/_bh >_scale){
                //按高度计算 640 * 407
                _h = _bh - _paddingTop*2;
                if(_h > 568){_h = 562;}else if(_h < 407){_h = 407;}
                _w = Math.floor(_h * (890/560));
            }else{
                //按宽度计算
                _w = _bw - _paddingTop*2;
                if(_w > 892){_w = 892;}else if(_w < 640){_w = 640;}
                _h = Math.floor(_w/(890/560));
            }
            _box.css("width",_w+"px");
            _foot.css("width",_w-2+"px");
            _body.css("width",_w-2+"px");
            _body.css("height",_h-2+"px");
            _img.css("width",_w-2+"px");
            _img.css("height",_h-2+"px");
            //更新Dialog位置
            introDialog.view.show();
        }
        var intId = null;
        W(window).on('resize', function() {
            if(intId) {
                clearTimeout(intId);
                intId = null;
            }
            intId = setTimeout(setResize, 100);
        });
        //定位 target : 点击对象 ，  index : 1 || -1
        function viewLele(target,index){
            currentHelp += index;
            //过滤条件以及按钮使用状态判断
            if(currentHelp < 1){//pre
                target.addClass("pre_no");
            }else{
                W(".intro_foot .intro_pre").removeClass("pre_no").removeClass("pre_hover");
            }
            if(currentHelp > 3){//next
                target.addClass("next_no");
            }else{
                W(".intro_foot .intro_next").removeClass("next_no").removeClass("next_hover");
            }
            //翻页
            currentTarget.hide();
            currentTarget = W(".intro_bd img").item(currentHelp);
            currentTarget.show();
            if(currentHelp==4){
               // alert("显示完成按钮");
               W(".intro_foot .intro_over").removeClass("over_no");
            }else{
                if(!W(".intro_foot .intro_over").hasClass("over_no"))W(".intro_foot .intro_over").addClass("over_no");
            }
        }
    }
    function firstIntro(){
        if(UID!="")return;
        if(!!QW.Cookie.get('hasIntro')&&parseInt(QW.Cookie.get('hasIntro'))>0)return;
        showIntro(true);
        if(!QW.Cookie.get('hasIntro'))QW.Cookie.set('hasIntro',"0");
    }
    var _timer=-1;
    function closeIntroHandler(){
        var _target = W("#video_type .lele a");
        var i = 0;
        clearInterval(_timer);
        _timer = setInterval(function(){
            if(i%2==0){
                _target.removeClass("a_hover");
            }else{
                _target.addClass("a_hover");
            }
            if(i>3)clearInterval(_timer);
            i++;
        },200);  
    }
});



