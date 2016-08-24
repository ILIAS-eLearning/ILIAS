il.News = {

	items: {},

	current_id: 0,

	ajax_url: "",

	init: function () {

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
	},

	setAjaxUrl: function (url) {
		var t = il.News;

		t.ajax_url = url;
	},

	setItems: function (items) {
		var t = il.News;

		t.items = items;
		console.log(t.items);
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
		$("#news_content_long").val(t.items[id].content_long);

		$('#ilNewsEditModal').modal('show');

		return false;
	},

	save: function () {
		var t = il.News, cmd, d;

		// data
		d = {
			news_title: $("#news_title").val(),
			news_content: $("#news_content").val(),
			news_content_long: $("#news_content_long").val()
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
