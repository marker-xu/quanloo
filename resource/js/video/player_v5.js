Dom.ready(function(){
	var doc1 = (QW.Browser.firefox || QW.Browser.ie) ? document.documentElement : document.body;
	doc1.scrollTop = 1;
	//监听输入字符组件QW.checkInputTextNum
	(function(){
		var checkInputTextNum = function(option){
			this._option = {
	    		//输入框QW对象
	    		inputTarget:null
	    	};
	    	QW.ObjectH.mix(this._option, option, true);
	    	return this._init();
		};
		checkInputTextNum.prototype = {
			CHECK_STEP:'checkStep',
			currentNum:0,
			_init:function(){
				var o = this;
				if(!o._option.inputTarget)return;
				o.bindEvent();
			},
			get:function(key){
	    		return this[key];
	    	},
	    	set:function(key,value){
	    		this[key] = value;
	    		return value;
	    	},
	    	bindEvent:function(){
	    		var o = this,
	    			input = QW.g(o._option.inputTarget),
	    			callback = o.checkEventHandler;
	    		QW.CustEvent.createEvents(o,o.CHECK_STEP);
	    		if ("onpropertychange" in input) { 
					//IE6/IE7/IE8        
					input.onpropertychange = function(){            
						if (window.event.propertyName == "value"){                
							callback.call(o, window.event)            
						}        
					}    
				} else {
			        // Fix Firefox     
			        input.addEventListener("input", function(){callback.call(o);}, false);    
				}
	    	},
	    	checkEventHandler:function(){
	    		var o = this;
	    		//计算已经输入多少字符
	    		var textStr = o._option.inputTarget.val().trim();
	    		o.currentNum = QW.StringH.byteLen(textStr);
	    		o.fire(o.CHECK_STEP);
	    	}
		};
		QW.provide('checkInputTextNum', checkInputTextNum);
	})();
	/*模块跟随*/
	(function(){
		var follower = function(option){
			this._option = {
	    		//需要跟随的对象
	    		followTarget:null
	    	};
	    	QW.ObjectH.mix(this._option, option, true);
	    	return this._init();
		};
		follower.prototype = {
			_topOffset:0,
			_isScrollCall:false,
			_init:function(){
				var o = this;
				if(!o._option.followTarget)return;
				o.getOffset();
				o.bindEvent();
			},
			get:function(key){
	    		return this['_'+key];
	    	},
	    	set:function(key,value){
	    		this['_'+key] = value;
	    		return value;
	    	},
	    	//计算模块的top offset值
	    	getOffset:function(){
	    		var o = this;
	    		var target = o._option.followTarget;
	    		o._topOffset = target.getXY()[1];
	    	},
	    	bindEvent:function(){
	    		var o = this,
	    			target = o._option.followTarget;;
	    		W(window).on('scroll',function(){
	    			//获取滚动上卷高度
	    			var topScroll = Dom.getDocRect()['scrollY'];
	    			if(topScroll>o._topOffset){
	    				if(target.css('position') != 'fixed')
	    				target.css({'position':'fixed','top':'0px'});
	    				//删除监听
	    				//W(window).un('scroll');
	    			}else{
	    				if(target.css('position') != 'static')
	    				target.css({'position':'static','top':'auto'});
	    			}
	    		});
	    	}
		};
		QW.provide('follower', follower);
	})();
	/*检查输入文字*/
	var checkInputText = function(txt,maxNum){
		var s = '',
			len = QW.StringH.byteLen(txt);
		if(len == 0){
			s = '输入不能为空。';
		}else if(len > maxNum){
			s = '对不起，超出'+Math.floor(maxNum*0.5)+'个字了';
		}
		return s;
	};
	/*播放器功能 - 宽屏普屏切换*/
	(function(){
		var isMin,
			isMinC = QW.Cookie.get('isMin'),
			playerhand = W('#videoWrap'),
			rightAreaHand = W('#rightArea'),
			screenNum = [660,960],
			rightMTop = [-628,0],
			text = ['宽屏观看','普屏观看'];;
		//读取cookies的屏幕状态
		if(!isMinC){
			isMin = true;
		}else{
			isMin = (isMinC == '0')?true:false;
		}    
		//初始化屏幕
		//写在php中，更改需同步
		if(W('.j_toggle-screen').length == 0)return;
		W('.j_toggle-screen').on('click',function(){
			QW.use('Anim',function(){
				//如果是chrome
				if(!!QW.Browser.chrome){
					playerhand.css('width',screenNum[isMin?1:0]+'px');
					rightAreaHand.css('margin-top',rightMTop[isMin?1:0]+'px');
					if(!!vfollower)vfollower.getOffset();
					return;
				}
				var Anim = QW.ElAnim;
				var tweenPlay = new Anim(playerhand, {
					"width" : {to:screenNum[isMin?1:0]}
				}, 300, Anim.Easing.easeOut);
				var tweenRight = new Anim(rightAreaHand, {
					"margin-top" : {to:rightMTop[isMin?1:0]}
				}, 300, Anim.Easing.easeIn);
				tweenRight.on('end',function(){
					playerhand.css('z-index','');
					if(!!vfollower)vfollower.getOffset();
				});
				playerhand.css('z-index','1');
				tweenPlay.start();
				tweenRight.start();
			});
			//改变按钮状态
			W(this).html('<em class="ico"></em>'+text[isMin?1:0]);
			isMin = !isMin;
			QW.Cookie.set('isMin',isMin?'0':'1');  
		});
	})();
	/*播放器功能 - 对齐观看*/
	QW.use('Anim',function(){
		var alignReferenceHand = W('.j_align-reference');
		if(alignReferenceHand.length == 0)return;
		W('.j_justify').on('click',function(){
			var Anim = QW.ElAnim;
			var _y = alignReferenceHand.getXY()[1];
			var doc = (QW.Browser.firefox || QW.Browser.ie) ? document.documentElement : document.body;
			W(doc).animate({scrollTop:{to:_y-8,duration:400}});
		});
	});
	/*播放器功能 - 弹出播放*/
	(function(){
		if(W('.j_play-win').length == 0)return;
		W('.j_play-win').on('click',function(){
			window.open ('s.html', 'newwindow', 'height=460, width=400, top=0, left=0, toolbar=no, menubar=no, scrollbars=no, resizable=no,location=n o, status=no')
		});
	})();
	/*播放器功能 - 开关灯*/
	QW.use('Panel',function(){
		if(W('.j_sec-light').length == 0)return;
		var isLight = true,
			btnHand = W('.j_sec-light');
		var mask = new QW.Mask({useIframe:false});
			mask.addClassName('lightMask');
		var closeLight = function(){
			mask.show();
			W('#player').css('position','relative').css('z-index',200);
			W('.j_light').css('position','relative').css('z-index',200);
			W('#videoWrap').css({'overflow':'hidden','z-index':'','position':'static'});
			btnHand.html('<em class="ico"></em>开灯');
			isLight = false;
		};
		var showlight = function(){
			mask.hide();
			W('#player').css('position','static').css('z-index','');
			W('.j_light').css('position','static').css('z-index','');
			W('#videoWrap').css({'overflow':'visible','z-index':'','position':'relative'});
			btnHand.html('<em class="ico"></em>关灯');
			isLight = true;
		};
		W('.j_sec-light').on('click',function(){
			isLight?closeLight():showlight();
		});
	});
	/*播放器功能 - 场景*/
	QW.use('Panel',function(){
		if(W('.j_change-skin').length == 0)return;
		var mix = QW.ObjectH.mix,
			res = W('#local-data').getJss('res');
		var _arrRes = res.split('.');
			res = _arrRes[_arrRes[0]=='www'?1:0];
		var playerBarMask = {
				p_youku:40,
				p_56:36,
				p_tudou:1,
				p_ku6:41,
				p_sohu:42,
				p_sina:26,
				p_letv:1,
				p_pptv:30,
				p_joy:44,
				p_iqiyi:36,
				p_pps:42
			};
		var addSenceClose = function(){
			floatHand.appendChild(closeHand);
			closeHand.on('click',function(){
				if(!!mask)closeScene();
			});
		};
		var isScene = false,
			barMaskH = playerBarMask['p_'+res] || 40,
			SceneWH = [737,406],
			floatHand = W('.js_player-hand'), 
			barMask = W('<div style="width:'+SceneWH[0]+'px;height:10px;background-color:#000;position:absolute;bottom:0px;left:0;"></div>'),
			closeHand = W('<div title="返回" class="b_scene_close" style="position:absolute;bottom:-46px;right:15px;"></div>'),
			sceneHand = W('<div class="scene_cinema"></div>');
			//append 关闭
			closeHand.hide();
			barMask.hide();
			floatHand.appendChild(barMask);
			/*floatHand.appendChild(closeHand);
			closeHand.on('click',function(){
				if(!!mask)closeScene();
			});*/
			addSenceClose();
			//初始化场景 'background-position':'-625px -334px'
			sceneHand.hide();
			sceneHand.css({'width':'1250px','height':'668px','position':'absolute','z-index':'199'});
			W('#player').insert('afterbegin',sceneHand);
			//增加resize回调
			mix(QW.Mask.prototype,{
				resizeFn:function(){
					var instance = this,i = 0;
					if(instance._resizeTimer) {
		                clearTimeout(instance._resizeTimer);
		                instance._resizeTimer = null;
		            }
		            instance._resizeTimer = setTimeout(function(){
						if(!instance.isVisible()) return;
						instance.fire('windowresize');
		            },300);
				},
				listerResize:function(){
					var instance = this;
					QW.CustEvent.createEvents(instance, 'windowresize');
					W(window).on('resize',function(){
						instance.resizeFn();
					});
				}
			});
		var mask = new QW.Mask({useIframe:false});
			mask.addClassName('sceneMask');
			mask.listerResize();
			//电影居中处理
			mask.on('windowresize',function(){
				resizeHandler();
			});
		var resizeHandler = function(){
			var sceneWH = SceneWH,
				//参考坐标系
				refXY = W('#videoWrap').getXY();
			var _winRect = Dom.getDocRect(),
				_winW = _winRect['width'],
				_winH = _winRect['height'],
				_left = (_winW - sceneWH[0])*0.5 - refXY[0],
				_top = (_winH - sceneWH[1])*0.5,
				_top = (_top >=100?100:_top) - refXY[1];
			var _sceneLeft = _left - (1250 - SceneWH[0])*0.5,
				_sceneTop = _top - 76;
			floatHand.css({'left':_left+'px','top':_top+'px'});
			sceneHand.css({'left':_sceneLeft+'px','top':_sceneTop+'px'});
		};	
		var closeScene = function(){
			mask.hide();
			mask.visibility = false;
			floatHand.css({'position':'static','z-index':'','height':'460px','width':'auto'});
			//还原播放区域的overflow hidden
    		W('#videoWrap').css({'overflow':'hidden','z-index':''});
			//还原body滚动条
			W('body').css('overflow-x','auto');
			closeHand.hide();
			sceneHand.hide();
			barMask.hide();
			doc1.scrollTop = 1;
			isScene = false;
		};
		var showScene = function(){
			var sceneWH = SceneWH;
			mask.show();
			mask.visibility = true;
    		floatHand.css({'position':'absolute','z-index':200,'height':sceneWH[1]+'px','width':sceneWH[0]+'px'});
    		//取消播放区域的overflow hidden
    		W('#videoWrap').css({'overflow':'visible','z-index':'120'});
			//隐藏X滚动条
			W('body').css('overflow-x','hidden');
    		//显示关闭按钮
    		closeHand.show();
    		sceneHand.show();
    		barMask.css('height',barMaskH+'px');
    		barMask.show();
    		resizeHandler();
    		
			//解决部分浏览器切换播放器有残影
			setTimeout(function(){
				doc1.scrollTop = 0;
			},110)
			isScene = true;
		};
		
		W('.j_change-skin').on('click',function(){
			showScene();
		});
		window.addSenceClose = addSenceClose;
	});
	/*心情*/
	QW.use('Ajax,Anim',function(){
		if(W('#j_select-mood').length == 0)return;
		var format = QW.StringH.format,
			exp = QW.StringH.evalExp,
			Anim = QW.ElAnim,
			format = QW.StringH.format;
		var isIE6 = !!QW.Browser.ie;
		var mood = {
			vid:W('#local-data').getJss('vid'),
			showBtnHand:W('#j_select-mood'),
			moodHand:W('.j_mood-face-place'),
			userHand:W('.j_mood-user-place'),
			moodClass:'.j_mood',
			tween:null,
			moodTextArr:{
				xh:'喜欢',
				gx:'哈哈',
				zj:'震惊',
				bs:'鄙视',
				fn:'愤怒',
				ex:'恶心',
				gd:'感动',
				ng:'难过',
				gl:'给力',
				bj:'杯具'
			},
			getMoodUrl:'/video/moods2',
			postMoodUrl:'/video/mood',
			tmplMoodWrap:function(content){
				var s = '';
					s += '<div class="inner cls">';
					s += '<s class="ico ico-tri-t"></s>';
					s += '<ul class="list cls">';
					s += content;
					s += '</ul>';
					s += '</div>';
				return s;
			},
			tmplMood:(function(){
				var s = '';
					s += '<li class="item">';
					s += '<em class="c-num">{1}</em>';
					s += '<a hidefocus=""  data-jss="moodId:\'{3}\'" class="cla face-{0} j_mood" href="javascript:void(0)">';										        
					s += '<em class="ico-face"></em>';												
					s += '</a>';
					s += '<em class="title">{2}</em>';
					s += '</li>';
				return s;
			})(),
			/**
			 * @content 头像数据
			 * @isShow 是否显示"更多"按钮
			 */
			tmplUserWrap:function(content,isShow){
				isShow = false;
				var show = isShow?'block':'none';
				var s = '';
					s += '<div class="inner">';
					s += '<ul class="list cls j_user-ul">';
					s += content;
					s += '</ul>';
					s += '</div>';
					s += '<div class="bar" style="display:'+show+';">';
					s += '<a class="more j_mood-user-more" href="javascript:void(0)">更多</a>';
					s += '</div>';
				return s;
			},
			tmplUser:(function(){
				var s = '';
					s += '<li class="item m_u-item" uid="u{3}">';
					s += '<a href="{1}" class="cla item-a face-min-{0}" target="_blank">';
					s += '<img src="{2}" alt="" /><em class="ico-face"></em>';
					s += '</a>';
					s += '</li>';
					return s;
			})(),
			init:function(){
				this.loadMood();
			},
			showMood:function(){	
				var o = this,
					target = W('.j_mood-face-wrap');
				target.show();
				if(isIE6){
					return;
				}
				var tw = new Anim(target, {
					"height" : {to:102},
					"opacity" : {to:1}
				}, 300, Anim.Easing.easeOut);
				tw.start();
			},
			hideMood:function(){
				var o = this,
				target = W('.j_mood-face-wrap');
				if(isIE6){
					target.hide();
					return;
				}
				o.tween = new Anim(target, {
					"height" : {to:0},
					"opacity" : {to:0}
				}, 300, Anim.Easing.easeOut);
				o.tween.on('end',function(){
					target.hide();
				});
				o.tween.start();
			},
			//加载表情和头像数据
			loadMood:function(){
				var o = this;
				//加载前显示loading
				o.moodHand.addClass('f_loading');
				QW.Ajax.get(o.getMoodUrl,{type:1,id:o.vid,offset:0,count:14},function(data){
					var	moodStr = '',
						userStr = '';
					if(data.err != 'ok'){
						return;
					}
					//移除loading
					o.moodHand.removeClass('f_loading');
					//渲染表情
					var moodData = data.data.moodData;
					moodData.forEach(function(el,i){
						moodStr += format(o.tmplMood,el['id'],el['num'],el['name'],el['id']);
					});
					moodStr = o.tmplMoodWrap(moodStr);
					o.moodHand.html(moodStr);
					//渲染头像
					var userData = data.data.userData;
					userData.forEach(function(el,i){
						userStr += format(o.tmplUser,el['data'],QW.PageUtil.userUrl(el['uid']),el['avatar'],el['uid']);
					});
					//判断用户数是否超过12
					var userTotal = data.data.userTotal;
					//如果第一页满12个，并且总数显示超过12，显示更多按钮,因为总数和实际值可能有误差
					if(userData.length == 12 && userTotal>12){
						userStr = o.tmplUserWrap(userStr,true);
					}else{
						userStr = o.tmplUserWrap(userStr,false);
					}
					o.userHand.html(userStr);
					//绑定事件
					o.bindEvent();
				});
			},
			bindEvent:function(){
				var o = this;
				//显示心情按钮点击事件
				o.showBtnHand.on('click',function(){
					if(o.moodHand.isVisible()){
						o.hideMood();
					}else{
						o.showMood();
					}
				});
				//心情click
				o.moodHand.query(o.moodClass).on('click',function(){
					var ow = W(this),
						loadingHand = ow.query('em'),
						mid = ow.getJss('moodId'),
						numTip = ow.previousSibling('.c-num'); 
					if(loadingHand.hasClass('f_loading'))return;
					//........
					loadingHand.addClass('f_loading');
					QW.use('Ajax',function(){
						QW.Ajax.get(o.postMoodUrl,{id:o.vid,mood:mid,circle:''},function(data){
							loadingHand.removeClass('f_loading');
							//判断是否登录，true:显示"提交成功"，并关闭心情模块，false:显示"提交成功,想要表达心情并留下你的头像?，请点击这里登录"
							if(data.err == 'ok'){
								var el = data.data,
									userUl = W('.j_user-ul');
								//未登录
								if(!el['uid']){
									o.showTips(W('.j_mood-tips'),'发表成功，想要留下你的足迹吗?请点击此处 <a class="js_fast-login j_login" href="javascript:void(0)">登录</a>',3000,false);
									o.hideMood();
								}else{
								//登录
									o.hideMood();
									//增加头像到人物模块,如果模块中已经有该用户，只改变心情
									var userli = userUl.query('[uid="u'+el['uid']+'"]');
									if(userli.length != 0){
										userli.query('.item-a').attr('className','cla item-a face-min-'+el['data']);
									}else{
										var userItem = format(o.tmplUser,el['data'],QW.PageUtil.userUrl(el['uid']),el['avatar'],el['uid']);
										userUl.insert('afterbegin',W(userItem));
									}
								}
								//数字加1
								var nums = parseInt(numTip.html()) + 1;
								numTip.html(nums); 
								//显示选择后的头像
								o.showBtnHand.attr('className','face-'+el['data']);
							}else if(data.err == 'sys.acts'){
								o.showTips(W('.j_mood-tips'),'今天对此视频已经发表过心情了',3000,true);
								o.hideMood();
							}else{
								return;
							}
						});
					});
				});
				//more点击委托
				W('body').delegate('.j_mood-user-more','click',function(){
					//异步加载更多人物
				});
			},
			//清除tips
			clearTips:function(ow,t){
				var o = this;
				clearTimeout(o.timer);
				o.timer = setTimeout(function(){
					//临时处理ie8透明度问题
					if(QW.Browser.ie == '8.0'){
						ow.hide();
						return;
					}
					if(!!tween)tween.cancel();
					var tween = new ElAnim(ow, {
									"opacity" : {to:0}
								}, 400, ElAnim.Easing.easeOut);
					tween.start();
				},t);
			},
			showTips:function(ow,text,t,isClear){
				ow.show();
				ow.html(text).css('opacity',1);
				if(!!isClear)this.clearTips(ow,t);
			}
		};
		mood.init();
	});
	/*观点墙*/
	QW.use('Ajax,Anim',function(){
		if(W('.j_init-wall').length == 0)return;
		var each = QW.ArrayH.forEach,
			exp = QW.StringH.evalExp,
			format = QW.StringH.format,
			trim = QW.StringH.trim;
		var tagWall = {
			vid:W('#local-data').getJss('vid'),
			//public
			getTagUrl:'/video/xComments',
			postTagUrl:'/video/addXComment',
			allWidth:610,
			initCount:50,
			//显示tag总数
			totalHand:W('.j_tag_total'),
			initWall:W('.j_init-wall'),
			colorArr:['#9B9B9B','#848484','#72A800','#629100','#4B781F','#336600'],
			//colorArr:['#9B9B9B','#848484','#63B9E7','#32A2DE','#208ECA','#0066CC'],
			tmplWall:(function(){
				var s = '';
					s += '<li><a class="wallItem" data-jss="txt:\'{2}\',num:\'{0}\'" href="javascript:void(0)" title="此观点被顶了{0}次" style="background-color:{1};">{2}</a></li>';
				return s;
			})(),
			init:function(){
				this.loadWall();
			},
			loadWall:function(){
				var o = this;
				QW.Ajax.get(o.getTagUrl,{id:o.vid,offset:0,count:o.initCount},function(data){
					if(data.err!='ok'){return;}
					data = data.data;
					var totalNum = data['total'];
					//显示总数
					o.totalHand.html(totalNum);
					//将容器设置透明度为0 ，初步渲染tag墙
					o.initWall.css('opacity','0');
					var wallData = data.data,
						wallStr = '';
					//先找出最大值
					var maxData = 0;
					wallData.forEach(function(el,i){
						var _num = el['num'];
						if(_num > maxData) maxData = _num;
					});
					wallData.forEach(function(el,i){
						//计算颜色,<=6的都用灰底色,剩下的采用分级
						var _color = '',
							_num = el['num'];
						if(_num <= 2){
							_color = o.colorArr[0];
						}else{
							_color = o.colorArr[Math.floor((el['num']/maxData)*4)+1];
						}
						wallStr += format(o.tmplWall,el['num'],_color,el['data']);
					});
					o.initWall.html(wallStr);
					//重渲染墙体
					o.reRender(o.initWall.query('li'));
					//显示wall
					o.initWall.css('opacity','1');
					o.bindEvent();
				});
			},
			//渲染对齐
			reRender:function(itemArr){
				var o = this,
					_currentW = 0,
					_currentY = 0,
					_index = -1,
					_wallArr = [],
					_itemWidthArr = [];
				//检查每个砖块的位置信息，排成行
				itemArr.forEach(function(el,i){
					var target = W(el),
						size_w = target.getSize()['width'],
						xy_y = target.getXY()[1];
					if(xy_y != _currentY){
						_index ++;
						_wallArr[_index] = [];
						_itemWidthArr[_index] = 0;
					}
					_wallArr[_index].push({el:target,w:size_w});
					_itemWidthArr[_index] += size_w;
					
					_currentY = xy_y,
					_currentW = size_w;
				});
				//重设砖块宽度
				each(_wallArr,function(el,i){
					var allW = _itemWidthArr[i],
						num = el.length,
						//间隙的总宽度
						spaceW = num-1,
						//剩余宽度
						shenW = o.allWidth - allW - spaceW,
						//每个item增加的宽度
						addW = Math.floor(shenW/num),
						//最后一个item增加的宽度
						lastAddW = shenW - addW*num + addW;
					//重设宽度
					each(el,function(obj,i){
						var itm = obj['el'],
							itmNW = obj['w'];
						var addnum = (i==num-1)?lastAddW:addW;
						itm.css('width',(itmNW + addnum)+'px');
					});
				});
			},
			bindEvent:function(){
				var o = this,
					postInput = W('.j_wall-input'),
					wallNum = W('.j_wall-num');
				//观点输入框监听
				var maxNum = 30;
				var wallTextCheck = new QW.checkInputTextNum({
					inputTarget:postInput
				});
				wallTextCheck.on(wallTextCheck.CHECK_STEP,function(){
					var nowNum = wallTextCheck.get('currentNum'),
						nn = Math.floor(nowNum/2),
						ln = Math.floor((maxNum - nowNum)/2);
					if(ln<0){  
						wallNum.html('<span style="color:red;">已超出15个字</span>');
					}else{
						wallNum.html('<strong class="cur-num">'+nn+'</strong>/15');
					}
				});
				//**********提交tag
				W('.j_post-tag').on('click',function(e){
					//获取input值
					var oo = W(this);
					if(oo.hasClass('f_loading'))return;
					var val = trim(postInput.val());
					//前端过滤
					var hasTag = W('#local-data').getJss('alreadyTag');
					if(!!hasTag){
						o.showTips(W('.j_tag-tips'),'你已经提交过了',3000,true);
						return;
					}
					var result = checkInputText(val,30);
					if(!!result){
						o.showTips(W('.j_tag-tips'),result,3000,true);
						return;
					}
					var $srcData = {
		                'type':'j_post-tag'
		                , 'source': this
		                , 'event': e.type
		            };
					//客户端检查是否登录
					/*if(W('#logined_mark').length == 0){
						$srcData && window.XLogin && XLogin.storeTriggerData( $d, $srcData );
                        LOGIN_POPUP();
                        return;
					}*/
					//loading f_loading
					oo.addClass('f_loading');
					
					QW.Ajax.post(o.postTagUrl,{id:o.vid,content:val,circle:'',type:'submit'},function(data){
						//移除loading
						oo.removeClass('f_loading');
						if(data.err == 'sys.permission.need_login'){
							$srcData && window.XLogin && XLogin.storeTriggerData( data, $srcData );
	                        LOGIN_POPUP();
	                        return;
						}else if(data.err == 'ok'){
							//清空input
							wallNum.html('<strong class="cur-num">0</strong>/15');
							postInput.val('');

							//增加tag在后面
							var myItemStr = format(o.tmplWall,1,o.colorArr[0],val);
							o.initWall.appendChild(W(myItemStr));
							//总数+1
							var nnum = parseInt(o.totalHand.html()) + 1;
							o.totalHand.html(nnum);
							//提示成功
							o.showTips(W('.j_tag-tips'),'提交成功',3000,true);
						}else if(data.err == 'sys.acts'){
							//设置全局jss表明已经发表过观点，目的只做前端过滤
							W('#local-data').setJss('alreadyTag','1');
							o.showTips(W('.j_tag-tips'),'你已经提交过了',3000,true);
						}else{
							o.showTips(W('.j_tag-tips'),data['msg'],3000,true);
							return;
						}
					});
				});
				//显示输入模块
				W('.j_postag-place').show();
				//*************点击砖块事件,顶短评
				W('body').delegate('.wallItem','click',function(evt){
					//获取短评id
					var oo = W(this);
					if(oo.hasClass('f_loading'))return;
					var txt = oo.getJss('txt'),
						num = parseInt(oo.getJss('num'));
					//前端过滤
					var hasUp = W('#local-data').getJss('alreadyUp');
					if(!!hasUp){
						o.showTips(W('.j_tag-tips'),'你已经顶过了',3000,true);
						return;
					}
					//loading f_loading
					oo.addClass('f_loading');  
					QW.Ajax.post(o.postTagUrl,{id:o.vid,content:txt,circle:'',type:'up'},function(data){
						//提交成功，显示'顶'成功
						oo.removeClass('f_loading');
						if(data.err == 'ok'){
							//显示 +1 动画
							o.showTips(W('.j_tag-tips'),'观点\"'+txt+'\"\+1',3000,true);
							//更新被顶次数
							oo.setJss('num',num+1),
							oo.attr('title','此观点被顶了'+(num+1)+'次');
							//总数+1
							var nnum = parseInt(o.totalHand.html()) + 1;
							o.totalHand.html(nnum);
							//提示成功
							o.showTips(W('.j_tag-tips'),'提交成功',3000,true);
						}else if(data.err == 'sys.acts'){
							//设置全局jss表明已经发表过观点，目的只做前端过滤
							W('#local-data').setJss('alreadyUp','1');
							o.showTips(W('.j_tag-tips'),'你已经顶过了',3000,true);
						}else{
							return;
						}
					});
				});
			},
			//清除tips
			clearTips:function(ow,t){
				var o = this;
				clearTimeout(o.timer);
				o.timer = setTimeout(function(){
					//临时处理ie8透明度问题
					if(QW.Browser.ie == '8.0'){
						ow.hide();
						return;
					}
					if(!!tween)tween.cancel();
					var tween = new ElAnim(ow, {
									"opacity" : {to:0}
								}, 400, ElAnim.Easing.easeOut);
					tween.start();
				},t);
			},
			showTips:function(ow,text,t,isClear){
				ow.show();
				ow.html(text).css('opacity',1);
				if(!!isClear)this.clearTips(ow,t);
			}
		};
		tagWall.init();
	});	
	/*评论*/
	(function(){
		if(W('.j_sub_comment').length == 0)return;
		var each = QW.ArrayH.forEach,
			format = QW.StringH.format,
			Anim = QW.ElAnim,
			trim = QW.StringH.trim;
		var subComment = {
			vid:W('#local-data').getJss('vid'),
			subBtn:W('.j_sub_comment'),
			textArea:W('.j_comment-input'),
			numSpan:W('.j_remNum'),
			totalHand:W('.j_comment-total'),
			commentPlace:W('#comment_list'),
			maxNum:400,
			subCommentUrl:'/video/comment',
			tmplCommentLi:(function(){
				var s = '<li class="first"><div class="user-info">';
					s += '<p class="face-box">';
					s += '<a target="_blank" href="{0}"><img data-id="{1}" src="{5}" class="face ava_popup_"></a></p>';
					s += '</div>';
					s += '<p class="name"><a target="_blank" href="{0}">{2}</a></p>';
					s += '<div class="content">{3}</div>';
					s += '<div class="attract">';
					s += '<div class="done"><a href="javascript:void(0)" class="reply j_comment-reply" data-jss="name:\'{2}\'" >回复</a></div>';
					s += '<div class="info">';
					s += '<p style="display: none" class="digg"><span class="num">11</span><a href="#" class="do"><em class="ico"></em></a></p>';
					s += '<p class="date">{4}</p>';
					s += '</div></div></li>';
				return s;	
			})(),
			init:function(){
				var o = this;
				o.commentsTextCheck();
				o.bindEvent();
			},
			//输入框字数控制处理
			commentsTextCheck:function(){
				var o = this;
				var textCheck = new QW.checkInputTextNum({
					inputTarget:W('.j_comment-input')
				});
				textCheck.on(textCheck.CHECK_STEP,function(){
					var nowNum = textCheck.get('currentNum'),
						ln = Math.floor((o.maxNum - nowNum)/2);
					if(ln<0){
						o.numSpan.html('已经超出<span class="num" style="color:red;">'+Math.abs(ln)+'</span>个字符');
					}else{
						o.numSpan.html('还可以输入<span class="num">'+ln+'</span>个字符');
					}
				});
			},
			bindEvent:function(){
				var o = this;
				o.subBtn.on('click',function(e){
					var oo = W(this);
					if(oo.hasClass('f_loading'))return;
					//获取输入内容，并检查
					var val = trim(o.textArea.val());
					//前端过滤
					var result = checkInputText(val,o.maxNum);
					if(!!result){
						o.showTips(W('.j_comms-tips'),result,3000,true);
						return;
					}
					var $srcData = {
		                'type':'j_sub_comment'
		                , 'source': this
		                , 'event': e.type
		            };
					oo.addClass('f_loading');
					
					QW.Ajax.post(o.subCommentUrl,{id:o.vid,content:val,circle:'',rnd:Math.random(),format:"json"},function(data){
						oo.removeClass('f_loading');
						if(data.err == 'sys.permission.need_login'){
							$srcData && window.XLogin && XLogin.storeTriggerData(data,$srcData );
	                        LOGIN_POPUP();
	                        return;
						}else if(data.err == 'ok'){
							//清空input
							o.numSpan.html('还可以输入<span class="num">200</span>个字符');
							o.textArea.val('');

							var cdata = data.data.data[0];
							//如果有“暂无视频DOM节点 隐藏”
							if(W('#no_comment').length)W('#no_comment').hide();
							//先删除第一列的first class
							o.commentPlace.one('li').removeClass('first');
							//过滤@为超链接
							var contents = PageUtil.formatUserLinkText(cdata.data,cdata.users);
							//增加新评论到第一列
							var commentItemStr = format(o.tmplCommentLi,QW.PageUtil.userUrl(cdata.user_id),cdata.user_id,cdata.nick,contents,cdata.create_time_str,cdata.avatar);
							o.commentPlace.insert('afterbegin',W(commentItemStr));
							//总数+1
							var nnum = parseInt(o.totalHand.html()) + 1;
							o.totalHand.html(nnum);
							//提示成功
							o.showTips(W('.j_comms-tips'),'提交成功',3000,true);
						}else if(data.err == 'sys.acts'){
							
						}else{
							o.showTips(W('.j_comms-tips'),data['msg'],3000,true);
							return;
						}
					});
				});
				//回复按钮绑定
				W('body').delegate('.j_comment-reply','click',function(){
					var name = W(this).getJss('name');
					o.textArea.val('@'+name+' ');
					//制定滚动条位置
					var topY = o.textArea.getXY()[1],
						topY = topY -70,
						scrollTop = Dom.getDocRect().scrollY;
					//如果卷起的高度小于topY  return
					if(topY > scrollTop){
						o.textArea.core[0].focus();
						return;
					}
					var backTop = new Anim(W(doc1), {
						"scrollTop" : {to:topY}
					}, 300, Anim.Easing.easeIn);
					backTop.on('end',function(){
						o.textArea.core[0].focus();
					});
					backTop.start();
				});
			},
			//清除tips
			clearTips:function(ow,t){
				var o = this;
				clearTimeout(o.timer);
				o.timer = setTimeout(function(){
					//临时处理ie8透明度问题
					if(QW.Browser.ie == '8.0'){
						ow.hide();
						return;
					}
					if(!!tween)tween.cancel();
					var tween = new ElAnim(ow, {
									"opacity" : {to:0}
								}, 400, ElAnim.Easing.easeOut);
					tween.start();
				},t);
			},
			showTips:function(ow,text,t,isClear){
				ow.show();
				ow.html(text).css('opacity',1);
				if(!!isClear)this.clearTips(ow,t);
			}
		};
		subComment.init();
	})();
	/*跟随*/
	var vfollower = new QW.follower({followTarget:W('#popVideos')});  
	searchSug2();
	monitarSug();
});
/**
 * 播放器 播放结束回调-----------------------------------------------------------
 */ 
