/**
 * This links datetime-pickers together (for duration input)
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */

var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
(function ($, UI) {

	il.UI.Input.duration = (function ($) {
		var init = function (id) {
			var id = '#' + id,
				inpts = $(id).find('.il-input-datetime'),
				from = inpts[0],
				until = inpts[1];

			$(from).on("dp.change", function (e) {
				$(until).data("DateTimePicker").minDate(e.date);
			});
			$(until).on("dp.change", function (e) {
				$(from).data("DateTimePicker").maxDate(e.date);
			});
		};

		return {
			init: init
		};

	})($);
})($, il.UI.Input);
