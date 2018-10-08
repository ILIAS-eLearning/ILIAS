if (typeof il == "undefined") {
	il = {};
}

il.ExtLink = {

	/**
	 * Linkify wrapper
	 */
	autolink: function (selector, link_class) {
		$(selector).linkify();
		if (typeof link_class !== "undefined") {
			$(selector + " a.linkified").addClass(link_class);
		}

		if (typeof selector === "string") {
			$(selector + " a.linkified[target='_blank']").attr("rel", "noreferrer noopener");
		} else {
			$(selector).find("a.linkified[target='_blank']").attr("rel", "noreferrer noopener");
		}
	}
}
