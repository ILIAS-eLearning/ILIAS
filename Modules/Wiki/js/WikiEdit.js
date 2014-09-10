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

			il.Wiki.Edit.initTextInputAutoComplete();

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
	},

	/**
	 * Init autocomplete for target page
	 */
	initTextInputAutoComplete: function () {
		$.widget( "custom.iladvancedautocomplete", $.ui.autocomplete, {
			more: false,
			_renderMenu: function(ul, items) {
				var that = this;
				$.each(items, function(index, item) {
					that._renderItemData(ul, item);
				});

				that.options.requestUrl = that.options.requestUrl.replace(/&fetchall=1/g, '');

				// set position to be absolute, note relative (standar behaviour)
				$("#ilIntLinkPanel ul.ui-autocomplete").css("position", "absolute");
			}
		});

		$('input#target_page').iladvancedautocomplete({
			requestUrl: il.Wiki.Edit.url + "&cmd=insertWikiLinkAC",
			appendTo: "#ilIntLinkPanel",
			source: function( request, response ) {
				var that = this;
				$.getJSON( that.options.requestUrl, {
					term: request.term
				}, function(data) {
					if (typeof data.items == "undefined") {
						response(data);
					} else {
						that.more = data.hasMoreResults;
						response(data.items);
					}
				});
			},
			minLength: 3
		});
	}

}