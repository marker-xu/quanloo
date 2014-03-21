Dom.ready(function(){
	var panelWrap = '<div class="panel panel-t1" style="width:559px;display:none;" id="feedback_popup"><div class="panel-content">';
		panelWrap += '<div class="hd"><h3>用户反馈</h3></div><div class="bd"><div class="fbk_wrap">';
		panelWrap += '</div></div><div class="ft"></div></div><span class="co1"><span></span></span><span class="co2"><span></span></span><span class="cue"></span>';
		panelWrap += '<span class="sd"></span><span class="close close__"></span><span class="resize"></span></div>';
	var wpanel = W(panelWrap);    
	var hasbindEvent = false; 
	//add to body        
    W('body').appendChild(wpanel);
	W("#feed_back").on("click",function(e){
		e.preventDefault(); 
		var _this = this;
		W(".fbk_wrap").removeClass("fbk_no");
		W("#feedback_popup .fbk_wrap").setHtml("");
        QW.use('Panel,Ajax', function(){
            QW.pageGlobal.fbk = new Dialog(g('feedback_popup'));
            QW.pageGlobal.fbk.on("afterhide",function(){
            	clearInterval(QW.pageGlobal.fbk_timer);
            });
            QW.pageGlobal.fbk.show();
            QW.Ajax.get(QW.Config.get('host') + '/user/addfeedback', {},function(data){
				if(data.err=="ok"){
					var chtml = data.data["html"];
					W("#feedback_popup .fbk_wrap").setHtml(chtml);
					//bind events
					if(!hasbindEvent)bindEvent();
				}else{alert(data.err);}
			});
        });
	});	
	var checkInputNum = function(){
		var tg = W(".fbk_textara");
		if(!tg.length)return;

		var value = tg.val().trim().length;
        var num = W(".fbk_t_num");
        if(value>500){
        	num.setHtml('<strong>你输入已经超过500字</strong>');
        	return;
        }
        num.setHtml('<strong>'+value+'</strong>/500');
	};
	var listenerInput = function(input, callback){    
		if ("onpropertychange" in input) { 
			//IE6/IE7/IE8        
			input.onpropertychange = function(){            
				if (window.event.propertyName == "value"){                
					callback.call(this, window.event)            
				}        
			}    
		} else {
	        // Fix Firefox     
	        input.addEventListener("input", callback, false);    
		}
	}
	/**
	 *	绑定事件
	 */
	var bindEvent = function(){
		//判断输入字符数
		/*W(document).on("keyup",function(){
			var tg = W(".fbk_textara");
			if(!tg.length)return;
			checkInputNum();
		}).on("mouseup",function(){
			var tg = W(".fbk_textara");
			if(!tg.length)return;
			checkInputNum();
		});*/
		listenerInput(g("intro"),function(){
			var tg = W(".fbk_textara");
			if(!tg.length)return;
			checkInputNum();
		});
		//绑定提交
		W(document).delegate(".fbk_submit","click",function(){
			if(W(this).hasClass("loading"))return;
			//验证字符
			var tg = W(".fbk_textara");
			if(!tg)return;
			var value = tg.val().trim().length;
			if(value == 0){
				alert("输入字符不能为空");
				return;
			}else if(value > 500){
				alert("输入字符不能超过500字");
				return;
			}
			var checkStr = W("input[name=csrf_token]").val();
			//提交
			QW.use('Ajax', function(){
				//loading
				W(".fbk_submit").addClass("loading");
				var _content = tg.val();
				QW.Ajax.post(QW.Config.get('host') + '/user/addfeedback', {
					csrf_token : checkStr,
					content : _content
				},function(data){
					if(data.err=="ok"){
						var chtml ='<div style="height:200px!important;" class="panel-invite-friend fbk_success"><h3 class="title"><span>非常感谢你的建议，在你的帮助下圈乐会做得更好!</span></h3>';
						chtml += '<div style="margin-top: 20px;" class="close-tips"><span>请稍候<label class="timer__">3</label>秒钟后自动关闭</span></div>';
						chtml += '<button class="bt-close close__" type="button">立即关闭</button></div>';
						W(".fbk_wrap").addClass("fbk_no");
						W("#feedback_popup .fbk_wrap").setHtml(chtml);
						W(".fbk_submit").removeClass("loading");
						//定时关闭窗口
						var timehand = W(".fbk_success .timer__"),
							i = 0;
						QW.pageGlobal.fbk_timer = setInterval(function(){
							timehand.setHtml(2-i);
							i++;
							if(i>2){clearInterval(QW.pageGlobal.fbk_timer);QW.pageGlobal.fbk.hide();}
						},1000);
					}else{alert(data.err);W(".fbk_submit").removeClass("loading");}
				});
			});
		});
		//绑定关闭
		W(document).delegate(".fbk_success .close__","click",function(e){
			e.preventDefault(); 
			clearInterval(QW.pageGlobal.fbk_timer);
			QW.pageGlobal.fbk.hide();
		});
		hasbindEvent = true;
	};
});
	
