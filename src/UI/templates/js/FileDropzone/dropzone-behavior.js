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
			"darkendDropzoneHighlight": "darkend-highlight",
			"dropzoneBorder": "border"
		};

		var _darkendDesign = false;

		var _createDarkendHtmlIfNotExists = function () {
			if (!$("#il-dropzone-darkend").length) {
				$("body").prepend("<div id=\"il-dropzone-darkend\"></div>");
			}
		};

		var enableAutoDesign = function () {
			if (_darkendDesign) {
				enableDarkendDesign();
			} else {
				enableDefaultDesign();
			}
		};

		var enableDarkendDesign = function () {
			$("#il-dropzone-darkend").addClass(css.darkendBackground);
			// $("body").css("pointer-events", "none");
			$(".il-file-dropzone").addClass(css.darkendDropzoneHighlight + " " + css.dropzoneBorder);
			// $(".il-file-dropzone").css("pointer-events", "auto");
		};

		var enableDefaultDesign = function () {

		};

		var disableDesign = function () {
			$("#il-dropzone-darkend").removeClass(css.darkendBackground);
			$(".il-file-dropzone").removeClass(css.darkendDropzoneHighlight);
			$(".il-file-dropzone").removeClass(css.dropzoneBorder);
		};

		var setDarkendDesign = function (darkendDesign) {
			_darkendDesign = darkendDesign;
			_createDarkendHtmlIfNotExists();
		};


		return {
			enableAutoDesign: enableAutoDesign,
			enableDarkendDesign: enableDarkendDesign,
			enableDefaultDesign: enableDefaultDesign,
			disableDesign: disableDesign,
			setDarkendDesign: setDarkendDesign
		};
	})($);
})($, il.UI);