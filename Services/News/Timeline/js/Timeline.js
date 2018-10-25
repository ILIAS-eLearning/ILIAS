il.Timeline = {
	compressEntries: function () {
		var minspace, prev, el_top, prev_badge_top, el, mt, mt2, mt3, prev_top, d;
		var t = il.Timeline;
		t.removeRedundantBadges();

		$("ul.ilTimeline > li")
			.css("margin-top", "0px");

		// if we do not have a float right element (narrow sreen view) > do not compress
		d = $(".ilTimelinePanel:eq(1)");
		if (!d.length) {
			return;
		}
		if (window.getComputedStyle(d.get(0),null).getPropertyValue("float") != "right") {
			return;
		}

		// minimum space is the heigth of the badge
		minspace = $("ul.ilTimeline div.ilTimelineBadge").outerHeight();

		//console.log(minspace);

		$("ul.ilTimeline > li")
			.each(function () {
				el = this;
				prev = $(el).prev("li");
				if (prev.length) {

					// y position of previous element
					prev_top = $(prev).position().top;

					// y position of element
					el_top = $(el).position().top;

					// at least two badges lower than the last on the other side
					mt = prev_top + (1.2 * minspace) - el_top;

					// if an element exists over our element, move up to element
					prev2 = $(prev).prev("li");
					if (prev2.length) {
						prev2_bottom = $(prev2).position().top + $(prev2).outerHeight(true);
						mt2 = prev2_bottom - el_top;
						if (mt2 > mt) {
							mt = mt2;
						}
					}

					// if a previous badge exists, do not go futher than the badge
					if ($(el).prevAll("li").find(".ilTimelineBadge").length > 0) {
						prev_badge_top = $(el).prevAll("li").find(".ilTimelineBadge").first().position().top;
						if (prev_badge_top > 0) {
							mt3 = prev_badge_top + minspace - el_top;
							if (mt3 > mt) {
								mt = mt3;
							}
						}
					}

					if (mt < 0) {
						$(el).css("margin-top", mt + "px");
					}
			}
		});

	},

	removeRedundantBadges: function() {
		var last_el;
		$(".ilTimelineBadge").each(function () {

			if (typeof last_el != "undefined" && $(this).html() == $(last_el).html()) {
				$(last_el).remove();
			}
			last_el = this;
		});
	}
};

$(function () {
	//$('.dynamic-max-height').dynamicMaxHeight();
	il.Timeline.compressEntries();
	$(window).resize(il.Timeline.compressEntries);
});

$(window).on("load", function() {
	$('.dynamic-max-height').dynamicMaxHeight();
	il.Timeline.compressEntries();
	$(window).resize(il.Timeline.compressEntries);
});