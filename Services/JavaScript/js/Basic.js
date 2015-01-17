
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
		$().ready(func);
/*		if (!document.getElementById | !document.getElementsByTagName) return;
	
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
		}*/
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
	 * Fix position
	 */
	fixPosition: function (el) {
		var r = il.Util.getRegion(el),
			vp = il.Util.getViewportRegion();

		// we only fix absolute positioned items
		if ($(el).css("position") != "absolute") {
			return;
		}

		if (vp.right - 20 < r.right) {
			il.Util.setX(el, r.x - (r.right - vp.right + 20));
		}

		r = il.Util.getRegion(el);
		if (r.left < 0) {
			$(el).removeClass("pull-right");
			il.Util.setX(el, 0);
		}
	},

	/**
	 * Set x
	 */
	setX: function (el, x) {
		$(el).offset({left: x});
	},

	setY: function (el, y) {
		$(el).offset({top: y});
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
	},
	
	/**
	 * print current window, thanks to anoack for the mathjax fix (see bug #)
	 */
	print: function () {
		if (typeof(window.print) != 'undefined') {
			if (typeof MathJax !== 'undefined') {
				MathJax.Hub.Queue(
					["Delay",MathJax.Callback,700],
					window.print
				);
			} else {
				window.print();
			}
		}
	}
}

