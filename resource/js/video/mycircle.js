Dom.ready(function(){
	//关闭登录注册提示
    W(".js_close-adbar").on('click',function(){
        W("#circle-login").hide();
        if(!QW.Cookie.get('hasCloseCircleLogin'))QW.Cookie.set('hasCloseCircleLogin',"1");
    });
    //获取页面级js参数
    var globaConfig = QW.StringH.evalExp(W('#web-js-config').attr('data'));
    
	/**
	 * 热播列表list
	 **/
	var trunList;
	QW.use('Anim,Ajax,Marmot',function(){
		//热播列表
		trunList = function(option){
	    	this._option = {
	    		//单步偏移像素值
	    		step:116,
	    		//展示区域数
	    		displayNum:5,
	    		//列表总数
	    		totalNum:10,
	    		//实际总数
	    		theTotalNum:0,
	    		//QW对象
	    		moveHand:null
	    	};
	    	QW.ObjectH.mix(this._option, option, true);
	    	return this._init();
	    };
	    trunList.prototype = {
	    	_init:function(argument) {
	    		if(!!this._option.moveHand){return;}
	    	},
	    	_cache:[
	    	],
	    	//记录当前偏移量  左为负
	    	_offset:0,
	    	_tween:null,
	    	_tweenPre:null,
	    	get:function(key){
	    		return this._option[key];
	    	},
	    	set:function(key,value){
	    		this._option[key] = value;
	    		return value;
	    	},
	    	getCache:function(key){
	    		if(!!this._cache[key])
				return this._cache[key];
				else return null;
	    	},
	    	setCache:function(key, value){
	    		this._cache[key] = value;
				return value;
	    	},
	    	next:function(){
	    		var o = this;
	    		var hand = o._option.moveHand,
	    			step = o._option.step,
	    			total = o._option.totalNum,
	    			theTotal = o._option.theTotalNum;
	    			dis = o._option.displayNum,
	    			spilthNum = o._offset + theTotal - dis, //不足的个数
	    			page = Math.ceil(total/dis);
	    		if(page>1){
	    			var moveDistance = dis;
	    			//每次动画前，判断剩余个数是否<展示个数,如果满足则调整整体坐标
					if(spilthNum < dis){
						var mustOffset = 0;
						if(total<2*dis){
							mustOffset = total*2 - spilthNum - dis;
						}else{
							mustOffset = total - spilthNum - dis;
						}
						hand.css('left','-'+mustOffset*step+'px');
	    				o._offset = 0 - mustOffset;
	    			}
	    			//if(!!o._tween){o._tween.reset();}
	    			o._tween = new ElAnim(hand, {
									"left" : {to:(o._offset-moveDistance)*step}
								}, 400, ElAnim.Easing.easeOut);
	    			o._tween.start();
	    			o._offset -= moveDistance;
	    		}
	    	},
	    	prev:function(){
	    		var o = this;
	    		var hand = o._option.moveHand,
	    			total = o._option.totalNum,
	    			theTotal = o._option.theTotalNum,
	    			step = o._option.step,
	    			dis = o._option.displayNum,
	    			spilthNum = -o._offset, //不足的个数
	    			page = Math.ceil(total/dis);
	    		if(page>1){
	    			var moveDistance = dis;
	    			//每次动画前，判断剩余个数是否<展示个数,如果满足则调整整体坐标
					if(spilthNum<dis){
						var mustOffset = 0;
						if(total<2*dis){
							mustOffset = total*2 - spilthNum - dis;
						}else{
							mustOffset = total - spilthNum - dis;
						}
						mustOffset = theTotal - dis - mustOffset;
						hand.css('left','-'+mustOffset*step+'px');
	    				o._offset = 0-mustOffset;
	    			}
	    			//if(!!o._tweenPre){o._tweenPre.reset();}
	    			o._tweenPre = new ElAnim(hand, {
									"left" : {to:(o._offset+moveDistance)*step}
								}, 400, ElAnim.Easing.easeOut);
	    			o._tweenPre.start();
	    			o._offset += moveDistance;
	    		}
	    	},
	    	//填充节点内容
	    	setChild:function(htmlStr,totalNum){
	    		var o = this,
	    			displayNum = o._option.displayNum;
	    		//判断总数据是否超过2页，如果在一页内，需要复制2个copy。
	    		if(totalNum>displayNum){
	    			if(totalNum<2*displayNum){
	    				htmlStr += htmlStr + htmlStr;
	    				o._option.theTotalNum = totalNum*3;
	    			}else{
	    				htmlStr += htmlStr;
	    				o._option.theTotalNum = totalNum*2;
	    			}
	    		}
	    		o._option.moveHand.html(htmlStr);
	    		o._option.totalNum = totalNum || 0;
	    		o._offset = 0;
	    	}
	    };
	    //判断是否有list模块，没有则return
	    if(!W('#hot-video').length)return;
	    //绑定上一个下一个按钮事件
	    var currentTab = globaConfig['defaulType'];
	    var initType = globaConfig['defaulType'];
		var listData = QW.StringH.evalExp(W('#hot-video').attr('data'));
		var _displayNum = listData.displayNum || 7;
		var _step = listData.step || 124;
	    var _trunList = new trunList({
	    	step:_step,
			displayNum:_displayNum,
			moveHand:W('.y-v-list')
	    });
	    //根据数据填充，return 所有视频id字符串 "id,id,id"
    	var fullIn = function(dataArr){
    		_trunList.get('moveHand').css('left',0);
			var str = '',idStr='';
			dataArr.forEach(function(d,i){
				idStr += d._id+(i==dataArr.length-1?'':',');
				var datam = "{page_id:'click_recommendation',item_id:'"+d._id+"',item_pos:'"+(i+1)+"',rec_zone:'circle_hot_"+currentTab+"'}";
				str += '<li><dl class="y-v-box y-vbox-1"><dt>';
	            str += '<a class="marmot" target="_blank" data-lks="circle='+globaConfig['circleId']+'" data--marmot="'+datam+'" href="'+PageUtil.videoPlayUrl(d._id)+'">';
	            str += '<img src="'+PageUtil.videoThumbnailUrl(d.thumbnail)+'" alt="'+d.title+'">';
	            str += '</a></dt><dd><a class="marmot" title="'+d.title+'" data-lks="circle='+globaConfig['circleId']+'" data--marmot="'+datam+'" target="_blank" href="'+PageUtil.videoPlayUrl(d._id)+'">'+d.title+'</a>';
	            str += '</dd></dl></li>';
			});
			_trunList.setChild(str,dataArr.length);
			return idStr;
    	};
	    //显示第一列
	    var items = fullIn(DefaultData);
	    //初次加载的热播帮日志上报
	    W(window).on('load',function(){
	    	 QW.Marmot.log && QW.Marmot.log({page_id:'recommendation',rec_zone:'circle_hot_'+initType,item_list:items});
	    });
	    //将第一列数据缓存
	    _trunList.setCache(initType,DefaultData);
	    var _moveHand = W('.y-v-list');
	    _moveHand.css('position','relative');
	    W('.y-scorll-left').on('click',function(){
	    	_trunList.prev();
	    });
	    W('.y-scorll-right').on('click',function(){
	    	_trunList.next();
	    });
	    //绑定tab切换按钮
	    W('#hot-video li').on('click',function(){
	    	var data = QW.StringH.evalExp(W(this).attr('data-sns'));
	    	if(data.type == currentTab)return;
	    	currentTab = data.type; 
	    	//更改样式
	    	//取消所有选中状态
	    	W('.y-tab-left').removeClass('y-tab-left-select');
	    	W('.y-tab-center').removeClass('y-tab-center-select');
	    	W('.y-tab-right').removeClass('y-tab-right-select');
	    	//当前样式选中
	    	switch(currentTab){
	    		case 'day':
	    			W('.y-tab-left').addClass('y-tab-left-select');
	    		break;
	    		case 'week':
	    			W('.y-tab-center').addClass('y-tab-center-select');
	    		break;
	    		case 'month':
	    			W('.y-tab-right').addClass('y-tab-right-select');
	    		break;
	    	}
	    	var trun = _trunList;
	    	var params = {
	    		circle:globaConfig['circleId'],
	    		type:data['type'],
	    		count:14
	    	};
	    	
	    	//查看缓存
	    	var cacheData = trun.getCache(currentTab);
	    	if(!!cacheData){
	    		fullIn(cacheData);
	    		return;
	    	}
	    	//请求数据,并缓存
    		trun.get('moveHand').html('');
    		//请求数据，如果有缓存则不做请求
    		QW.Ajax.get('/circle/mostplayedvideos',params,function(datas){
    			if(datas.err != 'ok'){
    				W('#hot-video').html(datas.err);
    				return;
    			}
    			trun.setCache(currentTab,datas.data);
    			var idStr = fullIn(datas.data);
    			//日志上报
    			QW.Marmot.log({page_id:'recommendation',rec_zone:'circle_hot_'+currentTab,item_list:idStr});
    		});
	    });
		
		//onload时加载tab数据做缓存
		/*W(window).on('load',function(){
			var trun = _trunList;
			W('#hot-video li').forEach(function(el,i){
				var data = QW.StringH.evalExp(W(el).attr('data-sns'));
				var type = data['type'];
				var params = {
		    		circle:globaConfig['circleId'],
		    		type:data['type'],
		    		count:20
		    	};
				QW.Ajax.get('/circle/mostplayedvideos',params,function(datas){
	    			if(datas.err != 'ok'){
	    				return;
	    			}
	    			trun.setCache(type,datas.data);
	    		});
			});
		});*/
	});
	/**
	 * 圈友分页
	 **/
	 QW.use('Pager,Ajax',function(){
	 	if(!W('#circlePager').length)return;
	 	var _circleFriend = globaConfig.circleFriend || 0;
	 	var _circleId = globaConfig.circleId;
	 	var circlePager = new Pager('#circlePager',{
			total : _circleFriend,
			size:36,
			showOut :10,
			hasForm : false
		});
		if(_circleFriend<=36){W('#circlePager').hide();return;}
		circlePager.render();
	 });
	//日志上报
	W(window).on('load',function(){
	    //实体库剧集点击log
	    if(!!W('.y-warehouse-list-a').length){
	    	W('.y-warehouse-list-a').query('a').on('click',function(){
	    		QW.Marmot.log && QW.Marmot.log({page_id:'click_entity',item_list:'',circle_id:globaConfig['circleId'],id:W(this).html()});
	    	});
	    }
	});
});