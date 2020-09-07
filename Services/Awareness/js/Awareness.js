il.Awareness = {

	rendered: false,
	base_url: "",
	$body:  $(document.body),
	loader_src: "",


	setBaseUrl: function(url) {
		var t = il.Awareness;
		t.base_url = url;
	},

	getBaseUrl: function() {
		var t = il.Awareness;
		return t.base_url;
	},

	setLoaderSrc: function(loader) {
		var t = il.Awareness;
		t.loader_src = loader;
	},

	getLoaderSrc: function() {
		var t = il.Awareness;
		return t.loader_src;
	},

	init: function() {
		$('#awareness_trigger a').popover({
			html : true,
			placement : "bottom",
			viewport : { selector: 'body', padding: 10 },
			title: " "
		});

		$('#awareness_trigger > a').on('click', function (e) {
			e.preventDefault();
		});

		$('#awareness_trigger a').on('show.bs.popover', function () {
			//$("#awareness-content").html();
//		console.log(this);

		}).on('shown.bs.popover', function () {
			il.Awareness.show();

		}).on('hidden.bs.popover', function () {
			$("body").removeClass("modal-open");
		})

		// close popover when clicked outside. todo: move to a central place?
		$('body').on('click', function (e) {
			$('#awareness_trigger a').each(function () {
				//the 'is' for buttons that trigger popups
				//the 'has' for icons within a button that triggers a popup
				if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
					if ($(this).next(".popover").length) {
						$(this).popover('hide');
					}
				}
			});
		});

		$("#awareness_trigger a").on("awrn:shown", function( event ) {
//			console.log("awrn:shown thrown");
		});
	},

	show: function () {
		var t = il.Awareness;

		$('#awareness_trigger a').siblings(".popover").children(".popover-content").html(t.getContent());
		$("body").addClass("modal-open");
		t.afterListUpdate();
	},

	close: function() {
		$('#awareness_trigger a').popover("hide");
	},

	reload: function () {
		var t = il.Awareness;
		t.updateList($("#il_awareness_filter").val());
	},

	checkScrollbar: function () {
		if (document.body.clientWidth >= window.innerWidth) return
		this.scrollbarWidth = this.scrollbarWidth || this.measureScrollbar()
	},

	setScrollbar: function () {
		var bodyPad = parseInt((this.$body.css('padding-right') || 0), 10)
		if (this.scrollbarWidth) this.$body.css('padding-right', bodyPad + this.scrollbarWidth)
	},

	resetScrollbar: function () {
		this.$body.css('padding-right', '')
	},

	measureScrollbar: function () { // thx walsh
		var scrollDiv = document.createElement('div')
		scrollDiv.className = 'modal-scrollbar-measure'
		this.$body.append(scrollDiv)
		var scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth
		this.$body[0].removeChild(scrollDiv)
		return scrollbarWidth
	},

	getContent: function () {
		var t = il.Awareness;
		if (!t.rendered) {
			t.content = $("#awareness-content-container").html();
			$("#awareness-content-container").html("");
			t.updateList("");
			t.rendered = true;
		}
		return t.content;
	},

	ajaxReplaceSuccess: function(o) {
		var t = il.Awareness, cnt;

		// perform page modification
		if(o.html !== undefined)
		{
			t.content = o.html;
			$('#awareness-content').replaceWith(o.html);
			$('#il_awareness_filter').val(o.filter_val);
			t.afterListUpdate();

			cnt = o.cnt.split(":");
			t.setCounter(cnt[0], false);
			t.setCounter(cnt[1], true);

			// throw custom event
			$("#awareness_trigger a").trigger("awrn:shown");
		}
	},

	afterListUpdate: function() {
		var t = il.Awareness;

		t.fixHeight();

		$('.ilAwarenessItem').on('shown.bs.dropdown', function () {
			var t = il.Awareness;

			t.fixHeight();
		}).on('hidden.bs.dropdown', function () {
			// if done, height is corrected, but dd is not opened if clicked (when other dd has been opened before)
//				t.fixHeight();
		});

		$("#il_awrn_filter_form").submit(function (e) {
			var t = il.Awareness;
			$("#il_awrn_filer_btn").html("<img src='" + t.loader_src + "' />");
			t.updateList($("#il_awareness_filter").val());
			e.preventDefault();
		});
		$("#il_awareness_filter").each(function() {
			t = this;
			t.focus();
			if (t.setSelectionRange) {
				var len = $(t).val().length * 2;
				t.setSelectionRange(len, len);
			}
		});

		$("#awareness-list").trigger("il.user.actions.updated", ["awareness-list"]);
	},

	fixHeight: function() {

		if (!$("#awareness-list").length || !$('.ilAwarenessDropDown .popover').length) {
			return;
		}

		$('.ilAwarenessDropDown .popover').css('height', "");
		var st = $('#awareness-list').scrollTop();
		$('#awareness-list').css('height', "");

		var vp_reg = il.Util.getViewportRegion();
		var awpop = il.Util.getRegion('.ilAwarenessDropDown .popover');
		var awlist = il.Util.getRegion('#awareness-list');
		var pad_bot = 15;
		if ((awpop.top - vp_reg.top + awpop.height + pad_bot) > vp_reg.height) {
			var popHeight = vp_reg.height - (awpop.top - vp_reg.top) - pad_bot;
			$('.ilAwarenessDropDown .popover').css('height', popHeight + "px");

			var listHeight = vp_reg.height - (awlist.top - vp_reg.top) - pad_bot;
			$('#awareness-list').css('height', listHeight + "px");

			$('#awareness-list').scrollTop(st);
		}
	},

	updateList: function(filter) {
		var t = il.Awareness;

		$.ajax({
			url: t.getBaseUrl() + "&cmd=getAwarenessList"
				+ "&filter=" + encodeURIComponent(filter),
			dataType: "json"
		}).done(t.ajaxReplaceSuccess);

		//il.Util.sendAjaxGetRequestToUrl (t.getBaseUrl() + "&cmd=getAwarenessList"
		//	+ "&filter=" + encodeURIComponent(filter),
		//	{}, {}, t.ajaxReplaceSuccess);
	},

	setCounter: function(c, highlighted) {
		var id = "#awareness_badge";

		if (highlighted) {
			id = "#awareness_hbadge";
		}
		$(id + " span").html(c);
		if (c > 0) {
			$(id + " .badge").removeClass("ilAwrnBadgeHidden");
		} else {
			$(id + " .badge").addClass("ilAwrnBadgeHidden");
		}
	}
};
