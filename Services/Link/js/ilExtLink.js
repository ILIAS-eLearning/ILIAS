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
    const links = document.querySelectorAll('a[target="_blank"]');

    // Get the "target" parameter from the current page URL
    const currentUrlParams = new URLSearchParams(window.location.search);
        //  console.log(currentUrlParams);
    const targetParam = currentUrlParams.get("target");
        //  console.log(targetParam);

    links.forEach(link => {
      const url = new URL(link.href);
          //  console.log(url.hostname);
          //  console.log(url);

      if (
        url.hostname === window.location.hostname &&
        url.searchParams.get("window") === "current"
      ) {
        link.target = '_self';
        // console.log(`Changing ${link.href} to target _self`);

        // If the current page has a "target" param, add it to the link
        if (targetParam !== null) {
          url.searchParams.set("target", targetParam);
        // Remove window=same
          url.searchParams.delete("window");
          link.href = url.toString();
          // console.log(`Updated link with target param: ${link.href}`);
        }
      }
    });
  });

