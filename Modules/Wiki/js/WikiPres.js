if (!il.Wiki) {
	il.Wiki = {};
}

il.Wiki.Pres = {
	url: '',

	init: function (url) {
		var t = il.Wiki.Pres;

		t.url = url;
		$("#il_wiki_user_export").on("click", function (e) {
			e.preventDefault();
			$("<div id='il_wiki_export_progress'></div>").insertAfter("#il_wiki_user_export");
			t.startHTMLExport();
		});
	},

	startHTMLExport: function () {
		var t = il.Wiki.Pres;

		il.Util.sendAjaxGetRequestToUrl(t.url + "&cmd=initUserHTMLExport", {}, {}, function (o) {
			var t = il.Wiki.Pres;
			console.log(o.responseText);
			if (o.responseText == 2) {
				window.location.href = t.url + "&cmd=downloadUserHTMLExport";
			} else {
				il.Util.sendAjaxGetRequestToUrl(t.url + "&cmd=startUserHTMLExport", {}, {}, function () {
				});
				var t = il.Wiki.Pres;
				t.updateProgress();
			}
		});
	},

	updateProgress: function () {
		var t = il.Wiki.Pres;

		il.Util.sendAjaxGetRequestToUrl(t.url + "&cmd=getUserHTMLExportProgress", {}, {}, t.ajaxProgressSuccess);
	},

	ajaxProgressSuccess: function (o) {
		var t = il.Wiki.Pres;

		if(o.responseText !== undefined) {
			var s = JSON.parse(o.responseText);
			$("#il_wiki_export_progress").html(s.progressBar);
			if (s.status != 0) {
				window.setTimeout(t.updateProgress, 1000);
			} else {
				window.location.href = t.url + "&cmd=downloadUserHTMLExport";
				$("#il_wiki_export_progress").remove();
			}
		}
	}
};