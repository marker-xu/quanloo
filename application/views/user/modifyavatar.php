<%extends file="common/base.tpl"%>

<%block name="title" prepend%>个人设置——修改头像<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
	<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/userSetting.css?v=<%#v#%>">
	<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/avatar/imgareaselect-default.css?v=<%#v#%>" />
<%/block%>
<%block name="custom_js"%>
<script type="text/javascript" src="<%#resUrl#%>/js/third/jquery-1.7.2.min.js"></script> 
<script type="text/javascript" src="<%#resUrl#%>/js/video/imgareaselect.js?v=<%#v#%>"></script> 
<%/block%>
<!--- 测试 -->
<%block name="bd"%> 
<?php 
//	$width = 300;
//	$height = 300;
//	if(isset($user_info['avatar']['org'])) {
//		$avatar = Util::userAvatarUrl($user_info['avatar']['org']);
//		$info = getimagesize($avatar);
//		if($info) {
//			$width = $info[0];
//			$height = $info[1];
//		}
//	} 
?> 
        <div id="bd">
            	<!--个人设置-->
            	<div id="user_setting">
                    <%include file="user/user_setting_panel.inc"%>
                    <div class="setting_content editHeadPic">
                    	<div class="wrap cls">
                        	<!--左-->
                            <div class="L">
                            	<iframe name="hidden_frame" id="hidden_frame" style="display:none;" src="about:blank"></iframe>
                            	<form target="hidden_frame" action="<%#siteUrl#%>/user/modifyavatar" enctype="multipart/form-data" method="post" id="upfile-form">
                            	<div class="w_upload"><iframe class="iframemask" src="about:blank"></iframe><input type="file" size="1" name="avatar" id="avatar"><span id="uploadfileButon" class="btn s-ic-reg in-block" title="本地上传"></span>
                                <span id="cancelButton" class="btn s-ic-reg in-block" title="取消"></span>
                                <span class="tips"></span>
                                </div>
                                </form>
                                <div class="info">支持jpg、gif、png格式的图片，且小于5MB</div>
                                <div id="modifyPlace">
                                <script type="text/javascript">
									(function(win){  
										win.__picHand = {haspre:<%if isset($user_info.avatar.org)%>true<%else%>false<%/if%>,blankpic:"<%#resUrl#%>\/img\/blank.jpg"};
										var _w = 300,_h=300,w,h,top=0,left=0;
										var wh = _w/_h;
										if(wh>1){
											w = 300;
											h = Math.floor(300*_h/_w);
											top = Math.floor((300-h)*0.5);
											win.__picHand.quotiety = 300/_w;
										}else if(wh<1){
											h = 300;
											w = Math.floor(300*_w/_h);
											left = Math.floor((300-w)*0.5);
											win.__picHand.quotiety = 300/_h;
										}else{
											w = 300;
											h = 300;
											win.__picHand.quotiety = 300/_h;
										}
										var imgStr = '<img style="position:relative;width:'+w+'px; height:'+h+'px; top:'+top+'px; left:'+left+'px;" id="photo" src="<%#resUrl#%>/img/blank_up.jpg">';
										document.write(imgStr);
										
									})(window);
                                </script>
                                
                                </div>
                                <div class="w_submit">
                                	<span id="saveHeadPic" class="btn btn-complete s-ic-reg in-block" title="保存"></span>
                                    <span class="tips"></span>
                                    <!--<span class="btn btn-cancel s-ic-reg in-block" title="取消"></span>-->
                                </div>
                            </div>
                            
                            <!--左-->
                            <!--右-->
                            <div class="R">
                            	<div class="wrap">
                                    <div class="tit">你上传的头像会自动生成三种尺寸，<br>请注意中小尺寸头像是否清晰</div>
                                    <div class="cls">
                                        <div class="w_l">
                                            <div class="h_200 head"><img 
                                            src="<%if isset($user_info.avatar.200)%><%Util::userAvatarUrl($user_info.avatar.200)%><%else%><%#resUrl#%>/img/blank.jpg<%/if%>">
                                            </div><br>
                                            大尺寸头像，200*200像素
                                        </div>
                                        <div class="w_r">
                                            <div class="h_48 head"><img 
                                            src="<%if isset($user_info.avatar.48)%><%Util::userAvatarUrl($user_info.avatar.48)%><%else%><%#resUrl#%>/img/blank.jpg<%/if%>"></div>
                                            中尺寸头像<br>48*48像素<br>(自动生成)
                                            <br><br>
                                            <div class="h_30 head"><img src="<%if isset($user_info.avatar.30)%><%Util::userAvatarUrl($user_info.avatar.30)%><%else%><%#resUrl#%>/img/blank.jpg<%/if%>"></div>
                                            小尺寸头像<br>30*30像素<br>(自动生成)
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--右-->
                            
                            <input type="hidden" id="avatar" name="avatar" value="<%$user_info.avatar.org%>" />
                        </div>
                    </div>
                </div>
                <!--个人设置-->
        </div>
