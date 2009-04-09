function ilChatViewerBlock()
{
	var me = this;
	
	this.lastKnownId = 0;
	this.objectId = 'ilChatBlockMessageBody';
	this.selectId = 'ilChatBlockRoomSelect';
	this.autoscrollId = 'ilChatBlockMessageBodyEnableAutoscroll';

	this.currentSelectionChatId = 0;
	this.currentSelectionRoomId = 0;
	this.baseUrl = "";

	this.refreshInterval = false;
	this.requestMonitor = false;
	
	this.init = function() 
	{
		if (getRoomSelectObject().value)
			me.onRoomChange()
	}

	this.setBaseUrl = function (str)
	{
		me.baseUrl = str;
	}
	
	this.getNewMessages = function ()
	{
		var callback =
		{
			success: function(o)
			{
				var obj = false;
				try {
					obj = YAHOO.lang.JSON.parse(o.responseText);
					// ...
					if (!obj.messages)
						return;
					if (obj.ok == true)
					{
						appendNewMessages(obj.messages);
						me.lastKnownId = obj.lastId;
						//alert(obj.lastId);
					}
					else
					{
						//alert(obj.errormsg);
					}
				}
				catch(e) {
					//alert(e + "\n" + o.responseText);
				}
			},
			failure: function(o) {	},
			argument: {}
		};
		
		var request = YAHOO.util.Connect.asyncRequest
		(
			'GET',
			me.getLinkTo(me.currentSelectionChatId, me.currentSelectionRoomId),
			callback
		);
	}
	
	this.onRoomChange = function ()
	{
		if (me.refreshInterval)
			window.clearInterval(me.refreshInterval);

		me.lastKnownId = 0;
		emptyMessageBody();
		getCurrentSelection();
		
		if (me.currentSelectionChatId == 0)
			return;
		
		me.getNewMessages();
		
		me.refreshInterval = window.setInterval(me.getNewMessages, 5000);
	}
	
	this.getLinkTo = function (chatId, roomId)
	{
		var link = me.baseUrl.replace("#__ref_id", chatId);
		link = link.replace("#__room_id", roomId);
		return  link + "&chat_last_known_id="+me.lastKnownId;// + "&ref_id="+chatId+"&room_id="+roomId;
	}
	
	function getCurrentSelection()
	{
		var cVal = getRoomSelectObject().value;
		var parts = cVal.split(",");
		if (parts.length > 0)
			me.currentSelectionChatId = parts[0];
		if (parts.length > 1)
			me.currentSelectionRoomId = parts[1];
		else
			me.currentSelectionRoomId = 0;
	}
	
	function getMessageBodyObject()
	{
		var obj = document.getElementById(me.objectId);
		if (obj)
			return obj;
	}

	function getRoomSelectObject()
	{
		var obj = document.getElementById(me.selectId);
		if (obj)
			return obj;
	}
	
	function appendNewMessages(messages)
	{
		//alert(messages.length);
		var obj = getMessageBodyObject();
		for(i in messages)
		{
			var sp = document.createElement('div');
			sp.innerHTML = messages[i];
			obj.appendChild(sp);
		}
		if (doAutoscroll())
			obj.scrollTop = "100000"; 
	}
	
	function emptyMessageBody()
	{
		var child = false;
		var msgBody = getMessageBodyObject(); 
		while (child = msgBody.firstChild)
		{
			msgBody.removeChild(child);
		}
	}
	
	function doAutoscroll()
	{
		var obj = document.getElementById(me.autoscrollId);
		return (obj.checked);
	}
}

var ilChatViewerBlockHandler = new ilChatViewerBlock();

ilAddOnLoad
(
		function () {
			ilChatViewerBlockHandler.init();
		}
);