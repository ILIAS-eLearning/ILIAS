/**
 * @uthor nmaerchy <nm@studer-raimann.ch>
 * @version 0.0.1
 */

var il = il || {};
il.UI = il.UI || {};
(function($, UI) {
	UI.dropzone = (function ($) {

		var enableDropDesign = function (config) {
			$("#" + config.id).addClass("modal-backdrop in");
			$(".dropzone").addClass("dragenter");
		};

		var disableDropDesign = function (config) {
			$($("#" + config.id)).removeClass("modal-backdrop in");
			$(".dropzone").removeClass("dragenter");
		};

		return {
			enableDropDesign: enableDropDesign,
			disableDropDesign: disableDropDesign
		};
	})($);
})($, il.UI);