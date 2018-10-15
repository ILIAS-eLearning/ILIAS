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
		var onFieldUpdate = function(event, id, value_as_string) {
			var pop_id = $("#" + id).parents(".il-popover").attr("id");
			if (pop_id) {	// we have an already opened popover
				$("span[data-target='" + pop_id + "']").html(value_as_string);
			} else {
				// no popover yet, we are still in the same input group and search for the il-filter-field span
				$("#" + id).parents(".input-group").find("span.il-filter-field").html(value_as_string);
			}
		};

		/**
		 * Public interface
		 */
		return {
			onFieldUpdate: onFieldUpdate
		};

	})($);
})($, il.UI);