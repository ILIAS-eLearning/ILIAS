var ilHForm =
{
	drag_contents: [],

	disableDragContents: function()
	{
		var i;
		for (i in this.drag_contents)
		{
			this.drag_contents[i].locked = true;
		}
	},

	enableDragContents: function()
	{
		var i;
		for (i in this.drag_contents)
		{
			this.drag_contents[i].locked = false;
		}
	}
}

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
var oldclass = [];
var dragdropongoing = false;

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
	var t = YAHOO.util.Event.getXY(e);
	return t[0];



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
	var t = YAHOO.util.Event.getXY(e);
	return t[1];


	if (e.pageY)
	{
		return e.pageY;
	}
	else if (document.documentElement)
	{
		return e.clientY + ilGetWinPageYOffset();
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
	if (dragdropongoing) return;
	
	if(stopHigh) return;
	stopHigh=true;
	overId = id;
	setTimeout("stopHigh=false",10);
	obj = document.getElementById(id);
//	oldclass[id] = obj.className;
	//obj.className = mclass;
	current_mouse_over_id = id;
}

/**
* On mouse out: Set style class of element id to class
*/
function doMouseOut(id, mclass)
{
	if (dragdropongoing) return;
	if (id!=overId) return;
	stopHigh = false;
	obj = document.getElementById(id);
//	if (oldclass[id])
//	{
//		obj.className = oldclass[id];
//	}
//	else
//	{
	//	obj.className = mclass;
//	}
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
YAHOO.util.Dom.setXY(obj, [x,y], true);
return;


/* alert("x:" + x + " y:" + y); */
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

function M_in(cell)  {
    cell.style.cursor='pointer';
    $(cell).css("background-color", "#C0C0FF");
	
	if (cell.id.substr(0, 5) == "hftd_")
	{
//alert("real id:" + cell.id);

		var t = cell.id.substr(5);
		var tp = t.indexOf("_");
		var cid = t.substr(0, tp);
		var cnum = t.substr(tp+1);

//alert("id:" + cid + ", num:" + cnum);

		if (cnum == "0")
		{
			var sec = document.getElementById("hftd_" + cid + "_1");
			if (sec)
			{
				sec.bgColor='#C0C0FF';
			}
		}
		if (parseInt(cnum) > 0)
		{
			var sec = document.getElementById("hftd_" + cid + "_0");
			if (sec)
			{
				sec.bgColor='#C0C0FF';
			}
		}
	}
    doCloseContextMenuCounter=-1;
}

function M_out(cell) 
{
    $(cell).css("background-color", "");
	if (cell.id.substr(0, 5) == "hftd_")
	{
		var t = cell.id.substr(5);
		var tp = t.indexOf("_");
		var cid = t.substr(0, tp);
		var cnum = t.substr(tp+1);

		if (cnum == "0")
		{
			var sec = document.getElementById("hftd_" + cid + "_1");
			if (sec)
			{
				sec.bgColor='';
			}
		}
		if (parseInt(cnum) > 0)
		{
			var sec = document.getElementById("hftd_" + cid + "_0");
			if (sec)
			{
				sec.bgColor='';
			}
		}
	}
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
	// autoexpand node on first child operation

	if (first_child == 1)
	{
		var hform_ex = document.getElementById("il_hform_expand");
		hform_ex.value = node;
	}
//alert("-" + cmd + "-" + node + "-" + first_child + "-" + multi + "-");
	doCloseContextMenuCounter = 2;
	obj.submit();
}


function proceedDragDrop(source_id, target_id, first_child_drop_area, as_subitem)    
{
	var el = document.getElementById('ilsaving');
	el.style.display = '';
	ilHForm.disableDragContents();

	var obj = document.getElementById("form_hform");
	var hform_cmd = document.getElementById("il_hform_cmd");
	hform_cmd.value = "1";
	hform_cmd.name = "cmd[proceedDragDrop]";
	var hform_source_id = document.getElementById("il_hform_source_id");
	hform_source_id.value = source_id;
	var hform_target_id = document.getElementById("il_hform_target_id");
	hform_target_id.value = target_id;
	if (first_child_drop_area)
	{
		var hform_fc = document.getElementById("il_hform_fc");
		hform_fc.value = 1;
	}
	if (as_subitem)
	{
		var hform_as_subitem = document.getElementById("il_hform_as_subitem");
		hform_as_subitem.value = 1;
	}
	doCloseContextMenuCounter = 2;
	obj.submit();
}

function doDisam(as_subitem)
{
//alert(as_subitem);
	if (as_subitem == "1")
	{
		proceedDragDrop(cur_source_id,cur_target_id, cur_fc, true);
	}
	else
	{
		proceedDragDrop(cur_source_id,cur_target_id, cur_fc, false);
	}
}

// determine drag group
// (this function may be a "weak point". YUI documentation currently
// does not really tell us how to determine the group of the current
// drag/drop event.)
function ilDetermineDragGroup(drag_obj)
{
	if (drag_obj.groups)
	{
		for (var k in drag_obj.groups)
		{
			if (drag_obj.groups[k])
			{
				return k;
			}
		}
	}

	return '';
}


// This will be our extended DDProxy object
ilDragContent = function(id, sGroup, config)
{
    this.swapInit(id, sGroup, config);
	this.isTarget = false;
	this.initDragContent(id, sGroup, config);
};

// We are extending DDProxy now
YAHOO.extend(ilDragContent, YAHOO.util.DDProxy,
{
	initDragContent: function(id, sGroup, config)
	{
		this.originalStyles = [];
		ilHForm.drag_contents.push(this);
	},
	
	startDrag: function(x, y)
	{
		var targets = YAHOO.util.DDM.getRelated(this, true);

		for (var i=0; i<targets.length; i++)
		{
			var targetEl = this.getTargetDomRef(targets[i]);
			if (targetEl != null)
			{
				if (!this.originalStyles[targetEl.id])
				{
					this.originalStyles[targetEl.id] = targetEl.className;
				}
				$(targetEl).addClass("ilCOPGDropActice");
			}
		}
	},
	
	getTargetDomRef: function(oDD)
	{ 
		return oDD.getEl();  
	},
	
	endDrag: function(e)
	{
		this.resetTargets(); 
	}, 
	
	resetTargets: function()
	{
		// reset the target styles 
		var targets = YAHOO.util.DDM.getRelated(this, true);

		for (var i=0; i<targets.length; i++)
		{
			var targetEl = this.getTargetDomRef(targets[i]);
			if (targetEl)
			{
				var oldStyle = this.originalStyles[targetEl.id]; 
				if (oldStyle)
				{
					$(targetEl).removeClass("ilCOPGDropActice");
				}
			}
		}
	}
	
}
);



// protype: all instances will get this functions
ilDragContent.prototype.swapInit = function(id, sGroup, config)
{
    if (!id) { return; }
	this.init(id, sGroup, config);	// important!
	this.initFrame();				// important!
};

// overwriting onDragDrop function
// (ending a valid drag drop operation)
var cur_source_id;
var cur_target_id;
var cur_fc;
ilDragContent.prototype.onDragDrop = function(e, id)
{
	target_id = id.substr(9);
	source_id = this.id.substr(7);
	//target_id = id;
	//source_id = this.id;
	if (source_id != target_id)
	{
		//ilFormSend("moveAfter", source_id, target_id);
//		alert("Move " + source_id + " after " + target_id + "." + ilDetermineDragGroup(this) + "." + this.groups.grp_st + ".");
	}

	
	// do we need to disambiguate here?
	var dmenu_id = "diss_menu_" + target_id + "_" + ilDetermineDragGroup(this);
	var dmenu = document.getElementById(dmenu_id);

	if (target_id.substr(target_id.length - 2) == "fc")
	{
		target_id = target_id.substr(0, target_id.length - 2);
		var first_child_drop_area = true;
	}
	else
	{
		var first_child_drop_area = false;
	}

	if (dmenu)
	{
		if(menuBlocked || mouseUpBlocked) return;
		menuBlocked = true;
		setTimeout("nextMenuClick()", 100);
	
		if (!e) var e = window.event;

		Mposx = ilGetMouseX(e);
		Mposy = ilGetMouseY(e);

		openedMenu = dmenu_id;
		showMenu(dmenu_id, Mposx, Mposy-10);
		doCloseContextMenuCounter = 20;
		cur_source_id = source_id;
		cur_target_id = target_id;
		cur_fc = first_child_drop_area;
	}
	else
	{
		if (as_subitem[target_id + "_" + ilDetermineDragGroup(this)] == "1")
		{
			var as = true;
		}
		else
		{
			var as = false;
		}

		proceedDragDrop(source_id, target_id, first_child_drop_area, as);
	}
	
};


// overwriting onDragDrop function
ilDragContent.prototype.onDragEnter = function(e, id)
{
	target_id = id.substr(6);
	source_id = this.id.substr(7);
	if (source_id != target_id)
	{
		d_target = document.getElementById(id);
		oldclass[id] = d_target.className;
		$(d_target).addClass("ilCOPGDropActice");
	}
	dragdropongoing = true;
};

// overwriting onDragDrop function
ilDragContent.prototype.onDragOut = function(e, id)
{
	d_target = document.getElementById(id);
	$(d_target).addClass("ilCOPGDropActice");
	dragdropongoing = false;
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
YAHOO.extend(ilDragTarget, YAHOO.util.DDTarget);

// protype: all instances will get this functions
ilDragTarget.prototype.dInit = function(id, sGroup, config)
{
    if (!id) { return; }
	this.init(id, sGroup, config);	// important!
	//this.initFrame();				// important!
};
