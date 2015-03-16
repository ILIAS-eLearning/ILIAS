(function ($) {
	$(document).on('hidden.bs.modal', function (e) {
		$(e.target).removeData('bs.modal');
	});

	$.fn.extend({
		training_programme_tree: function (options) {
			var settings = $.extend({

			}, options);

			var element = this;

			$("body").on("async_form-success", function(data) {
				console.log("reload-tree!");
				$(element).jstree("refresh");
				//$(element).jstree("refresh");
				//console.log($(element).jstree("refresh", 0));
			});

			return true;
		},
		training_programme_modal: function(options) {
			var settings = $.extend({

			}, options);

			var element = this;

			$("body").on("async_form-success", function(data) {
				element.modal('hide');
			});

			$("body").on("async_form-cancel", function(data) {
				element.modal('hide');
			});

			return true;
		}
	});
}(jQuery));