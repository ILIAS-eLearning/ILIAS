
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

il.Help = {
	tt_activated: true,
	panel: false,
	ajax_url: '',
	padding_old: '-',

	// list help topics
	listHelp: function (e, back_clicked) {
		// prevent the default action		
		e.preventDefault();		
		// hide overlays
		il.Overlay.hideAllOverlays(e, true);
		// add panel
		this.initPanel(e, true);
	},

	// init help panel
	initPanel: function (e, sh) {
		var n, b, obj;
		if (!this.panel) {
			n = document.getElementById('ilHelpPanel');
			if (!n) {
				b = $("body");
				b.append("<div class='yui-skin-sam'><div id='ilHelpPanel' class='ilOverlay' style='overflow:auto;'>" +
					"&nbsp;</div>");
				n = document.getElementById('ilHelpPanel');
			}

			il.Overlay.add("ilHelpPanel", {yuicfg: {}});
			il.Overlay.show(e, "ilHelpPanel");
			this.panel = true;
		} else {
			il.Overlay.show(e, "ilHelpPanel");
//			this.panel.show();
		}
		il.Help.insertPanelHTML("");
		//il.Help.reduceMainContentArea();

		obj = document.getElementById('ilHelpPanel');
		obj.style.position = 'fixed';
		obj.style.top = '0px';
		obj.style.bottom = '0px';
		obj.style.right = '0px';
		obj.style.left = '';
		obj.style.width = '300px';
		obj.style.height = '100%';

		if (sh) {
			il.Util.sendAjaxGetRequestToUrl(this.getAjaxUrl(),
				{cmd: "showHelp"}, {}, this.handleAjaxSuccess);
		}
	},

	// show single help page
	showPage: function (id) {
		il.Util.sendAjaxGetRequestToUrl(this.getAjaxUrl(),
			{cmd: "showPage", help_page: id}, {}, this.handleAjaxSuccess);
		return false;
	},

	// called by tpl/ilHelpGUI::initCurrentHelpPage
	showCurrentPage: function (id) {
		if (this.ajax_url != '') {
			this.initPanel(null, false);
			il.Util.sendAjaxGetRequestToUrl(this.getAjaxUrl(),
				{cmd: "showPage", help_page: id}, {}, this.handleAjaxSuccess);
		}
		return false;
	},

	// set ajax url
	setAjaxUrl: function (url) {
		this.ajax_url = url;
	},

	// get ajax url
	getAjaxUrl: function () {
		return this.ajax_url;
	},

	// success handler
	handleAjaxSuccess: function (o) {
		// perform page modification
		if (o.responseText !== undefined) {
			if (o.argument.mode != 'resetCurrentPage' && o.argument.mode != 'tooltipHandling') {
				// default action: replace html
				il.Help.insertPanelHTML(o.responseText);

				if (typeof il.Accordion != "undefined") {
					il.Accordion.initByIntId('oh_acc');
					console.log("called init");
				}
			}
		}
	},

	// insert HTML into panel
	insertPanelHTML: function (html) {
		$('div#ilHelpPanel').html(html);
	},

	// add space at right of main content area
	reduceMainContentArea: function () {
		var obj = document.getElementById('mainspacekeeper');
		if (il.Help.padding_old == "-") {
			il.Help.padding_old = obj.style.paddingRight;
		}
		obj.style.paddingRight = '300px';
		il.Help.fixWebkit(obj);
	},
	
	// force repaint on webkit
	fixWebkit: function (obj) {
		// http://www.ilias.de/mantis/bug_view_page.php?bug_id=10362
		// the next few lines are needed to force a repaint in webkit
		// http://stackoverflow.com/questions/3485365/how-can-i-force-webkit-to-redraw-repaint-to-propagate-style-changes
		obj.style.display = 'none';
		obj.offsetHeight;
		obj.style.display = '';
	},

	// reset main content area
	resetMainContentArea: function () {
		var obj = document.getElementById('mainspacekeeper');
		obj.style.paddingRight = this.padding_old;
		
		il.Help.fixWebkit(obj);
	},

	// close panel
	closePanel: function (e) {
		if (this.panel) {
			il.Overlay.hide(e, "ilHelpPanel");
			il.Help.panel = false;
			//il.Help.resetMainContentArea();

			il.Util.sendAjaxGetRequestToUrl(this.getAjaxUrl(),
				{cmd: "resetCurrentPage"}, {mode: "resetCurrentPage"}, this.handleAjaxSuccess);

		}
	},

	// (de-)activate tooltips
	switchTooltips: function (e) {
		var t = il.Help;
		if (t.tt_activated) {
			t.tt_activated = false;
			il.Util.sendAjaxGetRequestToUrl(this.getAjaxUrl(),
				{cmd: "deactivateTooltips"}, {mode: "tooltipHandling"}, this.handleAjaxSuccess);
		} else {
			t.tt_activated = true;
			il.Util.sendAjaxGetRequestToUrl(this.getAjaxUrl(),
				{cmd: "activateTooltips"}, {mode: "tooltipHandling"}, this.handleAjaxSuccess);
		}
		il.Help.updateTooltips();
		return false;
	},

	updateTooltips: function () {
		var tips = $('#ilSubTab li, #ilTab li, .il_adv_sel, .dropdown-menu li a');
		if (!il.Help.tt_activated) {
			tips.qtip('disable', true);
			$('#help_tt_switch_on').css('visibility', 'hidden');
		} else {
			tips.qtip('enable');
			$('#help_tt_switch_on').css('visibility', 'visible');
		}
	},

	
	// show single help page
	openLink: function (e) {
		var s, pageid, href = e.currentTarget.href;
		s = href.split("#");
		page_id = s[1].substr(3);
		console.log(e.currentTarget.href);
		console.log(page_id);
		il.Util.sendAjaxGetRequestToUrl(this.getAjaxUrl(),
			{cmd: "showPage", help_page: page_id}, {}, this.handleAjaxSuccess);
		return false;
	},


};
