/**
	用来管理和发送数据到server 
	能够接收
*/
(function(){
	var mix = QW.ObjectH.mix,
		TH = QW.TweetH,
		stringify = QW.ObjectH.stringify,
		encodeURIJson = QW.ObjectH.encodeURIJson,
		CustEvent = QW.CustEvent,
		setStyle = QW.NodeH.setStyle;

	/**
	 * Marmot是一个控制器，她从页面上接收类型为"marmot"的事件消息
	 * 页面上发送的消息为 {msgType:"marmot", id:"id", atction:data}
	 * 并将这些消息分发给她辖下的各个Action进行处理
	 * 同时她拥有决定什么时候将消息发送给后端的权利
	 * 当Marmot的send事件被发起的时候，她将默认信息以及所有已经处于close状态的消息发出
	 * 消息默认走json协议，可以在beforesend中修改格式
	 * 发送消息的格式
	 */
	function Marmot(msgType, opts, metas){
		this.msgType = msgType || "marmot";
		this.server = "server";			//默认的server

		this.metas = metas || {};		//所有要发送的元数据
		//this.metas.Init_time = new Date().getTime();

		this.needFeedback = false;	 //是否需要服务器端响应，如果不需要的话，最简单的构造图片请求的方式发送数据
		//this.asyn = true;		//异步响应模式，收到事件不会立即响应
		this.actions = {};

		mix(this, opts, true);

		CustEvent.createEvents(this, ["beforesend"]);
		TH.receive(this, "marmot", function(evt){
			var id = evt.id;

			if(evt.receiver.actions[id] && !evt.receiver.actions[id].closed){
				TH.tweet(evt.receiver, id, evt); //重新分发
			}
		});
	}

	Marmot.prototype = {
		/**
		 *	开启一个Action，将接收到的消息中所有target为id的消息分发给对应的Action
		 *  
		 */
		open: function(id){
			if(!this.actions[id]){
				this.actions[id] = new MarmotAction(id);
			}
			if(this.actions[id].closed){
				this.actions[id].closed = false;
				return true;
			}
			return false;
		},
		/**
		 * 关闭一个Action，不再向这个Action发送消息
		 * 并且准备好向服务器端发送
		 */
		close: function(id){
			if(!this.actions[id]){
				return false;
			}
			if(!this.actions[id].closed){
				this.actions[id].closed = true;
				return true;
			}
			return false;
		},
		/*
		 * 关闭所有的Action
		 */
		closeAll: function(){
			for(var id in this.actions){
				this.close(id);
			}
		},
		/**
		 * 清除一个Action的所有消息
		 */
		clear: function(id){
			if(this.actions[id]){
				this.actions[id].record.action.length = 0;
				return true;
			}
			return false;
		},
		/**
		 * 发送消息到server
		 * unzip 是否解压action 
		 * 如果unzip，那么会将后面的属性覆盖前面的属性
		 */
		send: function(unzip){
			var data = mix({},this.metas);
			data.Log_time = new Date().getTime();

			for(var id in this.actions){
				var action = this.actions[id];

				if(action.closed){
					if(action.recorder.length > 0) {
						if(!unzip){
							if(action.recorder.length == 1)
								data[action.id] = stringify(action.recorder[0]);
							else
								data[action.id] = stringify(action.recorder);
						}
						else{
							for(var i = 0; i < action.recorder.length; i++){
								mix(data,action.recorder[i],true);
							}
						}
					}
					delete this.actions[id];
				}
			}

			this.fire("beforesend", data);

			var query = this.server + "?" + encodeURIJson(data);
			var img = document.createElement("img");
			setStyle(img, "display", "none");
			img.src = query;
			document.documentElement.appendChild(img);
		},
		/**
		 * 立即发出一个log
		 */
		log: function(id, data, unzip){ 
			this.open(id);
			TH.tweet(this,this.msgType,{id:id,action:data});
			this.close(id);
			this.send(unzip);			
		}
	}

	/**
		用户行为的抽象
		一个MarmotAction记录一种连续的用户行为
		它接收消息并且写入数据
	*/
	function MarmotAction(id){
		this.closed = true;
		this.id = id;

		this.recorder = [];	

		TH.receive(this, id, function(evt){
			if(evt.action)
				evt.receiver.recorder.push(evt.action);
		});
	}
	
	QW.provide("Marmot", Marmot);
})();