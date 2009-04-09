function ilChatMessages() {
	var me = this;
	
	this.messages = new Array();
	this.message_container = document.getElementById("il_message_container");
	this.interval = false;
	
	this.addMessage = function (str, time)
	{
		var date = new Date();
		var element = document.createElement("div");
		
		if (!time)
			time = 60;
		
		element.appendChild(document.createTextNode(str));
		element.style.width = "100%";
		if (me.message_container.firstChild)
			me.message_container.insertBefore(element, me.message_container.firstChild);
		else
			me.message_container.appendChild(element);
		me.messages.push(new Array(element, new Date().valueOf() + time * 1000));
		
	}
	
	this.check = function()
	{
		var messages = new Array();
		for(i in me.messages) {
			if (me.messages[i][1] <= new Date().valueOf()) {
				me.messages[i][0].parentNode.removeChild(me.messages[i][0]);
			}
			else {
				messages.push(me.messages[i]);
			}
		}
		me.messages = messages;
		me.interval = window.setTimeout("il_chat_message_handler.check()", 5000);
	}
}