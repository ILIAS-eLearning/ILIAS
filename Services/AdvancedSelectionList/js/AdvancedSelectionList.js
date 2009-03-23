ilAddOnLoad(ilInitAdvSelectionLists);
var il_adv_sel_lists = new Array();
var openedMenu="";					// menu currently opened

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

/** 
* Init selection lists
*/
function ilInitAdvSelectionLists()
{
	for (var i = 0; i < il_adv_sel_lists.length; ++i)
	{
		id = il_adv_sel_lists[i];

		// hide non-js section
		obj = document.getElementById('ilAdvSelListNoJS_' + id);
		if (obj)
			obj.style.display='none';
		
		// show js section
		obj = document.getElementById('ilAdvSelListJS_' + id);
		if (obj)
			obj.style.display='block';
	
		// show placeholder
		obj = document.getElementById('ilAdvSelListPH_' + id);
		if (obj)
			obj.style.display='block';
	}
}

function absTop(el) {
return (el.offsetParent)?
el.offsetTop+absTop(el.offsetParent) : el.offsetTop;
}

function absLeft(el) {
	left = eval(el).offsetLeft;
	op = eval(el).offsetParent;
  	while (op != null) {
  		left += op.offsetLeft;
  		op = op.offsetParent;
  	}
	return left;
}

var menuBlocked = false;
function nextMenuClick() {
	menuBlocked = false;
}

/**
* Show selection list
*/
function ilAdvSelListOn(id)
{
	doCloseContextMenuCounter=-1;

	if (openedMenu == id)
	{
		return;
	}
	if(menuBlocked) return;
	menuBlocked = true;
	setTimeout("nextMenuClick()",100);
	
	var nextMenu = id;
	
	if (openedMenu != "" || openedMenu == nextMenu) 
	{
		ilHideAdvSelList(openedMenu);
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
		ilShowAdvSelList(openedMenu);
	}
	doCloseContextMenuCounter = 20;

}

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
				ilHideAdvSelList(openedMenu);
				openedMenu = "";
				oldOpenedMenu = "";
			}
			doCloseContextMenuCounter=-1;
		}
	}
	setTimeout("doCloseContextMenu()",100);
}
setTimeout("doCloseContextMenu()",200);

function ilShowAdvSelList(id)
{
	obj = document.getElementById('ilAdvSelListTable_' + id);
	obj2 = document.getElementById('ilAdvSelListTableInner_' + id);
	anchor = document.getElementById('ilAdvSelListAnchorElement_' + id);
	
	var wih = ilGetWinInnerHeight();
	var yoff = ilGetWinPageYOffset();
	
/*alert("anchor.offsetLeft:" + anchor.offsetLeft
	);*/

	//obj.style.left = anchor.offsetLeft + 'px';
	obj.style.left = (absLeft(anchor) + 2) + 'px';
	obj.style.top = ((absTop(anchor) + anchor.offsetHeight) + 2) + 'px';
	obj.style.display='';
	
	var top = ilGetOffsetTop(obj);
	
	// make it smaller, if window height is not sufficient
	if (wih < obj.offsetHeight + 20)
	{
		newHeight = wih - 20;
		if (newHeight < 150)
		{
			newHeight = 150;
		}
		obj.style.height = newHeight + "px";
		obj2.style.height = newHeight + "px";
		//obj.style.width = obj.offsetWidth + 20 + "px";
		obj2.style.width = obj2.offsetWidth + 20 + "px";
//alert("Too small wih: " + wih + ", obj height: " + obj.offsetHeight);
	}
	
	// if too low: show it higher
	if (top + (obj.offsetHeight + 10) > wih + yoff)
	{
		obj.style.top = (wih + yoff - (obj.offsetHeight + 10)) + "px";
	}

	var wiw = getBodyWidth();
	// if too far on the right: show it more left
	if ((absLeft(obj) + (obj.offsetWidth + 10)) > wiw)
	{
/*alert ("absleft: " + absLeft(obj)
		+ "\n width: " + obj.offsetWidth
		+ "\n window width: " + wiw
	);*/
		obj.style.left = (wiw - (obj.offsetWidth + 10)) + "px";
	}

}

/**
* Hide selection list
*/
function ilAdvSelListOff(id)
{
	doCloseContextMenuCounter=5;
//	obj = document.getElementById('ilAdvSelListTable_' + id);
//	obj.style.display='none';
}

/**
* Hide selection list
*/
function ilHideAdvSelList(id)
{
	obj = document.getElementById('ilAdvSelListTable_' + id);
	obj.style.display='none';
}

function ilAdvSelItemOn(obj)
{
	obj.className = "il_adv_sel_act";
}

function ilAdvSelItemOff(obj)
{
	obj.className = "il_adv_sel";
}

function ilShowAdvSelListAnchor(id)
{
	anchor = document.getElementById(id);
	anchor.style.display='';
}

function ilAdvSelListFormSubmit(id, hid_name, hid_val, form_id, cmd)
{
	ilAdvSelSetHiddenInput(id, hid_name, hid_val);
	form_el = document.getElementById(form_id);
	hidden_cmd_el = document.getElementById("ilAdvSelListHiddenCmd_" + id);
	hidden_cmd_el.name = 'cmd[' + cmd + ']';
	hidden_cmd_el.value = '1';
	form_el.submit();
}

function ilAdvSelListFormSelect(id, hid_name, hid_val, title)
{
	ilAdvSelSetHiddenInput(id, hid_name, hid_val);
	anchor_text = document.getElementById("ilAdvSelListAnchorText_" + id);
	anchor_text.innerHTML = title;
	ilHideAdvSelList(id);
}

function ilAdvSelSetHiddenInput(id, hid_name, hid_val)
{
	hidden_el = document.getElementById("ilAdvSelListHidden_" + id);
	hidden_el.name = hid_name;
	hidden_el.value = hid_val;
}
