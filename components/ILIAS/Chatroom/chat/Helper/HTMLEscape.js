/**
 * @constructor
 */
function HTMLEscape() {}

/**
 * @returns {number}
 */
HTMLEscape.prototype.escape = function (html) {
	return String(html)
		.replace(/&(?!\w+;)/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/[\uD800-\uDBFF][\uDC00-\uDFFF]/g, '?');
};

/**
 * @type {HTMLEscape}
 */
module.exports = new HTMLEscape();
