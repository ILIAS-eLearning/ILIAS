il.News = {

	items: {},

	current_id: 0,

	ajax_url: "",

	requestRunning: false,

	scroll_init: false,

	init: function () {
		var t = il.News;
		$("#news_btn_cancel_update").on("click", function (e) {
			e.preventDefault();
			$(".ilAdminRow .alert").remove();
			$('#ilNewsEditModal').modal('hide');
		});
		$("#news_btn_update").on("click", function (e) {
			var t = il.News;
			//e.preventDefault();
			t.save();
			$('#ilNewsCreateModal').modal('hide');
		});
		$("#news_btn_cancel_delete").on("click", function (e) {
			e.preventDefault();
			$('#ilNewsDeleteModal').modal('hide');
		});
		$("#news_btn_delete").on("click", function (e) {
			var t = il.News;
			e.preventDefault();
			t.remove();
			$('#ilNewsDeleteModal').modal('hide');
		});
		t.moreOnScroll();
	},

  moreOnScroll: function() {
    var w = $('main'), t = il.News;
    console.log(w);
    if (!t.scroll_init) {
      w.on('scroll', function () {
        var main = $('main');
        var sp = main.scrollTop();    // scroll position (starting with 0)
        var vh = main.height();       // visible height
        var th = main[0].scrollHeight;  // total height
        if (sp + vh + 60 > th) {
          t.moreNews();
        }
      });
      t.scroll_init = true;
    }
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

		t.scroll_init = false;
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
			$('.ilNewsTimelineMoreLoader').removeClass('ilHidden');
		} else {
			$('.ilNewsTimelineMoreLoader').addClass('ilHidden');
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

	create: function(keep_values) {
		var t = il.News;

		t.current_id = 0;

		$('#ilNewsEditModal .modal-title').html(il.Language.txt("create"));
		$('#news_btn_update').attr("value", il.Language.txt("save"));
		if (!keep_values) {
			$("#news_title").val("");
			$("#news_content").val("");
			$("#news_content_long").val("");
			$(".help-block.alert").remove();
		}
		if (typeof tinyMCE != "undefined" && tinyMCE.get('news_content')) {
			tinyMCE.get('news_content').setContent("");
		}
		$('#ilNewsEditModal input[name="media_delete"]').css("display", "none");
		$('#ilNewsEditModal label[for="media_delete"]').css("display", "none");
		$('#ilNewsEditModal').modal('show');

		return false;
	},

	edit: function(id, keep_values) {
		var t = il.News;
		t.current_id = id;

		$('#ilNewsEditModal .modal-title').html(il.Language.txt("edit"));
		$('#news_btn_update').attr("value", il.Language.txt("save"));
		if (!keep_values) {
			$("#news_title").val(t.items[id].title);
			$("#news_visibility input[value='" + t.items[id].visibility + "']").prop('checked', true);
			if (typeof tinyMCE != "undefined" && tinyMCE.get('news_content')) {
				tinyMCE.get('news_content').setContent(t.items[id].content);
			} else {
				$("#news_content").val(t.items[id].content);
			}
			$(".help-block.alert").remove();
		}

		if (t.items[id].mob_id > 0) {
			$('#ilNewsEditModal input[name="media_delete"]').css("display", "");
			$('#ilNewsEditModal label[for="media_delete"]').css("display", "");
			$('#ilNewsEditModal input[name="media_delete"]').prop( "checked", false );
		} else {
			$('#ilNewsEditModal input[name="media_delete"]').css("display", "none");
			$('#ilNewsEditModal label[for="media_delete"]').css("display", "none");
		}


		$('#ilNewsEditModal').modal('show');

		return false;
	},

	save: function () {
		var t = il.News, cmd, d, content;

		if (typeof tinyMCE != "undefined" && tinyMCE.get('news_content')) {
			content = tinyMCE.get('news_content').getContent();
		} else {
			content = $("#news_content").val();
		}
		// data
		d = {
			news_title: $("#news_title").val(),
			news_visibility: $("#news_visibility input[type='radio']:checked").val(),
			news_content: content,
			news_content_long: ""
		}

		if (t.current_id > 0) {
			d.id = t.current_id;
			cmd = "update";
		} else {
			cmd = "save";
		}

		$("#id").val(d.id);
		$("#news_action").val(cmd);

	//	$("#form_news_edit_form").submit();

		return;

		//console.log(d); return;

		$.ajax({
			url : t.ajax_url + "&cmd=" + cmd,
			type: "POST",
			data : d,
			success: function(data, s, j) {
				console.log(data); return false;
				window.location.href = t.ajax_url + "&cmd=show";
			},
			error: function (j, s, e)
			{
				window.location.href = t.ajax_url + "&cmd=show";
			}
		});

	},

	delete: function(id) {
		var t = il.News;
		t.current_id = id;

		console.log("DELETE");
		//$('#news_btn_delete').attr("value", il.Language.txt("update"));
		$("#news_delete_news_title").html(t.items[id].title);

		const newsData = document.querySelector("[data-news-type='init']");
		if (newsData) {
			const signalId = newsData.dataset.newsDeleteModalSignal;
			console.log(signalId);
			$(document).trigger(
				signalId,
				{
					id: signalId,
					triggerer: $(this),
					options: JSON.parse('[]'),
				},
			);
		}
		//$('#ilNewsDeleteModal').modal('show');

		return false;
	},

	remove: function () {
		var t = il.News, cmd, d, content;

		cmd = "remove";

		d = {
			id: t.current_id
		};

		$.ajax({
			url : t.ajax_url + "&cmd=" + cmd,
			type: "POST",
			data : d,
			success: function(data, s, j) {
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
