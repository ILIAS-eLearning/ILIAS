function ilChatUserList(target, type, bodyid) {
	var me = this;
	
	this.title = "";
	this.hide_label = "";
	this.empty_message = "";
	this.target = target;
	this.type = type;
	this.bodyid = bodyid;
	
	this.users = new Array();
	this.data = new Array();
	
	this.body = false;
	this.head = false;
	
	this.setTitle = function(str) { me.title = str; }
	this.setHideLabel = function(str) { me.hide_label = str; }
	this.setEmptyMessage = function(str) { me.empty_message = str; }
	this.addUser = function(user) {	this.users.push(user); }
	
	this.build = function() {
		var obj = document.getElementById(me.target);
		
		if (obj !== undefined) {
			var body_display = 'block';

			if (me.body) {
				body_display = me.body.style.display;
			}
			
			if(me.type == 'userlist') {
				me.head = me.getHeadBox(me.title + " " + "[" + me.users.length + "]", body_display);
				me.body = me.getBodyBoxUsers();
			}
			else if (me.type == 'roomlist') {
				me.head = me.getHeadBox(me.title + " " + "[" + me.countRooms() + "]", body_display);
				me.body = me.getBodyBoxRooms();
			}

			if (me.bodyid && me.body)
				me.body.id = me.bodyid;

			while(node = obj.firstChild) {
				obj.removeChild(node);
			}
			
			obj.appendChild(me.head);
			obj.appendChild(me.body);
			
			me.body.style.display = body_display;
		}
	}
	
	this.getHeadBox = function(title, body_display) {
		var d1 = document.createElement('div');
		d1.className="tblheader std chatlist_header";
		
		var d11 = document.createElement('div');
		d11.style.cssFloat = "left";
		d11.appendChild(document.createTextNode(
				title
		));
		
		var d12 = document.createElement('div');
		var d12a = document.createElement('a');
		d12.style.cssFloat = "right";
		
		d12a.className = "il_ContainerItemCommand";
		d12a.href = "#";
		
		var lbl = body_display == 'block' ? chatLanguage.getTxt('hide') : chatLanguage.getTxt('show');
		
		d12a.onclick = function() {
			if (me.body.style.display == 'block') 
				me.body.style.display = 'none';
			else
				me.body.style.display = 'block';
		};
		
		d12a.appendChild(document.createTextNode(lbl));
		
		d12.appendChild(d12a);
		
		d1.appendChild(d11);
		d1.appendChild(d12);
		
		return d1;
	}
	
	this.getContextOpenAction = function (icon, title, image, men, size) {
		return function() {
			if (!men)
				return false;
			ilCreateChatContextMenu(image);
			contextMenu.setSize(size);
			contextMenu.setTitle(title);
			var x = 0;
			for(x = 0; x < men.length; x++) {
				contextMenu.addItem(
					//men[x][0],
					icon,
					men[x][1],
					men[x][2],
					men[x][3]
				);
			}
			contextMenu.show();
		}
		
	}
	
	this.getDefaultAction = function (men) {
		return function() {
			eval(men[0][2]);
			return false;
		}
	}
	
	this.getBodyBoxUsers = function() {
		var ul = document.createElement("ul");
	
		ul.className = "il_chat_list";
		
		if (me.users.length == 0) {
			var li = document.createElement("li");
			li.className = "smallred";
			li.appendChild(document.createTextNode(me.empty_message));
			
			ul.appendChild(li);
		}
		else {
			
			if (me.users.length >= 5)
				ul.className += " il_chat_list_limit_height";
			
			var i = 0;
			for(i = 0; i < me.users.length; i++) {
				var u = me.users[i];

				var li = document.createElement("li");
				li.className = "navigation small";
				
				var img = document.createElement("img");
				
				if (u.id == il_chat_async_handler.address_user_id)
					img.style.backgroundColor = "orange";
				else if (u.id == il_chat_async_handler.whisper_user_id)
					img.style.backgroundColor = "green";
				
				img.className = "list_icon";
				img.src = u.ico;
				img.style.cssFloat = "left";

				var men = u.menu;
				
				img.onclick = me.getContextOpenAction(img, u.display_name, u.uimage, men, 'large');
				
				var a = document.createElement("a");
				a.href="#";
				a.target="_top";
				a.onclick = me.getDefaultAction(men);
				a.style.display = 'inline';
				
				a.appendChild(document.createTextNode(u.display_name));
				
				li.appendChild(img);
				li.appendChild(a);
				
				ul.appendChild(li);
			}
		}
		
		return ul;
	}
	
	
	this.countRooms = function() {
		var lines = 0;
		if (me.data.currentRoom) {
			lines++;
			if(me.data.currentRoom.subrooms) {
				lines += me.data.currentRoom.subrooms.length;
			}
		}
		if (me.data.rooms) {
			for (i = 0; i < me.data.rooms.length; i++) {
				lines++;
				if (me.data.rooms[i].subrooms && me.data.rooms[i].subrooms.length) {
					lines += me.data.rooms[i].subrooms.length;
				}
				
			}
		}
		return lines;
	}
	
	this.getBodyBoxRooms = function() {
		var ul = document.createElement("ul");
		ul.id = "myID";
		ul.className = "il_chat_list";
		var i = 0;
		var x = 0;
		
		// calculate visible room lines
		var lines = me.countRooms();
		
		if (lines >= 5)
			ul.className += " il_chat_list_limit_height";
		
		if (me.data.currentRoom) {
			var cur = me.data.currentRoom;
			var li = me.getBodyBoxRoomLine(cur, 'list_icon');
			
			li.className += " tblactive";
			ul.appendChild(li);

			if (cur.subrooms) {
				for(i = 0; i < cur.subrooms.length; i++) {
					var li = me.getBodyBoxRoomLine(cur.subrooms[i], 'sublist_icon');
					li.className += " tblactive";
					ul.appendChild(li);				
				}
			}
		}

		if (me.data.rooms) {
			for (i = 0; i < me.data.rooms.length; i++) {
				var cur = me.data.rooms[i];
				var li = me.getBodyBoxRoomLine(cur, 'list_icon');
				ul.appendChild(li);
				
				var x = 0;
				for(x = 0; x < cur.subrooms.length; x++) {
					var li = me.getBodyBoxRoomLine(cur.subrooms[x], 'sublist_icon');
					ul.appendChild(li);				
				}			
			}
		}
		
		return ul;
	}
	
	this.getBodyBoxRoomLine = function(room, cls) {
		var u =room;

		var li = document.createElement("li");
		li.className = "navigation small";
		
		if (room.act == true) {
			li.className += " tblrowmarked";
			il_chat_async_handler.setPageTitle(room.title);
		}
		
		var img = document.createElement("img");
		img.className = cls;
		img.src = u.ico;
		img.style.cssFloat = "left";

		var men = u.menu;
		
		img.onclick = me.getContextOpenAction(img, u.title, '', men, 'small');

		var a = document.createElement("a");
		a.href="#";
		a.target="_top";
		a.onclick = me.getDefaultAction(men);
		a.style.display = "inline";
		
		if (u.recording == true) {
			a.appendChild(document.createTextNode(u.title + " (" + u.users_online + ") " +  chatLanguage.getTxt('recording')));	
		}
		else {
			a.appendChild(document.createTextNode(u.title + " (" + u.users_online + ")"));	
		}

		li.appendChild(img);
		li.appendChild(a);
		
		return li;
	}
}
