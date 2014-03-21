<%extends file="common/base.tpl"%>

<%block name="title" prepend%>缤纷来源<%/block%>
<%block name="view_conf"%>    
<%/block%>

<%block name="custom_css"%>
    <link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/source.css?v=<%#v#%>">
<%/block%>

<%block name="custom_js"%>    
    <script type="text/javascript" src="<%#resUrl#%>/js/components/switch/switch_all.js?v=<%#v#%>"></script>
<%/block%>

<%block name="bd"%>
<div id="bd">
    <div class="lyt-bd cls">
        <div class="lyt-bd-l l">
            <!--热门缤纷-->
            <div id="hot-bf" class="lyt-bd-l-1">
            <%$v=current($hot_binfen.circle_list)%>
            	<h2><a class="b-follow r" title="关注" href="#" data-action="{id:'<%$v._id%>'}"></a><span class="tit l">热门缤纷</span>
                <ul id="choose-hot">
                <%foreach $hot_binfen.circle_list as $k => $v%>
                    <li data-action="{id:'<%$k%>'}" class="<%if $v@first%>selected<%else%>unselected<%/if%>"><em><%$v.title|escape:'html'%></em></li>
                <%/foreach%>
                </ul>
                </h2>
                <ul class="m-pic tp-b cls">
                    <%call vitem data=$hot_binfen.video_list%>
                </ul>
            </div>
            <!--//热门缤纷-->
            <!--全部缤纷-->
            <div id="all-bf" class="lyt-bd-l-2">
            	<h2><span class="tit l">全部缤纷</span></h2>
                <ul class="m-pic tp-b cls">
                    <%call vitem data=$all_binfen.data playlist='binfen' vsrc=10 iscount=0%>
                </ul>
                <div class="pager top-mix" id="pager-binfen"></div>
            </div>
            <!--//全部缤纷-->
        </div>
        <div class="lyt-bd-r r">
            <!--一小时缤纷榜-->
            <div id="oneHourBf">
                <h2><span class="tit l">一小时缤纷榜</span></h2>
                <ul class="m-pic tp-d">
                <%foreach $top_binfen as $item%>
                    <li class="cls">
                        <div class="inx"><span class="s-ic id<%$item@index +1%>"></span></div>
                        <div class="inx-c">
                            <%if $item@index === 0%>
                                <%call vitem data=$item wrapel='div' classname='p1 cls' single=1%>
                            <%else%>
                                <%call vitem data=$item wrapel='div' classname='p2 cls' single=1%>
                            <%/if%>
                        </div>
                    </li>
                <%/foreach%>
                </ul>
            </div>
            <!--//一小时缤纷榜-->
        </div>
    </div>
</div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
Dom.ready(function() {	
	QW.use('Ajax,Switch,Pager', function() {
	    var chooseHotSwitch = new Switch.TabView(g('hot-bf'), {
	        events : ['click'],        
	        selector : {
	            nav : '#choose-hot>li', 
	            content : '#hot-bf>.m-pic'
	        }
	    }).render();
<%if $all_binfen.count > 16%>

        var intAllBinfenPageNum = 16;
        var strAllBinfenPagerSel = '#pager-binfen';
        var strAllBinfenContentSel = '#all-bf>.m-pic';
	    var objAllBinfenPager = new Pager(strAllBinfenPagerSel, {
			total: <%$all_binfen.count%>,
			size: intAllBinfenPageNum,
			lastText: null
		});
	    objAllBinfenPager.render(1);
	    objAllBinfenPager.on('go', function(e) {
			e.preventDefault();
			var offset = intAllBinfenPageNum * e.i;
			Ajax.get('/recommend/getbinfenlist', {
                format : 'html',
                offset : offset,
                _r : Math.random()
            }, function(data) {
				if (data["err"] == "ok") {
					W(strAllBinfenContentSel).setHtml(data["data"]["content"]);
					objAllBinfenPager = new Pager(strAllBinfenPagerSel, {
						total: data["data"]["total"],
						size: intAllBinfenPageNum,
						lastText: null
					});
					objAllBinfenPager.nowPn = e.i;
					objAllBinfenPager.render(1);
				}
			});
		});	    
<%/if%>			
	});

	W('#choose-hot li').click(function(){
		var dataActionVal = W(this).attr('data-action');   
	    W('#hot-bf .b-follow').attr('data-action', dataActionVal);
		Ajax.get('/recommend/gethotbinfenbycircle', ObjectH.mix(StringH.evalExp(dataActionVal), {
            format : 'html',
            _r : Math.random()
        }), function(data) {
			if (data["err"] == "ok") {
				W('#hot-bf>.m-pic').setHtml(data["data"]);
			}
		});	    
	});

	W('#oneHourBf li').on('mouseover', function(){
	    W('#oneHourBf li .p1').toggleClass('p1', 'p2');
	    W('.p2', this).toggleClass('p2', 'p1');
	}).on('mouseout', function(){    
	    W('.p1', this).toggleClass('p2', 'p1');
	    W('.p2', W('#oneHourBf .inx-c').first()).replaceClass('p2', 'p1');
	});
});
</script>
<%/block%>
