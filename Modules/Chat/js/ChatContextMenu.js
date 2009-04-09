var contextMenu;

var mouse_x;
var mouse_y;

document.onmousemove = function(e) {
	if (!e) {
		e = window.event;
	}
	mouse_x = e.clientX;
	mouse_y = e.clientY;
}

var ilChatContextMenuHideTimeout;

function ilCreateChatContextMenu(showPicture) {
	if (!contextMenu) {
		contextMenu = new ilChatContextMenu();
	}
	else {
		contextMenu.clear();
	}

	var obj = contextMenu.getObj();
	
	if (showPicture) {
		contextMenu.setUserImage(showPicture);
	}
	
	obj.onmouseout = function (e) {
		ilChatContextMenuHideTimeout = window.setTimeout("contextMenu.hide()", 3000);
	}

	obj.onmouseover = function (e) {
		if (ilChatContextMenuHideTimeout) {
			window.clearTimeout(ilChatContextMenuHideTimeout);
		}
	}
	
}

function ilChatContextMenu() {

	var context_menu_id = "il_chat_context";
	var me = this;
	var closeLabel = 'close';
	
	this.items = new Array();
	
	var IE = document.all ? true : false;

	this.get = function(id) {
		return document.getElementById(id);
	}
	
	this.clear = function () {
		var obj = me.get('il_chat_context_menuitems');
		while (tmp = obj.firstChild) {
			obj.removeChild(tmp);
		}

		obj = me.get('il_chat_context_userimage');
		while (tmp = obj.firstChild) {
			obj.removeChild(tmp);
		}
		obj.style.display = "none";

		obj = me.get('il_chat_context_title');
		while (tmp = obj.firstChild) {
			obj.removeChild(tmp);
		}		
	}

	this.addCloseButton = function(str) {
		var obj = me.get('il_chat_context_menuitems');

		var d = document.createElement("a");
		d.appendChild(document.createTextNode(str));
		d.onclick = function (e) {
			me.hide();
			return false;
		}
		d.href="#";
		//obj.appendChild(d);
	}

	this.setUserImage = function (path) {
		var img = document.createElement("img");

		img.src = path;

		me.get('il_chat_context_userimage').style.display = "block";
		me.get('il_chat_context_userimage').style.cssFloat = "left";
		me.get('il_chat_context_userimage').appendChild(img);
	}

	this.setTitle = function (str) {
		var obj = me.get("il_chat_context_title");
		var d = document.createElement("div");
		d.appendChild(document.createTextNode(str));
		obj.appendChild(d);
	}
	
	this.addItem = function (icon, text, link, target) {
		var obj = me.get('il_chat_context_menuitems');
		var d = document.createElement("div");
		//var img = document.createElement("img");
		var label = document.createElement("span");
		var lnk = document.createElement("a");

		this.items.push(new Array(icon, text, link, target));
		
		d.className = "il_context_menu_item";
		
		d.style.width = "100%";
		//img.style.width = "20px";
		//img.src=icon;
		lnk.setAttribute("href", '#');
		lnk.setAttribute("target", target);
		lnk.onclick = function(o) {eval(link);me.hide();return false;}

		label.appendChild(document.createTextNode(text));
		lnk.appendChild(label);

		//if (icon)
		//	d.appendChild(img);
		
		d.appendChild(lnk);
		obj.appendChild(d);
	}
	
	this.defaultAction = function () {
		if (this.items[0]) {
			eval(this.items[0][2]);
			return false;
		}
	}
	
	this.getObj = function () {
		return document.getElementById(context_menu_id);
	}

	this.show = function() {
		var obj = me.getObj();
		
		var scrOfX = 0, scrOfY = 0;
	  	if( typeof( window.pageYOffset ) == 'number' ) {
		  	//NS
		  	scrOfY = window.pageYOffset;
	    	scrOfX = window.pageXOffset;
	  	}
	  	else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		  	//DOM
		  	scrOfY = document.body.scrollTop;
	    	scrOfX = document.body.scrollLeft;
	  	}
	  	else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		  	//IE6
		  	scrOfY = document.documentElement.scrollTop;
	    	scrOfX = document.documentElement.scrollLeft;
	  	}
		
		obj.style.display = "block";
		obj.style.left = (mouse_x - 3 + scrOfX ) + "px";
		obj.style.top = (mouse_y - 3 + scrOfY) + "px";
		
	}

	this.hide = function() {
		me.getObj().style.display = "none";
	}

	this.setSize = function (s) {
		me.getObj().className = "small il_chat_context_" + s;
	}
}