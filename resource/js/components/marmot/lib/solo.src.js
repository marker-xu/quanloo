//document.write('<script type="text/javascript" src="' + srcPath + 'core/core_base.js"><\/script>');
(function() {
	var QW = {
		PATH: (function() {
			var sTags = document.getElementsByTagName("script");
			return sTags[sTags.length - 1].src.replace(/\/[^\/]+\/[^\/]+$/, "/");
		}()),
		loadJs: function(url, onsuccess, options) {
			options = options || {};
			var head = document.getElementsByTagName('head')[0] || document.documentElement,
				script = document.createElement('script'),
				done = false;
			script.src = url;
			if (options.charset) {
				script.charset = options.charset;
			}
			script.onerror = script.onload = script.onreadystatechange = function() {
				if (!done && (!this.readyState || this.readyState == "loaded" || this.readyState == "complete")) {
					done = true;
					if (onsuccess) {
						onsuccess();
					}
					script.onerror = script.onload = script.onreadystatechange = null;
					head.removeChild(script);
				}
			};
			head.insertBefore(script, head.firstChild);
		},
		error: function(obj, type) {
			type = type || Error;
			throw new type(obj);
		}
	};
	window.QW = QW;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'core/module.h.js"><\/script>');
(function() {
	var modules = {},
		loadJs = QW.loadJs,
		loadingModules = [],
		callbacks = [];
		isLoading = false;
	function mix(des, src, override) {
		for (var i in src) {
			if (override || !(i in des)) {
				des[i] = src[i];
			}
		}
		return des;
	}
	function isPlainObject(obj) {
		return !!obj && obj.constructor == Object;
	}
	function execCallback() {
		for (var i=0; i<callbacks.length; i++) {
			var callback = callbacks[i].callback,
				moduleNames =  callbacks[i].moduleNames.split(/\s*,\s*/g),
				isOk = true;
			for (var j = 0; j<moduleNames.length; j++) {
				var module = modules[moduleNames[j]];
				if (module.loadStatus != 2 && !(module.loadedChecker && module.loadedChecker())) {
					isOk = false;
					break;
				}
			}
			if(isOk){
				callback();
				callbacks.splice(i,1);
				i--;
			}
		}
	}
	function loadsJsInOrder() {
		//浏览器不能保证动态添加的ScriptElement会按顺序执行，所以人为来保证一下
		//参见：http://www.stevesouders.com/blog/2009/04/27/loading-scripts-without-blocking/
		//测试帮助：http://1.cuzillion.com/bin/resource.cgi?type=js&sleep=3&jsdelay=0&n=1&t=1294649352
		//todo: 目前没有充分利用部分浏览器的并行下载功能，可以改进。
		//todo: 如果服务器端能combo，则可修改以下内容以适应。
		var moduleI = loadingModules[0];
		function loadedDone() {
			moduleI.loadStatus = 2;
			execCallback();
			isLoading = false;
			loadsJsInOrder();
		}
		if (!isLoading && moduleI) {
			//alert(moduleI.url);
			isLoading = true;
			loadingModules.splice(0, 1);
			var checker = moduleI.loadedChecker;
			if (checker && checker()) { //如果有loaderChecker，则用loaderChecker判断一下是否已经加载过
				loadedDone();
			} else {
				loadJs(moduleI.url.replace(/^\/\//, QW.PATH), loadedDone);
			}
		}
	}
	var ModuleH = {
		provideDomains: [QW],
		provide: function(moduleName, value) {
			if (typeof moduleName == 'string') {
				var domains = ModuleH.provideDomains;
				for (var i = 0; i < domains.length; i++) {
					if (!domains[i][moduleName]) {domains[i][moduleName] = value; }
				}
			} else if (isPlainObject(moduleName)) {
				for (i in moduleName) {
					ModuleH.provide(i, moduleName[i]);
				}
			}
		},
		addConfig: function(moduleName, details) {
			if (typeof moduleName == 'string') {
				var json = mix({}, details);
				json.moduleName = moduleName;
				modules[moduleName] = json;
			} else if (isPlainObject(moduleName)) {
				for (var i in moduleName) {
					ModuleH.addConfig(i, moduleName[i]);
				}
			}
		},
		use: function(moduleName, callback) {
			var modulesJson = {},//需要加载的模块Json（用json效率快）
				modulesArray = [],//需要加载的模块Array（用array来排序）
				names = moduleName.split(/\s*,\s*/g),
				i,
				j,
				k,
				len,
				moduleI;
			while (names.length) { //收集需要排队的模块到modulesJson
				var names2 = {};
				for (i = 0; i < names.length; i++) {
					var nameI = names[i];
					if (!nameI || QW[nameI]) {//如果已被预加载，也会忽略
						continue;
					}
					if (!modulesJson[nameI]) { //还没进行收集
						if (!modules[nameI]) { //还没进行config
							throw 'Unknown module: ' + nameI;
						}
						if (modules[nameI].loadStatus != 2) { //还没被加载过  loadStatus:1:加载中、2:已加载
							var checker = modules[nameI].loadedChecker;
							if (checker && checker()) { //如果有loaderChecker，则用loaderChecker判断一下是否已经加载过
								continue;
							}
							modulesJson[nameI] = modules[nameI]; //加入队列。
						}
						var refs = ['requires', 'use'];
						for (j = 0; j < refs.length; j++) { //收集附带需要加载的模块
							var sRef = modules[nameI][refs[j]];
							if (sRef) {
								var refNames = sRef.split(',');
								for (k = 0; k < refNames.length; k++) {names2[refNames[k]] = 0; }
							}
						}
					}
				}
				names = [];
				for (i in names2) {
					names.push(i);
				}
			}
			for (i in modulesJson) { //转化成加载数组
				modulesArray.push(modulesJson[i]);
			}
			for (i = 0, len = modulesArray.length; i < len; i++) { //排序。 本排序法节约代码，但牺了性能
				if (!modulesArray[i].requires) {
					continue;
				}
				for (j = i + 1; j < len; j++) {
					if (new RegExp('(^|,)' + modulesArray[j].moduleName + '(,|$)').test(modulesArray[i].requires)) {
						//如果发现前面的模块requires后面的模块，则将被required的模块移到前面来，并重新查它在新位置是否合适
						var moduleJ = modulesArray[j];
						modulesArray.splice(j, 1);
						modulesArray.splice(i, 0, moduleJ);
						i--;
						break;
					}
				}
			}
			var loadIdx = -1,
				//需要加载并且未加载的最后一个模块的index
				loadingIdx = -1; //需要加载并且正在加载的最后一个模块的index
			for (i = 0; i < modulesArray.length; i++) {
				moduleI = modulesArray[i];
				if (!moduleI.loadStatus && (new RegExp('(^|,)' + moduleI.moduleName + '(,|$)').test(moduleName))) {
					loadIdx = i;
				}
				if (moduleI.loadStatus == 1 && (new RegExp('(^|,)' + moduleI.moduleName + '(,|$)').test(moduleName))) {
					loadingIdx = i;
				}
			}
			if (loadIdx != -1 || loadingIdx != -1) { //还有未开始加载的，或还有正在加载的
				callbacks.push({
					callback: callback,
					moduleNames: moduleName
				});
			} else {
				callback();
				return;
			}
			for (i = 0; i < modulesArray.length; i++) {
				moduleI = modulesArray[i];
				if (!moduleI.loadStatus) { //需要load的js。todo: 模块combo加载
					moduleI.loadStatus = 1;
					loadingModules.push(moduleI);
				}
			}
			loadsJsInOrder();
		}
	};
	QW.ModuleH = ModuleH;
	QW.use = ModuleH.use;
	QW.provide = ModuleH.provide;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'core/browser.js"><\/script>');
QW.Browser = (function() {
	var na = window.navigator,
		ua = na.userAgent.toLowerCase(),
		browserTester = /(msie|webkit|gecko|presto|opera|safari|firefox|chrome|maxthon|android|ipad|iphone|webos|hpwos)[ \/os]*([\d_.]+)/ig,
		Browser = {
			platform: na.platform
		};
	ua.replace(browserTester, function(a, b, c) {
		var bLower = b.toLowerCase();
		if (!Browser[bLower]) {
			Browser[bLower] = c;
		}
	});
	if (Browser.opera) { //Opera9.8后版本号位置变化
		ua.replace(/opera.*version\/([\d.]+)/, function(a, b) {
			Browser.opera = b;
		});
	}
	if (Browser.msie) {
		Browser.ie = Browser.msie;
		var v = parseInt(Browser.msie, 10);
		Browser['ie' + v] = true;
	}
	return Browser;
}());
if (QW.Browser.ie) {
	try {
		document.execCommand("BackgroundImageCache", false, true);
	} catch (e) {}
};
//document.write('<script type="text/javascript" src="' + srcPath + 'core/string.h.js"><\/script>');
(function() {
	var StringH = {
		trim: function(s) {
			return s.replace(/^[\s\xa0\u3000]+|[\u3000\xa0\s]+$/g, "");
		},
		mulReplace: function(s, arr) {
			for (var i = 0; i < arr.length; i++) {
				s = s.replace(arr[i][0], arr[i][1]);
			}
			return s;
		},
		camelize: function(s) {
			return s.replace(/\-(\w)/ig, function(a, b) {
				return b.toUpperCase();
			});
		},
		encode4Js: function(s) {
			return StringH.mulReplace(s, [
				[/\\/g, "\\u005C"],
				[/"/g, "\\u0022"],
				[/'/g, "\\u0027"],
				[/\//g, "\\u002F"],
				[/\r/g, "\\u000A"],
				[/\n/g, "\\u000D"],
				[/\t/g, "\\u0009"]
			]);
		},
		evalExp: function(s, opts) {
			return new Function("opts", "return (" + s + ");")(opts);
		}
	};
	QW.StringH = StringH;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'core/object.h.js"><\/script>');
(function() {
	var encode4Js = QW.StringH.encode4Js;
	function getConstructorName(o) {
		return o != null && Object.prototype.toString.call(o).slice(8, -1);
	}
	var ObjectH = {
		isString: function(obj) {
			return getConstructorName(obj) == 'String';
		},
		isArray: function(obj) {
			return getConstructorName(obj) == 'Array';
		},
		isPlainObject: function(obj) {
			return !!obj && obj.constructor === Object;
		},
		isWrap: function(obj, coreName) {
			return !!(obj && obj[coreName || 'core']);
		},
		mix: function(des, src, override) {
			if (ObjectH.isArray(src)) {
				for (var i = 0, len = src.length; i < len; i++) {
					ObjectH.mix(des, src[i], override);
				}
				return des;
			}
			for (i in src) {
				if (override || !(des[i] || (i in des))) {
					des[i] = src[i];
				}
			}
			return des;
		},
		create: function(proto, props) {
			var ctor = function(ps) {
				if (ps) {
					ObjectH.mix(this, ps, true);
				}
			};
			ctor.prototype = proto;
			return new ctor(props);
		},
		stringify: function(obj) {
			if (obj == null) {return null; }
			if (obj.toJSON) {
				obj = obj.toJSON();
			}
			var type = typeof obj;
			switch (type) {
			case 'string':
				return '"' + encode4Js(obj) + '"';
			case 'number':
			case 'boolean':
				return obj.toString();
			case 'object':
				if (obj instanceof Date) {return 'new Date(' + obj.getTime() + ')'; }
				if (obj instanceof Array) {
					var ar = [];
					for (var i = 0; i < obj.length; i++) {ar[i] = ObjectH.stringify(obj[i]); }
					return '[' + ar.join(',') + ']';
				}
				if (ObjectH.isPlainObject(obj)) {
					ar = [];
					for (i in obj) {
						ar.push('"' + encode4Js(i) + '":' + ObjectH.stringify(obj[i]));
					}
					return '{' + ar.join(',') + '}';
				}
			}
			return null; //无法序列化的，返回null;
		},
		encodeURIJson: function(json){
			var s = [];
			for( var p in json ){
				if(json[p]==null) continue;
				if(json[p] instanceof Array)
				{
					for (var i=0;i<json[p].length;i++) s.push( encodeURIComponent(p) + '=' + encodeURIComponent(json[p][i]));
				}
				else
					s.push( encodeURIComponent(p) + '=' + encodeURIComponent(json[p]));
			}
			return s.join('&');
		}
	};
	QW.ObjectH = ObjectH;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'core/array.h.js"><\/script>');
(function() {
	var ArrayH = {
		forEach: function(arr, callback, pThis) {
			for (var i = 0, len = arr.length; i < len; i++) {
				if (i in arr) {
					callback.call(pThis, arr[i], i, arr);
				}
			}
		},
		filter: function(arr, callback, pThis) {
			var rlt = [];
			for (var i = 0, len = arr.length; i < len; i++) {
				if ((i in arr) && callback.call(pThis, arr[i], i, arr)) {
					rlt.push(arr[i]);
				}
			}
			return rlt;
		},
		indexOf: function(arr, obj, fromIdx) {
			var len = arr.length;
			fromIdx |= 0; //取整
			if (fromIdx < 0) {
				fromIdx += len;
			}
			if (fromIdx < 0) {
				fromIdx = 0;
			}
			for (; fromIdx < len; fromIdx++) {
				if (fromIdx in arr && arr[fromIdx] === obj) {
					return fromIdx;
				}
			}
			return -1;
		}
	};
	QW.ArrayH = ArrayH;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'core/function.h.js"><\/script>');
(function() {
	var FunctionH = {
		methodize: function(func, attr) {
			if (attr) {
				return function() {
					return func.apply(null, [this[attr]].concat([].slice.call(arguments)));
				};
			}
			return function() {
				return func.apply(null, [this].concat([].slice.call(arguments)));
			};
		},
		mul: function(func, opt) {
			var getFirst = opt == 1,
				joinLists = opt == 2;
			if (getFirst) {
				return function() {
					var list = arguments[0];
					if (!(list instanceof Array)) {
						return func.apply(this, arguments);
					}
					if (list.length) {
						var args = [].slice.call(arguments, 0);
						args[0] = list[0];
						return func.apply(this, args);
					}
				};
			}
			return function() {
				var list = arguments[0];
				if (list instanceof Array) {
					var moreArgs = [].slice.call(arguments, 0),
						ret = [],
						i = 0,
						len = list.length,
						r;
					for (; i < len; i++) {
						moreArgs[0] = list[i];
						r = func.apply(this, moreArgs);
						if (joinLists) {
							if (r != null) {
								ret = ret.concat(r);
							}
						} else {
							ret.push(r);
						}
					}
					return ret;
				} else {
					return func.apply(this, arguments);
				}
			};
		},
		rwrap: function(func, wrapper, idx) {
			idx |= 0;
			return function() {
				var ret = func.apply(this, arguments);
				if (idx >= 0) {
					ret = arguments[idx];
				}
				return wrapper ? new wrapper(ret) : ret;
			};
		}
	};
	QW.FunctionH = FunctionH;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'core/helper.h.js"><\/script>');
(function() {
	var FunctionH = QW.FunctionH,
		create = QW.ObjectH.create,
		Methodized = function() {};
	var HelperH = {
		rwrap: function(helper, wrapper, wrapConfig) {
			var ret = create(helper);
			wrapConfig = wrapConfig || 'operator';
			for (var i in helper) {
				var wrapType = wrapConfig,
					fn = helper[i];
				if(fn instanceof Function){
					if (typeof wrapType != 'string') {
						wrapType = wrapConfig[i] || '';
					}
					if ('queryer' == wrapType) { //如果方法返回查询结果，对返回值进行包装
						ret[i] = FunctionH.rwrap(fn, wrapper, -1);
					} else if ('operator' == wrapType || 'methodized' == wrapType) { //如果方法只是执行一个操作
						if (helper instanceof Methodized || 'methodized' == wrapType) { //如果是methodized后的,对this直接返回
							ret[i] = (function(fn) {
								return function() {
									fn.apply(this, arguments);
									return this;
								};
							}(fn));
						} else {
							ret[i] = FunctionH.rwrap(fn, wrapper, 0); //否则对第一个参数进行包装，针对getter系列
						}
					}
				}else{
					ret[i] = fn;
				}
			}
			return ret;
		},
		gsetter: function(helper, gsetterConfig) {
			var ret = create(helper);
			gsetterConfig = gsetterConfig || {};
			for (var i in gsetterConfig) {
				if (helper instanceof Methodized) {
					ret[i] = (function(config) {
						return function() {
							return ret[config[Math.min(arguments.length, config.length - 1)]].apply(this, arguments);
						};
					}(gsetterConfig[i]));
				} else {
					ret[i] = (function(config) {
						return function() {
							return ret[config[Math.min(arguments.length, config.length) - 1]].apply(null, arguments);
						};
					}(gsetterConfig[i]));
				}
			}
			return ret;
		},
		mul: function(helper, mulConfig) {
			var ret = create(helper);
			mulConfig = mulConfig || {};
			for (var i in helper) {
				var fn = helper[i];
				if (fn instanceof Function) {
					var mulType = mulConfig;
					if (typeof mulType != 'string') {
						mulType = mulConfig[i] || '';
					}
					if ("getter" == mulType || "getter_first" == mulType || "getter_first_all" == mulType) {
						//如果是配置成gettter||getter_first||getter_first_all，那么需要用第一个参数
						ret[i] = FunctionH.mul(fn, 1);
					} else if ("getter_all" == mulType) {
						ret[i] = FunctionH.mul(fn, 0);
					} else {
						ret[i] = FunctionH.mul(fn, 2); //operator、queryer的话需要join返回值，把返回值join起来的说
					}
					if ("getter" == mulType || "getter_first_all" == mulType) {
						//如果配置成getter||getter_first_all，那么还会生成一个带All后缀的方法
						ret[i + "All"] = FunctionH.mul(fn, 0);
					}
				}else{
					ret[i] = fn;
				}
			}
			return ret;
		},
		methodize: function(helper, attr) {
			var ret = new Methodized(); //因为 methodize 之后gsetter和rwrap的行为不一样
			for (var i in helper) {
				var fn = helper[i];
				if (fn instanceof Function) {
					ret[i] = FunctionH.methodize(fn, attr);
				}else{
					ret[i] = fn;
				}
			}
			return ret;
		}
	};
	QW.HelperH = HelperH;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'core/custevent.js"><\/script>');
(function() {
	var mix = QW.ObjectH.mix,
		indexOf = QW.ArrayH.indexOf;
	//----------QW.CustEvent----------
	var CustEvent = function(target, type, eventArgs) {
		this.target = target;
		this.type = type;
		mix(this, eventArgs || {}, true); //支持自定义类型的覆盖
	};
	mix(CustEvent.prototype, {
		target: null,
		currentTarget: null,
		type: null,
		returnValue: undefined,
		preventDefault: function() {
			this.returnValue = false;
		}
	});
	var CustEventTargetH = {
		on: function(target, sEvent, fn) {
			var cbs = (target.__custListeners && target.__custListeners[sEvent]) || QW.error("unknown event type", TypeError);
			if (indexOf(cbs, fn) > -1) {
				return false;
			}
			cbs.push(fn);
			return true;
		},
		un: function(target, sEvent, fn) {
			var cbs = (target.__custListeners && target.__custListeners[sEvent]) || QW.error("unknown event type", TypeError);
			if (fn) {
				var idx = indexOf(cbs, fn);
				if (idx < 0) {
					return false;
				}
				cbs.splice(idx, 1);
			} else {
				cbs.length = 0;
			}
			return true;
		},
		fire: function(target, sEvent, eventArgs) {
			if (sEvent instanceof CustEvent) {
				var custEvent = mix(sEvent, eventArgs, true);
				sEvent = sEvent.type;
			} else {
				custEvent = new CustEvent(target, sEvent, eventArgs);
			}
			var cbs = (target.__custListeners && target.__custListeners[sEvent]) || QW.error("unknown event type", TypeError);
			if (sEvent != "*") {
				cbs = cbs.concat(target.__custListeners["*"] || []);
			}
			custEvent.returnValue = undefined; //去掉本句，会导致静态CustEvent的returnValue向后污染
			custEvent.currentTarget = target;
			var obj = custEvent.currentTarget;
			if (obj && obj['on' + custEvent.type]) {
				var retDef = obj['on' + custEvent.type].call(obj, custEvent); //对于独占模式的返回值，会弱影响event.returnValue
			}
			for (var i = 0; i < cbs.length; i++) {
				cbs[i].call(obj, custEvent);
			}
			return custEvent.returnValue !== false || (retDef === false && custEvent.returnValue === undefined);
		},
		createEvents: function(target, types) {
			types = types || [];
			if (typeof types == "string") {
				types = types.split(",");
			}
			var listeners = target.__custListeners;
			if (!listeners) {
				listeners = target.__custListeners = {};
			}
			for (var i = 0; i < types.length; i++) {
				listeners[types[i]] = listeners[types[i]] || []; //可以重复create，而不影响之前的listerners.
			}
			listeners['*'] = listeners["*"] || [];
			return target;
		}
	};
	var CustEventTarget = function() {
		this.__custListeners = {};
	};
	var methodized = QW.HelperH.methodize(CustEventTargetH);
	mix(CustEventTarget.prototype, methodized);
	CustEvent.createEvents = function(target, types) {
		CustEventTargetH.createEvents(target, types);
		return mix(target, methodized);//尊重对象本身的on。
	};
	QW.CustEvent = CustEvent;
	QW.CustEventTargetH = CustEventTargetH;
	QW.CustEventTarget = CustEventTarget;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'dom/selector.js"><\/script>');
(function() {
	var trim = QW.StringH.trim,
		encode4Js = QW.StringH.encode4Js;
	var Selector = {
		queryStamp: 0,
		_operators: { //以下表达式，aa表示attr值，vv表示比较的值
			'': 'aa',
			//isTrue|hasValue
			'=': 'aa=="vv"',
			//equal
			'!=': 'aa!="vv"',
			//unequal
			'~=': 'aa&&(" "+aa+" ").indexOf(" vv ")>-1',
			//onePart
			'|=': 'aa&&(aa+"-").indexOf("vv-")==0',
			//firstPart
			'^=': 'aa&&aa.indexOf("vv")==0',
			// beginWith
			'$=': 'aa&&aa.lastIndexOf("vv")==aa.length-"vv".length',
			// endWith
			'*=': 'aa&&aa.indexOf("vv")>-1' //contains
		},
		_pseudos: {
			"first-child": function(a) {
				return !(a = a.previousSibling) || !a.tagName && !a.previousSibling;
			},
			"last-child": function(a) {
				return !(a = a.nextSibling) || !a.tagName && !a.nextSibling;
			},
			"only-child": function(a) {
				var el;
				return !((el = a.previousSibling) && (el.tagName || el.previousSibling) || (el = a.nextSibling) && (el.tagName || el.nextSibling));
			},
			"nth-child": function(a, nth) {
				return checkNth(a, nth);
			},
			"nth-last-child": function(a, nth) {
				return checkNth(a, nth, true);
			},
			"first-of-type": function(a) {
				var tag = a.tagName;
				var el = a;
				while (el = el.previousSlibling) {
					if (el.tagName == tag) return false;
				}
				return true;
			},
			"last-of-type": function(a) {
				var tag = a.tagName;
				var el = a;
				while (el = el.nextSibling) {
					if (el.tagName == tag) return false;
				}
				return true;
			},
			"only-of-type": function(a) {
				var els = a.parentNode.childNodes;
				for (var i = els.length - 1; i > -1; i--) {
					if (els[i].tagName == a.tagName && els[i] != a) return false;
				}
				return true;
			},
			"nth-of-type": function(a, nth) {
				var idx = 1;
				var el = a;
				while (el = el.previousSibling) {
					if (el.tagName == a.tagName) idx++;
				}
				return checkNth(idx, nth);
			},
			//JK：懒得为这两个伪类作性能优化
			"nth-last-of-type": function(a, nth) {
				var idx = 1;
				var el = a;
				while (el = el.nextSibling) {
					if (el.tagName == a.tagName) idx++;
				}
				return checkNth(idx, nth);
			},
			//JK：懒得为这两个伪类作性能优化
			"empty": function(a) {
				return !a.firstChild;
			},
			"parent": function(a) {
				return !!a.firstChild;
			},
			"not": function(a, sSelector) {
				return !s2f(sSelector)(a);
			},
			"enabled": function(a) {
				return !a.disabled;
			},
			"disabled": function(a) {
				return a.disabled;
			},
			"checked": function(a) {
				return a.checked;
			},
			"focus": function(a) {
				return a == a.ownerDocument.activeElement;
			},
			"indeterminate": function(a) {
				return a.indeterminate;
			},
			"input": function(a) {
				return /input|select|textarea|button/i.test(a.nodeName);
			},
			"contains": function(a, s) {
				return (a.textContent || a.innerText || "").indexOf(s) >= 0;
			}
		},
		_attrGetters: (function() {
			var o = {
				'class': 'el.className',
				'for': 'el.htmlFor',
				'href': 'el.getAttribute("href",2)'
			};
			var attrs = 'name,id,className,value,selected,checked,disabled,type,tagName,readOnly,offsetWidth,offsetHeight,innerHTML'.split(',');
			for (var i = 0, a; a = attrs[i]; i++) o[a] = "el." + a;
			return o;
		}()),
		_relations: {
			//寻祖
			"": function(el, filter, topEl) {
				while ((el = el.parentNode) && el != topEl) {
					if (filter(el)) return el;
				}
				return null;
			},
			//寻父
			">": function(el, filter, topEl) {
				el = el.parentNode;
				return el != topEl && filter(el) ? el : null;
			},
			//寻最小的哥哥
			"+": function(el, filter, topEl) {
				while (el = el.previousSibling) {
					if (el.tagName) {
						return filter(el) && el;
					}
				}
				return null;
			},
			//寻所有的哥哥
			"~": function(el, filter, topEl) {
				while (el = el.previousSibling) {
					if (el.tagName && filter(el)) {
						return el;
					}
				}
				return null;
			}
		},
		selector2Filter: function(sSelector) {
			return s2f(sSelector);
		},
		test: function(el, sSelector) {
			return s2f(sSelector)(el);
		},
		filter: function(els, sSelector, pEl) {
			pEl = pEl || document,
				groups = trim(sSelector).split(",");
			if (groups.length < 2) {
				return filterByRelation(pEl || document, els, splitSelector(sSelector));
			}
			else {//如果有逗号关系符，则满足其中一个selector就通过筛选。以下代码，需要考虑：“尊重els的原顺序”。
				var filteredEls = filterByRelation(pEl || document, els, splitSelector(groups[0]));
				if (filteredEls.length == els.length) { //如果第一个过滤筛完，则直接返回
					return filteredEls;
				}
				for(var j = 0, el; el = els[j++];){
					el.__QWSltFlted=0;
				}
				for(j = 0, el; el = filteredEls[j++];){
					el.__QWSltFlted=1;
				}
				var leftEls = els,
					tempLeftEls;
				for(var i=1;i<groups.length;i++){
					tempLeftEls = [];
					for(j = 0, el; el = leftEls[j++];){
						if(!el.__QWSltFlted) tempLeftEls.push(el);
					}
					leftEls = tempLeftEls;
					filteredEls = filterByRelation(pEl || document, leftEls, splitSelector(groups[i]));
					for(j = 0, el; el = filteredEls[j++];){
						el.__QWSltFlted=1;
					}
				}
				var ret=[];
				for(j = 0, el; el = els[j++];){
					if(el.__QWSltFlted) ret.push(el);
				}
				return ret;
			}
		},
		query: function(refEl, sSelector) {
			Selector.queryStamp = queryStamp++;
			refEl = refEl || document;
			var els = nativeQuery(refEl, sSelector);
			if (els) return els; //优先使用原生的
			var groups = trim(sSelector).split(",");
			els = querySimple(refEl, groups[0]);
			for (var i = 1, gI; gI = groups[i]; i++) {
				var els2 = querySimple(refEl, gI);
				els = els.concat(els2);
				//els=union(els,els2);//除重有负作用，例如效率或污染，放弃除重
			}
			return els;
		},
		one: function(refEl, sSelector) {
			var els = Selector.query(refEl, sSelector);
			return els[0];
		}
	};
	window.__SltPsds = Selector._pseudos; //JK 2010-11-11：为提高效率
	function retTrue() {
		return true;
	}
	function arrFilter(arr, callback) {
		var rlt = [],
			i = 0;
		if (callback == retTrue) {
			if (arr instanceof Array) {
				return arr.slice(0);
			} else {
				for (var len = arr.length; i < len; i++) {
					rlt[i] = arr[i];
				}
			}
		} else {
			for (var oI; oI = arr[i++];) {
				callback(oI) && rlt.push(oI);
			}
		}
		return rlt;
	}
	var elContains,
		hasNativeQuery;
	function getChildren(pEl) { //需要剔除textNode与“<!--xx-->”节点
		var els = pEl.children || pEl.childNodes,
			len = els.length,
			ret = [],
			i = 0;
		for (; i < len; i++) if (els[i].nodeType == 1) ret.push(els[i]);
		return ret;
	}
	function findId(id) {
		return document.getElementById(id);
	}
	(function() {
		var div = document.createElement('div');
		div.innerHTML = '<div class="aaa"></div>';
		hasNativeQuery = (div.querySelectorAll && div.querySelectorAll('.aaa').length == 1); //部分浏览器不支持原生querySelectorAll()，例如IE8-
		elContains = div.contains ?
			function(pEl, el) {
				return pEl != el && pEl.contains(el);
			} : function(pEl, el) {
				return (pEl.compareDocumentPosition(el) & 16);
			};
	}());
	function checkNth(el, nth, reverse) {
		if (nth == 'n') {return true; }
		if (typeof el == 'number') {
			var idx = el;
		} else {
			var pEl = el.parentNode;
			if (pEl.__queryStamp != queryStamp) {
				var nEl = {nextSibling: pEl.firstChild},
					n = 1;
				while (nEl = nEl.nextSibling) {
					if (nEl.nodeType == 1) nEl.__siblingIdx = n++;
				}
				pEl.__queryStamp = queryStamp;
				pEl.__childrenNum = n - 1;
			}
			if (reverse) idx = pEl.__childrenNum - el.__siblingIdx + 1;
			else idx = el.__siblingIdx;
		}
		switch (nth) {
		case 'even':
		case '2n':
			return idx % 2 == 0;
		case 'odd':
		case '2n+1':
			return idx % 2 == 1;
		default:
			if (!(/n/.test(nth))) return idx == nth;
			var arr = nth.replace(/(^|\D+)n/g, "$11n").split("n"),
				k = arr[0] | 0,
				kn = idx - arr[1] | 0;
			return k * kn >= 0 && kn % k == 0;
		}
	}
	var filterCache = {};
	function s2f(sSelector, isForArray) {
		if (!isForArray && filterCache[sSelector]) return filterCache[sSelector];
		var pseudos = [],
			//伪类数组,每一个元素都是数组，依次为：伪类名／伪类值
			s = trim(sSelector),
			reg = /\[\s*((?:[\w\u00c0-\uFFFF-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\3|)\s*\]/g,
			//属性选择表达式解析,thanks JQuery
			sFun = [];
		s = s.replace(/\:([\w\-]+)(\(([^)]+)\))?/g,  //伪类
			function(a, b, c, d, e) {
				pseudos.push([b, d]);
				return "";
			}).replace(/^\*/g,
			function(a) { //任意tagName缩略写法
				sFun.push('el.nodeType==1');
				return '';
			}).replace(/^([\w\-]+)/g,//tagName缩略写法
			function(a) {
				sFun.push('(el.tagName||"").toUpperCase()=="' + a.toUpperCase() + '"');
				return '';
			}).replace(/([\[(].*)|#([\w\-]+)|\.([\w\-]+)/g,//id缩略写法//className缩略写法
			function(a, b, c, d) {
				return b || c && '[id="' + c + '"]' || d && '[className~="' + d + '"]';
			}).replace(reg, //普通写法[foo][foo=""][foo~=""]等
			function(a, b, c, d, e) {
				var attrGetter = Selector._attrGetters[b] || 'el.getAttribute("' + b + '")';
				sFun.push(Selector._operators[c || ''].replace(/aa/g, attrGetter).replace(/vv/g, e || ''));
				return '';
			});
		if (!(/^\s*$/).test(s)) {
			throw "Unsupported Selector:\n" + sSelector + "\n-" + s;
		}
		for (var i = 0, pI; pI = pseudos[i]; i++) { //伪类过滤
			if (!Selector._pseudos[pI[0]]) throw "Unsupported Selector:\n" + pI[0] + "\n" + s;
			if (/^(nth-|not|contains)/.test(pI[0])) {
				sFun.push('__SltPsds["' + pI[0] + '"](el,"' + encode4Js(pI[1]) + '")');
			} else {
				sFun.push('__SltPsds["' + pI[0] + '"](el)');
			}
		}
		if (sFun.length) {
			if (isForArray) {
				return new Function('els', 'var els2=[];for(var i=0,el;el=els[i++];){if(' + sFun.join('&&') + ') els2.push(el);} return els2;');
			} else {
				return (filterCache[sSelector] = new Function('el', 'return ' + sFun.join('&&') + ';'));
			}
		} else {
			if (isForArray) {
				return function(els) {
					return arrFilter(els, retTrue);
				};
			} else {
				return (filterCache[sSelector] = retTrue);
			}
		}
	}
	var queryStamp = 0,
		nativeQueryStamp = 0,
		querySimpleStamp = 0;
	function nativeQuery(refEl, sSelector) {
		if (hasNativeQuery && /^((^|,)\s*[.\w-][.\w\s\->+~]*)+$/.test(sSelector)) {
			//如果浏览器自带有querySelectorAll，并且本次query的是简单selector，则直接调用selector以加速
			//部分浏览器不支持以">~+"开始的关系运算符
			var oldId = refEl.id,
				tempId,
				arr = [],
				els;
			if (!oldId && refEl.parentNode) { //标准的querySelectorAll中的selector是相对于:root的，而不是相对于:scope的
				tempId = refEl.id = '__QW_slt_' + nativeQueryStamp++;
				try {
					els = refEl.querySelectorAll('#' + tempId + ' ' + sSelector);
				} finally {
					refEl.removeAttribute('id');
				}
			}
			else{
				els = refEl.querySelectorAll(sSelector);
			}
			for (var i = 0, elI; elI = els[i++];) arr.push(elI);
			return arr;
		}
		return null;
	}
	function querySimple(pEl, sSelector) {
		querySimpleStamp++;
		//最优先：原生查询
		var els = nativeQuery(pEl, sSelector);
		if (els) return els; //优先使用原生的
		var sltors = splitSelector(sSelector),
			pEls = [pEl],
			i,
			elI,
			pElI;
		var sltor0;
		//次优先：在' '、'>'关系符出现前，优先正向（从上到下）查询
		while (sltor0 = sltors[0]) {
			if (!pEls.length) return [];
			var relation = sltor0[0];
			els = [];
			if (relation == '+') { //第一个弟弟
				filter = s2f(sltor0[1]);
				for (i = 0; elI = pEls[i++];) {
					while (elI = elI.nextSibling) {
						if (elI.tagName) {
							if (filter(elI)) els.push(elI);
							break;
						}
					}
				}
				pEls = els;
				sltors.splice(0, 1);
			} else if (relation == '~') { //所有的弟弟
				filter = s2f(sltor0[1]);
				for (i = 0; elI = pEls[i++];) {
					if (i > 1 && elI.parentNode == pEls[i - 2].parentNode) continue; //除重：如果已经query过兄长，则不必query弟弟
					while (elI = elI.nextSibling) {
						if (elI.tagName) {
							if (filter(elI)) els.push(elI);
						}
					}
				}
				pEls = els;
				sltors.splice(0, 1);
			} else {
				break;
			}
		}
		var sltorsLen = sltors.length;
		if (!sltorsLen || !pEls.length) return pEls;
		//次优先：idIdx查询
		for (var idIdx = 0, id; sltor = sltors[idIdx]; idIdx++) {
			if ((/^[.\w-]*#([\w-]+)/i).test(sltor[1])) {
				id = RegExp.$1;
				sltor[1] = sltor[1].replace('#' + id, '');
				break;
			}
		}
		if (idIdx < sltorsLen) { //存在id
			var idEl = findId(id);
			if (!idEl) return [];
			for (i = 0, pElI; pElI = pEls[i++];) {
				if (!pElI.parentNode || elContains(pElI, idEl)) {
					els = filterByRelation(pElI, [idEl], sltors.slice(0, idIdx + 1));
					if (!els.length || idIdx == sltorsLen - 1) return els;
					return querySimple(idEl, sltors.slice(idIdx + 1).join(',').replace(/,/g, ' '));
				}
			}
			return [];
		}
		//---------------
		var getChildrenFun = function(pEl) {
			return pEl.getElementsByTagName(tagName);
		},
			tagName = '*',
			className = '';
		sSelector = sltors[sltorsLen - 1][1];
		sSelector = sSelector.replace(/^[\w\-]+/, function(a) {
			tagName = a;
			return "";
		});
		if (hasNativeQuery) {
			sSelector = sSelector.replace(/^[\w\*]*\.([\w\-]+)/, function(a, b) {
				className = b;
				return "";
			});
		}
		if (className) {
			getChildrenFun = function(pEl) {
				return pEl.querySelectorAll(tagName + '.' + className);
			};
		}
		//次优先：只剩一个'>'或' '关系符(结合前面的代码，这时不可能出现还只剩'+'或'~'关系符)
		if (sltorsLen == 1) {
			if (sltors[0][0] == '>') {
				getChildrenFun = getChildren;
				var filter = s2f(sltors[0][1], true);
			} else {
				filter = s2f(sSelector, true);
			}
			els = [];
			for (i = 0; pElI = pEls[i++];) {
				els = els.concat(filter(getChildrenFun(pElI)));
			}
			return els;
		}
		//走第一个关系符是'>'或' '的万能方案
		sltors[sltors.length - 1][1] = sSelector;
		els = [];
		for (i = 0; pElI = pEls[i++];) {
			els = els.concat(filterByRelation(pElI, getChildrenFun(pElI), sltors));
		}
		return els;
	}
	function splitSelector(sSelector) {
		var sltors = [];
		var reg = /(^|\s*[>+~ ]\s*)(([\w\-\:.#*]+|\([^\)]*\)|\[\s*((?:[\w\u00c0-\uFFFF-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\6|)\s*\])+)(?=($|\s*[>+~ ]\s*))/g;
		var s = trim(sSelector).replace(reg, function(a, b, c, d) {
			sltors.push([trim(b), c]);
			return "";
		});
		if (!(/^\s*$/).test(s)) {
			throw "Unsupported Selector:\n" + sSelector + "\n--" + s;
		}
		return sltors;
	}
	function filterByRelation(pEl, els, sltors) {
		var sltor = sltors[0],
			len = sltors.length,
			needNotTopJudge = !sltor[0],
			filters = [],
			relations = [],
			needNext = [],
			relationsStr = '';
		for (var i = 0; i < len; i++) {
			sltor = sltors[i];
			filters[i] = s2f(sltor[1], i == len - 1); //过滤
			relations[i] = Selector._relations[sltor[0]]; //寻亲函数
			if (sltor[0] == '' || sltor[0] == '~') needNext[i] = true; //是否递归寻亲
			relationsStr += sltor[0] || ' ';
		}
		els = filters[len - 1](els); //自身过滤
		if (relationsStr == ' ') return els;
		if (/[+>~] |[+]~/.test(relationsStr)) { //需要回溯
			//alert(1); //用到这个分支的可能性很小。放弃效率的追求。
			function chkRelation(el) { //关系人过滤
				var parties = [],
					//中间关系人
					j = len - 1,
					party = parties[j] = el;
				for (; j > -1; j--) {
					if (j > 0) { //非最后一步的情况
						party = relations[j](party, filters[j - 1], pEl);
					} else if (needNotTopJudge || party.parentNode == pEl) { //最后一步通过判断
						return true;
					} else { //最后一步未通过判断
						party = null;
					}
					while (!party) { //回溯
						if (++j == len) { //cache不通过
							return false;
						}
						if (needNext[j]) {
							party = parties[j - 1];
							j++;
						}
					}
					parties[j - 1] = party;
				}
			};
			return arrFilter(els, chkRelation);
		} else { //不需回溯
			var els2 = [];
			for (var i = 0, el, elI; el = elI = els[i++];) {
				for (var j = len - 1; j > 0; j--) {
					if (!(el = relations[j](el, filters[j - 1], pEl))) {
						break;
					}
				}
				if (el && (needNotTopJudge || el.parentNode == pEl)) els2.push(elI);
			}
			return els2;
		}
	}
	QW.Selector = Selector;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'dom/dom.u.js"><\/script>');
(function() {
	var DomU = {
		create: (function() {
			var temp = document.createElement('div'),
				wrap = {
					option: [1, '<select multiple="multiple">', '</select>'],
					optgroup: [1, '<select multiple="multiple">', '</select>'],
					legend: [1, '<fieldset>', '</fieldset>'],
					thead: [1, '<table>', '</table>'],
					tbody: [1, '<table>', '</table>'],
					tfoot : [1, '<table>', '</table>'],
					tr: [2, '<table><tbody>', '</tbody></table>'],
					td: [3, '<table><tbody><tr>', '</tr></tbody></table>'],
					th: [3, '<table><tbody><tr>', '</tr></tbody></table>'],
					col: [2, '<table><tbody></tbody><colgroup>', '</colgroup></table>'],
					_default: [0, '', '']
				},
				tagName = /<(\w+)/i;
			return function(html, rfrag, doc) {
				var dtemp = (doc && doc.createElement('div')) || temp,
					root = dtemp,
					tag = (tagName.exec(html) || ['', ''])[1],
					wr = wrap[tag] || wrap._default,
					dep = wr[0];
				dtemp.innerHTML = wr[1] + html + wr[2];
				while (dep--) {
					dtemp = dtemp.firstChild;
				}
				var el = dtemp.firstChild;
				if (!el || !rfrag) {
					while (root.firstChild) {
						root.removeChild(root.firstChild);
					}
					//root.innerHTML = '';
					return el;
				} else {
					doc = doc || document;
					var frag = doc.createDocumentFragment();
					while (el = dtemp.firstChild) {
						frag.appendChild(el);
					}
					return frag;
				}
			};
		}()),
		ready: function(handler, doc) {
			doc = doc || document;
			if (/complete/.test(doc.readyState)) {
				handler();
			} else {
				if (doc.addEventListener) {
					if ('interactive' == doc.readyState) {
						handler();
					} else {
						doc.addEventListener('DOMContentLoaded', handler, false);
					}
				} else {
					var fireDOMReadyEvent = function() {
						fireDOMReadyEvent = new Function();
						handler();
					};
					(function() {
						try {
							doc.body.doScroll('left');
						} catch (exp) {
							return setTimeout(arguments.callee, 1);
						}
						fireDOMReadyEvent();
					}());
					doc.attachEvent('onreadystatechange', function() {
						('complete' == doc.readyState) && fireDOMReadyEvent();
					});
				}
			}
		}
	};
	QW.DomU = DomU;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'dom/node.h.js"><\/script>');
(function() {
	var ObjectH = QW.ObjectH,
		StringH = QW.StringH,
		DomU = QW.DomU,
		Browser = QW.Browser,
		Selector = QW.Selector;
	var g = function(el, doc) {
		if ('string' == typeof el) {
			if (el.indexOf('<') == 0) {return DomU.create(el, false, doc); }
			return (doc || document).getElementById(el);
		} else {
			return (ObjectH.isWrap(el)) ? arguments.callee(el[0]) : el; //如果NodeW是数组的话，返回第一个元素(modified by akira)
		}
	};
	var NodeH = {
		getAttr: function(el, attribute, iFlags) {
			el = g(el);
			if ((attribute in el) && 'href' != attribute) {
				return el[attribute];
			} else {
				return el.getAttribute(attribute, iFlags || (el.nodeName == 'A' && attribute.toLowerCase() == 'href' && 2) || null);
			}
		},
		setAttr: function(el, attribute, value, iCaseSensitive) {
			el = g(el);
			if (attribute in el) {
				el[attribute] = value;
			} else {
				el.setAttribute(attribute, value, iCaseSensitive || null);
			}
		},
		query: function(el, selector) {
			el = g(el);
			return Selector.query(el, selector || '');
		},
		one: function(el, selector) {
			el = g(el);
			return Selector.one(el, selector || '');
		},
		setStyle: function(el, attribute, value) {
			el = g(el);
			if ('object' != typeof attribute) {
				var displayAttribute = StringH.camelize(attribute),
					hook = NodeH.cssHooks[displayAttribute];
				if (hook) {
					hook.set(el, value);
				} else {
					el.style[displayAttribute] = value;
				}
			} else {
				for (var prop in attribute) {
					NodeH.setStyle(el, prop, attribute[prop]);
				}
			}
		},
		cssHooks: (function() {
			var hooks = {
					'float': {
						get: function(el, current, pseudo) {
							if (current) {
								var style = el.ownerDocument.defaultView.getComputedStyle(el, pseudo || null);
								return style ? style.getPropertyValue('cssFloat') : null;
							} else {
								return el.style.cssFloat;
							}
						},
						set: function(el, value) {
							el.style.cssFloat = value;
						},
						remove : function (el) {
							el.style.removeProperty('float');
						}
					}
				};
			if (Browser.ie) {
				hooks['float'] = {
					get: function(el, current) {
						return el[current ? 'currentStyle' : 'style'].styleFloat;
					},
					set: function(el, value) {
						el.style.styleFloat = value;
					},
					remove : function (el) {
						el.style.removeAttribute('styleFloat');
					}
				};
				hooks.opacity = {
					get: function(el, current) {
						var opacity;
						if (el.filters['alpha']) {
							opacity = el.filters['alpha'].opacity / 100;
						} else if (el.filters['DXImageTransform.Microsoft.Alpha']) {
							opacity = el.filters['DXImageTransform.Microsoft.Alpha'].opacity / 100;
						}
						if (isNaN(opacity)) {
							opacity = 1;
						}
						return opacity;
					},
					set: function(el, value) {
						if (el.filters['alpha']) {
							el.filters['alpha'].opacity = value * 100;
						} else {
							el.style.filter += 'alpha(opacity=' + (value * 100) + ')';
						}
						el.style.opacity = value;
					},
					remove : function (el) {
						el.style.filter = '';
						el.style.removeAttribute('opacity');
					}
				};
			}
			return hooks;
		}())
	};
	NodeH.g = g;
	QW.NodeH = NodeH;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'dom/node.w.js"><\/script>');
(function() {
	var ObjectH = QW.ObjectH,
		mix = ObjectH.mix,
		isString = ObjectH.isString,
		isArray = ObjectH.isArray,
		push = Array.prototype.push,
		NodeH = QW.NodeH,
		g = NodeH.g,
		query = NodeH.query,
		one = NodeH.one,
		create = QW.DomU.create;
	var NodeW = function(core) {
		if (!core) {//用法：var w=NodeW(null);	返回null
			return null;
		}
		var arg1 = arguments[1];
		if (isString(core)) {
			if (/^</.test(core)) { //用法：var w=NodeW(html);
				var list = create(core, true, arg1).childNodes,
					els = [];
				for (var i = 0, elI; elI = list[i]; i++) {
					els[i] = elI;
				}
				return new NodeW(els);
			} else { //用法：var w=NodeW(sSelector);
				return new NodeW(query(arg1, core));
			}
		} else {
			core = g(core, arg1);
			if (this instanceof NodeW) {
				this.core = core;
				if (isArray(core)) { //用法：var w=NodeW(elementsArray);
					this.length = 0;
					push.apply(this, core);
				} else { //用法：var w=new NodeW(element)//不推荐;
					this.length = 1;
					this[0] = core;
				}
			} else {//用法：var w=NodeW(element); var w2=NodeW(elementsArray);
				return new NodeW(core);
			}
		}
	};
	NodeW.one = function(core) {
		if (!core) {//用法：var w=NodeW.one(null);	返回null
			return null;
		}
		var arg1 = arguments[1];
		if (isString(core)) { //用法：var w=NodeW.one(sSelector);
			if (/^</.test(core)) { //用法：var w=NodeW.one(html);
				return new NodeW(create(core, false, arg1));
			} else { //用法：var w=NodeW(sSelector);
				return new NodeW(one(arg1, core));
			}
		} else {
			core = g(core, arg1);
			if (isArray(core)) { //用法：var w=NodeW.one(array);
				return new NodeW(core[0]);
			} else {//用法：var w=NodeW.one(element);
				return new NodeW(core);
			}
		}
	};
	NodeW.pluginHelper = function(helper, wrapConfig, gsetterConfig) {
		var HelperH = QW.HelperH;
		helper = HelperH.mul(helper, wrapConfig); //支持第一个参数为array
		var st = HelperH.rwrap(helper, NodeW, wrapConfig); //对返回值进行包装处理
		if (gsetterConfig) {//如果有gsetter，需要对表态方法gsetter化
			st = HelperH.gsetter(st, gsetterConfig);
		}
		mix(NodeW, st); //应用于NodeW的静态方法
		var pro = HelperH.methodize(helper, 'core');
		pro = HelperH.rwrap(pro, NodeW, wrapConfig);
		if (gsetterConfig) {
			pro = HelperH.gsetter(pro, gsetterConfig);
		}
		mix(NodeW.prototype, pro);
	};
	mix(NodeW.prototype, {
		first: function() {
			return NodeW(this[0]);
		},
		last: function() {
			return NodeW(this[this.length - 1]);
		},
		item: function(i) {
			return NodeW(this[i]);
		},
		filter: function(callback, pThis) {
			if (typeof callback == 'string') {
				callback = QW.Selector.selector2Filter(callback);
			}
			return NodeW(ArrayH.filter(this, callback, pThis));
		}
	});
	QW.NodeW = NodeW;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'dom/event.h.js"><\/script>');
(function() {
	function getDoc(e) {
		var target = EventH.getTarget(e),
			doc = document;
		if (target) { //ie unload target is null
			doc = target.ownerDocument || target.document || ((target.defaultView || target.window) && target) || document;
		}
		return doc;
	}
	var EventH = {
		getPageX: function(e) {
			e = e || EventH.getEvent.apply(EventH, arguments);
			var doc = getDoc(e);
			return ('pageX' in e) ? e.pageX : (e.clientX + (doc.documentElement.scrollLeft || doc.body.scrollLeft) - 2);
		},
		getPageY: function(e) {
			e = e || EventH.getEvent.apply(EventH, arguments);
			var doc = getDoc(e);
			return ('pageY' in e) ? e.pageY : (e.clientY + (doc.documentElement.scrollTop || doc.body.scrollTop) - 2);
		},
		getDetail: function(e) {
			e = e || EventH.getEvent.apply(EventH, arguments);
			return e.detail || -(e.wheelDelta || 0);
		},
		getKeyCode: function(e) {
			e = e || EventH.getEvent.apply(EventH, arguments);
			return ('keyCode' in e) ? e.keyCode : (e.charCode || e.which || 0);
		},
		getTarget: function(e) {
			e = e || EventH.getEvent.apply(EventH, arguments);
			var node = e.srcElement || e.target;
			if (node && node.nodeType == 3) {
				node = node.parentNode;
			}
			return node;
		},
		getRelatedTarget: function(e) {
			e = e || EventH.getEvent.apply(EventH, arguments);
			if ('relatedTarget' in e) {return e.relatedTarget; }
			if (e.type == 'mouseover') {return e.fromElement; }
			if (e.type == 'mouseout') {return e.toElement; }
		},
		getEvent: function(event, element) {
			if (event) {
				return event;
			} else if (element) {
				if (element.document) {return element.document.parentWindow.event; }
				if (element.parentWindow) {return element.parentWindow.event; }
			}
			if (window.event) {
				return window.event;
			} else {
				var f = arguments.callee;
				do {
					if (/Event/.test(f.arguments[0])) {return f.arguments[0]; }
				} while (f = f.caller);
			}
		},
		_EventPro: {
			stopPropagation: function() {
				this.cancelBubble = true;
			},
			preventDefault: function() {
				this.returnValue = false;
			}
		},
		standardize: function(e){
			e = e || EventH.getEvent.apply(EventH, arguments);
			e.target = EventH.getTarget(e);
			e.relatedTarget = e.relatedTarget || EventH.getRelatedTarget(e);
			if (!('pageX' in e)) {
				e.pageX = EventH.getPageX(e);
				e.pageY = EventH.getPageY(e);
			}
			if (!('detail' in e)) {
				e.detail = EventH.getDetail(e);
			}
			if (!('keyCode' in e)) {
				e.keyCode = EventH.getKeyCode(e);
			}
			for(var i in EventH._EventPro){
				if (e[i] == null) {
					e[i] = EventH._EventPro[i];
				}
			}
			return e;
		}
	};
	QW.EventH = EventH;
}());
//document.write('<script type="text/javascript" src="' + srcPath + 'dom/eventtarget.h.js"><\/script>');
(function() {
	var g = QW.NodeH.g,
		mix = QW.ObjectH.mix,
		standardize = QW.EventH.standardize;
	var Cache = function() {
		var cacheSeq = 1,
			seqProp = '__QWETH_id';
		return {
			get: function(el, eventName, handler, selector) {
				var data = el[seqProp] && this[el[seqProp]];
				if (data && handler[seqProp]) {
					return data[eventName + handler[seqProp] + (selector || '')];
				}
			},
			add: function(realHandler, el, eventName, handler, selector) {
				if (!el[seqProp]) el[seqProp] = cacheSeq++;
				if (!handler[seqProp]) handler[seqProp] = cacheSeq++;
				var data = this[el[seqProp]] || (this[el[seqProp]] = {});
				data[eventName + handler[seqProp] + (selector || '')] = realHandler;
			},
			remove: function(el, eventName, handler, selector) {
				var data = el[seqProp] && this[el[seqProp]];
				if (data && handler[seqProp]) {
					delete data[eventName + handler[seqProp] + (selector || '')];
				}
			},
			removeEvents: function(el, eventName) {
				var data = el[seqProp] && this[el[seqProp]];
				if (data) {
					var reg = new RegExp('^[a-zA-Z.]*' + (eventName || '') + '\\d+$');
					for (var i in data) {
						if (reg.test(i)) {
							EventTargetH.removeEventListener(el, i.split(/[^a-zA-Z]/)[0], data[i]);
							delete data[i];
						}
					}
				}
			},
			removeDelegates: function(el, eventName, selector) {
				var data = el[seqProp] && this[el[seqProp]];
				if (data) {
					var reg = new RegExp('^([a-zA-Z]+\\.)?' + (eventName || '') + '\\d+.+');
					for (var i in data) {
						if (reg.test(i) && (!selector || i.substr(i.length - selector.length) == selector)) {
							var name = i.split(/\d+/)[0].split('.'),
								needCapture = EventTargetH._DelegateCpatureEvents.indexOf(name[1]||name[0]) > -1;
							EventTargetH.removeEventListener(el, i.split(/[^a-zA-Z]/)[0], data[i], needCapture);
							delete data[i];
						}
					}
				}
			}
		};
	}();
	function listener(el, sEvent, handler, userEventName) {
		return Cache.get(el, sEvent + (userEventName ? '.' + userEventName : ''), handler) || function(e) {
			if (!userEventName || userEventName && EventTargetH._EventHooks[userEventName][sEvent](el, e)) {
				return fireHandler(el, e, handler, sEvent);
			}
		};
	}
	function delegateListener(el, selector, sEvent, handler, userEventName) {
		return Cache.get(el, sEvent + (userEventName ? '.' + userEventName : ''), handler, selector) || function(e) {
			var elements = [],
				node = e.srcElement || e.target;
			if (!node) {
				return;
			}
			if (node.nodeType == 3) {
				node = node.parentNode;
			}
			while (node && node != el) {
				elements.push(node);
				node = node.parentNode;
			}
			elements = QW.Selector.filter(elements, selector, el);
			for (var i = 0, l = elements.length; i < l; ++i) {
				if (!userEventName || userEventName && EventTargetH._DelegateHooks[userEventName][sEvent](elements[i], e || window.event)) {
					return fireHandler(elements[i], e, handler, sEvent);
				}
				if (elements[i].parentNode && elements[i].parentNode.nodeType == 11) { //fix remove elements[i] bubble bug
					if (e.stopPropagation) {
						e.stopPropagation();
					} else {
						e.cancelBubble = true;
					}
					break;
				}
			}
		};
	}
	function fireHandler(el, e, handler, sEvent) {
		return EventTargetH.fireHandler.apply(null, arguments);
	}
	var EventTargetH = {
		_EventHooks: {},
		_DelegateHooks: {},
		_DelegateCpatureEvents:'change,focus,blur',
		fireHandler: function(el, e, handler, sEvent) {
			e = standardize(e);
			return handler.call(el, e);
		},
		addEventListener: (function() {
			if (document.addEventListener) {
				return function(el, sEvent, handler, capture) {
					el.addEventListener(sEvent, handler, capture || false);
				};
			} else {
				return function(el, sEvent, handler) {
					el.attachEvent('on' + sEvent, handler);
				};
			}
		}()),
		removeEventListener: (function() {
			if (document.removeEventListener) {
				return function(el, sEvent, handler, capture) {
					el.removeEventListener(sEvent, handler, capture || false);
				};
			} else {
				return function(el, sEvent, handler) {
					el.detachEvent('on' + sEvent, handler);
				};
			}
		}()),
		on: function(el, sEvent, handler) {
			el = g(el);
			var hooks = EventTargetH._EventHooks[sEvent];
			if (hooks) {
				for (var i in hooks) {
					var _listener = listener(el, i, handler, sEvent);
					EventTargetH.addEventListener(el, i, _listener);
					Cache.add(_listener, el, i+'.'+sEvent, handler);
				}
			} else {
				_listener = listener(el, sEvent, handler);
				EventTargetH.addEventListener(el, sEvent, _listener);
				Cache.add(_listener, el, sEvent, handler);
			}
		},
		delegate: function(el, selector, sEvent, handler) {
			el = g(el);
			var hooks = EventTargetH._DelegateHooks[sEvent],
				needCapture = EventTargetH._DelegateCpatureEvents.indexOf(sEvent) > -1;
			if (hooks) {
				for (var i in hooks) {
					var _listener = delegateListener(el, selector, i, handler, sEvent);
					EventTargetH.addEventListener(el, i, _listener, needCapture);
					Cache.add(_listener, el, i+'.'+sEvent, handler, selector);
				}
			} else {
				_listener = delegateListener(el, selector, sEvent, handler);
				EventTargetH.addEventListener(el, sEvent, _listener, needCapture);
				Cache.add(_listener, el, sEvent, handler, selector);
			}
		},
		fire: (function() {
			if (document.dispatchEvent) {
				return function(el, sEvent) {
					var evt = null,
						doc = el.ownerDocument || el;
					if (/mouse|click/i.test(sEvent)) {
						evt = doc.createEvent('MouseEvents');
						evt.initMouseEvent(sEvent, true, true, doc.defaultView, 1, 0, 0, 0, 0, false, false, false, false, 0, null);
					} else {
						evt = doc.createEvent('Events');
						evt.initEvent(sEvent, true, true, doc.defaultView);
					}
					return el.dispatchEvent(evt);
				};
			} else {
				return function(el, sEvent) {
					return el.fireEvent('on' + sEvent);
				};
			}
		}())
	};
	EventTargetH._defaultExtend = function() {
		var extend = function(types) {
			function extendType(type) {
				EventTargetH[type] = function(el, handler) {
					if (handler) {
						EventTargetH.on(el, type, handler);
					} else {
						(el[type] && el[type]()) || EventTargetH.fire(el, type);
					}
				};
			}
			for (var i = 0, l = types.length; i < l; ++i) {
				extendType(types[i]);
			}
		};
		extend('submit,reset,click,focus,blur,change'.split(','));
		EventTargetH.hover = function(el, enter, leave) {
			el = g(el);
			EventTargetH.on(el, 'mouseenter', enter);
			EventTargetH.on(el, 'mouseleave', leave || enter);
		};
		var UA = navigator.userAgent;
		if (/firefox/i.test(UA)) {
			EventTargetH._EventHooks.mousewheel = EventTargetH._DelegateHooks.mousewheel = {
				'DOMMouseScroll': function(e) {
					return true;
				}
			};
		}
		mix(EventTargetH._EventHooks, {
			'mouseenter': {
				'mouseover': function(el, e) {
					var relatedTarget = e.relatedTarget || e.fromElement;
					if (!relatedTarget || !(el.contains ? el.contains(relatedTarget) : (el.compareDocumentPosition(relatedTarget) & 17))) {
						//relatedTarget为空或不被自己包含
						return true;
					}
				}
			},
			'mouseleave': {
				'mouseout': function(el, e) {
					var relatedTarget = e.relatedTarget || e.toElement;
					if (!relatedTarget || !(el.contains ? el.contains(relatedTarget) : (el.compareDocumentPosition(relatedTarget) & 17))) {
						//relatedTarget为空或不被自己包含
						return true;
					}
				}
			}
		});
		mix(EventTargetH._DelegateHooks, EventTargetH._EventHooks);
		if (!document.addEventListener) {
			function getElementVal(el) {
				switch (el.type) {
				case 'checkbox':
				case 'radio':
					return el.checked;
				case "select-multiple":
					var vals = [],
						opts = el.options;
					for (var j = 0; j < opts.length; ++j) {
						if (opts[j].selected) {vals.push(opts[j].value); }
					}
					return vals.join(',');
				default:
					return el.value;
				}
			}
			function specialChange(el, e) {
				var target = e.target || e.srcElement;
				//if(target.tagName == 'OPTION') target = target.parentNode;
				if (getElementVal(target) != target.__QWETH_pre_val) {
					return true;
				}
			}
			mix(EventTargetH._DelegateHooks, {
				'change': {
					'focusin': function(el, e) {
						var target = e.target || e.srcElement;
						target.__QWETH_pre_val = getElementVal(target);
					},
					'deactivate': specialChange,
					'focusout': specialChange,
					'click': specialChange
				},
				'focus': {
					'focusin': function(el, e) {
						return true;
					}
				},
				'blur': {
					'focusout': function(el, e) {
						return true;
					}
				}
			});
		}
	};
	EventTargetH._defaultExtend(); //JK: 执行默认的渲染。另：solo时如果觉得内容太多，可以去掉本行进行二次solo
	QW.EventTargetH = EventTargetH;
}());

/*import from ../components/twitter/tweet.h.js,(by build.py)*/

/*
 * @fileoverview send events like twitter
 * @author　Akira
 * @version $version
 */
(function(){

	var mix = QW.ObjectH.mix,
		CustEvent = QW.CustEvent;

	var eventTarget = CustEvent.createEvents({},[]);
	//var timeout = 200; //default timeout
	var receiveMap = {};
	
	eventTarget.on("*", function(evt){
		var type = evt.type;
		var receiveList = receiveMap[type] || [];

		for (var i = 0, len = receiveList.length; i < len; i++){
			var r = receiveList[i];
			mix(evt, {target:r.receiver, receiver:r.receiver}, true);
			r.callback.call(r.receiver, evt);
		}
	});

	var TweetH = {
		tweet : function(target, type, data){
			data = data || {};

			eventTarget.createEvents([type]);	//如果有需要，创建对应类型的事件
			eventTarget.fire(type, mix(data, {sender:target, type:type}, true));
		},
		receive : function(target, type, callback){
			var list = receiveMap[type] = receiveMap[type] || []; //创建对应事件的hash表
			list.push({receiver:target, callback:callback}); //将接收者存入列表
		}
	}

	QW.provide("TweetH",TweetH);
})();/*import from ../components/twitter/tweet_retouch.js,(by build.py)*/

(function(){
	var TweetH = QW.TweetH;

	QW.NodeW.pluginHelper(TweetH, 'operator');
})();

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

(function(){
var _sTags = document.getElementsByTagName('script');
var scriptTag = _sTags[_sTags.length - 1];

QW.DomU.ready(function(){
	var data = {},
		W = QW.NodeW,
		mix = QW.ObjectH.mix,
		evalExp = QW.StringH.evalExp,
		forEach = QW.ArrayH.forEach,
		g = QW.NodeH.g,
		delegate = QW.EventTargetH.delegate,
		setAttr = QW.NodeH.setAttr,
		getAttr = QW.NodeH.getAttr;
	
	//取<script type="text/marmot">标签里的数据进行合并
	var els = W("script");
	forEach(els,
		function(el){
			if(el.type == "text/marmot"){
				var more_data = g(el).innerHTML;
				data = mix(data, evalExp(more_data), true);
			}
		}
	);
	
	var opts = evalExp(getAttr(scriptTag, "data--opts"));
	var marmot = new QW.Marmot("marmot", opts); 
	marmot.log('data', data);

	//class == marmot 并且有 data--marmot ， click 发送 data--marmot 中的内容
	//disMarmot != 1 发送一次
	delegate(document.body, ".marmot","click", function(evt){
		if(getAttr(g(this), "data--disMarmot") != "1"){
			var more = getAttr(g(this), "data--marmot");
			more = evalExp(more);

			marmot.log('data', mix(data, more, true));

			if(getAttr(g(this), "data--disMarmot")){
				setAttr(g(this), "data--disMarmot", "1");
			}
		}
	}); 

	QW.Marmot.log = function(more){
		marmot.log('data', mix(data, more, true));
	};
});
})();