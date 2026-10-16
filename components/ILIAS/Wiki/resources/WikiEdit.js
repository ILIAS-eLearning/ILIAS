if (!il.Wiki) {
	il.Wiki = {};
}

il.Wiki.Edit = {
	url: '',
	txt: {},

	setTargetInfoText: function (t) {
		$("#il_prop_cont_target_page div.help-block").html(t);
	},

	openLinkDialog: function(url, target_page, addCallback) {
		il.Wiki.Edit.url = url;
		il.Wiki.Edit.addCallback = addCallback;

		il.IntLink.showPanel();
		il.repository.core.fetchHtml(url + "&cmd=insertWikiLink").then(function(html) {
			var val_sel;

			// output html
			if(html !== undefined) {
				$('#ilIntLinkModalContent').html(html);
			}

			il.Wiki.Edit.setTargetInfoText("&nbsp;");

			$("#ililIntLinkModalContent .ilFormHeader input.submit").css("display", "none");

			il.Wiki.Edit.initTextInputAutoComplete();

			$('input#target_page').on('input', function() {
				il.Wiki.Edit.setTargetInfoText(il.Wiki.Edit.txt.new_page);
			});

			val_sel = target_page;
			if (val_sel != "") {
				$("input#target_page").val(val_sel);
				$("input#target_page").focus();
				document.getElementById("target_page").dispatchEvent(new KeyboardEvent("keyup", {key: "Enter"}));
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
					il.Wiki.Edit.addCallback("[[" + tp + "]]");
				}
				e.stopPropagation();
				e.preventDefault();
				il.IntLink.hidePanel();
			});

			// cancel inserting wiki link
			$("input[name*='searchWikiLink']").on("click", function(e) {
				e.stopPropagation();
				e.preventDefault();
				il.repository.core.fetchHtml(url + "&cmd=searchWikiLinkAC&term=" + encodeURIComponent($("input#target_page").val())).then(function(html) {
					// output html
					if(html !== undefined) {
						$('#ilIntLinkModalContent').html(html);
					}
					$("#ilWikiACSearchResult a").click(function () {
						il.Wiki.Edit.openLinkDialog(il.Wiki.Edit.url, $(this).text(), il.Wiki.Edit.addCallback);
						return false;
					});
					$("#ilWikiACSearchCancel").click(function () {
						console.log("search cancel!");
						il.Wiki.Edit.openLinkDialog(il.Wiki.Edit.url, "", il.Wiki.Edit.addCallback);
					});

				});
			});


		});
	},

	/**
	 * Init autocomplete for target page
	 */
	initTextInputAutoComplete: function () {
		const input = document.getElementById("target_page");
		il.LegacyForm.autocomplete.init(input, {
			dataSource: il.Wiki.Edit.url + "&cmd=insertWikiLinkAC",
			delimiter: null,
			autocompleteLength: 3,
			moreText: il.Wiki.Edit.txt.more,
			open: function(list) {
				const matches = Array.from(list.children).some(function(item) {
					return item.dataset.value.toLowerCase() === input.value.toLowerCase();
				});
				il.Wiki.Edit.setTargetInfoText(matches ? il.Wiki.Edit.txt.page_exists : il.Wiki.Edit.txt.new_page);
				list.addEventListener("click", function() {
					il.Wiki.Edit.setTargetInfoText(il.Wiki.Edit.txt.page_exists);
				});
				list.addEventListener("keydown", function(e) {
					if (e.key === "Enter") {
						il.Wiki.Edit.setTargetInfoText(il.Wiki.Edit.txt.page_exists);
					}
				});
			}
		});
	}

}