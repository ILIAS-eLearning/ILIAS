/**
 * A helper class to generate a timestamp
 *
 * @constructor
 */
var HTMLEscape = function HTMLEscape(){};

/**
 * Creates a timestamp
 *
 * @returns {number}
 */
HTMLEscape.prototype.escape = function(html) {
	html = String(html)
		.replace(/&(?!\w+;)/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;');

	return html.replace(new RegExp("[\uD800-\uDBFF][\uDC00-\uDFFF]", "g"), "?");
};

/**
 * @type {HTMLEscape}
 */
module.exports = new HTMLEscape();