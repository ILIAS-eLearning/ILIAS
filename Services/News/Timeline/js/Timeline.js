il.Timeline = {
	compressEntries: function () {
		var minspace, prev, el_top, prev_badge_top, el, mt, mt2, mt3, prev_top;

		// minimum space is the heigth of the badge
		minspace = $("ul.ilTimeline div.ilTimelineBadge").outerHeight();

		console.log(minspace);

		$("ul.ilTimeline > li")
			.css("margin-top", "0px")
			.each(function () {
				el = this;
				prev = $(el).prev("li");
				if (prev.length) {

					// y position of previous element
					prev_top = $(prev).position().top;

					// y position of element
					el_top = $(el).position().top;

					// at least two badges lower than the last on the other side
					mt = prev_top + (1.5 * minspace) - el_top;

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
					prev_badge_top = $(el).prevAll("li").find(".ilTimelineBadge").first().position().top;
					if (prev_badge_top > 0) {
						mt3 = prev_badge_top + minspace - el_top;
						if (mt3 > mt || mt == 0) {
							mt = mt3;
						}
					}

					if (mt < 0) {
						$(el).css("margin-top", mt + "px");
					}
			}
		});

	}
};

$(function () {
	il.Timeline.compressEntries();
	$(window).resize(il.Timeline.compressEntries);
});