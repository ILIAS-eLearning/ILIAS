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
			"darkendBackground": "modal-backdrop in", // <- bootstrap classes, should not be changed
			"darkendDropzoneHighlight": "darkend-highlight",
			"defaultDropzoneHighlight": "default-highlight"
		};

		var SELECTOR = {
			"darkendBackground": "il-dropzone-darkend",
			"fileDropzones": "il-file-dropzone"
		};

		var _darkendDesign = false;

		/**
		 * Prepends a div to the body tag to enable the darkend background.
		 * @private
		 */
		var _createDarkendHtmlIfNotExists = function () {
			if (!$("#" + SELECTOR.darkendBackground).length) {
				$("body").prepend("<div id=" + SELECTOR.darkendBackground + "></div>");
			}
		};

		/**
		 * Enables the darkend background design for dropzones.
		 * @private
		 */
		var _enableDarkendDesign = function () {
			$("#" + SELECTOR.darkendBackground).addClass(CSS.darkendBackground);
			$("." + SELECTOR.fileDropzones).addClass(CSS.darkendDropzoneHighlight);
		};

		/**
		 * Enables the default background design for dropzones.
		 * @private
		 */
		var _enableDefaultDesign = function () {
			$("." + SELECTOR.fileDropzones).addClass(CSS.defaultDropzoneHighlight);
		};

		/**
		 * Enables either the darkend design or the default design depending on the {@link _darkendDesign} variable.
		 */
		var enableAutoDesign = function () {
			if (_darkendDesign) {
				_enableDarkendDesign();
			} else {
				_enableDefaultDesign();
			}
		};

		/**
		 * Enables the highlight design. If the passed in argument is true, the darkend style will be used.
		 * @param {boolean} darkendBackground Flag to enable the darkend design.
		 */
		var enableHighlightDesign = function(darkendBackground) {
			if (darkendBackground) {
				_createDarkendHtmlIfNotExists(); // <- Just to ensure the darkend html does exist.
				_enableDarkendDesign();
			} else {
				_enableDefaultDesign();
			}
		};

		/**
		 * Disables all highlight designs which are active.
		 */
		var disableHighlightDesign = function () {
			$("#" + SELECTOR.darkendBackground).removeClass(CSS.darkendBackground);
			$("." + SELECTOR.fileDropzones).removeClass(CSS.darkendDropzoneHighlight)
				.removeClass(CSS.defaultDropzoneHighlight);
		};

		/**
		 * Sets the {@link _darkendDesign} and calls the {@link _createDarkendHtmlIfNotExists} function.
		 * @param darkendDesign
		 */
		var setDarkendDesign = function (darkendDesign) {
			_darkendDesign = darkendDesign;
			_createDarkendHtmlIfNotExists();
		};


		return {
			enableAutoDesign: enableAutoDesign,
			enableHighlightDesign: enableHighlightDesign,
			disableHighlightDesign: disableHighlightDesign,
			setDarkendDesign: setDarkendDesign
		};
	})($);
})($, il.UI);