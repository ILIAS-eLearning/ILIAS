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

		il.Util.sendAjaxGetRequestToUrl(t.url + "&cmd=initUserHTMLExport", {}, {}, function () {

		});
	},

	updateProgress: function () {
		var t = il.Wiki.Pres;

		il.Util.sendAjaxGetRequestToUrl(t.url + "&cmd=getUserHTMLExportProgress", {}, {}, t.ajaxProgressSuccess);
	},

	ajaxProgressSuccess: function (o) {
		var t = il.Wiki.Pres;

		if(o.responseText !== undefined) {
			$("#il_wiki_export_progress").html(o.responseText);
			window.setTimeout(t.updateProgress, 1000);
		}
	}
};