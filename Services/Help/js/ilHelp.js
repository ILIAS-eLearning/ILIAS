
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

il.Help = {
	tt_activated: true,
	panel: false,
	ajax_url: '',
	padding_old: '-',

	// list help topics
	listHelp: function (e, back_clicked) {
		// prevent the default action		
//		e.preventDefault();
		// hide overlays
//		il.Overlay.hideAllOverlays(e, true);
		// add panel
		this.initPanel(e, true);
	},

	sendAjaxGetRequestToUrl: function (url, par = {}, args= {}) {
		let k;
		args.url = url;
		for (k in par) {
			url = url + "&" + k + "=" + par[k];
		}
		il.repository.core.fetchHtml(url).then((html) => {
			this.handleAjaxSuccess({
				argument: args,
				responseText: html
			});
		});
	},


	// init help panel
	initPanel: function (e, sh) {
		var n, b, obj;
		if (!this.panel) {
			n = document.getElementById('ilHelpPanel');
			this.panel = true;
		} else {
		}
		il.Help.insertPanelHTML("");
		if (sh) {
			this.sendAjaxGetRequestToUrl(this.getAjaxUrl(),
				{cmd: "showHelp"});
		}
	},

	// show single help page
	showPage: function (id) {
		this.sendAjaxGetRequestToUrl(this.getAjaxUrl(),
			{cmd: "showPage", help_page: id});
		return false;
	},

	// called by tpl/ilHelpGUI::initCurrentHelpPage
	showCurrentPage: function (id) {
		if (this.ajax_url != '') {
			this.initPanel(null, false);
			this.sendAjaxGetRequestToUrl(this.getAjaxUrl(),
				{cmd: "showPage", help_page: id});
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
//				$('div#ilHelpPanel').css("overflow", "auto"); // Ensure overflow auto, see 20639
			}
		}
	},

	// insert HTML into panel
	insertPanelHTML: function (html) {
		var t = il.Help;
		$('div#ilHelpPanel').html(html);
		t.initEvents();
		$("#il_help_search_term").each(function() {
			t = this;
			t.focus();
			if (t.setSelectionRange) {
				var len = $(t).val().length * 2;
				t.setSelectionRange(len, len);
			}
		});
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

      il.Help.resetCurrentPage();
		}
	},

  resetCurrentPage: function () {
    this.sendAjaxGetRequestToUrl(this.getAjaxUrl(),
      {cmd: "resetCurrentPage"}, {mode: "resetCurrentPage"});
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
		this.sendAjaxGetRequestToUrl(this.getAjaxUrl(),
			{cmd: "showPage", help_page: page_id});
		return false;
	},

	// init events
	initEvents: function () {
		$("#il_help_search_form").submit(function (e) {
			var t = il.Help;
			t.search($("#il_help_search_term").val());
			e.preventDefault();
		});
	},

	// perform search
	search: function (term) {
		var t = il.Help;
		this.sendAjaxGetRequestToUrl(t.getAjaxUrl(),
			{cmd: "search", term: term});
	}



};
