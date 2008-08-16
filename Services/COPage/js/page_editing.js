var stopHigh = false;
var Mposx = 0;
var Mposy = 0;
var sel_edit_areas = Array();
var edit_area_class = Array();
var edit_area_original_class = Array();
var openedMenu="";					// menu currently opened
var current_mouse_over_id;

document.onmousemove=followmouse1;

/**
* Get inner height of window
*/
function ilGetWinInnerHeight()
{
	if (self.innerHeight)
	{
		return self.innerHeight;
	}
	// IE 6 strict Mode
	else if (document.documentElement && document.documentElement.clientHeight)
	{
		return document.documentElement.clientHeight;
	}
	// other IE
	else if (document.body)
	{
		return document.body.clientHeight;
	}
}

function ilGetWinPageYOffset()
{
	if (typeof(window.pageYOffset ) == 'number')
	{
		return window.pageYOffset;
	}
	else if(document.body && (document.body.scrollLeft || document.body.scrollTop ))
	{
		return document.body.scrollTop;
	}
	else if(document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop))
	{
		return document.documentElement.scrollTop;
	}
	return 0;
}

function getBodyWidth()
{
	if (document.body && document.body.offsetWidth)
	{
		return document.body.offsetWidth;
	}
	else if (document.documentElement && document.documentElement.offsetWidth)
	{
		return document.documentElement.offsetWidth;
	}
	return 0;
}

function ilGetOffsetTop(el)
{
	var y = 0;
	
	if (typeof(el) == "object" && document.getElementById)
	{
		y = el.offsetTop;
		if (el.offsetParent)
		{
			y += ilGetOffsetTop(el.offsetParent);
		}
		return y;
	}
	else 
	{
		return false;
	}
}

function ilGetMouseX(e)
{
	if (e.pageX)
	{
		return e.pageX;
	}
	else if (document.documentElement)
	{
		return e.clientX + document.documentElement.scrollLeft;
	}
	if (document.body)
	{
		Mposx = e.clientX + document.body.scrollLeft;
	}
}

function ilGetMouseY(e)
{
	if (e.pageY)
	{
		return e.pageY;
	}
	else if (document.documentElement)
	{
		return e.clientY + document.documentElement.scrollTop;
	}
	if (document.body)
	{
		Mposx = e.clientY + document.body.scrollTop;
	}
}

/**
* On mouse over: Set style class of element id to class
*/
function doMouseOver (id, mclass)
{
	if(stopHigh) return;
	stopHigh=true;
	overId = id;
	setTimeout("stopHigh=false",10);
	obj = document.getElementById(id);
	edit_area_class[id] = mclass;
	if (obj.className != "il_editarea_selected")
	{
		edit_area_original_class[id] = obj.className;
	}
	if (sel_edit_areas[id])
	{
		obj.className = "il_editarea_active_selected";
	}
	else
	{
		if (obj.className == "il_editarea_disabled")
		{
			obj.className = "il_editarea_disabled_selected";
		}
		else
		{
			obj.className = mclass;
		}
	}
	
	current_mouse_over_id = id;
}

/**
* On mouse out: Set style class of element id to class
*/
function doMouseOut(id, mclass)
{
	if (id!=overId) return;
	stopHigh = false;
	obj = document.getElementById(id);
	if (sel_edit_areas[id])
	{
		obj.className = "il_editarea_selected";
	}
	else
	{
		//obj.className = mclass;
		obj.className = edit_area_original_class[id];
	}
}

function followmouse1(e) 
{
    if (!e) var e = window.event;
    
	Mposx = ilGetMouseX(e);
	Mposy = ilGetMouseY(e);
}

