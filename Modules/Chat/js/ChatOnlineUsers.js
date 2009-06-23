function ilChatOnlineUsers()
{
	var title = chatLanguage.getTxt('online_users_title');
	var users = [];
	var me = this;
	var usercount;
	
	// containers
	var div_title;
	var div_body;
	var div_container;
	var span_usercount;
	var div_show_hide;
	
	function setCount(count, invis)
	{
		usercount = count;
		if (span_usercount)
		{
			var invisible_text = "";
			if (invis)
				invisible_text = ", " + invis + " " + chatLanguage.getTxt('hidden');
				
			span_usercount.innerHTML = ' (' + usercount + " " + chatLanguage.getTxt('visible') + invisible_text + ')';
		}
	}
	
	this.setTitle = function (str) { title = str };

	// scope should be a user element
	this.createContextMenu = function()
	{
		var menuconfig = [];
		var user_id = this.id;
		var user_display_name = this.dn;
		var user_login = this.login;

		//check for invite permission
		if (this.pmi == true)
		{
			menuconfig.push(
				{ text : chatLanguage.getTxt('invite'), onclick : { fn : function() {il_chat_async_handler.invite(user_id);return false;}}}
			);
		}
		
		//check for disinvite permission
		if (this.pmdi == true)
		{
			menuconfig.push(
				{ text : chatLanguage.getTxt('disinvite'), onclick : { fn : function() {il_chat_async_handler.disinvite(user_id); return false;}}}
			);
		}
		
		//check for public profile
		if (this.pp == true)
		{
			menuconfig.push
			(
				{ text : chatLanguage.getTxt('profile'), onclick : { fn : function() {il_chat_async_handler.showProfile(user_id); return false;}}}
			);
		}
		
		if (true || this.permission_add_addressbook == true)
		{
			menuconfig.push
			(
				{ text : chatLanguage.getTxt('add_to_addressbook'), onclick : { fn : function() {il_chat_async_handler.addUserToAddressbook(user_login); return false;}}}
			);
		}
		
		var menu_id = 'on' + user_id;
		
		var conf = { id : menu_id, items : menuconfig, titles : [this.dn]};
		
		if (this.img)
		{
			conf.additional = document.createElement('div');
			conf.additional.innerHTML = '<img src=\''+this.img+'\' />';
		}
		
		chatmenu.addMenuConfiguration(conf);
		// permission_invite
		if (this.pmi)
			this.element.elementTitle.onclick = function() {il_chat_async_handler.invite(user_id);return false;};
		
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
		img.src = "templates/default/images/icon_usr.gif";
		return img;
	}
	
	this.update = function (in_users)
	{
		var visible_users = in_users.users;
		// perform update... check currently displayed users
		// for delete or update information
		// count users
		setCount(visible_users.length, in_users.hidden_count);
		
		// used to determine if user is still available (has been updated
		// during current run)
		var timestamp_update_run = new Date().getTime();
		
		// update users
		current_index = 0;
		// current_users

		for (var uId in visible_users)
		{
			if (users[uId])
			{
				for(var i in visible_users[uId])
				{
					users[uId][i] = visible_users[uId][i];	
				}
				users[uId].lastUpdated = timestamp_update_run;
				// display_name
				users[uId].element.elementTitle.innerHTML = users[uId].dn;
				this.createContextMenu.call(users[uId]);
			}
			else
			{
				users[uId] = visible_users[uId];
				users[uId].element = document.createElement("li");
				users[uId].element.elementTitle = document.createElement("span");
				//display_name
				users[uId].element.elementTitle.innerHTML = users[uId].dn;
				users[uId].lastUpdated = timestamp_update_run;
							
				var icon = this.getIcon();
				var menu_id = this.createContextMenu.call(users[uId]);

				icon.onclick = this.contextMenuCallback(menu_id);

				users[uId].element.appendChild(icon);
				users[uId].element.appendChild(users[uId].element.elementTitle);
				div_body.appendChild(users[uId].element);
			}
			users[uId].element.className = 'enter_class';
			
			// check if room is listed in correct order, else switch to
			// position
			if (div_body.childNodes[current_index] != users[uId].element)
			{
				if (div_body.childNodes[current_index + 1])
				{
					div_body.removeChild(users[uId].element);
					div_body.insertBefore(users[uId].element, div_body.childNodes[current_index]);
				}
			}
			current_index++;
		}
		
		// iterate through rooms. delete all rooms/subrooms where
		// element.lastUpdated != timestamp_update_run
		for(var iUsers in users)
		{
			if (users[iUsers].lastUpdated != timestamp_update_run)
			{
				if (users[iUsers].element && users[iUsers].element.parentNode)
				{
					users[iUsers].element.parentNode.removeChild(users[iUsers].element);	
				}
				delete users[iUsers];
			}
		}
		
		if (users.length > 5)
		{
			div_body.className = 'il_chat_list il_chat_list_limit_height';
		}
		else
		{
			div_body.className = 'il_chat_list';	
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
		span_usercount = document.createElement("span");
		div_body = document.createElement("ul");
		div_show_hide = document.createElement("a");
		div_show_hide.href = "#";
		div_title.className = 'tblheader std chatlist_header';
		div_body.className = 'il_chat_list';
		
		div_show_hide.appendChild(document.createTextNode(chatLanguage.getTxt('hide')));
		div_show_hide.onclick = me.hide;
		div_show_hide.className = 'il_ContainerItemCommand';
		div_show_hide.style.cssFloat = "right";
		div_show_hide.style.styleFloat = "right";
		div_show_hide.style.lineHeight = "100%";
		
		div_title_count_container = document.createElement("div");
		div_title_count_container.appendChild(document.createTextNode(title));
		div_title_count_container.appendChild(span_usercount);
		
		div_title_count_container.style.width = "70%";
		div_title_count_container.style.cssFloat = "left";
		div_title_count_container.style.styleFloat = "left";

		//div_show_hide.style.width = "20%";
		
		div_title.appendChild(div_title_count_container);
		div_title.appendChild(div_show_hide);
		
		span_usercount.innerHTML = " (" + users.length + ")";
		div_container.appendChild(div_title);
		div_container.appendChild(div_body);
		div_container.setAttribute("id", "users_room_list_container");
		obj.appendChild(div_container);
	}
	
	this.hide = function ()
	{
		div_body.style.display = "none";
		div_show_hide.innerHTML = chatLanguage.getTxt('show');
		div_show_hide.onclick = me.show;
	};

	this.show = function ()
	{
		div_body.style.display = "block";
		div_show_hide.innerHTML = chatLanguage.getTxt('hide');
		div_show_hide.onclick = me.hide;
	};	
};

