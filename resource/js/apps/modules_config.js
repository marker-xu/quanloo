/*Lib Module*/
QW.ModuleH.addConfig({
	Core: {
		url: '//apps/core_dom_xvideo.js',
		loadedChecker:function(){
			return !!(QW.W);
		}
	},
	Ajax: {
		url: '//components/ajax/ajax.combo.js'
	},
	Anim: {
		url: '//components/animation/anim.js',
		exports: ['QW.Anim','QW.ElAnim']
	},
	Cache: {
		url: '//components/cache/cache.js',loadedChecker:function(){
			return !!(QW.Cookie);
		}
	},
	Cookie : {
		url:'//components/cache/cookie.js'
	},
	Drag: {
		url: '//components/drag/drag_all.combo.js',
		requires: 'Anim'
	},
	Panel: {
		url: '//components/panel/panel.js',
		requires: 'Anim'
	},
	Editor: {
		url: '//components/editor/editor.js',
		requires: 'Panel'
	},
	Combobox: {
		url: '//components/combobox/combobox.js',
		loadedChecker:function(){
			return !!(QW.ComboBox);
		}
	},
	'Switch': {
		url: '//components/switch/switch_all.js',
		requires: 'Anim'
	},
	/*Tree: {
		url: '//components/tree/tree.js',
	},*/
	Timing : {
		url: '//components/timing/timing.base.js'
	},
	Twitter : {
		url: '//components/twitter/twitter.combo.js',
		loadedChecker: function(){
			return !!(QW.TweetH);
		}
	},
	Valid: {
		url: '//components/valid/valid.js'
	},
	Marmot: {
		url: '//components/marmot/src/marmot.combo.js',
		requires: 'Twitter'
	},
	Pager: {
		url: '//components/pager/pager.js'
	},
	Suggest : {
		url: '//components/suggest/suggest.js',
		requires: 'Panel'
	}
}); 