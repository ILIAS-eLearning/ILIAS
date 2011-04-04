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

var sel_edit_areas = Array();
var edit_area_class = Array();
var edit_area_original_class = Array();
var cmd_called = false;

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
	current_mouse_over_id = id;

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

	var typetext = document.getElementById("label_" + id);
	if (typetext)
	{
		typetext.style.display = '';
	}
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
	if (sel_edit_areas[id])
	{
		obj.className = "il_editarea_selected";
	}
	else
	{
		//obj.className = mclass;
		obj.className = edit_area_original_class[id];
	}

	var typetext = document.getElementById("label_" + id);
	if (typetext)
	{
		typetext.style.display = 'none';
	}
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

function doMouseDblClick(e, id)
{
	if (cmd_called) return;
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
    cell.bgColor='';
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

function doActionForm(cmd, node, subcmd)
{
	if(subcmd != "#editParagraph")
	{
		var obj = document.getElementById("form_hform");
		var hform_cmd = document.getElementById("il_hform_cmd");
		hform_cmd.value = "1";
		hform_cmd.name = "cmd[" + cmd + "]";
		var hform_node = document.getElementById("il_hform_node");
		hform_node.value = node;
		var hform_subcmd = document.getElementById("il_hform_subcmd");
		hform_subcmd.value = subcmd;

		doCloseContextMenuCounter = 2;
		obj.submit();
	}
	else
	{
		editParagraph(node);
	}
}

function ilEditMultiAction(cmd, subcmd)
{
	var obj = document.getElementById("form_hform");
	var hform_cmd = document.getElementById("il_hform_cmd");
	hform_cmd.value = "1";
	hform_cmd.name = "cmd[" + cmd + "]";
	var hform_subcmd = document.getElementById("il_hform_subcmd");
	hform_subcmd.value = subcmd;

	var sel_ids = "";
	var delim = "";
	for (var key in sel_edit_areas)
	{
		if (sel_edit_areas[key])
		{
			sel_ids = sel_ids + delim + key;
			delim = ";";
		}
	}

	var hform_multi = document.getElementById("il_hform_multi");
	hform_multi.value = sel_ids;

	form.submit();

	return false;
}

var tinyinit = false;
var ed_para = null;
function editParagraph(paragraph_id)
{
	ed_para = paragraph_id;
	var pdiv = document.getElementById(paragraph_id);
	var pdiv_reg = YAHOO.util.Region.getRegion(pdiv);
	
	var ta = new YAHOO.util.Element(document.createElement('textarea'));
	ta = YAHOO.util.Dom.insertAfter(ta, pdiv);
	ta.id = 'tinytarget';
	ta.className = 'par_textarea';

	var tinytarget = document.getElementById("tinytarget");
	tinytarget.style.display = '';

	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		editor_selector : "par_textarea",
		language : "en",
		plugins : "safari,save",
		fix_list_elements : true,
		theme_advanced_blockformats : tiny_formats,
		theme_advanced_toolbar_align : "left",
		theme_advanced_buttons1 : tiny_buttons1,
		theme_advanced_buttons2 : tiny_buttons2,
		theme_advanced_buttons3 : tiny_buttons3,
		theme_advanced_toolbar_location : "external",
		theme_advanced_path : true,
		theme_advanced_statusbar_location : "bottom",
		valid_elements : tiny_valid,
		remove_linebreaks : false,
		convert_newlines_to_brs : false,
		force_p_newlines : false,
		force_br_newlines : true,
		forced_root_block : '',
		content_css : "",
		save_onsavecallback : "saveParagraph",
		theme_advanced_resize_horizontal : false,
		theme_advanced_resizing : true,
		cleanup_on_startup : true,
		cleanup: true,
		setup : function(ed) {
			ed.onInit.add(function(ed, evt) {
			var tinyifr = document.getElementById("tinytarget_ifr");

			tinyifr.style.width = pdiv_reg.width + "px";
			pdiv.style.display = "none";

			ed.setProgressState(1); // Show progress
			ajaxFormSend("editJS", paragraph_id);
		  });
		}
	});

	tinyinit = true;
}

function saveParagraph()
{
	ajaxFormSend("saveJS", ed_para);
}

