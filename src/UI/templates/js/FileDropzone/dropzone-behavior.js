/**
 * Provides functions for the dropzone highlighting.
 *
 * @author nmaerchy <nm@studer-raimann.ch>
 * @version 0.0.4
 */

var il = il || {};
il.UI = il.UI || {};
(function($, UI) {
	UI.dropzone = (function ($) {

		var css = {
			"darkendBackground": "modal-backdrop in", // <- bootstrap classes, should not be changed
			"darkendDragEnter": "darkend-drag-enter"
		};

		/**
		 * Enables the darkend background and highlights all file dropzones on the page.
		 *
		 * @param {string} dropzoneId the html id of the dropzone
		 */
		var enableDarkendBackground = function (dropzoneId) {
			$("#" + dropzoneId + "-darkend").addClass(css.darkendBackground);
			$(".il-file-dropzone").addClass(css.darkendDragEnter);
		};

		/**
		 *
		 * Disables the darkend background and removes the highlighting of all file dropzones on the page.
		 *
		 * @param {string} dropzoneId the html id of the dropzone
		 */
		var disableDarkendBackground = function (dropzoneId) {
			$("#" + dropzoneId + "-darkend").removeClass(css.darkendBackground);
			$(".il-file-dropzone").removeClass(css.darkendDragEnter);
		};

		return {
			enableDarkendBackground: enableDarkendBackground,
			disableDarkendBackground: disableDarkendBackground
		};
	})($);
})($, il.UI);