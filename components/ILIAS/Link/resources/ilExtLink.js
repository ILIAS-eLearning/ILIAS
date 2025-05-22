/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

if (typeof il == "undefined") {
	il = {};
}

il.ExtLink = {

	/**
	 * Linkify wrapper
	 */
	autolink: function (selector, link_class) {
		const options = {
			validate: {
				url: (val) => /^https?:\/\//.test(val), // only allow URLs that begin with a protocol
				email: false // don't linkify emails
			}
		};
		$(selector).each(function () {
			linkifyElement(this, options);
		});
		if (typeof link_class !== "undefined") {
			$(selector).find('a.linkified').addClass(link_class);
		}

		$(selector).find("a.linkified[target='_blank']").attr("rel", "noreferrer noopener");
	}
}
