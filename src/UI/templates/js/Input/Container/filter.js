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

            var input_name = $("#" + id).attr("name");
            var input_num = input_name.substring(13);
            var input_label = $("#" + id).parents(".input-group").find("#leftaddon").html();
            if (input_label == undefined) {
                var old_input_label = $("#" + input_num).html();
                var last_char = old_input_label.indexOf(":");
                old_input_label = old_input_label.substring(0, last_char);
                $("span[id='" + input_num + "']").html(old_input_label + ":" + value_as_string + ",");
            } else {
                $("span[id='" + input_num + "']").html(input_label + ":" + value_as_string + ",");
			}
		};

        /**
         *
         * @param event
         * @param id
         */
        var onRemoveClick = function(event, id) {
            // hide the Input in the Filter which should be removed
        };

		/**
		 * Public interface
		 */
		return {
			onFieldUpdate: onFieldUpdate,
			onRemoveClick: onRemoveClick
		};

	})($);
})($, il.UI);

//Popover of Add-Button always at the bottom
$(document).ready(function() {
    $('.input-group .btn.btn-bulky').attr('data-placement', 'bottom');
});
