
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/*
   Please note that this file should only contain common Javascript code
   used on many ILIAS screens. Please do not add any code that is only useful
   for single components here.
   See http://www.ilias.de/docu/goto_docu_pg_38968_42.html for the JS guidelines
*/

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
	setStdScreenReaderFocus: function() {
		var obj = document.getElementById("il_message_focus");
		if (obj) {
			obj.focus();
			self.location.hash = 'il_message_focus';
		} else {
			obj = document.getElementById("il_lm_head");
			if (obj && self.location.hash == '') {
				obj.focus();
				self.location.hash = 'il_lm_head';
			} else {
				obj = document.getElementById("il_mhead_t_focus");
				if (obj && self.location.hash == '') {
					obj.focus();
					self.location.hash = 'il_mhead_t_focus';
				}
			}
		}
	},
	
	/**
	 * Get region information (coordinates + size) for an element
	 */
	getRegion: function (el) {
		var w = $(el).outerWidth(),
			h = $(el).outerHeight(),
			o = $(el).offset();
			
		return {top: o.top, right: o.left + w,bottom: o.top + h, left: o.left, height: h, width: w, y: o.top, x: o.left};
	},
	
	/**
	 * Get region information (coordinates + size) for viewport
	 */
	getViewportRegion: function () {
		var w = $(window).width(),
			h = $(window).height(),
			t = $(window).scrollTop(),
			l = $(window).scrollLeft();
			
		return {top: t, right: l + w,bottom: t + h, left: l, height: h, width: w, y: t, x: l};
	},
	
	/**
	 * Checks whether coordinations are within an elements region
	 */
	coordsInElement: function (x, y, el) {
		var w = $(el).outerWidth(),
			h = $(el).outerHeight(),
			o = $(el).offset();
		if (x >= o.left && x <= o.left + w && y >= o.top && y <= o.top + h) {
			return true;
		}
		return false;
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

/* Main menu handling */
il.MainMenu = {
	
	removeLastVisitedItems: function (url) {
		
		$('.ilLVNavEnt').remove();
		il.Util.sendAjaxGetRequestToUrl(url, {}, {}, this.dummyCallback);
		
		return false;
	},
	
	dummyCallback: function () {
	}
}

/* UICore */
il.UICore = {
	refreshLayout: function () {
		var el = document.getElementById("left_nav"),
			sm = document.getElementById("mainspacekeeper"),
			bc = document.getElementById("bot_center_area"),
			fc = document.getElementById("fixed_content"),
			nb_reg, vp_reg;
			
		// fix fixed content
		/*if (fc && sm) {
			sm_reg = il.Util.getRegion(sm);
			fc_reg = il.Util.getRegion(fc);
			vp_reg = il.Util.getViewportRegion();
			if (sm_reg.top < vp_reg.top) {
				$(fc).offset({top: vp_reg.top});
			} else {
				$(fc).offset({top: sm_reg.top});
			}
		}*/
		
		// fix left navigation area
		if (el && sm) {
			sm_reg = il.Util.getRegion(sm);
			nb_reg = il.Util.getRegion(el);
			vp_reg = il.Util.getViewportRegion();
			if (sm_reg.top < vp_reg.top) {
				$(el).css("top", "0px");
				$(fc).css("top", "0px");
			} else {
				$(el).css("top", (sm_reg.top - vp_reg.top) + "px");
				$(fc).css("top", (sm_reg.top - vp_reg.top) + "px");
			}
			
			// bottom center area?
			if (bc) {
				bc_reg = il.Util.getRegion(bc);
				$(fc).css("bottom", bc_reg.height + "px");
			} else {
				$(fc).css("bottom", "0px");
			}
		}
	},
	
	initLayoutDrag: function() {
		$('#bot_center_area_drag').mousedown(function(e){
			e.preventDefault();
			$('#drag_zmove').css("display","block");
			$('#drag_zmove').mousemove(function(e){
				var vp_reg = il.Util.getViewportRegion();
				var drag_y = e.pageY;
				$('#bot_center_area').css("height", vp_reg.height - drag_y);
				il.UICore.refreshLayout();
			});
		});
		$(document).mouseup(function(e){
			$('#bot_center_area_drag').unbind('mousemove');
			$('#drag_zmove').css("display","none");
			$(document).unbind('mousemove');
		});

	}
};

il.Util.addOnLoad(function () {
	$(window).resize(il.UICore.refreshLayout);
	$(window).scroll(il.UICore.refreshLayout);
	il.UICore.refreshLayout();
});

/* Rating */
il.Rating = {
	
	cache: [],
	
	setValue: function (category_id, value, prefix) {
		
		// set hidden field
		$("#"+prefix+"rating_value_"+category_id).val(value);
		
		// handle icons
		for(i=1;i<=5;i++)
		{
			var icon_id = prefix+"rating_icon_"+category_id+"_"+i;
			var src = $("#"+icon_id).attr("src");		
			
			// active
			if(i <= value)
			{					
				if(src.substring(src.length-6) == "on.png")
				{
					src = src.substring(0, src.length-6)+"on_user.png";					
				}
				else if(src.substring(src.length-7) == "off.png")
				{
					src = src.substring(0, src.length-7)+"on_user.png";	
				}											
			}
			// inactive
			else
			{
				if(src.substring(src.length-6) == "on.png")
				{
					src = src.substring(0, src.length-6)+"off.png";					
				}
				else if(src.substring(src.length-11) == "on_user.png")
				{
					src = src.substring(0, src.length-11)+"off.png";	
				}							
			}			
		
			// resetting img cache so onmouseout will not change icons again
			il.Rating.cache[icon_id] = "";
			
			$("#"+icon_id).attr("src", src);			
		}
		
		return false;
	},
	
	toggleIcon: function (el, value, is_out) {
		
		$(el).children().each(function(){
		
			if($(this).attr("id"))
			{
				var org = $(this).attr("id");
				var grp = org.substring(0, org.length-1);
				for(i=1;i<=5;i++)
				{		
					var id = grp + i;
					
					if(is_out == undefined)
					{										
						// determine type of current icon
						var src_parts = $("#"+id).attr("src").split("_");
						var icon_type = src_parts.pop();
						icon_type = icon_type.substring(0, icon_type.length-4);
						if(icon_type == "user")
						{							
							icon_type = src_parts.pop();							
						}
						if($.isNumeric(icon_type))
						{
							icon_type = "on";
						}
						var icon_base = src_parts.join("_");
						
						// onmouseout should revert to original img
						var src = $("#"+id).attr("src");				
						il.Rating.cache[id] = src;
						
						// active
						if(i <= value)
						{					
							src = icon_base+"_on_user.png";																				
						}
						// inactive
						else
						{
							src = icon_base+"_off.png";													
						}			
					}
					else
					{
						var src = il.Rating.cache[id];
					}
					
					if(src)
					{
						$("#"+id).attr("src", src);
					}
				}
			}
		});		
	}
}

/* keep ios wepapp mode (do not open safari mobile if links are clicked) */
/*if (("standalone" in window.navigator) && !window.navigator.standalone ){
	il.Util.addOnLoad(function () {
		$(document).on(
			"click",
			'a[href!="#"][href!=""]',
			function(event){
				if (event.target.nodeName == "A") {
					event.preventDefault();
					location.href = $(event.target).attr("href");
				}
			}
		);
	});
}*/

////
//// The following methods should be moved to the corresponding components
////

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

