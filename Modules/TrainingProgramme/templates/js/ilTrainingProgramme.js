(function ($) {
	$(document).on('hidden.bs.modal', function (e) {
		$(e.target).removeData('bs.modal');
	});

	$.fn.extend({
		training_programme_tree: function (element_config, options) {

			var settings = $.extend({

			}, options);

			var element_config = element_config;
			var element = this;
			var counter = 0;

			return true;
	}});
}(jQuery));