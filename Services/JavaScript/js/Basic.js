// console dummy object
if (!window.console) {
	(function() {
		var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml", "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];
	    window.console = {};
	    for (var i = 0; i < names.length; ++i)
	    	window.console[names[i]] = function(data) {}
	})();
}

// global il namespace, additional objects usually should be added to this one
il = {};

// utility functions
il.Util = {
	
	addOnLoad: function(func)
	{
		if (!document.getElementById | !document.getElementsByTagName) return;
	
		var oldonload=window.onload;
		if (typeof window.onload != 'function')
		{
			window.onload = func;
		}
		else
		{
			window.onload = function()
			{
				oldonload();
				func();
			}
		}
	},

	addOnUnload: function (func)
	{
		if (!document.getElementById | !document.getElementsByTagName) return;
		
		var oldonunload = window.onunload;
		if (typeof window.onunload != 'function')
		{
			window.onunload = func;
		}
		else
		{
			window.onunload = function()
			{
				oldonunload();
				func();
			}
		}
	},
	
	// ajax related functions
	
	ajaxReplace: function(url, el_id)
	{
		this.sendAjaxGetRequestToUrl (url, {}, {el_id: el_id, inner: false}, this.ajaxReplaceSuccess)
	},
	
	ajaxReplaceInner: function(url, el_id)
	{
		this.sendAjaxGetRequestToUrl (url, {}, {el_id: el_id, inner: true}, this.ajaxReplaceSuccess)
	},
	
	ajaxReplaceSuccess: function(o)
	{
		// perform page modification
		if(o.responseText !== undefined)
		{
			if (o.argument.inner)
			{
				$('#' + o.argument.el_id).html(o.responseText);
			}
			else
			{
				$('#' + o.argument.el_id).replaceWith(o.responseText);
			}
		}
	},
	
	sendAjaxGetRequestToUrl: function(url, par, args, succ_cb)
	{
		var cb =
		{
			success: succ_cb,
			failure: this.handleAjaxFailure,
			argument: args
		};
		for (k in par)
		{
			url = url + "&" + k + "=" + par[k];
		}
		var request = YAHOO.util.Connect.asyncRequest('GET', url, cb);
	},
	
	// FailureHandler
	handleAjaxFailure: function(o)
	{
		console.log("ilNotes.js: Ajax Failure.");
	}
}

// ILIAS Object related functions
il.Object = {
	url_redraw_ah: "",
	url_redraw_li: "",
	
	setRedrawAHUrl: function(url) {
		this.url_redraw_ah = url;
	},
	
	getRedrawAHUrl: function() {
		return this.url_redraw_ah;
	},
	
	redrawActionHeader: function() {
		var ah = document.getElementById("il_head_action");
		if (this.url_redraw_ah && ah != null)
		{
			il.Util.ajaxReplaceInner(this.url_redraw_ah, "il_head_action");
		}
	},
	
	setRedrawListItemUrl: function(url) {
		this.url_redraw_li = url;
	},
	
	getRedrawListItemUrl: function() {
		return this.url_redraw_li;
	},
	
	redrawListItem: function(ref_id) {
		var li = document.getElementById("lg_div_" + ref_id);
		if (this.url_redraw_li && li != null)
		{
			il.Util.ajaxReplace(this.url_redraw_li + "&child_ref_id=" + ref_id, "lg_div_" + ref_id);
		}
	},
	
	togglePreconditions: function(link, id, txt_show, txt_hide) {
		var li = document.getElementById("il_list_item_precondition_obl_" + id);
		if(li != null)
		{
			if(li.style.display == "none")
			{
				li.style.display = "";
				$(link).html("&raquo; "+txt_hide);
			}
			else
			{
				li.style.display = "none";
				$(link).html("&raquo; "+txt_show);
			}
		}
		li = document.getElementById("il_list_item_precondition_opt_" + id);
		if(li != null)
		{
			if(li.style.display == "none")
			{
				li.style.display = "";
				$(link).html("&raquo; "+txt_hide);
			}
			else
			{
				li.style.display = "none";
				$(link).html("&raquo; "+txt_show);
			}
		}
	}
}

/**
* Adds a function to the window onload event
*/


// The following functions have been in <skin>/functions.js before.
// @todo Revision of javascript function names and usage
function isEmpty(form, a_values, a_checks) 
{	
	feed_back = "";
	
	if (a_values != "")
	{
		if (a_values == "all")
		{
			for(var i=0;i<form.length;i++)
			{				
				if (form.elements[i].type == "text" || form.elements[i].type == "textarea")
				{
					if (form.elements[i].value == "")
						feed_back += "-> " + form.elements[i].id + "\n";
				}
			}
		}
	}
	
	if (feed_back != "") {
		alert("Please insert these data:\n\n" + feed_back);
		return false;
	}
	
	return true;
}

