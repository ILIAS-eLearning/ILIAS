(function (root, scope, factory) {
	scope.ForumExplorer = factory(root.jQuery);
}(window, il, function($) {

	"use strict";

	var pub = {};

	pub.init = function(options) {
		var settings = $.extend({
			selectors: {
				container:            "",
				unlinked_content:     ""
			}
		}, options);

		$(function() {
			$(settings.selectors.container).on("click", settings.selectors.unlinked_content, function(e) {
				e.preventDefault();
				e.stopPropagation();
			});
		});
	};

	return pub;
}));