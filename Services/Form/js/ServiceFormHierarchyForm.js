var stopHigh;
var overId;
var current_mouse_over_id;
var menuBlocked = false;
var mouseUpBlocked = false;
var dragDropShow = false;
var openedMenu="";					// menu currently opened
var doCloseContextMenuCounter = -1;
var oldMposx = -1;
var oldMposy = -1;    


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

// unblock menu clicking
function nextMenuClick()
{
	menuBlocked = false;
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
	obj.className = mclass;
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
	obj.className = mclass;
}

/**
* Process Single Mouse Click
*/
function doMouseClick(e, id) 
{
	if(menuBlocked || mouseUpBlocked) return;
	menuBlocked = true;
	setTimeout("nextMenuClick()", 100);

	if (!e) var e = window.event;
	
	clickcmdid = id.substr(6);
	var nextMenu = "dropareamenu_" + id;
	
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

function hideMenu(id)
{
	obj = document.getElementById(id);
	if (obj)
	{
		obj.style.visibility = 'hidden';
	}
}

function showMenu(id, x, y)
{
	var obj = document.getElementById(id);
	
	if (!obj) return;
	
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

function doActionForm(cmd, node, first_child, multi)    
{
	var obj = document.getElementById("form_hform");
	var hform_cmd = document.getElementById("il_hform_cmd");
	hform_cmd.value = "1";
	hform_cmd.name = "cmd[" + cmd + "]";
	var hform_node = document.getElementById("il_hform_node");
	hform_node.value = node;
	var hform_fc = document.getElementById("il_hform_fc");
	hform_fc.value = first_child;
	var hform_multi = document.getElementById("il_hform_multi");
	hform_multi.value = multi;
//alert("-" + cmd + "-" + node + "-" + first_child + "-" + multi + "-");
	doCloseContextMenuCounter = 2;
	obj.submit();
}
