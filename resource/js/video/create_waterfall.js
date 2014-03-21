Dom.ready(function(){

    if(!W("#waterfall").length){
        return
    }

    var pager = W('#pager').hide();
    var loading = W("#loader").hide();
    var waterfallOpts = window.waterfallOpts || {};

   
    var waterfall = new Waterfall(waterfallOpts.initOpts);
    //对圈子页,个人页进行处理
    var firstBrick = W(".first_brick");
    if(firstBrick.length){
        W("#waterfall .cols")[0].appendChild(firstBrick[0]);
    }

    waterfallOpts.target = waterfall;//方便外界调用这个实例
    var isIE678 = !+"\v1";//处理IE678
    var isMacSafari = navigator.userAgent.indexOf("Mac OS X") != -1 && /Apple/i.test(navigator.vendor) ;
    Waterfall.isMacSafari = isMacSafari;

    var requestCount = 0;//用于记录请求了多少



    waterfall.request = function( callback ){
        waterfall.requesting = true;
        var opts = waterfallOpts.requestOpts;
        //至少传两个参数过去 当前偏移量offset与每次取多少count(size)
        if(!opts.offset){
            opts.offset = requestCount * opts.count;
        }
        requestCount++;
        function call(json){
            if( json.err === "ok" ){
                loading.hide();
                waterfall.requesting = false;
                //将JSON数据转换成HTML数据
                var bricks = [], jsonData = json.data.data ? json.data.data : json.data;
                if(!jsonData.length){//如果没有数据了就不发出请求了
                    waterfall.log("后台没有数据返回")
                    waterfallOpts.requestInBottom = false
                    return
                }
                for(var i = 0, obj; obj = jsonData[i++];){
                    obj.playlist = opts.playlist
                    obj.offset = opts.offset
                    obj.count = opts.count;
                    obj.user = opts.user;
                    if(opts.type == "1" || opts.type == "2"){
                        obj.rec_type = "";//处理首页切换的砖头标签，如果是新与热页面就不用加标签了
                    }
                    obj._index = i - 0;//用于日志系统
                    obj.playlist = opts.playlist;//用于日志系统
                    //现在后台直接输出高，不需要预先获取了
                    bricks.push( QW.ejs("brick_tmpl",obj) )
                }
                waterfallOpts.requestOpts.offset = opts.count * requestCount;
                //将HTML数据转换成节点数据并加入瀑布流中
                waterfall.addBricks( bricks ,function (){
                    //第一屏的砖头不以动画形式展示出来
                    if( requestCount === 1 ){
                        var els = waterfall.bricks.slice( 0, 8 );
                        for(var j = 0, el; el = els[j++];){
                            el.setStyle("opacity", "1")
                        }
                    }
                });
                // ●页，屏，请求
                (callback || function(){}).call(waterfall,json);
            }else{
                //如果超时，继续连上去
                QW.Ajax.get( waterfallOpts.requestUrl, opts, call)
                waterfall.log(json.err);//打印请求超时等错误消息
            }
        }
        if(opts.firstData){
            call(opts.firstData);
            delete opts.firstData;
        }else{
            QW.Ajax.get( waterfallOpts.requestUrl, opts,call);
        }
    };
    //第一次请求
    waterfall.request( waterfallOpts.requestCallback );

    waterfall.scroll(function(scrollY, scrollHeight, B){
        var a = B.scrollHeight - B.scrollY - B.height;
        //如果这里5改成10以上的数会在mac的safari下卡死
       // if(typeof scrollY == "number")
          //  waterfall.log(a)
        if( (typeof scrollY == "number") &&( a < 10 ) ){
            if(waterfallOpts.requestInBottom && !waterfall.requesting){//如果还需要发出请求
                if(pager.length){//如果有分页就优先显示分页，否则显示loading
                    pager.show();
                }else{
                    loading.show();
                }
               
                waterfall.request( waterfallOpts.requestCallback );
            }
        }
    });
    //如果需要淡出才添加以下回调
    if(! (isIE678 || isMacSafari ) ){
        waterfall.scroll(function( el, i, els ){
            if(typeof el !== "number"){
                el.animate({
                    "opacity":{
                        to: 1
                    }
                }, {
                    complete:function(){//去掉已淡入的砖头，防止重复计算
                        var ii = QW.ArrayH.indexOf(els, el);
                        if(ii >= 0)
                            els.splice(ii, 1);
                    },
                    duration: 600
                });
            }
        });
    }

});

// ●页，屏，请求
//                if(json.data.total){
//                    QW.use('Pager', function(){
//                        //如果不满2页，则不出现页码
//                        //http://tm.sdo.com/issues/21971
//                        var show = json.data.total/  opts.count ;
//                        show = show < 2 ? 0 : show >= 8 ? 8 : Math.ceil(show)
//                        var pagination = new Pager('#pager center',{
//                            //startPn : 1,
//                            qsize     : 'size',
//                            qoffset   : 'offset',
//                            total     : json.data.total,
//                            firstText : '1',
//                            size      : opts.count,
//                            offset    : opts.offset,
//                            showOut   : show
//                        });
//                        pagination.layout = ['pre', 'first', 'list', 'next'];
//                        pagination.render();
//                    });
//                }
// ●●这是之前的需要（MAC下改40个）
//   var playlist = waterfallOpts.requestOpts.playlist
//   var fixMac = isMacSafari &&  playlist == "home_page" || playlist == "personal_recommend"
//    if( fixMac ){
//        waterfallOpts.requestOpts.count = 40;
//        waterfallOpts.requestInBottom = false
//    }
