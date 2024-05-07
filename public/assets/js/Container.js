il = il || {};
il.Container = il.Container || {};
(function($, il) {
	il.Container = (function($) {
		var initShowMore = function (id, block, url) {
			$("#" + id).on("click", function(e) {
				e.preventDefault();
				var ids = $("#" + id).closest(".ilContainerItemsContainer").find("[data-list-item-id]")
					.map(function() { return $(this).data("list-item-id"); }).get();
				il.Util.sendAjaxPostRequestToUrl(url, {ids: ids}, function(o) {
					$("#" + id).closest(".ilContainerShowMore").replaceWith($(o).find(".ilContainerItemsContainer").children());
				})
			});
		};

		return {
			initShowMore: initShowMore
		};
	})($);
})($, il);

(function($){
	$(function() {
		var container_header_exp = $("div.ilContainerBlock[data-behaviour='2'] > div.ilContainerBlockHeader");
		var container_header_col = $("div.ilContainerBlock[data-behaviour='1'] > div.ilContainerBlockHeader");

		var container_header = container_header_exp.add(container_header_col);
		var class_expanded = "ilContainerBlockHeaderExpanded";
		var class_collapsed = "ilContainerBlockHeaderCollapsed";

		// init style
		container_header_exp.addClass(class_expanded);
		container_header_col.addClass(class_collapsed);
		container_header_col.parent().children(".ilContainerItemsContainer").addClass("ilNoDisplay").hide();

		function saveAction(url, act) {
			if (url) {
				il.Util.sendAjaxGetRequestToUrl(url + "&act="+ act, {}, {}, null);
			}
		}

		// init event
		container_header.on("click", function (e) {
			var source_element = $(e.target);
			var t = $(this);
			var item_container = t.parent().children(".ilContainerItemsContainer").first();
			var bs_css_correction_on = {'margin-left': '-15px', 'margin-right': '-15px', 'padding-left': '15px', 'padding-right': '15px'};
			var bs_css_correction_off = {'margin-left': '', 'margin-right': '', 'padding-left': '', 'padding-right': ''};

			// check event source for being the header (not included buttons for the drop downs)
			if (!source_element.hasClass("ilContainerBlockHeader")) {
				return;
			}

			//console.log(item_container);
			//console.log($(this).parent().children(".ilContainerItemsContainer"));
			//$(this).parent().children(".ilContainerItemsContainer").addClass("ilNoDisplay");

			if (item_container.hasClass("ilNoDisplay")) {
				item_container.removeClass("ilNoDisplay");
				item_container.css(bs_css_correction_on);
				item_container.animate({height: "show"}, 150, function () {
					item_container.css(bs_css_correction_off);
					t.addClass(class_expanded);
					t.removeClass(class_collapsed);
					saveAction(t.parent().attr("data-store-url"), "expand");
				});
			} else {
				item_container.css(bs_css_correction_on);
				item_container.animate({height: "hide"}, 150, function () {
					item_container.addClass("ilNoDisplay");
					item_container.css(bs_css_correction_off);
					t.addClass(class_collapsed);
					t.removeClass(class_expanded);
					saveAction(t.parent().attr("data-store-url"), "collapse");
				});
			}
		});

		container_header.find("a").click(function(e) {
			e.stopPropagation(); // enable links inside of accordion header
		});

	});
}($));