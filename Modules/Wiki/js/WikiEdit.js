if (!il.Wiki) {
	il.Wiki = {};
}

il.Wiki.Edit = {
	url: '',

	openLinkDialog: function(url) {
		il.Wiki.Edit.url = url;

		il.IntLink.showPanel();
		il.Util.sendAjaxGetRequestToUrl(url + "&cmd=insertWikiLink", {}, {el_id: "ilIntLinkPanel"}, function(o) {
			// output html
			if(o.responseText !== undefined) {
				$('#' + o.argument.el_id).html(o.responseText);
			}

			$("input#target_page").val(ilCOPage.getSelection());

			// add wiki link
			$("input[name*='addWikiLink']").on("click", function(e) {
				var tp, lt;

				tp = $("input#target_page").val();
				lt = $("input#link_text").val();
				if (tp != "") {
					if (lt != "" && lt != tp) {
						tp = tp + "|" + lt;
					}
					ilCOPage.addBBCode("[[" + tp + "]]", "", true);
				}
				e.stopPropagation();
				e.preventDefault();
				il.IntLink.hidePanel();
			});

			// cancel inserting wiki link
			$("input[name*='cancelInsertWikiLink']").on("click", function(e) {
				e.stopPropagation();
				e.preventDefault();
				il.IntLink.hidePanel();
			});


		});
	}
}