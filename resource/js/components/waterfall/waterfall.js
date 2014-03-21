/*
 * QWrap plugin waterfall
 * 
 * @描述：定宽不定高，瀑布式添加。
 * 页面没有处理过的元素为死水，className为backwater
 * 瀑布式布局元素为活水元素，className为water
 * 
 * @参数描述：
 * container 父元素容器QWrap选择器，字符串类型（必填）
 * block 子元素QWrap选择器，字符串类型（必填）
 * blkWidth 规定子元素的宽度，数值类型
 * blkRight 规定子元素右侧margin值，数值类型
 * blkBottom 规定子元素下方margin值，数值类型
 * insertType 排列计算方式，true为动态计算高度进行插入，false为循环队列插入
 * blkCols 列数，默认为0，即动态计算获取。数值类型
 * 
 * @瀑布布局预置方案
 * 添加Ele属性data-position 设置为数值，负值表示从右向左数，正值表示从左向右数
 * 
 * @关于页面元素重新排列
 * 重新排列可再次调用conf方法
 * 更新参数时可直接用conf({xxx:xxx})
 * 
 * ajax传入的新结构用backwater作为className
 * 并执行WaterFall.stream();
 * 
 */
(function(){
	//loadCss('waterfall.css');
	window.WaterFall = {
		//默认设置
		deft : {
			'container' : '',
			'block' : '',
			'blkWidth' : 220,
			'blkRight' : 15,
			'blkBottom' : 15,
			'insertType' :true,
			'blkCols' : 0
		},
		
		//运行时，存储的临时数据
		runvar : {
			//记录所有列的className
			cols : [],
			//记录所有列className的left值
			colsLeft : [],
			//记录所有列最后一元素的底部高度
			colsHeight: []
		},
		
		//配置信息
		conf : function(opt){
			
			//根据配置信息按需执行瀑布流式
			if(this.deft.block && opt){
				this.runvar.cols.length = 0;
				this.runvar.colsLeft.length = 0;
				this.runvar.colsHeight.length = 0;
				W(this.deft.block).removeClass('water').addClass('backwater');
			}
			
			var deft = this.deft;
			Object.mix(deft,opt,true),
			//预置模块
			run = this.runvar,
			cols = run.cols,
			colsLeft = run.colsLeft,
			colsHeight = run.colsHeight,
			//判断页面参数传递
			con = W(deft.container),
			blk = W(deft.block),
			//判断列数
			colen = deft.blkCols === 0 ? Math.floor((con.size().width+deft.blkRight)/(deft.blkWidth+deft.blkRight)) : deft.blkCols;
			deft.blkCols = colen;
			
			//得到页面上已现有元素
			if(!ObjectH.isWrap(con) || !ObjectH.isWrap(blk)){
				return;
			};
			
			//对页面上已有元素进行死水处理
			if(!con.hasClass('con-fall')){
				con.addClass('con-fall');
			}
			blk.addClass('backwater');
			
			//根据父元素计算子元素的列数并创建相关class
			for(var i=0; i<colen; i++){
				this.runvar.cols.push('col'+i);
				this.runvar.colsLeft.push(i*(deft.blkWidth+deft.blkRight));
				this.runvar.colsHeight.push(0);
			}
			
			//判断是否有预置的模块
			if(W(deft.block+'[data-position]').length > 0){
				W(deft.block+'[data-position]').forEach(function(el,i){
					var ele = W(el),
					pre = ele.getAttr('data-position',0) === '' ? 0 : ele.getAttr('data-position',0),
					index = 0;
					
					if(pre.indexOf('-') !== -1){
						index = isNaN(parseInt(pre))?0:parseInt(pre)+(run.cols.length-1);
					}else{
						index = isNaN(parseInt(pre))?0:parseInt(pre);
					}
					var topV = run.colsHeight[index]===0 ? 0 : run.colsHeight[index]+WaterFall.deft.blkBottom,
					leftV = run.colsLeft[index];
					ele.css({
						'left' : leftV + 'px',
						'top' : topV + 'px'
					},1).addClass('water').removeClass('backwater');
					colsHeight[index] = topV + ele.size().height;
				})
			}
			
			//对死水布局进行活水处理
			this.stream();
			// return this;
		},
		//通过现有名个列的高度，返回一个最小的用于插入新模块,返回为对象
		getMinCol : function(isCall){
			var minval = Math.min.apply(Math,this.runvar.colsHeight),heights = this.runvar.colsHeight,l=heights.length;
			if(isCall != undefined){
				return minval;
			}
			for(var i=0; i<l; i++){
				if(minval === heights[i]){
					return i;
				}
			}
		},
		//取页面现有元素高度最低的数值
		getMaxCol : function(){
			return Math.max.apply(Math,this.runvar.colsHeight);
		},
		
		//执行流水布局，针对页面已存在的结构
		stream : function(coln){
			var run = this.runvar,
			cols=run.cols,
			colsLeft=run.colsLeft,
			colsHeight=run.colsHeight,
			//进行活水处理的元素collection集合
			coln = coln ? coln : W('.backwater');
			
			//动态计算高度并插入
			coln.forEach(function(el,i){
				var ele = W(el),
				colsInx = WaterFall.getMinCol(),
				leftV = colsLeft[colsInx],
				topV = colsHeight[colsInx]===0?colsHeight[colsInx]:colsHeight[colsInx]+WaterFall.deft.blkBottom;
				ele.css({
					'left' : leftV + 'px',
					'top' : topV + 'px'
				},1).addClass('water').removeClass('backwater');
				colsHeight[colsInx] = topV+ele.size().height;
			});
			
			//设置父元素高度
			W(this.deft.container).css('height',this.getMaxCol()+20+'px');
		}
	}
})()
