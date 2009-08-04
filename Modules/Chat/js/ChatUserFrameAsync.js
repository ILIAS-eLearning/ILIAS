function ilChatController(linkToOnlineUsers, linkToActiveUsers, linkToCurrentRoom) {
	var me = this;
	
	var refreshTime = 4000;
	
	this.baseUrl = "";
	this.baseRefId = "";
	this.baseRoomId = "";
	this.serverTarget = "";
	
	this.address_user_id = false;
	this.whisper_user_id = false;
	this.target_user_name = false;
	
	this.baseServerAddress = "";
	
	this.refreshCounter = 0;
	this.timeoutSet = false;
	
	this.setBaseChatserverAddress = function (str)
	{
		me.baseServerAddress = str;
	}
	
	this.setBaseUrl = function (url) {
		me.baseUrl = url;
	}
	
	this.setPageTitle = function (str) {
		var obj = document.getElementById('ilChatTitle');
		if (obj) {
			if (obj.firstChild)
				obj.firstChild.nodeValue = str;
			else 
				obj.appendChild(document.createTextNode(str));
		}
	}
	
	this.getLinkTo = function (cmd, refid, params) {
		var url = me.baseUrl;
		url = url.replace("#__cmd", cmd);
		url = url.replace("#__ref_id", refid);

		for(i in params) {
			url += ("&" + i + "=" + params[i]); 
		}
		
		return url;
	}

	/**
	 * @todo: dirty!
	 */
	this.decRefreshCounter = function ()
	{
		me.refresh(false);
	}
	
	/**
	 * displays a message for 60 seconds within the message area
	 * @param	string	a message
	 */
	this.showMessage = function (msg)
	{
		il_chat_message_handler.addMessage(msg, 60);
	}
	
	/**
	 * default handler for async failures
	 * 
	 */
	this.asyncFailure = function(o)
	{
		if (console)
			console.log("An error occured. If you experience any further problems, please close this window and open the chat in a new one.");
		//me.refresh();
	}

	/*
	 *----------------------------------------------------------
	 * DATA UPDATE HANDLER
	 *----------------------------------------------------------
	 */
	this.onlineUsersSuccess = function(o)
	{
		ilOnlineUsers.update(o);
	}
	this.getOnlineUsers = function() {
		var callback =
		{
			success: me.onlineUsersSuccess,
			failure: me.asyncFailure,
			argument: {}
		};

		var params = new Array();
		if (me.baseRoomId) {
			params['room_id'] = me.baseRoomId;
		}
		YAHOO.util.Connect.asyncRequest('GET', me.getLinkTo('getOnlineUsersAsync', me.baseRefId, params), callback);
		
		return false;
	};
	
	this.activeUsersSuccess = function(o) {
		ilActiveUsersRoom.update(o);
	}
	this.getActiveUsers = function() {
		var callback =
		{
			success: me.activeUsersSuccess,
			failure: me.asyncFailure,
			argument: {}
		};

		var params = new Array();
		if (me.baseRoomId) {
			params['room_id'] = me.baseRoomId;
		}
		YAHOO.util.Connect.asyncRequest('GET', me.getLinkTo('getActiveUsersAsync', me.baseRefId, params), callback);
		return false;
	};
	
	this.roomsSuccess = function (o) {
		ilRooms.update(o);
	}
	this.getRooms = function() {
		var callback =
		{
			success: me.roomsSuccess,
			failure: me.asyncFailure,
			argument: {}
		};

		var params = new Array();
		if (me.baseRoomId) {
			params['room_id'] = me.baseRoomId;
		}
		var link = me.getLinkTo('getCurrentRoomAsync', me.baseRefId, params);
		YAHOO.util.Connect.asyncRequest('GET', link, callback);
		return false;
	}
	
	var last_update = 0;
	
	this.refresh = function (force) {
		var callback =
		{
			success: function(e) {
				try {
					var response = YAHOO.lang.JSON.parse(e.responseText);
					
					if (response.forceRedirect)
					{
						window.location.href = response.forceRedirect;
						return;
					}
					
					me.roomsSuccess(response.rooms);
					me.activeUsersSuccess(response.activeUsers);
					me.onlineUsersSuccess(response.onlineUsers);
				}
				catch(e) {}
			
			},
			failure: me.asyncFailure,
			argument: {}
		};

		if ( force || ((new Date().getTime() - last_update) > 5000))
		{
			var params = new Array();
			if (me.timeoutSet)
			{
				window.clearTimeout(me.timeoutSet);
				me.timeoutSet = false;
			}
			
			if (me.baseRoomId)
			{
				params['room_id'] = me.baseRoomId;
			}
			
			last_update = new Date().getTime();
			
			var link = me.getLinkTo('getUpdateAsync', me.baseRefId, params);
			var request = YAHOO.util.Connect.asyncRequest('GET', link, callback);
		}
		
		return false;
	}
	
	this.invite = function(uid)
	{
		var callback =
		{
			success: function(o) {
				var obj = false;
				try {
					obj = YAHOO.lang.JSON.parse(o.responseText);
					if (obj.ok != true)
					{
						me.showMessage(obj.errormsg);
					}
					else if (obj.infomsg)
					{
						me.showMessage(obj.infomsg);
					}
				}
				catch(e)
				{
					if (console)
						console.log(o);
					me.showMessage("could not invite user");
				}
			},
			failure: me.asyncFailure,
			argument: {}
		};

		var chk = window.confirm(chatLanguage.getTxt('confirm_invite_user'));
		if (chk)
		{
			var params = new Array();
			params['i_id'] = uid;
			params['room_id'] = me.baseRoomId;
			cmd = "inviteAsync";
			var request = YAHOO.util.Connect.asyncRequest('GET', me.getLinkTo(cmd, me.baseRefId, params), callback);
		}
		
	}
	this.disinvite = function (uid)
	{
		var callback =
		{
			success: function(o) {
				var obj = false;
				try
				{
					obj = YAHOO.lang.JSON.parse(o.responseText);
					if (obj.ok != true)
					{
						me.showMessage(obj.errormsg);
					}
					else if (obj.infomsg)
					{
						me.showMessage(obj.infomsg);
					}
				}
				catch(e)
				{}
			},
			failure: me.asyncFailure,
			argument: {}
		};

		var params = new Array();
		params['i_id'] = uid;
		params['room_id'] = me.baseRoomId;
		
		cmd = "dropAsync";
		var request = YAHOO.util.Connect.asyncRequest('GET', me.getLinkTo(cmd, me.baseRefId, params), callback);
	}
	
	this.enterRoom = function (refid, roomid)
	{
		me.cancelAddress();
		ref_id = refid;
		room_id = roomid;
		cmd = "enterRoomAsync";

		var callback =
		{
			success: function(o)
			{
				var data = "";
				try
				{
					var data = YAHOO.lang.JSON.parse(o.responseText);
				}
				catch (e)
				{}

				window.open(data.serverTarget, 'server');
				
				me.serverTarget = data.serverTarget;
				
				me.baseRefId = ref_id;
				me.baseRoomId = room_id;
				
				me.refresh(true);
			},
			failure: me.asyncFailure,
			argument: {}
		};

		var params = new Array();
		if (roomid)
			params['room_id'] = roomid;
		
		var request = YAHOO.util.Connect.asyncRequest('GET', me.getLinkTo(cmd, refid, params), callback);
	}
	
	this.emptyRoom = function ()
	{
		var callback =
		{
			success: function(o) {
				window.open(me.serverTarget, 'server');
				me.refresh(true);
			},
			failure: me.asyncFailure,
			argument: {}
		};
		var params = new Array();
		params['room_id'] = me.baseRoomId;
		cmd = "emptyRoomAsync";
		var request = YAHOO.util.Connect.asyncRequest('GET', me.getLinkTo(cmd, me.baseRefId, params), callback);
	}

	this.addPrivateRoom = function ()
	{
		var name_object = document.getElementById("il_chat_txt_add_private_room");
		if (name_object) {
			var room_name = name_object.value;
			var params = new Array();
			params['room_id'] = me.baseRoomId;
			params['room_name'] = room_name;
			
			var callback =
			{
				success: function(o)
				{
					try
					{
						var response = YAHOO.lang.JSON.parse(o.responseText);
						if (response.ok == true)
						{
							me.enterRoom(response.ref_id, response.room_id);
						}
						else
						{
							me.showMessage(response.infomsg);
						}
					}
					catch(e)
					{}

					var obj = document.getElementById('il_chat_txt_add_private_room');
					if (obj) obj.value = "";
				},
				failure: me.asyncFailure,
				argument: {}
			};
			YAHOO.util.Connect.asyncRequest('GET', me.getLinkTo("addPrivateRoomAsync", me.baseRefId, params), callback);
		}
		return false;
	}
	
	this.deletePrivateRoom = function (id)
	{
		var callback =
		{
			success: function(o)
			{
				o = YAHOO.lang.JSON.parse(o.responseText);
				me.refresh(true);
			},
			failure: me.asyncFailure,
			argument: {}
		};
		var chk = window.confirm(chatLanguage.getTxt('confirm_delete_private_room'));
		if (chk)
		{
			var params = new Array();
			params['room_id_delete'] = id;
			cmd = "deleteRoomAsync";
			var request = YAHOO.util.Connect.asyncRequest('GET', me.getLinkTo(cmd, me.baseRefId,params), callback);
		}
	}
	
	this.kickUser = function (uid) {
		var callback =
		{
			success: function(o) {
				me.getActiveUsers();
				try
				{
					var data = YAHOO.lang.JSON.parse(o.responseText);
					if (data.ok)
					{
						me.showMessage(data.infomsg);
					}
					else
					{
						me.showMessage(chatLanguage.getTxt('unknown_error'));
					}
				}
				catch(e)
				{
				}
			},
			failure: me.asyncFailure,
			argument: {}
		};
		
		var chk = window.confirm(chatLanguage.getTxt('confirm_kick_user'));
		if (chk)
		{
		
			var params = new Array();
			params['kick_id'] = uid;
			cmd = "kickUserAsync";
			
			var request = YAHOO.util.Connect.asyncRequest('GET', me.getLinkTo(cmd, me.baseRefId,params), callback);
		}
	}
	
	this.unkickUser = function (uid) {
		var callback =
		{
			success: function(o)
			{
				try
				{
					var data = YAHOO.lang.JSON.parse(o.responseText);
					if (data.ok == true)
					{
						me.showMessage(data.infomsg);
					}
					else
					{
						me.showMessage(chatLanguage.getTxt('unknown_error'));
					}
				}
				catch(e)
				{}
				me.getActiveUsers();
			},
			failure: me.asyncFailure,
			argument: {}
		};
		var params = new Array();
		params['kick_id'] = uid;
		cmd = "unkickUserAsync";
		
		YAHOO.util.Connect.asyncRequest('GET', me.getLinkTo(cmd, me.baseRefId,params), callback);
	}
	

	this.addressUser = function (uid, name)
	{
		me.address_user_id = uid;
		me.whisper_user_id = false;
		me.target_user_name = name;
		me.setRecipientMessage(chatLanguage.getTxt('address') + ": ",  name);
		me.refresh();
	}
	
	this.whisper = function (uid, name)
	{
		me.address_user_id = false;
		me.whisper_user_id = uid;
		me.target_user_name = name;
		me.setRecipientMessage(chatLanguage.getTxt('whisper') +  ": ", name);
		me.refresh();
	}
	
	this.cancelAddress = function()
	{
		me.address_user_id = false;
		me.whisper_user_id = false;
		me.target_user_name = false;
		me.clearRecipientMessage();
		return false;
	}
	
	this.showProfile = function(uid)
	{
		try
		{
			var link = me.getLinkTo('showUserProfile', me.baseRefId,{'user':uid});
			window.open(link, 'profiles');
		}
		catch(e) {}
	}
	
	this.postMessage = function()
	{
		var msg = false;
		if (document.getElementById("chat_post_message") && document.getElementById("chat_post_message").value.length)
		{
			msg = document.getElementById("chat_post_message").value;
		}
		else
		{
			return;
		}
		
		var callback =
		{
			success: function(o)
			{
				try
				{	
					var obj = YAHOO.lang.JSON.parse(o.responseText);
					if (obj.ok != true)
					{
						me.showMessage(obj.errormsg);
					}
					else
					{
						document.getElementById("chat_post_message").value = "";
					}
				}
				catch (e) {}

			},
			failure: me.asyncFailure,
			argument: {}
		};

		var params = new Array();
		params['room_id'] = me.baseRoomId;
		if(me.address_user_id != false)
			params['a_id'] = me.address_user_id;
		else if (me.whisper_user_id != false)
			params['p_id'] = me.whisper_user_id;
		
		cmd = "inputAsync";
		
		var color = document.forms['txt_input'].color.value;
		var type = false;
		for(var i = 0; i < document.forms['txt_input'].elements['type'].length; i++)
		{
			if (document.forms['txt_input'].elements['type'][i].checked == true)
			{
				type = document.forms['txt_input'].elements['type'][i].value;
				break;
			}
		}
		
		var face = "";
		for(var i = 0; i < document.forms['txt_input'].elements['face[]'].length; i++)
		{
			if (document.forms['txt_input'].elements['face[]'][i].checked)
			{
				face += "&face[]=" + document.forms['txt_input'].elements['face[]'][i].value;
			}
		}
		var request = YAHOO.util.Connect.asyncRequest('POST', me.getLinkTo(cmd, me.baseRefId,params), callback, "color="+color+"&message="+escape(msg)+face+"&type="+type+"&color=" + color);
		return false;
	}
	
	this.stopRecording = function() {
		var callback =
		{
			success: function(o)
			{
				try
				{	
					var obj = YAHOO.lang.JSON.parse(o.responseText);
					if (obj.ok != true)
					{
						me.showMessage(obj.errormsg);
					}
					else
					{
						me.showMessage(chatLanguage.getTxt("recording_stopped"));
					}
				}
				catch (e) {}
				me.refresh(true);
			},
			failure: me.asyncFailure,
			argument: {}
		};
		cmd = "stopRecordingAsync";

		var title = "";
		var params = new Array();

		params['room_id'] = me.baseRoomId;

		var request = YAHOO.util.Connect.asyncRequest('POST', me.getLinkTo(cmd, me.baseRefId,params), callback);
		return false;
	}
	
	this.startRecording = function()
	{
		var callback =
		{
			success: function(o)
			{
				try
				{	
					var obj = YAHOO.lang.JSON.parse(o.responseText);
					if (obj.ok != true)
					{
						me.showMessage(obj.errormsg);
					}
					else
					{
						me.showMessage(chatLanguage.getTxt("recording"));
					}
				}
				catch (e)
				{}
				me.refresh(true);
			},
			failure: me.asyncFailure,
			argument: {}
		};
		cmd = "startRecordingAsync";

		var title = "";
		
		if (document.forms['mod_input'].elements['title'])
		{
			title = document.forms['mod_input'].elements['title'].value;
		}
		
		if (title.length < 1)
		{
			alert(chatLanguage.getTxt('no_title_given'));
			return;
		}
		
		var params = new Array();
		params['room_id'] = me.baseRoomId;
		var request = YAHOO.util.Connect.asyncRequest('POST', me.getLinkTo(cmd, me.baseRefId,params), callback, "title="+title);
		return false;
	}
	
	this.clearRecipientMessage = function()
	{
		var obj = document.getElementById("recipient_message");
		if (obj)
		{
			var fc = false;
			while (fc = obj.firstChild)
			{
				obj.removeChild(fc);
			}
			obj.style.display = "none";
		}
	}
	
	this.setRecipientMessage = function(label, str)
	{
		var obj = document.getElementById("recipient_message");
		if (obj)
		{
			var lbl = document.createElement("b");
			lbl.className = "smallred";
			me.clearRecipientMessage();
			
			lbl.appendChild(document.createTextNode(label));
			
			obj.appendChild(lbl);
			obj.appendChild(document.createTextNode(str));

			obj.style.display = "block";
			
			var a = document.createElement("a");
			a.href="#";
			
			a.onclick = function()
			{
				il_chat_async_handler.cancelAddress();
				return false;
			};

			a.appendChild(document.createTextNode(" (" + chatLanguage.getTxt('cancel') + ")"));
			obj.appendChild(a);
		}
	}
	
	this.addRoomToBookmark = function (ref_id, room_id)
	{
		var callback =
		{
			success: function(o)
			{
				try
				{	
					var obj = YAHOO.lang.JSON.parse(o.responseText);
					if (obj.ok != true)
					{
						me.showMessage('Error: ' + obj.errormsg);
					}
					else
					{
						me.showMessage(obj.msg);
					}
				}
				catch (e)
				{}
			},
			failure: me.asyncFailure,
			argument: {}
		};
		cmd = "addRoomToBookmarkAsync";

		if (!ref_id)
		{
			if (console)
				console.log('no_ref_id');
			return;
		}
		
		var params = new Array();
		params['room_id'] = room_id;
		params['ref_id'] = ref_id
		var request = YAHOO.util.Connect.asyncRequest('POST', me.getLinkTo(cmd, me.baseRefId,params), callback);
		return false;
	}
	
	this.addUserToAddressbook = function (login)
	{
		var callback =
		{
			success: function(o)
			{
				try
				{	
					var obj = YAHOO.lang.JSON.parse(o.responseText);
					if (obj.ok != true)
					{
						me.showMessage(obj.msg);
					}
					else
					{
						me.showMessage(obj.msg);
					}
				}
				catch (e)
				{}
			},
			failure: me.asyncFailure,
			argument: {}
		};
		cmd = "addUserToAddressbookAsync";

		if (!login)
		{
			return;
		}
			
		var params = new Array();
		params['ulogin'] = login;
		var request = YAHOO.util.Connect.asyncRequest('POST', me.getLinkTo(cmd, me.baseRefId,params), callback);
		return false;
	}	
	
	this.checkRecordingStatus = function(room)
	{
		var rec_start_1 = document.getElementById("start_record_1");
		var rec_start_2 = document.getElementById("start_record_2");
		var rec_stop_1 = document.getElementById("stop_record_1");
		var rec_stop_2 = document.getElementById("stop_record_2");

		if (room.act != true)
		{
			for(i in room.subrooms)
			{
				if (room.subrooms[i].act == true)
				{
					room = room.subrooms[i];
					break;
				}
			}
		}
		
		if (room.recording == true)
		{
			if (rec_start_1)
				rec_start_1.style.display = "none";
			if (rec_start_2)
				rec_start_2.style.display = "none";
			if (rec_stop_1)
				rec_stop_1.style.display = "table-row";
			if (rec_stop_2)
				rec_stop_2.style.display = "table-row";
		}
		else
		{
			if (rec_start_1)
				rec_start_1.style.display = "table-row";
			if (rec_start_2)
				rec_start_2.style.display = "table-row";
			if (rec_stop_1)
				rec_stop_1.style.display = "none";
			if (rec_stop_2)
				rec_stop_2.style.display = "none";			
		}
	}
}

