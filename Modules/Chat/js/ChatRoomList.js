function ilRoomTable()
{
	var title = chatLanguage.getTxt('rooms_list_title');
	var rooms = [];
	var me = this;
	var roomcount;
	
	// containers
	var div_title;
	var div_body;
	var div_container;
	var span_roomcount;
	var div_show_hide;
	
	function countRooms()
	{
		var count = 0;
		for (var i = 0; i < rooms.length; i++)
		{
			count++;
			if (rooms[i].subrooms)
				count += rooms[i].subrooms.length;
		}
		return count;
	}
	
	function setRoomCount(count)
	{
		roomcount = count;
		if (span_roomcount)
			span_roomcount.innerHTML = ' (' + roomcount + ')';
	}
	
	this.setTitle = function (str) { title = str };

	this.addRoom = function (room, parent)
	{
		if (!parent)
		{
			rooms[room.id] = room;
		}
		else if (parent && rooms[parent])
		{
			if (!rooms[parent].subrooms)
				rooms[parent].subrooms = [];
			
			rooms[parent].subrooms[room.id] = room;
		}
	}

	// scope should be a room element
	this.createContextMenu = function(ref_id, room_id)
	{
		var menuconfig = [];
		//check for enter permission
		if (this.pme == true)
		{
			menuconfig.push(
				{ text : chatLanguage.getTxt('open'), onclick : { fn : function() {il_chat_async_handler.enterRoom(ref_id, room_id);return false;}}}
			);
		}
		
		//check for empty permission
		if (this.pmem == true)
		{
			menuconfig.push(
				{ text : chatLanguage.getTxt('empty'), onclick : { fn : function() {il_chat_async_handler.emptyRoom(); return false;}}}
			);
		}
		
		//check for delete permission
		if (this.pmde == true)
		{
			menuconfig.push(
				{ text : chatLanguage.getTxt('delete'), onclick : { fn : function() {il_chat_async_handler.deletePrivateRoom(room_id); return false;}}}
			);
		}
		
		//check for bookmark permission
		if (this.pmbo == true)
		{
			menuconfig.push(
				{ text : chatLanguage.getTxt('add_to_bookmark'), onclick : { fn : function() {il_chat_async_handler.addRoomToBookmark(ref_id, room_id); return false;}}}
			);
		}
		var menu_id = ref_id + ',' + room_id;
		chatmenu.addMenuConfiguration({ id : menu_id, items : menuconfig, titles : [this.title]});
		
		this.element.elementTitle.onclick = function () {il_chat_async_handler.enterRoom(ref_id, room_id);return false;};
		
		return menu_id;
	}
	
	this.contextMenuCallback = function (menu_id)
	{
		return function ()
		{
			chatmenu.show(menu_id);
		}
	}
	
	this.getIcon = function()
	{
		var img = document.createElement("img");
		img.src = "templates/default/images/icon_chat.gif";
		return img;
	}
	
	this.update = function (visible_rooms)
	{
		// perform update... check currently displayed rooms
		// for delete or update information

		// count rooms
		var count = 0;
		if (visible_rooms.currentRoom && visible_rooms.currentRoom.subrooms)
		{
			count = 1 + visible_rooms.currentRoom.subrooms.length;
		}
		
		for(var i in visible_rooms.rooms)
		{
			count += 1 + visible_rooms.rooms[i].subrooms.length;
		}
		setRoomCount(count);
		
		// used to determine if room is still available (has been updated
		// during current run)
		var timestamp_update_run = new Date().getTime();
		
		// update rooms
		current_index = 0;
		// current_room and private rooms within
		var cId = visible_rooms.currentRoom.ref_id;
		if (cId)
		{
			if (rooms[cId])
			{
				for(var i in visible_rooms.currentRoom)
				{
					rooms[cId][i] = visible_rooms.currentRoom[i];	
				}
				rooms[cId].element.className = 'navigation small tblactive';
				rooms[cId].element.updateUserCount.call(rooms[cId]);
				rooms[cId].lastUpdated = timestamp_update_run;
				this.createContextMenu.call(rooms[cId], rooms[cId].ref_id, rooms[cId].room_id);
			}
			else
			{
				rooms[cId] = visible_rooms.currentRoom;
				rooms[cId].elementSubrooms = [];
				rooms[cId].element = document.createElement("li");
				rooms[cId].element.elementTitle = document.createElement("span");
				rooms[cId].element.elementSublist = document.createElement("ul");
				rooms[cId].lastUpdated = timestamp_update_run;
				rooms[cId].element.updateUserCount = function()
				{
					this.element.elementTitle.innerHTML = this.title + " (" + this.users_online + ")";
					if (this.recording)
					{
						this.element.elementTitle.innerHTML += "&nbsp;" + chatLanguage.getTxt('chat_recording_running');
					}
				};
				rooms[cId].element.updateUserCount.call(rooms[cId]);
				
				var icon = this.getIcon();
				var menu_id = this.createContextMenu.call(rooms[cId], rooms[cId].ref_id, rooms[cId].room_id);
				icon.onclick = this.contextMenuCallback(menu_id);
				
				rooms[cId].element.appendChild(icon);
				rooms[cId].element.appendChild(rooms[cId].element.elementTitle);
				rooms[cId].element.appendChild(rooms[cId].element.elementSublist);
				
				div_body.appendChild(rooms[cId].element);
			}
			rooms[cId].element.className = 'navigation small tblactive';
			if (rooms[cId].act == true)
			{
				rooms[cId].element.className += ' tblrowmarked';
				il_chat_async_handler.setPageTitle(rooms[cId].title);
			}
			var sub_current_index = 0;
			for(var i in visible_rooms.currentRoom.subrooms)
			{
				var rId = visible_rooms.currentRoom.subrooms[i].own_room_id;
				
				if (rooms[cId].elementSubrooms[rId])
				{
					for(var x in visible_rooms.currentRoom.subrooms[i])
					{
						rooms[cId].elementSubrooms[rId][x] = visible_rooms.currentRoom.subrooms[i][x];	
					}
					rooms[cId].elementSubrooms[rId].element.updateUserCount.call(rooms[cId].elementSubrooms[rId]);
					rooms[cId].elementSubrooms[rId].lastUpdated = timestamp_update_run;
					this.createContextMenu.call(rooms[cId].elementSubrooms[rId], rooms[cId].elementSubrooms[rId].ref_id, rooms[cId].elementSubrooms[rId].room_id);
				}
				else
				{
					rooms[cId].elementSubrooms[rId] = visible_rooms.currentRoom.subrooms[i];
					rooms[cId].elementSubrooms[rId].element = document.createElement("li");
					rooms[cId].elementSubrooms[rId].element.elementTitle = document.createElement("span");
					rooms[cId].elementSubrooms[rId].lastUpdated = timestamp_update_run;
					rooms[cId].elementSubrooms[rId].element.updateUserCount = function()
					{
						this.element.elementTitle.innerHTML = this.title + " (" + this.users_online + ")";
						if (this.recording)
						{
							//var ico = document.createElement("div");
							//ico.className = 'ilChatRecordingImage';
							//this.element.appendChild(ico);
							this.element.elementTitle.innerHTML += "&nbsp;" + chatLanguage.getTxt('recording_running');
						}
					};
					rooms[cId].elementSubrooms[rId].element.updateUserCount.call(rooms[cId].elementSubrooms[rId]);
					var icon = this.getIcon();
					var menu_id = this.createContextMenu.call(rooms[cId].elementSubrooms[rId], rooms[cId].elementSubrooms[rId].ref_id, rooms[cId].elementSubrooms[rId].room_id);
					icon.onclick = this.contextMenuCallback(menu_id);
					rooms[cId].elementSubrooms[rId].element.appendChild(icon);
					
					rooms[cId].elementSubrooms[rId].element.appendChild(rooms[cId].elementSubrooms[rId].element.elementTitle);
					rooms[cId].element.elementSublist.appendChild(rooms[cId].elementSubrooms[rId].element);

				}
				
				if (rooms[cId].elementSubrooms[rId].act == true)
				{
					rooms[cId].elementSubrooms[rId].element.className = 'tblrowmarked';
					il_chat_async_handler.setPageTitle(rooms[cId].elementSubrooms[rId].title);
				}
				else
				{
					rooms[cId].elementSubrooms[rId].element.className = 'tblrow';
				}
				
				// check if room is listed in correct order, else switch to
				// position
				if (rooms[cId].element.elementSublist.childNodes[sub_current_index] != rooms[cId].elementSubrooms[rId].element)
				{
					if (rooms[cId].element.elementSublist.childNodes[sub_current_index + 1])
					{
						rooms[cId].element.elementSublist.removeChild(rooms[cId].elementSubrooms[rId].element);
						rooms[cId].element.elementSublist.insertBefore(rooms[cId].elementSubrooms[rId].element, rooms[cId].element.elementSublist.childNodes[sub_current_index]);
					}
				}
				sub_current_index++;
			}
			
			if (rooms[cId].elementSubrooms.length <= 0)
				rooms[cId].element.elementSublist.style.display = "none";
			else
				rooms[cId].element.elementSublist.style.display = "block";
			
			il_chat_async_handler.checkRecordingStatus(rooms[cId]);
			
			// check if room is listed in correct order, else switch to
			// position
			if (div_body.childNodes[current_index] != rooms[cId].element)
			{
				if (div_body.childNodes[current_index + 1])
				{
					div_body.removeChild(rooms[cId].element);
					div_body.insertBefore(rooms[cId].element, div_body.childNodes[current_index]);
				}
			}
			current_index++;
		}
		// further rooms and private rooms
		for (var i in visible_rooms.rooms)
		{
			var cId = visible_rooms.rooms[i].ref_id;
			if (rooms[cId])
			{
				for(var x in visible_rooms.rooms[i])
				{
					rooms[cId][x] = visible_rooms.rooms[i][x];	
				}
				rooms[cId].element.updateUserCount.call(rooms[cId]);
				rooms[cId].lastUpdated = timestamp_update_run;
				this.createContextMenu.call(rooms[cId], rooms[cId].ref_id, rooms[cId].room_id);
			}
			else
			{
				rooms[cId] = visible_rooms.rooms[i];
				rooms[cId].elementSubrooms = [];
				rooms[cId].element = document.createElement("li");
				rooms[cId].element.elementSublist = document.createElement("ul");
				rooms[cId].element.elementTitle = document.createElement("span");
				rooms[cId].lastUpdated = timestamp_update_run;
				rooms[cId].element.updateUserCount = function()
				{
					this.element.elementTitle.innerHTML = this.title + " (" + this.users_online + ")";
						if (this.recording)
						{
							this.element.elementTitle.innerHTML += "&nbsp;" + chatLanguage.getTxt('recording_running');
						}
				};
				rooms[cId].element.updateUserCount.call(rooms[cId]);
				var icon = this.getIcon();
				var menu_id = this.createContextMenu.call(rooms[cId], rooms[cId].ref_id, rooms[cId].room_id);
				icon.onclick = this.contextMenuCallback(menu_id);
				
				rooms[cId].element.appendChild(icon);
				rooms[cId].element.appendChild(rooms[cId].element.elementTitle);
				rooms[cId].element.appendChild(rooms[cId].element.elementSublist);
				div_body.appendChild(rooms[cId].element);
			}
			rooms[cId].element.className = 'navigation small';
			for(var x in visible_rooms.rooms[i].subrooms)
			{
				var rId = visible_rooms.rooms[i].subrooms[x].own_room_id;
				
				if (rooms[cId].elementSubrooms[rId])
				{
					for(var y in visible_rooms.rooms[i].subrooms[x])
					{
						rooms[cId].elementSubrooms[rId][y] = visible_rooms.rooms[i].subrooms[x][y];	
					}
					rooms[cId].elementSubrooms[rId].element.updateUserCount.call(rooms[cId].elementSubrooms[rId]);
					rooms[cId].elementSubrooms[rId].lastUpdated = timestamp_update_run;
					this.createContextMenu.call(rooms[cId].elementSubrooms[rId], rooms[cId].elementSubrooms[rId].ref_id, rooms[cId].elementSubrooms[rId].room_id);
				}
				else
				{
					rooms[cId].elementSubrooms[rId] = visible_rooms.rooms[i].subrooms[x];
					rooms[cId].elementSubrooms[rId].element = document.createElement("li");
					rooms[cId].elementSubrooms[rId].element.elementTitle = document.createElement("span");
					rooms[cId].elementSubrooms[rId].lastUpdated = timestamp_update_run;
					rooms[cId].elementSubrooms[rId].element.updateUserCount = function()
					{
						this.element.elementTitle.innerHTML = this.title + " (" + this.users_online + ")";
						if (this.recording)
						{
							this.element.elementTitle.innerHTML += "&nbsp;" + chatLanguage.getTxt('recording_running');
						}
					};
					rooms[cId].elementSubrooms[rId].element.updateUserCount.call(rooms[cId].elementSubrooms[rId]);
					var icon = this.getIcon();
					var menu_id = this.createContextMenu.call(rooms[cId].elementSubrooms[rId], rooms[cId].elementSubrooms[rId].ref_id, rooms[cId].elementSubrooms[rId].room_id);
					icon.onclick = this.contextMenuCallback(menu_id);
					
					rooms[cId].elementSubrooms[rId].element.appendChild(icon);
					rooms[cId].elementSubrooms[rId].element.appendChild(rooms[cId].elementSubrooms[rId].element.elementTitle);
					rooms[cId].element.elementSublist.appendChild(rooms[cId].elementSubrooms[rId].element);
				}
				rooms[cId].elementSubrooms[rId].element.className = '';				
			}
			if (rooms[cId].elementSubrooms.length <= 0)
				rooms[cId].element.elementSublist.style.display = "none";
			else
				rooms[cId].element.elementSublist.style.display = "block";
		}
		
		// iterate through rooms. delete all rooms/subrooms where
		// element.lastUpdated != timestamp_update_run
		for(var iRooms in rooms)
		{
			if (rooms[iRooms].lastUpdated != timestamp_update_run)
			{
				if (rooms[iRooms].element && rooms[iRooms].element.parentNode)
				{
					rooms[iRooms].element.parentNode.removeChild(rooms[iRooms].element);	
				}
				delete rooms[iRooms];
			}
			else
			{
				for(var iSubRooms in rooms[iRooms].elementSubrooms)
				{
					if (rooms[iRooms].elementSubrooms[iSubRooms].lastUpdated != timestamp_update_run)
					{
						if (rooms[iRooms].elementSubrooms[iSubRooms].element && rooms[iRooms].elementSubrooms[iSubRooms].element.parentNode)
						{
							rooms[iRooms].elementSubrooms[iSubRooms].element.parentNode.removeChild(rooms[iRooms].elementSubrooms[iSubRooms].element);	
						}
						delete rooms[iRooms].elementSubrooms[iSubRooms];
					}
				}
			}
		}
	}
	
	this.render = function (container)
	{
		var obj;

		if (typeof container == 'string')
			obj = document.getElementById(container);
		else if (typeof container == 'object')
			obj = container;
		
		// create elements
		div_container = document.createElement("div");
		div_title = document.createElement("div");
		span_roomcount = document.createElement("span");
		div_body = document.createElement("ul");
		div_show_hide = document.createElement("a");
		div_show_hide.href = "#";
		div_title.className = 'tblheader std chatlist_header';
		div_body.className = 'il_chat_list il_chat_list_limit_height';
		
		//div_show_hide.appendChild(document.createTextNode(chatLanguage.getTxt('hide')));
		
		var clsImg = document.createElement('img');
		clsImg.src ="templates/default/images/icon_close2_s.gif";
		clsImg.alt = chatLanguage.getTxt('hide');
		div_show_hide.appendChild(clsImg);
		
		div_show_hide.onclick = me.hide;
		div_show_hide.className = 'il_hide_cmd';
		div_show_hide.style.cssFloat = "right";
		div_show_hide.style.styleFloat = "right";
		div_show_hide.style.lineHeight = "100%";
		
		div_title_count_container = document.createElement("div");
		div_title_count_container.appendChild(document.createTextNode(title));
		div_title_count_container.appendChild(span_roomcount);
		
		div_title_count_container.style.width = "70%";
		div_title_count_container.style.cssFloat = "left";
		div_title_count_container.style.styleFloat = "left";

		//div_show_hide.style.width = "20%";
		
		div_title.appendChild(div_title_count_container);
		div_title.appendChild(div_show_hide);
		
		span_roomcount.innerHTML = " (" + countRooms() + ")";
		div_container.appendChild(div_title);
		div_container.appendChild(div_body);
		div_container.setAttribute("id", "room_list_container");
		obj.appendChild(div_container);
	}
	
	this.hide = function ()
	{
		div_body.style.display = "none";
		div_show_hide.innerHTML = '<img src="templates/default/images/icon_open2_s.gif" alt="'+chatLanguage.getTxt('show');+'"/>';
		div_show_hide.onclick = me.show;
	};

	this.show = function ()
	{
		div_body.style.display = "block";
		div_show_hide.innerHTML = '<img src="templates/default/images/icon_close2_s.gif" alt="'+chatLanguage.getTxt('hide');+'"/>';
		div_show_hide.onclick = me.hide;
	};
};


