if (!il.Wiki) {
	il.Wiki = {};
}

il.Wiki.Edit = {
	url: '',
	txt: {},

	setTargetInfoText: function (t) {
		$("#il_prop_cont_target_page div.help-block").html(t);
	},

	openLinkDialog: function(url, target_page) {
		il.Wiki.Edit.url = url;

		il.IntLink.showPanel();
		il.Util.sendAjaxGetRequestToUrl(url + "&cmd=insertWikiLink", {}, {el_id: "ilIntLinkModalContent"}, function(o) {
			var val_sel;

			// output html
			if(o.responseText !== undefined) {
				$('#' + o.argument.el_id).html(o.responseText);
			}

			il.Wiki.Edit.setTargetInfoText("&nbsp;");

			$("#ililIntLinkModalContent .ilFormHeader input.submit").css("display", "none");

			il.Wiki.Edit.initTextInputAutoComplete();

			$('input#target_page').on('input', function() {
				il.Wiki.Edit.setTargetInfoText("&nbsp;");
			});

			if (target_page) {
				val_sel = target_page;
			} else {
				val_sel = ilCOPage.getSelection();
			}
			if (val_sel != "") {
				$("input#target_page").val(val_sel);
				$("input#target_page").focus();
				$("input#target_page").iladvancedautocomplete('search', val_sel);
			} else {
				$("input#target_page").focus();
			}

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
			$("input[name*='searchWikiLink']").on("click", function(e) {
				e.stopPropagation();
				e.preventDefault();
				il.Util.sendAjaxGetRequestToUrl(url + "&cmd=searchWikiLinkAC&term=" + encodeURIComponent($("input#target_page").val()), {}, {el_id: "ilIntLinkModalContent"}, function(o) {
					// output html
					if(o.responseText !== undefined) {
						$('#' + o.argument.el_id).html(o.responseText);
					}
					$("#ilWikiACSearchResult a").click(function () {
						il.Wiki.Edit.openLinkDialog(il.Wiki.Edit.url, $(this).html());
						return false;
					});
					$("#ilWikiACSearchCancel").click(function () {
						console.log("search cancel!");
						il.Wiki.Edit.openLinkDialog(il.Wiki.Edit.url);
					});

				});
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
				$("#ilIntLinkModalContent ul.ui-autocomplete").css("position", "absolute");
				if (items[0] && ($("input#target_page").val().toLowerCase() == items[0].value.toLowerCase())) {
					il.Wiki.Edit.setTargetInfoText(il.Wiki.Edit.txt.page_exists);
				}
			}
		});

		$('input#target_page').iladvancedautocomplete({
			requestUrl: il.Wiki.Edit.url + "&cmd=insertWikiLinkAC",
			appendTo: "#ilIntLinkModalContent",
			response: function(e, u) {
				il.Wiki.Edit.setTargetInfoText(il.Wiki.Edit.txt.new_page);
			},
			select: function(e, u) {
				il.Wiki.Edit.setTargetInfoText(il.Wiki.Edit.txt.page_exists);
			},
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