/**
 * Filter
 *
 * @author <killing@leifos.com>
 */

var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

	UI.filter = (function ($) {
		/**
		 *
		 * @param event
		 * @param id
		 * @param value_as_string
		 */
		var handleChange = function(event, id, value_as_string) {
			var pop_id = $("#" + id).parents(".il-popover").attr("id");
			$("span[data-target='" + pop_id + "']").html(value_as_string);
		};

		/**
		 * Public interface
		 */
		return {
			handleChange: handleChange
		};

	})($);
})($, il.UI);