/* */

il.Accordion = {

	duration : 150,

	data: {},

	/**
	 * Add accordion element
	 *
	 * Options:
	 * id: id,
	 * toggle_class: toggle_class,
	 * toggle_act_class: toggle_act_class,
	 * content_class: content_class,
	 * width: width,
	 * height: height,
	 * orientation: orientation,
	 * behaviour: behaviour,
	 * save_url: save_url,
	 * active_head_class: active_head_class,
	 * int_id: int_id,
	 * initial_opened: initial opened accordion tabs (nr, separated by ;)
	 * multi: multi
	 * show_all_element: ID of HTML element that triggers show all
	 * hide_all_element: ID of HTML element that triggers hide all
	 */
	add: function (options) {
		options.animating = false;
		options.clicked_acc = null;
		options.last_opened_acc = null;

		if (typeof options.show_all_element == "undefined") {
			options.show_all_element = null;
		}

		if (typeof options.hide_all_element == "undefined") {
			options.hide_all_element = null;
		}

		if ((typeof options.initial_opened != "undefined") && options.initial_opened && options.initial_opened.length > 0) {
			options.initial_opened = options.initial_opened.split(";");
		} else {
			options.initial_opened = [];
		}

		il.Accordion.data[options.id] = options;
		il.Accordion.init(options.id);
	},

	init: function (id) {
		var t, el, next_el, acc_el, a = il.Accordion.data[id];

		// open the inital opened tabs
		if (a.initial_opened.length > 0) {
			for (var i = 0; i < a.initial_opened.length; i++) {
				acc_el = $("#" + id + " div." + a.content_class + ":eq(" + (parseInt(a.initial_opened[i])-1) + ")");
				acc_el.removeClass("ilAccHideContent");
				il.Accordion.addActiveHeadClass(id, acc_el[0]);
				a.last_opened_acc = acc_el;
			}
		} else if (a.behaviour == "FirstOpen") {
			acc_el = $("#" + id + " div." + a.content_class + ":eq(0)");
			acc_el.removeClass("ilAccHideContent");
			il.Accordion.addActiveHeadClass(id, acc_el[0]);
			a.last_opened_acc = acc_el;
		}

		// register click handler (if not all opened is forced)
		if (a.behaviour != "ForceAllOpen") {
			$("#" + id).children().children("." + a.toggle_class).each(function () {
				t = $(this);
				t.on("click", { id: id, el: t}, il.Accordion.clickHandler);
			});
		}

		if (a.show_all_element) {
			$("#" + a.show_all_element).prop("onclick", "").on("click", { id: id}, il.Accordion.showAll);
		}
		if (a.hide_all_element) {
			$("#" + a.hide_all_element).prop("onclick", "").on("click", { id: id}, il.Accordion.hideAll);
		}
	},

	isOpened: function (el) {
		return !$(el).hasClass("ilAccHideContent");
	},

	getAllOpenedNr: function (id) {
		var opened_str = "", lim = "", t = 1, a = il.Accordion.data[id];

		$("#" + id).children().children("." + a.content_class).each(function () {
			if (!$(this).hasClass("ilAccHideContent")) {
				opened_str = opened_str + lim + "" + t;
				lim = ";";
			}
			t++;
		});

		return opened_str;
	},

	getAllNr: function (id) {
		var all_str = "", lim = "", t = 1, a = il.Accordion.data[id];

		$("#" + id).children().children("." + a.content_class).each(function () {
			all_str = all_str + lim + "" + t;
			lim = ";";
			t++;
		});
		return all_str;
	},

	clickHandler: function (e) {
		var a, el, id;
//console.log("clicked");
		id = e.data.id
		a = il.Accordion.data[id];
		el = e.data.el;
		e.preventDefault();

		if (a.animating) {
			return false;
		}

		a.clicked_acc = el.next()[0];

		if (il.Accordion.isOpened(a.clicked_acc)) {
			il.Accordion.deactivate(id, el);
		} else {
			il.Accordion.handleAccordion(id, el);
		}
		return false;
	},

	initByIntId: function(int_id) {
		for(var a in il.Accordion.data) {
			if (a.int_id == int_id) {
				il.Accordion.init(a.id);
			}
		}
	},

	addActiveHeadClass: function (id, acc_el) {
		var a = il.Accordion.data[id];

		if (a.active_head_class && a.active_head_class != "" && acc_el) {
			$(acc_el.parentNode).children("div:first").children("div:first").
				addClass(a.active_head_class);
		}
	},

	removeActiveHeadClass: function (id, acc_el) {
		var a = il.Accordion.data[id];

		if (a.active_head_class && a.active_head_class != "" && acc_el) {
			$(acc_el.parentNode).children("div:first").children("div:first").
				removeClass(a.active_head_class);
		}
	},

	showAll: function (e) {
		var options, id = e.data.id;
		var a = il.Accordion.data[id];
		e.preventDefault();
		e.stopPropagation();
		if (a.multi) {

			//console.log("deactivate");
			a.animating = true;

			$("#" + id).children().children("." + a.content_class).each(function () {
				t = $(this);
				if (t.hasClass("ilAccHideContent")) {

					if (a.active_head_class) {
						$(this.parentNode).children("div:first").children("div:first").
							addClass(a.active_head_class);
					}

					// fade in the accordion (currentAccordion)
					options = il.Accordion.prepareShow(a, t);
					$(t).animate(options, il.Accordion.duration, function () {

						$(t).css("height", "auto");

						// set the currently shown accordion
						a.last_opened_acc = t;
						il.Accordion.rerenderMathJax(t);

						a.animating = false;
					});
				}
			});

			il.Accordion.saveAllAsOpenedTabs(a, id);
		}

		return false;
	},

	hideAll: function (e) {
		var id = e.data.id;
		var a = il.Accordion.data[id];
		e.preventDefault();
		e.stopPropagation();
		if (a.multi) {
//			console.log("hide all");

			//console.log("deactivate");
			a.animating = true;

			$("#" + id).children().children("." + a.content_class).each(function () {
				t = $(this);
				if (!t.hasClass("ilAccHideContent")) {

					il.Accordion.removeActiveHeadClass(id, t);

					if (a.orientation == 'vertical') {
						options = { height: 0 }
					} else {
						options = { width: 0 }
					}

					t.animate(options, il.Accordion.duration, function () {
//						console.log("adding hide to");
//						console.log(this);
						$(this).addClass("ilAccHideContent");
						a.last_opened_acc = null;
						a.animating = false;
					});
				}
			});

			if (typeof a.save_url != "undefined" && a.save_url != "") {
				il.Util.sendAjaxGetRequestToUrl(a.save_url + "&act=clear&tab_nr=", {}, {}, null);
			}
		}
		return false;
	},

	deactivate: function(id, el) {
		var options, act, a = il.Accordion.data[id];

//console.log("deactivate");
		a.animating = true;

		//$(el).css("display", "block");

		il.Accordion.removeActiveHeadClass(id, a.clicked_acc);

		if (a.orientation == 'vertical') {
			options = { height: 0 }
		} else {
			options = { width: 0 }
		}

		$(a.clicked_acc).animate(options, il.Accordion.duration, function () {
			$(a.clicked_acc).addClass("ilAccHideContent");
			a.last_opened_acc = null;
			a.animating = false;
			if (typeof a.save_url != "undefined" && a.save_url != "") {
				act = (a.multi)
					? "&act=rem"
					: "&act=clear";
				tab_nr = il.Accordion.getTabNr(a.clicked_acc);
				il.Util.sendAjaxGetRequestToUrl(a.save_url + act + "&tab_nr=" + tab_nr, {}, {}, null);
			}
		});
	},

	getTabNr: function (acc_el) {
		var tab_nr = 1;
		var cel = acc_el.parentNode;
		while(cel = cel.previousSibling) {
			if (cel.nodeName.toUpperCase() == 'DIV') {
				tab_nr++;
			}
		}
		return tab_nr;
	},

	prepareShow: function(a, acc_el) {
		var options;
		if (a.orientation == 'vertical')
		{
			$(acc_el).css("position", 'relative')
				.css("left", '-10000px')
				.css("display", 'block');

			$(acc_el).removeClass("ilAccHideContent");

			var nh = a.height
				? a.height
				: $(acc_el).prop("scrollHeight");

			$(acc_el).css("height", '0px')
				.css("position", '')
				.css("display", '')
				.css("left", '');

			options = {height: a.height
				? a.height
				: $(acc_el).prop("scrollHeight")};
		}
		else
		{
			$(acc_el).removeClass("ilAccHideContent");
			options = { width: a.width
				? a.width
				: $(acc_el).prop("scrollWidth")};
		}
		return options;
	},

	saveAllAsOpenedTabs: function(a, id) {
		if (typeof a.save_url != "undefined" && a.save_url != "") {
			tab_nr = il.Accordion.getAllNr(id);
			il.Util.sendAjaxGetRequestToUrl(a.save_url + "&act=set&tab_nr=" + tab_nr, {}, {}, null);
		}
	},

	saveOpenedTabs: function(a, id) {
		if (typeof a.save_url != "undefined" && a.save_url != "")
		{
			if (a.multi) {
				tab_nr = il.Accordion.getAllOpenedNr(id);
			} else {
				tab_nr = il.Accordion.getTabNr(a.last_opened_acc);
			}
			act = "&act=set";
			il.Util.sendAjaxGetRequestToUrl(a.save_url + act + "&tab_nr=" + tab_nr, {}, {}, null);
		}
	},

	handleAccordion: function(id, el) {
//console.log("handle");
		var options, options2, last_acc, tab_nr, a = il.Accordion.data[id];
		a.animating = true;

		// add active class to opened accordion
		if (a.active_head_class && a.active_head_class != '') {
			if (a.last_opened_acc && !a.multi) {
				$(a.last_opened_acc.parentNode).children("div:first").children("div:first").
					removeClass(a.active_head_class);
			}
			$(a.clicked_acc.parentNode).children("div:first").children("div:first").
				addClass(a.active_head_class);
		}

		// fade in the new accordion (currentAccordion)
		options = il.Accordion.prepareShow(a, a.clicked_acc);

		$(a.clicked_acc).animate(options, il.Accordion.duration, function () {

			$(a.clicked_acc).css("height", "auto");

			// set the currently shown accordion
			a.last_opened_acc = a.clicked_acc;

			il.Accordion.rerenderMathJax(a.clicked_acc);

			il.Accordion.saveOpenedTabs(a, id);

			a.animating = false;
		});


		// fade out the currently shown accordion (last_opened_acc)
		if ((last_acc = a.last_opened_acc) && !a.multi) {

			if (a.orientation == 'vertical') {
				options2 = {height: 0};
			} else {
				options2 = {width: 0};
			}
			$(last_acc).animate(options2, il.Accordion.duration, function () {
				$(last_acc).addClass("ilAccHideContent");
			});
		}
	},

	rerenderMathJax: function(acc_el) {
		if (typeof MathJax != "undefined") {
			MathJax.Hub.Queue(["Reprocess",MathJax.Hub, acc_el[0]]);
		}
		// see http://docs.mathjax.org/en/latest/typeset.html
	}

};