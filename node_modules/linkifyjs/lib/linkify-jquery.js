'use strict';

exports.__esModule = true;
exports.default = apply;

var _linkifyElement = require('./linkify-element');

var _linkifyElement2 = _interopRequireDefault(_linkifyElement);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// Applies the plugin to jQuery
function apply($) {
	var doc = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;


	$.fn = $.fn || {};

	try {
		doc = doc || document || window && window.document || global && global.document;
	} catch (e) {/* do nothing for now */}

	if (!doc) {
		throw new Error('Cannot find document implementation. ' + 'If you are in a non-browser environment like Node.js, ' + 'pass the document implementation as the second argument to linkify/jquery');
	}

	if (typeof $.fn.linkify === 'function') {
		// Already applied
		return;
	}

	function jqLinkify(opts) {
		opts = _linkifyElement2.default.normalize(opts);
		return this.each(function () {
			_linkifyElement2.default.helper(this, opts, doc);
		});
	}

	$.fn.linkify = jqLinkify;

	$(doc).ready(function () {
		$('[data-linkify]').each(function () {
			var $this = $(this);
			var data = $this.data();
			var target = data.linkify;
			var nl2br = data.linkifyNlbr;

			var options = {
				nl2br: !!nl2br && nl2br !== 0 && nl2br !== 'false'
			};

			if ('linkifyAttributes' in data) {
				options.attributes = data.linkifyAttributes;
			}

			if ('linkifyDefaultProtocol' in data) {
				options.defaultProtocol = data.linkifyDefaultProtocol;
			}

			if ('linkifyEvents' in data) {
				options.events = data.linkifyEvents;
			}

			if ('linkifyFormat' in data) {
				options.format = data.linkifyFormat;
			}

			if ('linkifyFormatHref' in data) {
				options.formatHref = data.linkifyFormatHref;
			}

			if ('linkifyTagname' in data) {
				options.tagName = data.linkifyTagname;
			}

			if ('linkifyTarget' in data) {
				options.target = data.linkifyTarget;
			}

			if ('linkifyValidate' in data) {
				options.validate = data.linkifyValidate;
			}

			if ('linkifyIgnoreTags' in data) {
				options.ignoreTags = data.linkifyIgnoreTags;
			}

			if ('linkifyClassName' in data) {
				options.className = data.linkifyClassName;
			} else if ('linkifyLinkclass' in data) {
				// linkClass is deprecated
				options.className = data.linkifyLinkclass;
			}

			options = _linkifyElement2.default.normalize(options);

			var $target = target === 'this' ? $this : $this.find(target);
			$target.linkify(options);
		});
	});
}

// Try assigning linkifyElement to the browser scope
try {
	!undefined.define && (window.linkifyElement = _linkifyElement2.default);
} catch (e) {/**/}