<%extends file="common/base.tpl"%>

<%block name="title" prepend%>个人信息页<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
	<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/my.css?v=<%#v#%>">
<%/block%>
<%block name="bd"%>
<div id="bd">
    <div class="lyt-bd cls">
    	<div class="lyt-bd-l l cls">
        	<div class="lyt-bd-l-l l">
            	<!--个人能信息-->
            	<div id="my-info" class="lyt-bd-l-l-1">
                	<img class="h" src="<%Util::userAvatarUrl($user_info.avatar.120)%>">
                </div>
                <!--//个人能信息-->
                
                <!--你的-->
                <div id="menu" class="lyt-bd-l-l-2">
                    <a class="s-ic a select" href="/user/watchlater"><span>我收藏的</span></a>
                    <a class="s-ic b" href="/user"><span>你的圈子</span><i class="open"></i></a>
                    <!--//你的-->
                </div>
            </div>
            <div class="lyt-bd-l-r r">
            	<!--个人信息与设置-->
                <div id="my-set" class="lyt-bd-l-r-1">
                	<h1 user_id="<%$user_info._id%>"><span class="tit"><%$user_info.nick|escape:'html'%></span></h1>
                    <ul>
                        <li>活跃度：<%$user_info.activity|number_format%></li>
                        <li class="laji">
                            <span class="tx">推过：<strong><a href="/user/feedlist?f_type=2" class="a"><%$user_info.like_count|number_format%></a></strong></span>
                            <span class="tx">吐槽：<strong><a href="/user/feedlist?f_type=3" class="a"><%$user_info.comment_count|number_format%></a></strong></span>
                            <span class="tx clear">看过：<strong><a href="/user/feedlist?f_type=1" class="a"><%$user_info.watch_count|number_format%></a></strong></span>
                        </li>
                    </ul>
                    <a class="sets s-ic" href="<%Url::site('user/setting', null, false)%>"><span>个人设置</span></a>
                </div>
                <!--//个人信息与设置-->
                <!--内容区tab-->
                <div class="lyt-bd-l-r-2" id="group-content">
                    <div class="tab s-rx">
                        <ul class="cls">
                        <li d_type="all" class="s-rx select">我收藏的</li>
                        </ul>
                    </div>
                    <div class="content">
                        <div class="watchLater">
                            <ul class="m-pic tp-b">
                                <li class="s-rx cls">
                                	<a href="" class="a"><img src="../resource/img/c3.jpg" class="h"><span class="btm">12′.18″</span><span class="add"></span><span class="Clarity"></span><span class="source"></span></a>
                                    <div class="wrap">
                                        <div class="tit"><a title="兽兽公开道歉"污染了大家的眼睛"" href="http://videosearch.sii.sdo.com:8000/video?id=fc726c64872a9cda940c41746736bd84">兽兽公开道歉"污染了大家的眼睛"</a></div>
                                        <div class="count"><span class="p"></span>73<span class="s"></span>36</div>
                                        <div class="they">
                                            12月12日20：19
                                        </div>
                                    </div>
                                    <span class="s-ic close" title="关闭"></span>
                                </li>
                                <li class="s-rx cls">
                                	<a href="" class="a"><img src="../resource/img/c3.jpg" class="h"><span class="btm">12′.18″</span><span class="add"></span><span class="Clarity"></span><span class="source"></span></a>
                                    <div class="wrap">
                                        <div class="tit"><a title="兽兽公开道歉"污染了大家的眼睛"" href="http://videosearch.sii.sdo.com:8000/video?id=fc726c64872a9cda940c41746736bd84">兽兽公开道歉"污染了大家的眼睛"</a></div>
                                        <div class="count"><span class="p"></span>73<span class="s"></span>36</div>
                                        <div class="they">
                                            12月12日20：19
                                        </div>
                                    </div>
                                    <span class="s-ic close" title="关闭"></span>
                                </li>
                            </ul>
                            <div id="pager-watchLater" class="pager">翻页</div>
                        </div>
                        
                    </div>
                </div>
                <!--//内容区tab-->
            </div>
        </div>
        <div class="lyt-bd-r r">
            <%if ! empty($recommend_circle_list)%>
            <!--可能感兴趣的圈子-->
            <div id="relish-group" class="lyt-bd-r-1">
            	<h2><span class="tit">可能感兴趣的圈子</span></h2>
                <ul>
                <%if isset($recommend_circle_list.popular_circle)%>
                    <%$v=$recommend_circle_list.popular_circle%>
                	<li>
                    	<div class="wrap">
                            <em class="s-ic"></em>
                            <span class="in"><a href="<%Util::circleUrl($v._id, null, $v)%>"><%$v.title|escape:'html'%></a></span>
                            <span class="k">你的圈友也关注了Ta</span>
                            <a class="b-follow in-block" title="加关注" href="#" data-action="{id:'<%$v._id%>'}"></a>
                            <span title="关闭" class="s-ic close"></span>
                        </div>
                    </li>
                <%/if%>
                <%if isset($recommend_circle_list.video_circle)%>
                    <%$v=$recommend_circle_list.video_circle%>
                	<li>
                    	<div class="wrap">
                            <em class="s-ic"></em>
                            <span class="in"><a href="<%Util::circleUrl($v._id, null, $v)%>"><%$v.title|escape:'html'%></a></span>
                            <span class="k">你看过的视频属于这里</span>
                            <a class="b-follow in-block" title="加关注" href="#" data-action="{id:'<%$v._id%>'}"></a>
                            <span title="关闭" class="s-ic close"></span>
                        </div>
                    </li>
                <%/if%>
                <%if isset($recommend_circle_list.friend_circle)%>
                    <%$v=$recommend_circle_list.friend_circle%>
                	<li>
                    	<div class="wrap">
                            <em class="s-ic"></em>
                            <span class="in"><a href="<%Util::circleUrl($v._id, null, $v)%>"><%$v.title|escape:'html'%></a></span>
                            <span class="k">你的圈友还看过这里的视频</span>
                            <a class="b-follow in-block" title="加关注" href="#" data-action="{id:'<%$v._id%>'}"></a>
                            <span title="关闭" class="s-ic close"></span>
                        </div>
                    </li>
                <%/if%>
                </ul>
            </div>
            <!--//可能感兴趣的圈子-->
            <%/if%>
        </div>
    </div>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
	var _s_circle = {
		setParam:function(){
			this.pageNum=10,//动态每页显示条数
			this.url = "/video/playlist"; //我收藏的接口
			this.pagerHand = "#pager-watchLater";	//分页div
			this.msgHand = "#group-content .watchLater ul";	//动态内容容器
			
			this.pager=0; //全局分页模块
			
			this.nowPageNum = 0;//记录当前的页码
			this.urlPram = {};		//参数
		},
		//格式化视频时长
		fm_playtime:function(t){
			var _t = PageUtil.sec2time(t);
			return _t;
		}
	};
	_s_circle.extend=function(c,d){
		for(var e in d){c[e]=d[e]}
		return c;
	};
	_s_circle.extend(_s_circle,{
		//刷新前loading显示
		onloading:function(contentName){
			var rt = this;
			var s = '正在加载数据...';
			W(contentName).setHtml(s);
		},
		//分页模块
		renderPager:function(pagename,total,nowP){
			var rt = this;
			var getPageNum = 0;
			getPageNum = rt.pageNum;
			
			rt.pager = new Pager(pagename,{
				total : total,
				size:getPageNum,
				lastText : null
			})
			rt.pager.nowPn = nowP;
			rt.pager.render(1);
			
			if(W(pagename).getAttr("has") == null){//这是第一次新建分页模块&绑定点击事件
				rt.pager.on('go',function(e){
					e.preventDefault();
					//隐藏分页模块
					W(pagename).hide();
					rt.getStateByPage(e.i);
					rt.nowPageNum = e.i;
				});
				W(pagename).setAttr("has",1);
			}
		}
	});
	//圈子动态   {id:c_id,offset:offsets,count:_s_circle.pageNum}
	_s_circle.extend(_s_circle,{
		getDomStr:function(d){
			var rt = this;
			var s = '';
			
			ArrayH.forEach(d.data,function(el,i){
				var vurl = PageUtil.videoPlayUrl(el["_id"]);
				var quality;
				switch(el["quality"]){
					case 2:// 清晰
						quality = "hd2"
					break;
					case 3:// 标清
						quality = "hd3"
					break;
					case 4:// 高清
						quality = "hd4"
					break;
					case 5:// 超清
						quality = "hd5"
					break;
				}
				var domain = el["domain"];
				domain = domain.replace(/\./g,"_");
				var _date =new Date(el["record_time"]*1000);
				_date = _date.format('M月d日 h:m');
				var vTitle = el["title"];
				var dataStr = "{id:'"+el["_id"]+"'}";
				var playNumStr = typeof el["watched_count"] == "undefined"?'style="display:none;"':'';
				var likeNumStr = typeof el["liked_count"] == "undefined"?'style="display:none;"':'';
				s += '<li class="s-rx cls"><a target="_blank" href="'+vurl+'" class="a">';
				s += '<img src="'+PageUtil.videoThumbnailUrl(el["thumbnail"])+'" alt="'+QW.StringH.encode4Html(vTitle)+'" class="h"><span class="btm">'+rt.fm_playtime(el["length"])+'</span>';
				s += '<span title="添加到我的收藏" data-action="'+dataStr+'" class="add"></span><span class="Clarity '+quality+'"></span><span class="source '+domain+'"></span></a>';
				s += '<div class="wrap"><div class="tit">';
				s += '<a target="_blank" title="'+vTitle+'" href="'+vurl+'">'+vTitle+'</a>';
				s += '</div><div class="count"><span class="p" '+playNumStr+'></span>'+(el["watched_count"]||"")+'<span '+likeNumStr+' class="s"></span>'+(el["liked_count"]||"")+'</div>';
				s += '<div class="they">'+_date+'</div></div><span class="s-ic close" title="关闭"></span></li>';
				
			});
			return s;
		},
		//分页动态数据
		getStateByPage:function(page){
			var rt = this;
			var offsets = rt.pageNum * page;
			//增加参数
			rt.extend(rt.urlPram,{
				offset:offsets,
				count:rt.pageNum,
				name:"watch_later",
                _r : Math.random()
			});
			rt.onloading(rt.msgHand);
			Ajax.get(rt.url,rt.urlPram,function(data){
				if(data["err"] == "ok"){
					var d = data["data"];
					var total = d.total;
					<%*记录获取到的数据时间戳
					这里比较特殊，这里点击tab或者分页的时候，都不需要更新时间戳,只有"点击了新动态消息框"和"第一次加载"才需要更新*%>
					if(W(rt.msgHand).getAttr("has") == null){
						rt.tm = d.tm;
						W(rt.msgHand).setAttr("has",1);
					}
					var fram = total>0?rt.getDomStr(d):"暂无数据";
					W(rt.msgHand).setHtml(fram);
					
					rt.renderPager(rt.pagerHand,total,page);
					W(rt.pagerHand).show();//取得数据后显示分页模块
				}
			});
		}
	});
	//初始
	Dom.ready(function(){
		var rt = _s_circle;
		QW.use("Ajax,Pager",function(){
			_s_circle.setParam();
			_s_circle.getStateByPage(0);
		});
	});
</script>
<%/block%>