function showMenu(id, x, y)
{
	var obj = document.getElementById(id);

	obj.style.visibility = '';
	obj.style.left = x + 10 + "px";
	obj.style.top = y + "px";
	
	var w = Math.floor(getBodyWidth() / 2);
	
	var wih = ilGetWinInnerHeight();
	var yoff = ilGetWinPageYOffset();
	var top = ilGetOffsetTop(obj);
	
/*alert("menu.offsetTop:" + top
	+ "\nmenu.offsetHeight:" + obj.offsetHeight
	+ "\nwin.innerHeight:" + wih
	+ "\nwin.pageYOffset:" + yoff
	);*/
	if (Mposx > w)
	{
		obj.style.left = Mposx - (obj.offsetWidth + 10) + "px";
	}

	if (top + (obj.offsetHeight + 10) > wih + yoff)
	{
		obj.style.top = (wih + yoff - (obj.offsetHeight + 10)) + "px";
	}
}

function hideMenu(id)
{
	obj = document.getElementById(id);
	if (obj)
	{
		obj.style.visibility = 'hidden';
	}
}

var dragDropShow = false;
var mouseIsDown = false;
var mouseDownBlocked = false;
var mouseUpBlocked = false;

var dragId = "";
var overId = "";

function doMouseDown(id) 
{
	//dd.elements.contextmenu.hide();
	if(mouseDownBlocked) return;
	mouseDownBlocked = true;
	setTimeout("mouseDownBlocked = false;",200);
	
	obj = document.getElementById(id);
	
	if (!mouseIsDown) {
//		dragId = id;
	
		oldMposx = Mposx;
		oldMposy = Mposy;
		mouseIsDown = true;
	}
}


var cmd1 = "";
var cmd2 = "";
var cmd3 = "";
var cmd4 = "";

/*function callBeforeAfterAction(setCmd3) 
{
	cmd3 = setCmd3;
	doActionForm(cmd1, cmd2, cmd3, cmd4);
}*/


function doMouseUp(id) 
{
/*	if (dragDropShow)
	{
		if(mouseUpBlocked) return;
		mouseUpBlocked = true;
		setTimeout("mouseUpBlocked = false;",200);
		
		// mousebutton released over new object. call moveafter
		//alert(dragId+" - "+overId);
		DID = overId.substr(7);
		OID = dragId.substr(7);
		if (DID != OID) 
		{ 
			doCloseContextMenuCounter = 20;
			openedMenu = "movebeforeaftermenu";
			dd.elements.movebeforeaftermenu.moveTo(Mposx,Mposy);
			dd.elements.movebeforeaftermenu.show();
			cmd1 = 'cmd[exec_'+OID+']';
			cmd2 = 'command'+OID;
			cmd3 = 'moveAfter';
			cmd4 = DID;
			//doActionForm('cmd[exec_'+OID+']','command'+OID+'', 'moveAfter', DID);
		}
	}
*/
	dragId = "";
	mouseIsDown = false;
	dragDropShow = false;
//	dd.elements.dragdropsymbol.hide();
//	dd.elements.dragdropsymbol.moveTo(-1000,-1000);
	setTimeout("dragDropShow = false",500);
}


/**
* Init all draggable elements (YUI)
*/
function initDragElements()
{
//alert("initDragElements: inner");
	// get all spans
	obj=document.getElementsByTagName('div')
	
	// run through them
	for (var i=0;i<obj.length;i++)
	{
		// make all edit areas draggable
		if(/il_editarea/.test(obj[i].className))
		{
			d = new ilDragContent(obj[i].id, "gr1");
		}
		// make all drop areas dropable
		if(/il_droparea/.test(obj[i].className))
		{
			d = new ilDragTarget(obj[i].id, "gr1");
		}
	}
}


/**
*   on Click show context-menu at mouse-position
*/

var menuBlocked = false;
function nextMenuClick() {
	menuBlocked = false;
}