/**
 * 播放结束回调 - 土豆
 */ 
function playEnd()
{
    PLAY_END_ACTION();
}
/**
 * 播放结束回调 - 优酷
 */ 
function onPlayerComplete()
{
    PLAY_END_ACTION();
}
/**
 * 播放结束回调 - 新浪\激动\搜狐 无内嵌
 */ 
function playCompleted()
{
    PLAY_END_ACTION();
}
/**
 * 播放结束回调 - 56
 */ 
function s2j_onPlayOver()
{
    PLAY_END_ACTION();
}
/**
 * 播放结束回调 - ku6 前提是参数为api=1
 */ 
function playFinish()
{
    PLAY_END_ACTION();
}
//youku播放失败回调
function onPlayerError(){
	PLAY_END_ACTION({reqtype:'playererror'});
}
//56播放失败回调
function s2j_onPlayError(){
	PLAY_END_ACTION({reqtype:'playererror'});
}
/**
 * 播放结束回调
 */
function PLAY_END_ACTION(opt)
{
    //PLAY_NEXT();
    
    var video_box_ = W('.js_player-hand');
    
    if( !video_box_.length ) return;
    
    var getParam = { "id": window.VIDEO_ID||'', "circle": window.CIRCLE_ID||'', "referer":window.REFERER || '', "rnd": Math.random() };
    if (opt) {
    	QW.ObjectH.mix(getParam, opt);
    }
    QW.Ajax.get
    (
        '/video/player_finish'
        , getParam                
        , function( $d )
        {
            if( $d && $d.err == 'ok' )
            {
                video_box_.html( $d.data );
                /*emment*/
                addSenceClose();
                init_event();
            }
            else
            {
                //alert('内部错误!');
            }
        }
                
        , {
            onerror:
            function()
            {
                //alert('网络失败');
            }
        }
    );
    
    function init_event()
    {
        var end_list_ = W('#end_list_');
        if( !end_list_.length ) return;
        
        var pre_page_ = W('a.pre_page_');
        var next_page_ = W('a.next_page_');
        
        if( !(pre_page_.length || next_page_.length ) ) return;
    
        var items = end_list_.query('> li');
        var list_len = items.length;
        var page_len = 3;
        
        var ids = [];
        
        items.forEach
        (
            function($ele)
            {
                if( W($ele).attr('data-id') )
                {
                    ids.push( W($ele).attr('data-id') );
                }
            }
        );
            
        QW.Marmot.log({
            "page_id": "recommendation"
            , "rec_zone": end_list_.attr('data-rec-zome') 
            , "item_list": ids.join()
            //, "user_id": window.UID || ''
        });
        
        if( list_len <= page_len ) 
        {
            pre_page_.hide();
            next_page_.hide();
            return
        }
        
        /**
         * 最受欢迎 - 垂直划动
         */         
        XItemSlider.vexec
        (
            {
                prev: 'end_list_pre',
                next: 'end_list_next',
                list: 'end_list_',
                
                "fixPosition": true,
                "itemHeight": 100,
                "mainHeight": 330
            }
        );
        
    }
} 
/**
 * 如果播放页是异步登陆, 则有可能SUG不起作用
 * 该函数修复这个问题 
 */ 
