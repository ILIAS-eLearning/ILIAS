/**
 * Provides functions for the dropzone highlighting.
 *
 * @author nmaerchy <nm@studer-raimann.ch>
 * @version 0.0.3
 */

var il = il || {};
il.UI = il.UI || {};
(function($, UI) {
	UI.dropzone = (function ($) {

		var css = {
			"default": "dz-drag-hover",
			"darkendBackground": "dragenter"
		};

		/**
		 * Enables highlighting for all file dropzones on the page by the passed in argument.
		 *
		 * @param config Object a javascript object like {"id": "", "darkendBackground": true}
		 */
		var enableDropDesign = function (config) {
			if (config.darkendBackground) {
				$("#" + config.id + "-darkend").addClass("modal-backdrop in"); // <- bootstrap classes, should not be changed
				$(".il-file-dropzone").addClass(css.darkendBackground);
			} else {
				$(".il-file-dropzone").addClass(css.default);
			}

		};

		/**
		 * Disables highlighting for all file dropzone on the page by the passed in argument.
		 *
		 * @param config Object a javascript object like {"id": "", "darkendBackground": true}
		 */
		var disableDropDesign = function (config) {
			if (config.darkendBackground) {
				$("#" + config.id + "-darkend").removeClass("modal-backdrop in"); // <- bootstrap classes, should not be changed
				$(".il-file-dropzone").removeClass(css.darkendBackground);
			} else {
				$(".il-file-dropzone").removeClass(css.default);
			}
		};

		return {
			enableDropDesign: enableDropDesign,
			disableDropDesign: disableDropDesign
		};
	})($);
})($, il.UI);