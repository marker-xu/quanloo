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
	
	var opts = evalExp(getAttr(scriptTag, "data--opts")) || {};
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
	QW.Marmot.pageId = data.page_id;
});
})();