function printPage()
{
	window.print();
	return true;
}

// used two times in notes and sessions
function CheckAll()
{
	if(document.cmd.all)
	{
		var c = document.cmd.all.checked;
	}
	for (var i=0;i<document.cmd.elements.length;i++)
	{
		var e = document.cmd.elements[i];
 	  	if(e.name != 'all') e.checked = c;
   	}
}



function setCheckedTest(e)
{
	return true;
}

// used in course items, frm wizard, scorm track items, session member row
// svy constraints, tst maintentance, tst marks, container list block, copy wizard
// paste into multi explorer, table, table2, user export
/**
 * Checks/unchecks checkboxes
 *
 * @param   string   the form name
 * @param   string   the checkbox name (or the first characters of the name, if unique)
 * @param   boolean  whether to check or to uncheck the element
 * @return  boolean  always true
 */
function setChecked(parent_el, checkbox_name, do_check){
	var e = document.forms[parent_el];
	if (!e)
	{
		e = document.getElementById(parent_el);
	}
	ilCheckBoxName = checkbox_name;
	els = YAHOO.util.Dom.getElementsBy(setCheckedTest, "input", e, null, null, null); 
	for (var i=0;i<els.length;i++)
	{
		if ((typeof els[i].name != 'undefined') && els[i].name.indexOf(checkbox_name) == 0
			&& els[i].disabled != true)
		{
			els[i].checked = do_check;
		}
	}
  return true;
} // end of the 'setCheckboxes()' function

// used by copy wizard block
/**
 * Checks/unchecks checkboxes
 *
 * @param   string   the form name
 * @param   string   the checkbox name (or the first characters of the name, if unique)
 * @param   boolean  whether to check or to uncheck the element
 * @return  boolean  always true
 */
function setCheckedById(the_form, id_name, do_check)
{
	for (var i=0;i<document.forms[the_form].elements.length;i++)
	{
		var e = document.forms[the_form].elements[i];
		if(e.id == id_name)
		{
			e.checked = do_check;
		}
	}
  return true;
} // end of the 'setCheckboxes()' function

// tpl users online row
/**
 * Opens a chat window
 *
 * @param   object	the link which was clicked
 * @param   int		desired width of the new window
 * @param   int		desired height of the new window
 */
function openChatWindow(oLink, width, height)
{
	if(width == null)
	{
		width = screen.availWidth;
	}
	leftPos = (screen.availWidth / 2)- (width / 2);	
	
	if(height == null)
	{
		height = screen.availHeight;
	}
	topPos = (screen.availHeight / 2)- (height / 2);				

	oChatWindow = window.open(
		oLink.href, 
		oLink.target, 
		'width=' + width + ',height=' + height + ',left=' + leftPos + ',top=' + topPos +
		',resizable=yes,scrollbars=yes,status=yes,toolbar=yes,menubar=yes,location=yes'
	);

	oChatWindow.focus();
}

// Set focus for screen reader
function ilGoSRFocus(id)
{
	obj = document.getElementById(id);
	if (obj)
	{
		obj.focus();
		self.location.hash = id;
	}
}

// Set focus for screen reader
function ilScreenReaderFocus()
{
	obj = document.getElementById("il_message_focus");
	if (obj)
	{
		obj.focus();
		self.location.hash = 'il_message_focus';
	}
	else
	{
		obj = document.getElementById("il_lm_head");
		if (obj && self.location.hash == '')
		{
			obj.focus();
			self.location.hash = 'il_lm_head';
		}
		else
		{
			obj = document.getElementById("il_mhead_t_focus");
			if (obj && self.location.hash == '')
			{
				obj.focus();
				self.location.hash = 'il_mhead_t_focus';
			}
		}
	}
}

function ilSubmitOnEnter(ev, form)
{
	if (typeof ev != 'undefined' && typeof ev.keyCode != 'undefined')
	{
		if (ev.keyCode == 13)
		{
			form.submit();
			return false;
		}
	}
	return true;
}

function startSAHS(SAHSurl, SAHStarget, SAHSopenMode, SAHSwidth, SAHSheight)
{
	if (SAHSopenMode == 1){
		SAHSwidth = "100%";
		SAHSheight = "650";
		if(document.body.offsetHeight) SAHSheight=document.getElementById("mainspacekeeper").offsetHeight;
	}
	if (SAHSopenMode == 1 || SAHSopenMode == 2){
		document.getElementById("mainspacekeeper").innerHTML='<iframe src="'+SAHSurl+'" width="'+SAHSwidth+'" height='+SAHSheight+' frameborder="0"></iframe>';
	} else if (SAHSopenMode == 5){
		window.open(SAHSurl,SAHStarget,'top=0,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no');
	} else {
		window.open(SAHSurl,SAHStarget,'top=0,width='+SAHSwidth+',height='+SAHSheight+',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no');
	}
}

