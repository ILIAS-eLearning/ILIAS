$(document).ready(function () {

});


il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

(function ($, maincontrols) {
	maincontrols.system_info = (function ($) {
		// alert($(this));
		/**
		 * decide and init condensed/wide version
		 */
		var init = function (id) {
			let body = $('#' + id).find('.il-system-info-body');
			let item_height = body.prop('scrollHeight');
			let container = body.closest('.il-system-info');
			let container_height = container.height() + 4;
			if (item_height > container_height) {
				let more_button = container.find('.il-system-info-more');
				more_button.show();
				more_button.click(function () {
					container.toggleClass('full');
					more_button.hide();
				});
			}
			// });

			// $('.il-system-info:not(:first-child)');
		};

		var close = function (id) {
			let element = $('#' + id);
			let close_uri = decodeURI(element.data('closeUri'));
			$.ajax({
				async: false,
				type: 'GET',
				url: close_uri,
				success: function(data) {
					element.slideUp(500);
				}
			});
		};

		return {
			init: init,
			close: close,
		}

	})($);
})($, il.UI.maincontrols);