// ILIAS Object related functions
il.Object = {
	url_redraw_ah: "",
	url_redraw_li: "",
	url_rating: "",
	
	setRedrawAHUrl: function(url) {
		this.url_redraw_ah = url;
	},
	
	getRedrawAHUrl: function() {
		return this.url_redraw_ah;
	},
	
	redrawActionHeader: function() {
		var ah = document.getElementById("il_head_action");
		if (this.url_redraw_ah && ah !== null)
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
		if (this.url_redraw_li)
		{
			var url = this.url_redraw_li;
			$('div[id^=lg_div_' + ref_id + '_pref_]').each(function() {		
				var id = $(this).attr('id');
				var parent = id.split("_").pop();				
				il.Util.ajaxReplace(url + "&child_ref_id=" + ref_id + "&parent_ref_id=" + parent, id);
			});
		}
	},
	
	togglePreconditions: function(link, id, txt_show, txt_hide) {
		var li = document.getElementById("il_list_item_precondition_obl_" + id);
		if(li !== null)
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
		if(li !== null)
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
	},
			
	setRatingUrl: function(url) {
		this.url_rating = url;
	},
			
	saveRating: function(mark) {		
		il.Util.sendAjaxGetRequestToUrl(this.url_rating + "&rating=" + mark, {}, {url_redraw: this.url_redraw_ah}, this.redrawAfterRating);
	},
			
	redrawAfterRating: function(o) {
		var ah = document.getElementById("il_head_action");
		if (ah !== null)
		{
			il.Util.ajaxReplaceInner(o.argument.url_redraw, "il_head_action");
		}
	},
			
	saveRatingFromListGUI: function(ref_id, hash, mark) {		
		il.Util.sendAjaxGetRequestToUrl(this.url_rating + "&rating=" + mark + "&child_ref_id=" + ref_id + "&cadh= " + hash, {}, {url_redraw: this.url_redraw_li, ref_id: ref_id}, this.redrawAfterRatingFromListGUI);
	},
			
	redrawAfterRatingFromListGUI: function(o) {	
		$('div[id^=lg_div_' + o.argument.ref_id + '_pref_]').each(function() {		
			var id = $(this).attr('id');
			var parent = id.split("_").pop();
			il.Util.ajaxReplace(o.argument.url_redraw + "&child_ref_id=" + o.argument.ref_id + "&parent_ref_id=" + parent, id);
		});
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

	//
	// Layout related
	//

	right_panel_wrapper: "",


	scrollToHash: function () {
		var h = self.location.hash;
		if (h != "") {
			h = h.substr(1);
			if ($("a[name='" + h + "']").length !== 0) {
				il.UICore.scrollToElement("a[name='" + h + "']");
			} else if ($("#" + h).length !== 0) {
				il.UICore.scrollToElement("#" + h);
			}
		}
	},

	// take care of initial layout
	scrollToElement: function (el) {
		// if we have an anchor, fix scrolling "behind" fixed top header
		var fixed_top_height = parseInt($("#mainspacekeeper").css("margin-top")) +
				parseInt($("#mainspacekeeper").css("padding-top")),
			vp_reg = il.Util.getViewportRegion(),
			el_reg = il.Util.getRegion(el);
		if (fixed_top_height > 0) {
			$('html, body').scrollTop(el_reg.top - fixed_top_height);
		}
	},

	handleScrolling: function() {
		il.UICore.refreshLayout();
	},

	refreshLayout: function () {
		var el = document.getElementById("left_nav"),
			sm = document.getElementById("mainspacekeeper"),
			bc = document.getElementById("bot_center_area"),
			fc = document.getElementById("fixed_content"),
			ft = document.getElementById("ilFooter"),
			rtop = document.getElementById("right_top_area"),
			rbot = document.getElementById("right_bottom_area"),
			nb_reg, vp_reg, ft_reg, rtop_reg, rbot_reg, el_reg, bc_reg;

		vp_reg = il.Util.getViewportRegion();
		$(".ilFrame").each(function() {
			var t = $(this);
//			console.log(t);
			var freg = il.Util.getRegion(this);
			if (freg.bottom < vp_reg.bottom) {
				t.height(t.height() + vp_reg.bottom - freg.bottom - 1);
			}
//console.log(freg);
//console.log(vp_reg);
		});

		// fix fixed content
		if ($(fc).css("position") != "static") {
			if (fc && sm) {
				sm_reg = il.Util.getRegion(sm);
				fc_reg = il.Util.getRegion(fc);
				if (sm_reg.top < vp_reg.top) {
					$(fc).offset({top: vp_reg.top});
				} else {
					$(fc).offset({top: sm_reg.top});
				}
			}

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
		}

		if (el && bc) {
			el_reg = il.Util.getRegion(el);
			bc_reg = il.Util.getRegion(bc);
			il.Util.setX(bc, el_reg.right);
		}
		if (bc && sm) {
			sm_reg = il.Util.getRegion(sm);
			bc_reg = il.Util.getRegion(bc);
			$(bc).css("width", parseInt(sm_reg.right - bc_reg.left) + "px");
		}

		// footer vs. left nav
		if (ft && el) {
			ft_reg = il.Util.getRegion(ft);
			if (ft_reg.top < vp_reg.bottom) {
				$(el).css("bottom", (vp_reg.bottom - ft_reg.top) + "px");
			} else {
				$(el).css("bottom", "0px");
			}
		}

		// fit width of right top/bottom regions into mainspacekeeper area
		if (rtop && sm) {
			sm_reg = il.Util.getRegion(sm);
			rtop_reg = il.Util.getRegion(rtop);
			$(rtop).css("width", parseInt(sm_reg.right - rtop_reg.left) + "px");
		}
		if (rbot && sm) {
			sm_reg = il.Util.getRegion(sm);
			rbot_reg = il.Util.getRegion(rbot);
			$(rbot).css("width", parseInt(sm_reg.right - rbot_reg.left) + "px");
		}

		il.UICore.collapseTabs(false);
	},

	collapseTabs: function (recheck) {
		var tabs = $('#ilTab.ilCollapsable'), tabsHeight, count, children, collapsed;
		if (tabs) {
			tabsHeight = tabs.innerHeight();
			if (tabsHeight >= 50) {
				$('#ilLastTab a').removeClass("ilNoDisplay");
				// as long as we have two lines...
				while (tabsHeight > 50) {
					children = tabs.children('li:not(:last-child)');
					count = children.size();

					// ...put last child into collapsed drop down
					$(children[count-1]).prependTo('#ilTabDropDown');
					tabsHeight = tabs.innerHeight();
				}
			} else {
				// as long as we have one line...
				while (tabsHeight < 50 && ($('#ilTabDropDown').children('li').size()>0)) {
					collapsed = $('#ilTabDropDown').children('li');
					count = collapsed.size();
					$(collapsed[0]).insertBefore(tabs.children('li:last-child'));
					tabsHeight = tabs.innerHeight();
				}
				if ($('#ilTabDropDown').children('li').size() == 0) {
					$('#ilLastTab a').addClass("ilNoDisplay");
				}
				if (tabsHeight>50 && !recheck) { // double chk height again
					il.UICore.collapseTabs(true);
				}
			}
		}
	},

	initFixedDropDowns: function () {
		$('.ilMainMenu.ilTopFixed .dropdown').on('shown.bs.dropdown', function () {
			var el = $(this).children(".dropdown-menu")[0];
			if (!el) {
				return;
			}
			var r = il.Util.getRegion(el),
				vp = il.Util.getViewportRegion(),
				newHeight;

			// make it smaller, if window height is not sufficient
			if (vp.bottom < r.bottom) {
				newHeight = r.height - r.bottom + vp.bottom;
				el.style.height = newHeight + "px";
				$(el).css("overflow", "auto");
			}
		}).on('hidden.bs.dropdown', function () {
		});
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

	},
	
	showRightPanel: function () {
		var n = document.getElementById('ilRightPanel');
		if (!n) {
			var b = $("body");
			b.append("<div class='yui-skin-sam'><div id='ilRightPanel' class='ilOverlay ilRightPanel'>" +
				"&nbsp;</div>");
			var n = document.getElementById('ilRightPanel');
			il.Overlay.add("ilRightPanel", {yuicfg: {}});
			il.Overlay.show(null, "ilRightPanel");
		}
		else
		{
			il.Overlay.show(null, "ilRightPanel");
		}
		
		il.Overlay.subscribe("ilRightPanel", "hide", function () {il.UICore.unloadWrapperFromRightPanel();});
		
		il.UICore.setRightPanelContent("");

		n = document.getElementById('ilRightPanel');
		n.style.width = '500px';
		n.style.height = '100%';
	},
	
	setRightPanelContent: function (c) {
		$('div#ilRightPanel').html(c);
	},
	
	// load content from wrapper element into right panel
	loadWrapperToRightPanel: function (wrapper_id) {
		this.right_panel_wrapper = wrapper_id;
		$("#" + wrapper_id).children().appendTo('#ilRightPanel');
	},
	
	// move the right panel content back to wrapper
	unloadWrapperFromRightPanel: function() {
		if (this.right_panel_wrapper != "") {
			$('#ilRightPanel').children().appendTo('#' + this.right_panel_wrapper);
		}
		this.right_panel_wrapper = '';
	},
	
	hideRightPanel: function () {
		il.UICore.unloadWrapperFromRightPanel();
		il.Overlay.hide(null, "ilRightPanel");
	}

};

// fixing anchor links presentation, unfortunately there
// is no event after browsers have scrolled to an anchor hash
// and at least firefox seems to do this multiple times when rendering a page
$(window).bind("load", function() {
	window.setTimeout(function() {
		il.UICore.scrollToHash();
	}, 500);
});

$(window).bind("hashchange", function () {
	il.UICore.scrollToHash();
});

il.Util.addOnLoad(function () {
	$(window).resize(il.UICore.refreshLayout);
	$(window).scroll(il.UICore.handleScrolling);

	il.UICore.refreshLayout();
	il.Util.omitPreventDoubleSubmission = false;

	// jQuery plugin to prevent double submission of forms
	// see http://stackoverflow.com/questions/2830542/prevent-double-submission-of-forms-in-jquery
	jQuery.fn.preventDoubleSubmission = function() {
		var t, ev;

		if ($(this).get(0)) {
			t = $(this).get(0).tagName;
			ev = (t == 'FORM') ? 'submit' : 'click';
			if (t == 'FORM') {
				$(this).find(":input[type=submit]").on('click',function(e) {
					il.Util.omitPreventDoubleSubmission = false;
					if($(this).hasClass('omitPreventDoubleSubmission')) {
						il.Util.omitPreventDoubleSubmission = true;
					}
				});
			}
			$(this).on(ev,function(e) {
				var $el = $(this);	
				
				// If form/submit button has been tagged do not prevent anything						
				if ($el.hasClass('omitPreventDoubleSubmission')) {
					return this;
				}
					
				if(ev == 'submit')
				{
					// if the submit button has been tagged separately
					if($(':input[type=submit]:focus').hasClass('omitPreventDoubleSubmission') || il.Util.omitPreventDoubleSubmission)
					{
						return this;
					}
				}
														
				if ($el.data('submitted') === true) {
					// Previously submitted - don't submit again
					e.preventDefault();
				} else {
					// Mark it so that the next submit can be ignored
					$('form.preventDoubleSubmission, .preventDoubleSubmission a.submit, a.preventDoubleSubmission').data('submitted', true);
					$('form.preventDoubleSubmission input:submit, .preventDoubleSubmission a.submit, a.preventDoubleSubmission').addClass("ilSubmitInactive");
					$('area.preventDoubleSubmission').data('submitted', true);
				}
			});
		}

		// Keep chainability
		return this;
	};
	// note: we need to call this two times, since the first time all forms will be handled,
	// the second time all links, and the get(0) line above handles only sets of elements
	// of the same type correctly
	$('form.preventDoubleSubmission').preventDoubleSubmission();
	$('.preventDoubleSubmission a.submit, a.preventDoubleSubmission').preventDoubleSubmission();
	// Used for image maps in "hot spot" questions:Modules/TestQuestionPool/templates/default/tpl.il_as_qpl_imagemap_question_output.html
	$('area.preventDoubleSubmission').preventDoubleSubmission();

	// fix positions of drop-downs to viewport
	$('.dropdown-menu').parent().on('shown.bs.dropdown', function (e) {
		$(this).children(".dropdown-menu").each(function() {
			il.Util.fixPosition(this);
		});
	});

	il.UICore.initFixedDropDowns();
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
				if(src.substring(src.length-6) == "on.svg")
				{
					src = src.substring(0, src.length-6)+"on_user.svg";					
				}
				else if(src.substring(src.length-7) == "off.svg")
				{
					src = src.substring(0, src.length-7)+"on_user.svg";	
				}											
			}
			// inactive
			else
			{
				if(src.substring(src.length-6) == "on.svg")
				{
					src = src.substring(0, src.length-6)+"off.svg";					
				}
				else if(src.substring(src.length-11) == "on_user.svg")
				{
					src = src.substring(0, src.length-11)+"off.svg";	
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
							src = icon_base+"_on_user.svg";																				
						}
						// inactive
						else
						{
							src = icon_base+"_off.svg";													
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
		if(SAHSheight==0) SAHSheight=document.body.offsetHeight-200;
		if(SAHSheight==0) SAHSheight=650;
	}
	if (SAHSopenMode == 1 || SAHSopenMode == 2){
		document.getElementById("mainspacekeeper").innerHTML='<iframe src="'+SAHSurl+'" width="'+SAHSwidth+'" height='+SAHSheight+' frameborder="0"></iframe>';
	} else if (SAHSopenMode == 5){
		window.open(SAHSurl,SAHStarget,'top=0,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no');
	} else {
		window.open(SAHSurl,SAHStarget,'top=0,width='+SAHSwidth+',height='+SAHSheight+',location=no,menubar=no,resizable=yes,scrollbars=yes,status=no');
	}
}

