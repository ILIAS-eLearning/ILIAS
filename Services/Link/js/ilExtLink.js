if (typeof il == "undefined") {
	il = {};
}

il.ExtLink = {

	/**
	 * Linkify wrapper
	 */
	autolink: function (selector, link_class) {
		$(selector).linkify({
			validate: {
				url: (val) => /^https?:\/\//.test(val), // only allow URLs that begin with a protocol
				email: false // don't linkify emails
			}
		});
		if (typeof link_class !== "undefined") {
			$(selector).find('a.linkified').addClass(link_class);
		}

		$(selector).find("a.linkified[target='_blank']").attr("rel", "noreferrer noopener");
	}
}


document.addEventListener("DOMContentLoaded", function() {
  // Adjust this selector to find the links you want
  const links = document.querySelectorAll('a[target="_blank"]');

  links.forEach(link => {
    // Optionally, only change if href matches a specific pattern
    if (link.href.includes((window.location.hostname))) {
      link.target = '_self';
      console.log(`Changing  ${link.href} to target _self `);

    }
  });
});
