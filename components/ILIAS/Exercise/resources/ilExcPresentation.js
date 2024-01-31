(function($, window) {
	"use strict";
	$(window).on("load", function () {

		// check if lense icons should be displayed or not
		var resizeHandler = function () {
			$(".ilExcOverview .ilExcAssImageContainer img.img-responsive").each(function () {
				if (this.naturalWidth > this.clientWidth) {
					$(this).parent().find("div > img").removeClass("ilNoDisplay");
				} else {
					$(this).parent().find("div > img").addClass("ilNoDisplay");
				}
			});
		};

		$(window).resize(resizeHandler);

		$(".il_VAccordionContentDef").on("il.accordion.opened", function (ev, el) {
			resizeHandler();
			$(el).find("video, audio").each(function(o) {
				$(this)[0].load();
			});
		});

		resizeHandler();
	});
}($, window));