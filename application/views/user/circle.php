<%extends file="common/base_user.tpl"%>
<%block name="seo_meta"%>
<%$user_nick_escape=HTML::chars($user_info.nick)%>
<%if $type == 0%><%$strTypeName='关注'%><%elseif $type == 2%><%$strTypeName='创建'%><%else%><%$strTypeName='分享过'%><%/if%>
    <meta name="keywords" content="<%$user_nick_escape%>,<%$strTypeName%>的圈子" />
    <meta name="description" content="<%$user_nick_escape%><%$strTypeName%>的圈子">
<%/block%>
<%block name="title" prepend%><%$user_nick_escape%><%$strTypeName%>的圈子<%/block%>
<%block name="custom_js"%><%/block%>

<%block name="user_nav"%>
<%include file="inc/user_nav.inc" pagetype=2%>
	<div class="nav-sub">
		<ul class="clearfix">
			<li<%if $type == 2%> class="on"<%/if%>><a href="<%Util::userUrl($user_info._id, 'circle', ['type' => 2])%>">创建的圈子</a></li>
			<li<%if $type == 0%> class="on"<%/if%>><a href="<%Util::userUrl($user_info._id, 'circle', ['type' => 0])%>">关注的圈子</a></li>
			<li<%if $type == 1%> class="on"<%/if%>><a href="<%Util::userUrl($user_info._id, 'circle', ['type' => 1])%>">分享过的圈子</a></li>
		</ul>
	</div>
<%/block%>
<%block name="article"%>
<%if $user_circle_list.count > 0%>
	<%if $is_admin && $type == 0%>
		<div class="title1 title-followed clearfix">
			<h2>已关注的圈子</h2>
			<div class="page-nav" id="getcriclepage">
				<div class="progress">1 / 10</div>
				<div class="btn">
					<a href="#" class="bt-pre"></a>
					<a href="#" class="bt-next"></a>
				</div>
			</div>
		</div>
		<div id="followedlist" class="circlelist"></div>

        <div class="title1 title-followed clearfix">
			<h2>你可能喜欢的圈子</h2>
			<div class="page-nav" id="getlikepage">
				<div class="progress">1 / 10</div>
				<div class="btn">
					<a href="#" class="bt-pre"></a>
					<a href="#" class="bt-next"></a>
				</div>
			</div>
		</div>
		<div  id="getlikelist" class="circlelist"></div>
	<%elseif $type==2 && $is_admin%>
		<div class="title1 title-followed clearfix">
			<h2>已创建的圈子</h2>
			<div class="page-nav" id="getcreatedpage">
				<div class="progress">1 / 10</div>
				<div class="btn">
					<a href="#" class="bt-pre"></a>
					<a href="#" class="bt-next"></a>
				</div>
			</div>
		</div>
		<div id="createdlist" class="circlelist"></div>
	<%else%>
		<div class="circlelist" style="height:auto;">
			<div class="videolist-inner">
                <ul class="list clearfix">
                    <%foreach $user_circle_list.data as $item%>
                    <li class="circleitem circleitem-t2">
                        <div class="hd">
                            <h3><a href="<%Util::circleUrl($item._id, null, $item)%>" title="<%$item.title|escape:'html'%>"><%Util::utf8SubStr($item.title,21)%></a></h3>
                            <span class="type <%Util::circleTypeCss($item)%>"></span>
                            <%if $type!=2%>
                            <div class="circle_own">
		                    <img class="in-block head_img" src="<%Util::userAvatarUrl($item.user.avatar.30, 30)%>">
		                    <%if $item.user._id == 1846037590%>
		                    <span class="lele"><%$item.user.nick|escape:"html"%></span>创建
		                    <%else%>
		                    <a class="user" target="_blank" href="<%Util::userUrl($item.user._id)%>"><%$item.user.nick|escape:"html"%></a>创建
		                    <%/if%>
                			</div>
                			<%/if%>
                        </div>
                        <div class="bd">
                            <a href="<%Util::circleUrl($item._id, null, $item)%>"><img src="<%Util::circlePreviewPic($item.tn_path)%>" alt="<%$item.title|escape:'html'%>" /></a>
                        </div>
                        <div class="ft clearfix">
                            <%if $item.is_focus%>
                            <%$followst = 'followed'%>
                            <%else%>
                            <%$followst = 'b-follow'%>
                            <%/if%>
                            <a class="btn <%$followst%>" href="#" data-action="{id:'<%$item._id%>'}"><span class="text0">关注</span><span class="text1">已关注</span><span class="text2">取消关注</span></a>
                            <a class="btn invite_friend" href="#" data-action="{'id':'<%$item._id%>', 'url': '<%Util::circleUrl($item._id, null, $item)%>'}">邀请好友</a>
                            <a class="btn share_group" href="#" data-action="{'id':'<%$item._id%>', 'url': '<%Util::circleUrl($item._id, null, $item)%>'}">分享Ta</a>
                        </div>
                    </li>
                    <%/foreach%>
                </ul>
            </div>
        </div>
        <%include file="inc/pager.inc" count=$count offset=$offset total=$user_circle_list.count inline%>
	<%/if%>
