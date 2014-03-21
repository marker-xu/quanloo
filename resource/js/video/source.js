Dom.ready(function(){

    var pager = new Pager('#pager center',{
        //startPn : 1,
        qsize : 'size',
        qoffset : 'offset',
        total : 100,
        firstText : null,
        preText   : '< 上一页',   //“上一页”链接文字，为null时不显示
        netxText  : '下一页 >',   //“下一页”链接文字，为null时不显示
        eachPg : 13,
        showOut : 20,
        hasForm : false
    });
    pager.layout = ['pre', 'first', 'list', 'next'];
    pager.on('go', function(e){
        var id = "#page_"+e.i;
        e.preventDefault();
        var page = W(id);
        if(page.length){
            W("#all_source ul").hide();
            page.show()
        }else{
            //<--- 从这里开始
            //请把这里的部分全部放到AJAX的回调中，并将返回的数据构建一个包含index与videos数组的对象
            var entity = {
                img_src:"../resource/img/source_"+ e.i +".jpg",//视频图片
                video_duration:"12′.18″",//视频的播放时间
                add_class:"add" ,//是否显示不加号,没有则为空白字符串
                hd_class:"" ,//表示是否为高清的图片 high_definition或exceed_definition或空字符
                source_class:"",//显示视频的求源的LOGO,如优酷的LOGO,酷六的LOGO,土豆的LOGO,
                video_title:"酷6出品最全二人",//视频的标题
                video_source_url:"www.baidu.com"//显示来源网站的域名
            }
            var videos = []
            for(var i= 0; i < 12; i++){
                videos.push(entity)
            }
            var tmpl = QW.ejs("all_source_tmpl",{
                index:e.i,
                videos:videos
            });
            W('#all_source').insertAdjacentHTML('beforeend', tmpl);
            W("#all_source ul").hide();
            W(id).show()
        //-----〉从这里结束
        }
    });
    pager.render();
    //请改造这里的函数，把后台传回来的数据弄成正确的格式，当作参数传给它
    var renderRanking = function(){
        var entity = {
            img_src:"../resource/img/source_"+ 1 +".jpg",//视频图片
            video_duration:"12′.18″",//视频的播放时间
            add_class:"add" ,//是否显示不加号,没有则为空白字符串
            hd_class:"exceed_definition" ,//表示是否为高清的图片 high_definition或exceed_definition或空字符
            source_class:"source",//显示视频的求源的LOGO,如优酷的LOGO,酷六的LOGO,土豆的LOGO,
            video_title:"酷6出品最全二人",//视频的标题
            video_source_url:"www.baidu.com"//显示来源网站的域名
        }
        var videos = []
        for(var i= 0; i < 10; i++){
            videos.push(entity)
        }
        var tmpl = QW.ejs("ranking_tmpl",{
            videos:videos
        });
        W('#ranking_list_wrapper')[0].innerHTML = tmpl
    }
    renderRanking();
    //如 renderRanking(data) data
    W(W(".ranking_hover_fix")[0]).addClass("show_big");
    //切换大小图
    W("#ranking_list_wrapper").on("mouseover",function(){
        W(W(".ranking_hover_fix")[0]).removeClass("show_big");
    }).on("mouseout",function(){
        W(W(".ranking_hover_fix")[0]).addClass("show_big");
    });
    //让用户点击来刷新排行榜
    W("body").delegate("#refresh_ranking","click",function(){
        alert("刷新排行榜")
        QW.Ajax.get("url",/*data*/{},function(data){
            //这里处理一下data，然后传入renderRanking
            renderRanking(data)
        })
    });
});

function getMoodIcon(obj){
    var max = 0, icon = "xh"
    for(var i in obj){
        if(obj[i] > max && i !== "total"){
            max = obj[i];
            icon = i
        }
    }
    return icon+"_mood"
}