<%/block%>

<%block name="foot_js"%>
<script type="text/javascript">
Dom.ready(function(){
	var btn_save = W("#saveHeadPic");//保存按钮
	var uploadInput = W("#avatar"); //提交input
	var btn_upload = W("#uploadfileButon"); //上传按钮
	var btn_cancel = W("#cancelButton"); //取消上传按钮
	var photo = W("#photo"); //舞台图片
	var imgAreaInstance = null;
	var picSize = photo.getSize();//得到图片大小
	var rebackEnable = false; //上传回调是否启用
	var hasinit = false;
	
	var init = function(){
		//为firefox专门写的一句，很呕
		uploadInput.removeAttr("disabled");
		//
		btn_save.hide();
		btn_cancel.hide();
		//photo.hide();
		//initHeadArea();
		bingEvents();
		//initImgarae全局
		window.initImgarae = initImgarae;
	};
	//重设图片舞台
	function resizePic(_w,_h){
		var w,h,top=0,left=0;
		var wh = _w/_h;
		if(wh>1){
			w = 300;
			h = Math.floor(300*_h/_w);
			top = Math.floor((300-h)*0.5);
			__picHand.quotiety = 300/_w;
		}else if(wh<1){
			h = 300;
			w = Math.floor(300*_w/_h);
			left = Math.floor((300-w)*0.5);
			__picHand.quotiety = 300/_h;
		}else{
			w = 300;
			h = 300;
			__picHand.quotiety = 300/_h;
		}
		photo.setStyle("width",w+"px").setStyle("height",h+"px").setStyle("top",top+"px").setStyle("left",left+"px");
		//更新picSize图片背景大小
		picSize = photo.getSize();
		//updata
		var bian = w/h > 1?h:w;	
		imgAreaInstance.setSelection(0, 0, bian, bian, true);
		imgAreaInstance.setOptions({ show: true ,enable :true});
		imgAreaInstance.update();
		previewHand(null,{x1:0,y1:0,x2:bian,y2:bian,width:bian,height:bian});
	}
	//上传照片
	function upFile(ob){
		//显示loading，禁用btn
		document.getElementById('upfile-form').submit();
		uploadInput.setAttr("disabled","disabled");
		btn_upload.addClass("loading");
		btn_cancel.show();
		rebackEnable = true;
	}
	//判断文件大小
	function fileChange(target,id) {
		var re = true;
		var isIE = /msie/i.test(navigator.userAgent) && !window.opera;          
		var fileSize = 0; 
		if (isIE && typeof target.files == "undefined") {
			  var filePath = target.value;     
			  var fileSystem = new ActiveXObject("Scripting.FileSystemObject");
			  
			  var file = fileSystem.GetFile(filePath);  
			  fileSize = file.Size;     
		} else {     
			  fileSize = target.files[0].size;   
		}    
		var size = fileSize / 1024;     
		if(size>5000){   
			 errorHandler("附件大小不能大于5M");   
			 re = false;
		}    
		if(size<=0){  
			errorHandler("附件大小不能为0M");
			re = false;   
		}
		return re;   
	 }   
	//上传报错提示
	function errorHandler(err){
		var _tips = W(".w_upload .tips");
		_tips.html(err);
		clearTips(_tips);
		//隐藏loading，启用btn
		uploadInput.removeAttr("disabled");
		btn_upload.removeClass("loading");
		btn_cancel.hide();
	};
	//上传照片完成的回调
	function initImgarae(jsons){
		initHeadArea();
		//设置布尔值，来确定是否让回调有效
		if(!rebackEnable)return;
		if(jsons['err'] == 'ok'){
			var dat = jsons['data'];
			//alert(dat['avatar'],dat['width'],dat['height']);
			var purl = PageUtil.userAvatarUrl(dat['avatar']); //返回图片的地址
			var _h = imgAreaInstance;
			//隐藏取消按钮
			btn_cancel.hide();
			photo.setAttr("src",__picHand.blankpic);
			//尝试几次请求purl
			var img=new Image(); 
			var tm = 0;
			W(img).on("error",function(){
				tm ++;
				setTimeout(function(){
					if(tm>50){errorHandler("上传超时,请尝试刷新页面或重新上传");return;}
					img.src = purl;
				},400);
			});
			W(img).on("load",function(){
				setPc();
			});
			img.src = purl;
			var setPc = function(){
				photo.setAttr("src",purl);
				W(".h_200 img").setAttr("src",purl);
				W(".h_48 img").setAttr("src",purl);
				W(".h_30 img").setAttr("src",purl);
				//_h.setSelection(70, 70, 230, 230, true);
				//_h.update();
				//headPicHand.imgArea.update();
				//隐藏loading，启用btn
				uploadInput.removeAttr("disabled");
				btn_upload.removeClass("loading");
				btn_save.show(); //显示保存按钮	
				
				//设置图片样式,更新裁剪模块 
				resizePic(dat['width'],dat['height']);
			};
		}else{
			//上传错误输出
			errorHandler(jsons['err']);
		}
	}
	//更新各个图片尺寸预览
	function preview(selector,size,img,selection){
		if (!selection.width || !selection.height)
			return;
		var scaleX = size / selection.width;
		var scaleY = size / selection.height;
		var _size = picSize;
		selector.css({
			width: Math.round(scaleX * _size.width),
			height: Math.round(scaleY * _size.height),
			marginLeft: -Math.round(scaleX * selection.x1),
			marginTop: -Math.round(scaleY * selection.y1)
		});
		$('#x1').val(selection.x1);
		$('#y1').val(selection.y1);
		$('#x2').val(selection.x2);
		$('#y2').val(selection.y2);
		$('#w').val(selection.width);
		$('#h').val(selection.height);    
	}
	//初始化裁剪回调
	function previewHand(img, selection) {
		preview($('.editHeadPic .h_200 img'),200,img, selection);
		preview($('.editHeadPic .h_48 img'),48,img, selection);
		preview($('.editHeadPic .h_30 img'),30,img, selection);
	}
	//裁剪变更回调
	function selectChangeHand(img, selection){
		previewHand(img, selection);
		btn_save.show(); //显示保存按钮
	}
	//初始化照片裁剪模块
	function initHeadArea(){
		if(hasinit)return;
		var _size = picSize;
		var bian = _size.width/_size.height > 1?_size.height:_size.width;
		imgAreaInstance = $('#photo').imgAreaSelect({
			x1: 0, y1: 0, x2: bian, y2: bian,
			aspectRatio: '1:1',
			persistent:true,
			instance: true,
			enable:false,//__picHand.haspre,
			onSelectChange:selectChangeHand,
			onInit:previewHand
		});
		hasinit = true;
	}
	//清楚提示
	function clearTips(obj){
		setTimeout(function(){
			if(typeof tween != "undefined")tween.cancel();
			var tween = new ElAnim(obj, {
							"opacity" : {to:0}
						}, 400, ElAnim.Easing.easeOut);
			tween.on("end",function(){
				obj.html('');
				obj.css('opacity',1);
			});
			tween.start();
		},2000);
	}
	//事件绑定
	function bingEvents(){
		var save_able = true;
		photo.on("load",function(){
			//上传图片加载后
			//photo.hide();
		});
		uploadInput.on("change",function(){
			try{
				if(!fileChange(this))return;
			}catch(e){}
			upFile("avatar");
		});	
		btn_cancel.on("click",function(){
			rebackEnable = false;
			errorHandler("已取消");
		});
		btn_save.on("click",function(){
			if(!save_able){return false;}
			save_able = false;
			btn_save.addClass("loading");
			
			QW.use("Ajax",function(){
				var dat = imgAreaInstance.getSelection(); //alert(hand.getSelection().width);
				var _x = dat.x1,_y = dat.y1,_w = dat.width,_h = dat.height;
				
				Ajax.get("/user/changeavatar",{x:_x,y:_y,width:_w,height:_h,quotiety:__picHand.quotiety, _r : Math.random()},function(data){
					if(data['err'] == 'ok'){
						//提示保存成功
						var _tips = W(".w_submit .tips");
						_tips.html("保存成功");
						btn_save.removeClass("loading");
						btn_save.hide();
						save_able = true;
						clearTips(_tips);
						//更新头像
						W("#hd .headp img").setAttr("src",PageUtil.userAvatarUrl(data['data']['avatar'],30));
					}else{
						var _tips2 = W(".w_submit .tips");
						_tips2.html(data['msg'] + ",请稍后重试");
						clearTips(_tips2);
						btn_save.removeClass("loading");
						save_able = true;
					}
				});
			});
		});
	}
	init();
});
</script>
<%/block%>