<%else%>
    <!-- 初始状态 -->
    <div class="init-status nodata_"<%if !$is_admin%> style="background:none; text-align:left"<%/if%>>
    	<%if $type == 2%>
            <%if $is_admin%>
        	您还没有创建过任何圈子，马上去创建有趣的圈子吧
            <%else%>
        	<div style="padding:30px 0;text-align:center;">他还没有创建任何圈子</div>
		    <%/if%>
        <%elseif $type == 1%>
            <%if $is_admin%>
        	您还没有分享过任何圈子，马上去发现有趣的<a href="/category/all">圈子</a>吧
        	<a class="map" href="/guesslike"></a>
            <%else%>
        	<div style="padding:30px 0;text-align:center;">他还没有分享任何圈子</div>
		    <%/if%>
        <%else%>
            <%if $is_admin%>
        	你还没有关注任何<a href="/category/all">圈子</a>哦，马上去关注
        	<a class="map" href="/guesslike"></a>
            <%else%>
        	<div style="padding:30px 0;text-align:center;">他还没有关注任何圈子</div>
		    <%/if%>
        <%/if%>
    </div>
    <!-- 初始状态 -->
<%/if%>
<%/block%>
<%block name="foot_js"%>
<%if $is_admin && $type!=1%>
<%*主人模式已关注圈子tab的动态获取start*%>
<style>
.hideAdd span.add
{
    display:none!important;
    width:0px;
    height:0px;
    overflow:hidden;
}
</style>
<script type="text/javascript">
	var myCircle = {
		urls : {
			'getlike':'/user/getcirclelist?type=2',
			'getcricle':'/user/getcirclelist?type=0',
			'getcreated':'/user/getcirclelist?type=3'
		},
		container: {
			'getlike':'#getlikelist',
			'getcricle':'#followedlist',
			'getcreated':'#createdlist'
		},
		pageid: {
			'getlike':'#getlikepage',
			'getcricle':'#getcriclepage',
			'getcreated':'#getcreatedpage'
		},
		pagecount: 6,
		rander : function (url,param,id,pn){
			var self = this,
				pn = pn;
			Ajax.get(url,param,function(json){
				if(json.err ==='ok'){
					W(self.container[id]).html(json.data.html);
					W(self.pageid[id]).html(self.pageBuild(pn,json.data.total));
                    //INVITE_FRIEND( W(self.container[id]) );
                    //SHARE_GROUP( W(self.container[id]) );
				}else{
					alert('系统错误，请刷新重试！');
				}
			});
		},
		pageBuild : function(pn,total){
			var currpn = pn,
				allpn = Math.ceil(total/this.pagecount),
				prepn = currpn - 1,
				nextpn = currpn + 1;
			var tmpl =[];
			tmpl.push('<div class="progress">'+ currpn +' / '+allpn+'</div>');
			tmpl.push('<div class="btn">');
			if(prepn < 1){
				tmpl.push('<a href="#'+prepn+'" class="bt-pre-dis"></a>');
			}else{
				tmpl.push('<a href="#'+prepn+'" class="act-pre bt-pre"></a>');
			}
			if(nextpn > allpn){
				tmpl.push('<a href="#'+nextpn+'" class="bt-next-dis"></a>');
			}else{
				tmpl.push('<a href="#'+nextpn+'" class="act-next bt-next"></a>');
			}
			tmpl.push('</div>');
			return tmpl.join('');
		},
		pageEvent : function(id){
			var self= this,
				pagecount = self.pagecount;
			W(self.pageid[id]).delegate('.act-pre','click',function(e){
				e.preventDefault();
				var pn = W(this).attr('href').replace(/.*#/,'');
				pn = parseInt(pn,10);
				var data = {
					'offset':(pn-1)*pagecount,
					'count':pagecount,
                    '_r' : Math.random()
				};
				var url = self.urls[id];
				self.rander(url,data,id,pn);
			});
			W(self.pageid[id]).delegate('.act-next','click',function(e){
				e.preventDefault();
				var pn = W(this).attr('href').replace(/.*#/,'');
				pn = parseInt(pn,10);
				var data = {
					'offset':(pn-1)*pagecount,
					'count':pagecount,
                    '_r' : Math.random()
				};
				var url = self.urls[id];
				self.rander(url,data,id,pn);
			});
		},
		init : function(){
			this.pageEvent('getlike');
			this.pageEvent('getcricle');
			this.pageEvent('getcreated');
			<%if $type == 0%>
			this.rander(this.urls['getlike'],{'offset':0,'count':this.pagecount, '_t': (new Date).getTime()},'getlike',1);
			this.rander(this.urls['getcricle'],{'offset':0,'count':this.pagecount, '_t': (new Date).getTime()},'getcricle',1);
			<%elseif $type == 2%>
			this.rander(this.urls['getcreated'],{'offset':0,'count':this.pagecount, '_t': (new Date).getTime()},'getcreated',1);
			<%/if%>
		}

    };
	Dom.ready(function(){
		QW.use('Ajax',function(){
			myCircle.init();
		});
    });
</script>
<%*主人模式已关注圈子tab的动态获取end*%>
<%/if%>
<%if $is_admin and ($user_circle_list.count <= 0) and ! $type%>
<%*需要弹出圈子选择*%>
<script>var is_need_pop = 1;</script>
<%* <script type="text/javascript" src="<%#resUrl#%>/js/video/group_popup.js?v=<%#v#%>"></script> *%>
<div id="popup_group_box"></div>
<%/if%>
<%/block%>
<%block name="custom_foot"%>    
<script type="text/marmot">
{
    "page_id"   : "usercircle"
}
</script>
<%/block%>