function monitarSug()
{
    W('#search-text2').attr('aj_detect', 1);
 
    W(document).delegate
    (
        "input.search-frame2", "click",
        function( $evt )
        {
            var p = W(this);
            
            if( !p.attr('aj_detect') )
            {
                p.attr('aj_detect', 1);
                searchSug2();
            }
        }
    );
}
/*搜索提示emment*/
function searchSug2(){
	/*suggest*/
	QW.use('Suggest', function(){
	    var sug = new QW.Suggest({
	        textbox: '#search-text2',
	        autoFocusFirstItem: false,
	        dataUrl: QW.Config.get('suggest_uri') + "?r="+Math.random(),
	        uiItemNumber: 10,            
	        uiReferEl: '.search-wd',
	        uiBaseLayerConfig:{
	            autoPosition: false,
	            left:0,
	            top:0
	        },
	        uiRender:function(oData){
	            var isFirst = this._prop.items.length==0;
	            var fmtfun = formatSugItem;
	            var hasInfo = oData.info.length>0?true:false;
	            var str = '<div class="item"><span class="key '+(hasInfo?'haschild':'')+'">'+ QW.StringH.encode4Html(oData.display) +'</span>';
	               /* str += '<ul class="info" style="display:none;">';
	                str += (function(info){
	                            if(!hasInfo)return '';
	                            var itemString = "";
	                            info.forEach(function(dat){
	                               itemString += fmtfun(dat);
	                            });
	                            return itemString;
	                        })(oData.info);
	                str += '</ul>';*/
	                str += '</div>';
	            var itemQw = W(str);
	            itemQw.on('mouseover', function(){
	                var panelLayer = W(this).parentNode('div').parentNode('div');
	                    panelLayer.query('ul').hide();
	                W(this).query('ul').show();
	            });
	            return itemQw;
	        },
	        uiHighlighter:function(oEl,i){
	            var isNullKey = sug.get('keyword')=='.toplist';
	            var elKeyEl = QW.NodeW(oEl).query('.key').core[0];
	            var sHtml = elKeyEl.innerHTML;
	            var _key = sug.getKeyword();
	           
	            var _index = sHtml.indexOf(_key);
	            if(isNullKey){//空key显示排行榜
	                var istr = '<b class="b-index">'+(i==9?'':'&nbsp;')+(i+1)+'. </b>';
	                elKeyEl.innerHTML = istr+sHtml;
	            }else if(_index!=-1){
	                var _a = sHtml.slice(0,_index);
	                var _b = sHtml.slice(_index + _key.length);
	                if(_a!="")_a = '<b>'+_a+'</b>';
	                if(_b!="")_b = '<b>'+_b+'</b>';
	                var st = _a + _key + _b;
	                elKeyEl.innerHTML = st;
	            }
	        },
	        dataHandler:function(sKey, oData, oThis){
	            //继承
	            if (oData.sug) {
	                for(var i = 0; i < oData.sug.length; i++){
	                    oData.sug[i].key = oData.sug[i].display = oData.sug[i].q;
	                    oData.sug[i].val = ' ';
	                }
	                oThis._read(oData.sug);
	                oThis._prop.cache.pushCache(sKey, oData.sug);
	            }else{
	                oThis._read([]);
	                oThis._prop.cache.pushCache(sKey, []);
	            }
	            //扩展
	            var dataList = oData.sug,
	                dataHasInfo = false,
	                wrap = W(".panel-suggest .bd");
	            if(typeof dataList == "undefined")return;
	            dataList.forEach(function(dat){
	                if(dat.info.length>0 && !dataHasInfo){
	                    dataHasInfo= true;
	                }
	            });
	            if(dataHasInfo){
	                wrap.css("height","300px");
	            }else{
	                wrap.css("height","auto");
	            }
	        },
	        callback: 'cb',
	        keyword: 'wd'
	    });                     
	    var formatSugItem = function(infoItem){
	        var objk = (function(obj){
	                        var tyclass = "",itemStr = "";
	                        var _obj = obj;
	                        var filerText = function(tx){
	                            return (!!QW.StringH.trim(tx))?tx:'未知';
	                        };
	                        var stopDefaut = 'onmousedown="QW.EventH.stopPropagation(null, this);" target="_blank"';
	                        switch(_obj.type){
	                            case "圈子":
	                                tyclass = "ty-circle";
	                                itemStr += (function(){
	                                    var str="";
	                                    _obj.info.forEach(function(ob,i){
	                                        str += '<div class="row '+(function(){
	                                            var al = _obj.info.length;
	                                            if(al<2)return '';
	                                            return (i<al-1?'dashed':'')
	                                        })()+'">';
	                                        str += '<span style="display:'+(parseInt(ob.like)<1?'none':'inline')+';" class="r">粉丝：'+ob.like+'</span><a '+stopDefaut+' href="'+PageUtil.circleUrl(parseInt(ob.cid), null, ob)+'">'+ob.title+'</a></div>';
	                                    });
	                                    return str;
	                                })();
	                            break;
	                            case "综艺":
	                                tyclass = "ty-entertainment";
	                                itemStr += '<div class="cls row"><div class="info-item"><div class="title">'+_obj.title+'</div><p class="lists">';
	                                itemStr += (function(){
	                                    var str="";
	                                    _obj.play_url.forEach(function(ob,i){
	                                        str += '<a '+stopDefaut+' class="play" href="'+ob.play_url+'">'+ob.date+' '+ob.desc+'</a>';
	                                    });
	                                    return str;
	                                })();
	                                itemStr += '</p></div><a '+stopDefaut+' class="pic-view" href="'+_obj.play_url[0].play_url+'"><img src="'+PageUtil.videoThumbnailUrl(_obj.cover_url)+'"></a></div>';
	                            break;
	                            case "动漫":
	                                tyclass = "ty-cartoon";
	                                itemStr += '<div class="cls row"><div class="info-item"><div class="title">'+_obj.title+'</div><p class="lists">';
	                                itemStr += '导演：'+ filerText(_obj.director)+'<br/>主演：'+filerText(_obj.player)+'<br/>';
	                                itemStr += '</p><div class="video-group">';
	                                itemStr += (function(){
	                                    var str="",
	                                        isOver5=_obj.play_url.length>5;
	                                    _obj.play_url.forEach(function(ob,i){
	                                        if(isOver5 && i==3){
	                                            var hasNo4 = QW.StringH.trim(ob.video_no) == "4";  
	                                            if(!hasNo4){
	                                                str += '<a '+stopDefaut+' style="cursor:default;" href="#">...</a>';
	                                            }
	                                        }
	                                        str += '<a '+stopDefaut+' href="'+ob.play_url+'">'+ob.video_no+'</a>';
	                                    });
	                                    return str;
	                                })();
	                                itemStr += '</div></div><a '+stopDefaut+' class="pic-view" href="'+_obj.play_url[0].play_url+'"><img src="'+PageUtil.videoThumbnailUrl(_obj.cover_url)+'"></a></div>';
	                            break;
	                            case "电视剧":
	                                tyclass = "ty-tv";
	                                itemStr += '<div class="cls row"><div class="info-item"><div class="title">'+_obj.title+'</div><p class="lists">';
	                                itemStr += '导演：'+ filerText(_obj.director)+'<br/>主演：'+filerText(_obj.player)+'<br/>';
	                                itemStr += '</p><div class="video-group">';
	                                itemStr += (function(){
	                                    var str="",
	                                        isOver5=_obj.play_url.length>5;
	                                    _obj.play_url.forEach(function(ob,i){
	                                        if(isOver5 && i==3){
	                                            var hasNo4 = QW.StringH.trim(ob.video_no) == "4";  
	                                            if(!hasNo4){
	                                                str += '<a '+stopDefaut+' style="cursor:default;" href="#">...</a>';
	                                            }
	                                        }
	                                        str += '<a '+stopDefaut+' href="'+ob.play_url+'">'+ob.video_no+'</a>';
	                                    });
	                                    return str;
	                                })();
	                                itemStr += '</div></div><a '+stopDefaut+' class="pic-view" href="'+_obj.play_url[0].play_url+'"><img src="'+PageUtil.videoThumbnailUrl(_obj.cover_url)+'"></a></div>';
	                            break;
	                            case "电影":
	                                tyclass = "ty-movie";      
	                                itemStr += '<div class="cls row"><div class="info-item"><div class="title">'+_obj.title+'</div><p class="lists">';
	                                itemStr += '导演：'+ filerText(_obj.director)+'<br/>主演：'+filerText(_obj.player)+'<br/>';
	                                itemStr += '</p><a '+stopDefaut+' class="play" href="'+_obj.play_url+'">播放影片</a></div><a '+stopDefaut+' class="pic-view" href="'+_obj.play_url+'"><img src="'+PageUtil.videoThumbnailUrl(_obj.cover_url)+'"></a></div>';
	                            break;
	                            case "人物":
	                                tyclass = "ty-superstar";
	                                itemStr += '<div class="cls row"><div class="info-item"><div class="title">'+_obj.name+'</div><p class="lists">';
	                                itemStr += (function(){
	                                    var str="";
	                                    _obj.works.forEach(function(ob,i){
	                                       str += '<a '+stopDefaut+' class="play" href="'+ob.play_url+'">'+ob.title+'</a>';
	                                    });
	                                    return str;
	                                })();
	                                itemStr += '</p></div><a '+stopDefaut+' style="cursor:default;" class="pic-view"><img src="'+PageUtil.videoThumbnailUrl(_obj.cover_url)+'"></a></div>';
	                            break;
	                        } 
	                        return {className:tyclass,typeName:obj.type,str:itemStr};
	                   })(infoItem);
	        var str = '<li class="'+objk.className+'">';
	            str += '<dl><dt>'+objk.typeName+'</dt><dd>';
	            str += objk.str;
	            str += '</dd></dl></li>';
	        return str;
	    };
	    function doSug (e){
	        if(sug.getKeyword()) {
	            var baseSearchUri = QW.Config.get('host') + '/search?q=' + encodeURIComponent(sug.getKeyword()),
	                query_src = '';
	            W('.panel-suggest .item').forEach(function(i){                        
	                if (sug.getKeyword() == QW.StringH.stripTags(W(i).html())) {
	                    query_src = '&query_src=2';
	                    return;
	                }
	            });
	            window.location = baseSearchUri + query_src;
	        }
	    }        
	    sug.on('enter', doSug);
	    sug.on('itemselect', doSug);
	    sug.on('itemfocus',function(e){
	        var index = e.target.index;
	        if(index<0)return;
	    });
	    sug.on('focus',function(e){
	        var ts = this;
	        if(!ts.getKeyword()){
	            ts.suggest('.toplist');
	        }
	    });
	    sug.on('backspace',function(e){
	        var ts = this;
	        if(ts.getKeyword().length == 1){
	            ts.suggest('.toplist');
	        }
	    });
	    var _timer = -1;
	    W("#search-text2").on("blur",function(event){
	        clearTimeout(_timer);
	        _timer = setTimeout(function(){sug.hide();},200);
	    });
	});
	W('#search-form2').on('submit', function(e){        
	    if(!QW.StringH.trim(W('#search-text').val())){
	        e.preventDefault();
	    }
	}); 
	//搜索框提示文字
	var sch = W(".search-frame2");
	sch.on("click",function(){
	    if (W(this).val() == W(this).attr('title')) {
	        W(this).val("");
	    }
	});
	sch.on("blur",function(){
	    if (W(this).val() == "") {
	        W(this).val(W(this).attr('title'));
	    } 
	}); 
}
