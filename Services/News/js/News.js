il.News = {

	items: {},

	current_id: 0,

	ajax_url: "",

	requestRunning: false,

	init: function () {
		var t = il.News;
		$("#news_btn_cancel_update").on("click", function (e) {
			e.preventDefault();
			$('#ilNewsEditModal').modal('hide');
		});
		$("#news_btn_update").on("click", function (e) {
			var t = il.News;
			e.preventDefault();
			t.save();
			$('#ilNewsCreateModal').modal('hide');
		});
		t.moreOnScroll();
	},

	moreOnScroll: function() {
		var w = $(window), t = il.News;
		w.off('scroll');
		w.on('scroll', function() {
			if($(window).scrollTop() + $(window).height() + 60 > $(document).height()) {
				t.moreNews();
			}
		});
	},

	startMoreRequest: function () {
		var t = il.News;
		if (t.requestRunning) {
			return false;
		}
		t.requestRunning = true;
		t.showLoader(true);

		return true;
	},

	stopMoreRequest: function () {
		var t = il.News;
		t.requestRunning = false;
		t.showLoader(false);
	},

	moreNews: function() {
		var t = il.News;
		if (!t.startMoreRequest()) {
			return;
		}
		//console.log("get more news");

		$(window).off('scroll');

		$.ajax({
			url:      il.News.ajax_url + "&cmd=loadMore",
			type:     "POST",
			dataType: "json",
			data:     {
				'rendered_news'   : $.map(il.News.items, function(e) {
					return e.id
				})
			}
		}).done(function (r) {
			if (r.data !== undefined && r.data.html !== '')
			{
				t.appendNews(r);
				//il.News.addScrollToBottomListener();
				t.stopMoreRequest();
			}
			else
			{
				t.stopMoreRequest();
			}
		}).fail(function (e) {
			t.stopMoreRequest();
		});
	},

	showLoader : function(s) {
		if(s) {
			$('.moreLoader').removeClass('ilHidden');
		} else {
			$('.moreLoader').addClass('ilHidden');
		}
	},

	appendNews: function (r) {
		var t = il.News;
		if (r.html == "") {
			return;
		}
		//console.log(r.data);
		for (var i in r.data) {
			t.items[i] = r.data[i];
		}
		$("ul.ilTimeline").append(r.html);

		$('.dynamic-height-active').removeClass("dynamic-height-active");
		$('.js-dynamic-show-hide').css("display", "").off("click");
		$('.dynamic-height-wrap').css('max-height', "");
		$('.dynamic-max-height').dynamicMaxHeight();

		il.Timeline.compressEntries();
		il.MediaObjects.autoInitPlayers();
		t.moreOnScroll();
	},

	setAjaxUrl: function (url) {
		var t = il.News;

		t.ajax_url = url;
	},

	setItems: function (items) {
		var t = il.News;

		t.items = items;
	},

	create: function() {
		var t = il.News;

		t.current_id = 0;

		$('#ilNewsEditModal .modal-title').html(il.Language.txt("create"));
		$('#news_btn_update').attr("value", il.Language.txt("save"));
		$("#news_title").val("");
		$("#news_content").val("");
		$("#news_content_long").val("");
		$('#ilNewsEditModal').modal('show');

		return false;
	},

	edit: function(id) {
		var t = il.News;
		t.current_id = id;

		$('#ilNewsEditModal .modal-title').html(il.Language.txt("edit"));
		$('#news_btn_update').attr("value", il.Language.txt("update"));
		$("#news_title").val(t.items[id].title);
		$("#news_content").val(t.items[id].content);

		if (typeof tinyMCE != "undefined" && tinyMCE.get('news_content_long')) {
			tinyMCE.get('news_content_long').setContent(t.items[id].content_long);
		} else {
			$("#news_content_long").val(t.items[id].content_long);
		}

		$('#ilNewsEditModal').modal('show');

		return false;
	},

	save: function () {
		var t = il.News, cmd, d, long;

		if (typeof tinyMCE != "undefined" && tinyMCE.get('news_content_long')) {
			long = tinyMCE.get('news_content_long').getContent();
		} else {
			long = $("#news_content_long").val();
		}

		// data
		d = {
			news_title: $("#news_title").val(),
			news_content: $("#news_content").val(),
			news_content_long: long
		}

		if (t.current_id > 0) {
			d.id = t.current_id;
			cmd = "update";
		} else {
			cmd = "save";
		}

		$.ajax({
			url : t.ajax_url + "&cmd=" + cmd,
			type: "POST",
			data : d,
			success: function(data, s, j) {
//				console.log(data);
				window.location.href = t.ajax_url + "&cmd=show";
			},
			error: function (j, s, e)
			{
				window.location.href = t.ajax_url + "&cmd=show";
			}
		});

	}

};

$(function() {
	il.News.init();
});
