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

		var enableDarkendBackground = function (dropzoneId) {
			$("#" + dropzoneId + "-darkend").addClass("modal-backdrop in"); // <- bootstrap classes, should not be changed
			$(".il-file-dropzone").addClass("darkend-drag-enter");
		};

		var disableDarkendBackground = function (dropzoneId) {
			$("#" + dropzoneId + "-darkend").removeClass("modal-backdrop in"); // <- bootstrap classes, should not be changed
			$(".il-file-dropzone").removeClass("darkend-drag-enter");
		};

		return {
			enableDarkendBackground: enableDarkendBackground,
			disableDarkendBackground: disableDarkendBackground
		};
	})($);
})($, il.UI);