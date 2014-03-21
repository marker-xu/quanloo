(function(){

var AsyncH = QW.AsyncH;

var TimingH = {
	prepareTask: function(target, task, delay){
		if(!task.__QWTIMINGH_id){
			task.__QWTIMINGH_id = "T_" + Math.random();
			AsyncH.wait(target, task.__QWTIMINGH_id, task);
		}
		setTimeout(function(){
			AsyncH.signal(target, task.__QWTIMINGH_id);
		}, delay);
	},
	stopTask: function(target, task){
		AsyncH.clearSequence(target, task.__QWTIMINGH_id);
	}
}

QW.NodeW.pluginHelper(TimingH, "operator");
QW.provide("TimingH", TimingH);
})();