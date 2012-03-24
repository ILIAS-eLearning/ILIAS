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
	
	/**
	 * Checks/unchecks checkboxes
	 *
	 * @param   string   parent name or id
	 * @param   string   the checkbox name (or the first characters of the name, if unique)
	 * @param   boolean  whether to check or to uncheck the element
	 * @return  boolean  always true
	 */
	 setChecked: function(parent_el, checkbox_name, do_check){
	 	var name_sel = '';
	 	if (checkbox_name != '')
	 	{
	 		name_sel = '[name^="' + checkbox_name + '"]';
	 	}
		if(do_check)
		{
			$("#" + parent_el).find("input:checkbox" + name_sel).attr('checked', 'checked');
			$('[name="' + parent_el + '"]').find("input:checkbox" + name_sel).attr('checked', 'checked');
		}
		else
		{
			$("#" + parent_el).find("input:checkbox" + name_sel).removeAttr('checked');
			$('[name="' + parent_el + '"]').find("input:checkbox" + name_sel).removeAttr('checked');
		}
	  return true;
	},
	
	
	submitOnEnter: function(ev, form)
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
	},
	
	// Screen reader related functions
	
	// Set focus for screen reader per element id
	setScreenReaderFocus: function(id)
	{
		var obj = document.getElementById(id);
		if (obj)
		{
			obj.focus();
			self.location.hash = id;
		}
	},
	
	// Set standard screen reader focus
	setStdScreenReaderFocus: function()
	{
		var obj = document.getElementById("il_message_focus");
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

