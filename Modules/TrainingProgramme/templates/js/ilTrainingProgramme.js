(function ($) {
	$(document).on('hidden.bs.modal', function (e) {
		$(e.target).removeData('bs.modal');
	});

	$.fn.extend({
		training_programme_tree: function (options) {
			var settings = $.extend({
				save_tree_url: ''
			}, options);

			var element = this;

			$("body").on("async_form-success", function(data) {
				console.log("reload-tree!");
				$(element).jstree("refresh");
				//$(element).jstree("refresh");
				//console.log($(element).jstree("refresh", 0));
			});

			$("body").on("training_programme-save_order", function() {
				var tree_data = $(element).jstree("get_json", -1, ['id']);
				var json_data = JSON.stringify(tree_data);

				if(settings.save_tree_url != "") {
					$.ajax({
						url: decodeURIComponent(settings.save_tree_url),
						type: 'post',
						dataType: 'json',
						data: {tree: json_data},
						success: function(response, status, xhr) {
							//try {
							if(response) {
								$("body").trigger({type: "training_programme-show_success", message: response.message});
							}
							/*} catch (error) {
							 console.log("The AJAX-response for the async form " + form.attr('id') + " is not JSON. Please check if the return values are set correctly: " + error);
							 }*/

						}
					});
				}
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