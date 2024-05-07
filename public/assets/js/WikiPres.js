if (!il.Wiki) {
	il.Wiki = {};
}

il.Wiki.Pres = {
	url: '',
	with_comments: 0,

	init: function (url) {
		var t = il.Wiki.Pres;

		t.url = url;
		$("#il_wiki_user_export").on("click", function (e) {
			e.preventDefault();
			t.with_comments = 0;
			t.performHTMLExport();
		});
	},

	performHTMLExportWithComments: function() {
		const t = il.Wiki.Pres;
		t.performHTMLExport(1);
	},

	performHTMLExport: function(with_comments = 0) {
		const t = il.Wiki.Pres;
		t.with_comments = with_comments;
		$("<div id='il_wiki_export_progress'></div>").insertAfter("#il_wiki_user_export");
		t.startHTMLExport();
	},

	getDownloadCommand: () => {
		var t = il.Wiki.Pres;
		if (t.with_comments) {
			return "downloadUserHTMLExportWithComments";
		}
		return "downloadUserHTMLExport";
	},

	startHTMLExport: function () {
		var t = il.Wiki.Pres;
		const par = {
			with_comments: t.with_comments
		};

		il.Util.sendAjaxGetRequestToUrl(t.url + "&cmd=initUserHTMLExport", par, {}, function (o) {
			var t = il.Wiki.Pres;
			console.log(o.responseText);
			if (o.responseText == 2) {
				window.location.href = t.url + "&cmd=" + t.getDownloadCommand();
			} else {
				il.Util.sendAjaxGetRequestToUrl(t.url + "&cmd=startUserHTMLExport", par, {}, function () {
				});
				var t = il.Wiki.Pres;
				t.updateProgress();
			}
		});
	},

	updateProgress: function () {
		var t = il.Wiki.Pres;
		const par = {
			with_comments: t.with_comments
		};

		il.Util.sendAjaxGetRequestToUrl(t.url + "&cmd=getUserHTMLExportProgress", par, {}, t.ajaxProgressSuccess);
	},

	ajaxProgressSuccess: function (o) {
		var t = il.Wiki.Pres;

		if(o.responseText !== undefined) {
			var s = JSON.parse(o.responseText);
			$("#il_wiki_export_progress").html(s.progressBar);
			if (s.status != 0) {
				window.setTimeout(t.updateProgress, 1000);
			} else {
				window.location.href = t.url + "&cmd=" + t.getDownloadCommand();
				$("#il_wiki_export_progress").remove();
			}
		}
	}
};