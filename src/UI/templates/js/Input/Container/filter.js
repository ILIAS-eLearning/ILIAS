/**
 * Filter
 *
 * @author <killing@leifos.com>
 */

var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

	$("*").on("il.ui.popover.show", function(e){
		//
	});

	// init the filter fields (hide hidden stuff)
	var init = function() {
		$("div.il-filter").each(function () {
			var $filter = this;
			var cnt = 0;
			$($filter).find(".il-filter-field-status").each(function() {
				$hidden_input = this;
				if ($($hidden_input).val() === "0") {
					$($("div.il-filter .il-popover-container")[cnt]).hide();
				} else {
					$($("div.il-filter .il-filter-add-list li")[cnt]).hide();
				}
				cnt++;
			});
		});
	};

	$(init);

	UI.filter = (function ($) {

		/**
		 * Store filter status (hidden or shown) in hidden input fields
		 * @param $el
		 * @param index
		 * @param val
		 */
		var storeFilterStatus = function($el, index, val) {
			$($el.parents(".il-filter").find(".il-filter-field-status").get(index)).val(val);
		};

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

			//Show labels and values in Filter Bar
            var input_name = $("#" + id).attr("name");
            var input_num = input_name.substring(13);
            var input_label = $("#" + id).parents(".input-group").find(".leftaddon").html();
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
			var index = $("#" + id).parents(".il-popover-container").index();
			storeFilterStatus($("#" + id), index, "0");

            //Remove Input Field from Filter
            $("#" + id).parents(".il-popover-container").hide();
            //Clear Input Field when it is removed
            $("#" + id).parents(".il-popover-container").find(".il-standard-popover-content").children().val("");
            $("#" + id).parents(".il-popover-container").find(".il-filter-field").html("");
            var label = $("#" + id).parents(".input-group").find(".input-group-addon").html();

            //Add Input Field to Add-Button
            $("#" + id).parents(".il-standard-form").find(".btn-link").filter(function() {
                return $(this).text() === label;
            }).parents("li").show();

            //Show Add-Button when not all Input Fields are shown in the Filter
            var addableInputs = $("#" + id).parents(".il-standard-form").find(".il-popover-container:hidden").length;
            if (addableInputs != 0) {
                $("#" + id).parents(".il-standard-form").find(".btn-bulky").parents(".il-popover-container").show();
            }
        };

        /**
         *
         * @param event
         * @param id
         */
        var onAddClick = function(event, id) {
            //Remove Input Field from Add-Button
            $("#" + id).parent().hide();
            var label = $("#" + id).text();

            // Store show/hide status in hidden status inputs
            var index = $("#" + id).parent().index();
			storeFilterStatus($("#" + id), index, "1");

            // Add Input Field to Filter
            $("#" + id).parents(".il-standard-form").find(".input-group-addon").filter(function() {
                return $(this).text() === label;
            }).parents(".il-popover-container").show();

            //Imitate a click on the Input Field in the Fiter and focus on the element (input, select,...) in the Popover
            $("#" + id).parents(".il-standard-form").find(".input-group-addon").filter(function() {
                return $(this).text() === label;
            }).parent().find(".il-filter-field").click()
                .parents(".il-popover-container").find(".il-standard-popover-content").children().focus();

            //Hide Add-Button when all Input Fields are shown in the Filter
            var addableInputs = $("#" + id).parents(".il-standard-form").find("li:visible").length;
            if (addableInputs == 0) {
                $("#" + id).parents(".il-standard-form").find(".btn-bulky").parents(".il-popover-container").hide();
            }

            //Hide the Popover of the Add-Button when adding Input Field
            $("#" + id).parents(".il-standard-form").find(".btn-bulky").parents(".il-popover-container").find(".il-popover").hide();
        };

		/**
		 * Public interface
		 */
		return {
			onFieldUpdate: onFieldUpdate,
			onRemoveClick: onRemoveClick,
			onAddClick: onAddClick
		};

	})($);
})($, il.UI);

$(document).ready(function() {
    //Popover of Add-Button always at the bottom
    $('.input-group .btn.btn-bulky').attr('data-placement', 'bottom');

    //Hide Add-Button when all Input Fields are shown in the Filter at the beginning
    var addableInputs = $(".il-popover-container:hidden").length;
    if (addableInputs == 0) {
        $(".btn-bulky").parents(".il-popover-container").hide();
    }

    $(".il-filter-field").keydown(function (event) {
        var key = event.which;
        if ((key === 13) || (key === 32)) {	// 13 = Return, 32 = Space
            $(this).click();
        }
    });
});
