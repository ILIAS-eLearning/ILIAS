function ilChatLanguage() {
	var me = this;
	
	this.txts = new Array();
	
	this.addTxt = function(name, txt) {
		me.txts[name] = txt;
	}
	
	this.getTxt = function(name) {
		if(me.txts[name] && me.txts[name] != "") {
			return me.txts[name];
		}
		else {
			return "-|" + name + "|-";
		}
	}
}
