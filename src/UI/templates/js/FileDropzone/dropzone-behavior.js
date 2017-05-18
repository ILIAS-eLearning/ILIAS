/**
 * Provides functions for the dropzone highlighting.
 *
 * @author nmaerchy <nm@studer-raimann.ch>
 * @version 0.0.5
 */

var il = il || {};
il.UI = il.UI || {};
(function($, UI) {
	UI.dropzone = (function ($) {

		var CSS = {
			"darkenedBackground": "modal-backdrop in", // <- bootstrap classes, should not be changed
			"darkenedDropzoneHighlight": "darkened-highlight",
			"defaultDropzoneHighlight": "default-highlight"
		};

		var SELECTOR = {
			"darkenedBackground": "il-dropzone-darkened",
			"dropzones": "il-dropzone"
		};

		var _darkenedDesign = false;

		/**
		 * Prepends a div to the body tag to enable the darkened background.
		 * @private
		 */
		var _createDarkenedHtmlIfNotExists = function () {
			if (!$("#" + SELECTOR.darkenedBackground).length && _darkenedDesign) {
				$("body").prepend("<div id=" + SELECTOR.darkenedBackground + "></div>");
			}
		};

		/**
		 * Enables the darkened background design for dropzones.
		 * @private
		 */
		var _enableDarkenedDesign = function () {
			$("#" + SELECTOR.darkenedBackground).addClass(CSS.darkenedBackground);
			$("." + SELECTOR.dropzones).addClass(CSS.darkenedDropzoneHighlight);
		};

		/**
		 * Enables the default background design for dropzones.
		 * @private
		 */
		var _enableDefaultDesign = function () {
			$("." + SELECTOR.dropzones).addClass(CSS.defaultDropzoneHighlight);
		};

		/**
		 * Enables either the darkened design or the default design depending on the {@link _darkenedDesign} variable.
		 */
		var enableAutoDesign = function () {
			if (_darkenedDesign) {
				_enableDarkenedDesign();
			} else {
				_enableDefaultDesign();
			}
		};

		/**
		 * Enables the highlight design. If the passed in argument is true, the darkened style will be used.
		 * @param {boolean} darkenedBackground Flag to enable the darkened design.
		 */
		var enableHighlightDesign = function(darkenedBackground) {
			if (darkenedBackground) {
				_createDarkenedHtmlIfNotExists(); // <- Just to ensure the darkened html exists.
				_enableDarkenedDesign();
			} else {
				_enableDefaultDesign();
			}
		};

		/**
		 * Disables all highlight designs which are active.
		 */
		var disableHighlightDesign = function () {
			$("#" + SELECTOR.darkenedBackground).removeClass(CSS.darkenedBackground);
			$("." + SELECTOR.dropzones).removeClass(CSS.darkenedDropzoneHighlight)
				.removeClass(CSS.defaultDropzoneHighlight);
		};

		/**
		 * Sets the {@link _darkenedDesign} and calls the {@link _createDarkenedHtmlIfNotExists} function.
		 * @param {boolean} darkenedDesign true to set the darkened design, otherwise false.
		 */
		var setDarkenedDesign = function (darkenedDesign) {
			_darkenedDesign = darkenedDesign;
			_createDarkenedHtmlIfNotExists();
		};


		return {
			enableAutoDesign: enableAutoDesign,
			enableHighlightDesign: enableHighlightDesign,
			disableHighlightDesign: disableHighlightDesign,
			setDarkenedDesign: setDarkenedDesign
		};
	})($);
})($, il.UI);