/**
* Process Single Mouse Click
*/
function doMouseClick(e, id) 
{
	if(menuBlocked || mouseUpBlocked) return;
	menuBlocked = true;
	setTimeout("nextMenuClick()",100);
	
	if (!e) var e = window.event;
	
	
	if (id.substr(0, 6) == "TARGET")
	{
		clickcmdid = id.substr(6);
		var nextMenu = "dropareamenu_" + clickcmdid;
	}
	else if (id.substr(0, 4) == "COL_")		// used in table data editor
	{
		clickcmdid = id.substr(4);
		var nextMenu = "col_menu_" + clickcmdid;
	}
	else if (id.substr(0, 4) == "ROW_")		// used in table data editor
	{
		clickcmdid = id.substr(4);
		var nextMenu = "row_menu_" + clickcmdid;
	}
	else
	{
		clickcmdid = id.substr(7);
		var nextMenu = "contextmenu_" + clickcmdid;
	}
	
	Mposx = ilGetMouseX(e);
	Mposy = ilGetMouseY(e);

	if (!dragDropShow) 
	{
		if (openedMenu != "" || openedMenu == nextMenu) 
		{
			hideMenu(openedMenu);
			//dd.elements[openedMenu].hide();
			oldOpenedMenu = openedMenu;
			openedMenu = "";
		}
		else
		{
			oldOpenedMenu = "";
		}
		
		if (openedMenu == "" && nextMenu != oldOpenedMenu)
		{
			openedMenu = nextMenu;
			showMenu(openedMenu, Mposx, Mposy-10);
		}
		doCloseContextMenuCounter = 20;
	}
}

/**
* Process Double Mouse Click
*/
function doMouseDblClick(e, id) 
{
	if (current_mouse_over_id == id)
	{
		obj = document.getElementById(id);
		if (sel_edit_areas[id])
		{
			sel_edit_areas[id] = false;
			obj.className = "il_editarea_active";
		}
		else
		{
			sel_edit_areas[id] = true;
			obj.className = "il_editarea_active_selected";
		}
	}
}

/**
*   on MouseOut of context-menu hide context-menu 
*/
var doCloseContextMenuCounter = -1;
function doCloseContextMenu() 
{
	if (doCloseContextMenuCounter>-1) 
	{
		doCloseContextMenuCounter--;
		if(doCloseContextMenuCounter==0) 
		{
			if(openedMenu!="") 
			{
				//dd.elements[openedMenu].hide();
				hideMenu(openedMenu);
				openedMenu = "";
				oldOpenedMenu = "";
			}
			doCloseContextMenuCounter=-1;
		}
	}
	setTimeout("doCloseContextMenu()",100);
}
setTimeout("doCloseContextMenu()",200);

var clickcmdid = 0;

function doActionForm(cmd, command, value, target)    
{
	
//alert("-" + cmd + "-" + command + "-" + value + "-" + target + "-");
    doCloseContextMenuCounter = 2;
    if(cmd=="cmd[exec]") 
	{
        cmd = "cmd[exec_"+clickcmdid+"]";
    }
    
    if (command=="command") 
	{
        command += clickcmdid;
    }
    
	if (value=="delete") 
	{
		if(!confirm(confirm_delete)) 
		{
			menuBlocked = true;
			setTimeout("nextMenuClick()",500);
			return;
		}
		menuBlocked = true;
		setTimeout("nextMenuClick()",500);
	}
	
	//alert(target+" - "+command+" - "+value+" - "+cmd);
	
/*
	html = "<form name=cmform id=cmform method=post action='"+actionUrl+"'>";
	html += "<input type=hidden name='target[]' value='"+target+"'>";
	html += "<input type=hidden name='"+command+"' value='"+value+"'>";
	html += "<input type=hidden name='"+cmd+"' value='Ok'>";
	html += "</form>";

	dd.elements.actionForm.write(html);
*/
	obj = document.getElementById("cmform");
	hid_target = document.getElementById("cmform_target");
	hid_target.value = target;
	hid_cmd = document.getElementById("cmform_cmd");
	hid_cmd.name = command;
	hid_cmd.value = value;
	hid_exec = document.getElementById("cmform_exec");
	hid_exec.name = cmd;
	
    obj.submit();
}

function M_in(cell) 
{
    cell.style.cursor='pointer';
    cell.bgColor='#C0C0FF';
    doCloseContextMenuCounter=-1;
}
function M_out(cell) 
{
    cell.bgColor='';
    doCloseContextMenuCounter=5;
}