ilAddOnLoad(
	function ()
	{
			document.getElementById('server_iframe').src = il_chat_async_handler.baseServerAddress;
			window.setInterval('il_chat_async_handler.refresh(false)', 1000);

			il_chat_message_handler = new ilChatMessages();
			il_chat_message_handler.interval = window.setTimeout(il_chat_message_handler.check, 5000);

	}
);

function insertSmiley(strText, objElem)
{
	if(document.selection)
	{
		objElem.focus();
		document.selection.createRange().text = strText;
		document.selection.createRange().select();
	}
	else if (objElem.selectionStart || objElem.selectionStart == '0')
	{
		intStart = objElem.selectionStart;
		intEnd = objElem.selectionEnd;
		objElem.value = (objElem.value).substring(0, intStart) + strText + (objElem.value).substring(intEnd, objElem.value.length);
		objElem.selectionStart=objElem.selectionEnd=intStart+strText.length;
		objElem.focus();
	}
	else
	{
		objElem.value += strText;
	}
}

function ilShowSmileySelector()
{
	document.getElementById('show_smilies_button').style.display = 'none';
	document.getElementById('hide_smilies_button').style.display = 'block';
	document.getElementById('smiley_selector').style.display = 'block';
}

function ilHideSmileySelector()
{
	document.getElementById('show_smilies_button').style.display = 'block';
	document.getElementById('hide_smilies_button').style.display = 'none';
	document.getElementById('smiley_selector').style.display = 'none';
}

function ilShowTextformatSelector()
{
	document.getElementById('show_textformat_button').style.display = 'none';
	document.getElementById('hide_textformat_button').style.display = 'block';
	document.getElementById('textformat_selector').style.display = 'block';
}

function ilHideTextformatSelector()
{
	document.getElementById('show_textformat_button').style.display = 'block';
	document.getElementById('hide_textformat_button').style.display = 'none';
	document.getElementById('textformat_selector').style.display = 'none';
}

function ilExportChat()
{
	var url = "ilias.php?cmd=export&baseClass=ilChatPresentationGUI&ref_id="+il_chat_async_handler.baseRefId+"&room_id=" + il_chat_async_handler.baseRoomId;
	window.open(url, '_blank');
}
