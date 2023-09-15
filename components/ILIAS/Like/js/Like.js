il = il || {};
il.Like = il.Like || {};
(function($, il) {
	il.Like = (function($) {

		var toggle = function (url, glyph_id, widget_id, exp_id) {
			var val;
			if ($("#" + glyph_id).hasClass("highlighted")) {
				$("#" + glyph_id).removeClass("highlighted");
				val = 0;
			} else {
				$("#" + glyph_id).addClass("highlighted");
				val = 1;
			}
			il.Util.ajaxReplace(url + "&cmd=saveExpression&exp=" + exp_id + "&val=" + val + "&dom_id=" + widget_id, widget_id + "_ec");
		};

		return {
			toggle: toggle
		};
	})($);
})($, il);