var oldMposx = -1;
var oldMposy = -1;    

/*function doKeyDown(e) 
{
    if (!e) var e = window.event;
    kc = e.keyCode;
    kc = kc * 1;

    if(kc == 17) 
	{
		dd.elements.contextmenu.hide();
		oldMposx = Mposx;
		oldMposy = Mposy;
		mouseIsDown = true;
	}
}*/

/*function doKeyUp(e)
{
	if (!e) var e = window.event;
	kc = e.keyCode;
	
	kc = kc*1;
	if(kc==17) 
	{
		mouseIsDown = false;
		dd.elements.dragdropsymbol.hide();
		dd.elements.dragdropsymbol.moveTo(-1000,-1000);
		setTimeout("dragDropShow = false",500);
	}
}*/

// This will be our extended DDProxy object
ilDragContent = function(id, sGroup, config)
{
    this.swapInit(id, sGroup, config);
	this.isTarget = false;
};

// We are extending DDProxy now
YAHOO.extend(ilDragContent, YAHOO.util.DDProxy);

// protype: all instances will get this functions
ilDragContent.prototype.swapInit = function(id, sGroup, config)
{
    if (!id) { return; }
	this.init(id, sGroup, config);	// important!
	this.initFrame();				// important!
};

// overwriting onDragDrop function
// (ending a valid drag drop operation)
ilDragContent.prototype.onDragDrop = function(e, id)
{
	target_id = id.substr(6);
	source_id = this.id.substr(7);
	if (source_id != target_id)
	{
		ilFormSend("moveAfter", source_id, target_id);
	}
};


ilDragContent.prototype.endDrag = function(e)
{
};

// overwriting onDragDrop function
ilDragContent.prototype.onDragEnter = function(e, id)
{
	target_id = id.substr(6);
	source_id = this.id.substr(7);
	if (source_id != target_id)
	{
		d_target = document.getElementById(id);
		d_target.className = "il_droparea_active";
	}
};

// overwriting onDragDrop function
ilDragContent.prototype.onDragOut = function(e, id)
{
	d_target = document.getElementById(id);
	d_target.className = "il_droparea";
};

///
///   ilDragTarget
///

// This will be our extended DDProxy object
ilDragTarget = function(id, sGroup, config)
{
    this.dInit(id, sGroup, config);
};

// We are extending DDProxy now
YAHOO.extend(ilDragTarget, YAHOO.util.DDProxy);

// protype: all instances will get this functions
ilDragTarget.prototype.dInit = function(id, sGroup, config)
{
    if (!id) { return; }
	this.init(id, sGroup, config);	// important!
	this.initFrame();				// important!
};


///
/// ilFormSend
///
function ilFormSend(cmd, source_id, target_id)
{
	hid_target = document.getElementById("ajaxform_target");
	hid_target.value = target_id;
	hid_cmd = document.getElementById("ajaxform_cmd");
	hid_cmd.name = "command" + source_id;
	hid_cmd.value = cmd;
	hid_exec = document.getElementById("ajaxform_exec");
	hid_exec.name = "cmd[exec_" + source_id + "]";
    form = document.getElementById("ajaxform");
	
	var str = form.action;
	return ilCOPageJSHandler(str);
}

function ilEditMultiAction(cmd)
{
	hid_exec = document.getElementById("cmform_exec");
	hid_exec.name = "cmd[" + cmd + "]";
	hid_cmd = document.getElementById("cmform_cmd");
	hid_cmd.name = cmd;
    form = document.getElementById("cmform");
	
	var sel_ids = "";
	var delim = "";
	for (var key in sel_edit_areas)
	{
		if (sel_edit_areas[key])
		{
			sel_ids = sel_ids + delim + key.substr(7);
			delim = ";";
		}
	}

	hid_target = document.getElementById("cmform_target");
	hid_target.value = sel_ids;
	
	form.submit();
	
	return false;
}