function ajaxFormSend(cmd, node_id)
{
	hid_cmd = document.getElementById("ajaxform_cmd");
	hid_cmd.name = "cmd[" + cmd + "]";
	hid_cmd.value = cmd;
	
	hid_node = document.getElementById("ajaxform_node");
	hid_node.value = node_id;
	
	if (cmd == 'saveJS')
	{
		hid_cont = document.getElementById("ajaxform_content");
		var ed = tinyMCE.get('tinytarget');
		hid_cont.value = ed.getContent();
		
		tinyMCE.execCommand('mceRemoveControl', false, 'tinytarget');
		var tt = document.getElementById("tinytarget");
		tt.style.display = 'none';

		var lg = new YAHOO.util.Element(document.createElement('img'));
		lg = YAHOO.util.Dom.insertAfter(lg, tt);
		lg.src = "./templates/default/images/loader.gif";
		lg.border = 0;
	}

	form = document.getElementById("ajaxform");
	return ilSurveyPageJSHandler(form.action);
}



function initDragElements()
{
//alert("initDragElements: inner");
	// get all spans
	obj=document.getElementsByTagName('div')

	// run through them
	for (var i=0;i<obj.length;i++)
	{
		// make all edit areas draggable
		if(/il_editarea_drag/.test(obj[i].className))
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


function proceedDragDrop(source_id, target_id)    
{
	var obj = document.getElementById("form_hform");
	var hform_cmd = document.getElementById("il_hform_cmd");
	hform_cmd.value = "1";
	hform_cmd.name = "cmd[renderPage]";
	var hform_cmd = document.getElementById("il_hform_subcmd");
	hform_cmd.value = "dnd";
	var hform_source_id = document.getElementById("il_hform_source_id");
	hform_source_id.value = source_id;
	var hform_target_id = document.getElementById("il_hform_target_id");
	hform_target_id.value = target_id;
	
	doCloseContextMenuCounter = 2;
	obj.submit();
}

function doDisam()
{
	proceedDragDrop(cur_source_id,cur_target_id);
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
				targetEl.className = "il_droparea_valid_target";
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
			var oldStyle = this.originalStyles[targetEl.id]; 
			if (oldStyle)
			{
				targetEl.className = oldStyle; 
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
	source_id = this.id;

	proceedDragDrop(source_id, target_id);
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
		d_target.className = "il_droparea_active";
	}
	dragdropongoing = true;
};

// overwriting onDragDrop function
ilDragContent.prototype.onDragOut = function(e, id)
{
	d_target = document.getElementById(id);
	d_target.className = "il_droparea_valid_target";
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





//
// js (ajax) content editing
//

var ilSurveyPageSuccessHandler = function(o)
{
	// perform page modification
	if(o.responseText !== undefined)
	{
		// edit load
		var ed = tinyMCE.getInstanceById('tinytarget');
		if(ed)
		{
			ed.setContent(o.responseText);
			ed.setProgressState(0); // Show progress
			tinyMCE.execCommand('mceAddControl', false, 'tinytarget');
			var e = tinyMCE.DOM.get(ed.id + '_external');
			tinyMCE.execCommand('mceFocus',false,'tinytarget');
			showToolbar('tinytarget');
		}
		// edit save
		else
		{
			window.location.reload();
		}
	}
}

var ilSurveyPageFailureHandler = function(o)
{
	alert('FailureHandler');
}

function ilSurveyPageJSHandler(sUrl)
{
	var ilSurveyPageCallback =
	{
		success: ilSurveyPageSuccessHandler,
		failure: ilSurveyPageFailureHandler
	};
	var form_str = YAHOO.util.Connect.setForm("ajaxform");
	var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, ilSurveyPageCallback);

	return false;
}

// copied from TinyMCE editor_template_src.js
function showToolbar(ed_id)
{
	var DOM = tinyMCE.DOM;
	var Event = tinyMCE.dom.Event;

	var e = DOM.get(ed_id + '_external');
	DOM.show(e);

//	DOM.hide(lastExtID);

	var f = Event.add(ed_id + '_external_close', 'click', function() {
		DOM.hide(ed_id + '_external');
		Event.remove(ed_id + '_external_close', 'click', f);
	});

	DOM.show(e);
	DOM.setStyle(e, 'top', 0 - DOM.getRect(ed_id + '_tblext').h - 1);

	// Fixes IE rendering bug
	DOM.hide(e);
	DOM.show(e);
	e.style.filter = '';

//	lastExtID = ed.id + '_external';

	e = null;
};