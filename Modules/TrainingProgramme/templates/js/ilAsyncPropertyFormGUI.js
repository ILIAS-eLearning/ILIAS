(function ($) {
	$.ilAsyncPropertyForm = {
		global_config: {
			error_message_template: null,
			alert_class: "alert"
		}
	},
	$.fn.extend({
		ilAsyncPropertyForm: function (options) {

			var settings = $.extend({
				alert_class: "alert"
			}, options);

			var element = this;

			var setup_async_form = function() {
				$(element).find(":submit").each(function () {
					console.log("found submit!");
					console.log(this);
					$(this).on("click", function(e){
						e.preventDefault();
						console.log("click item!");

						var form = $(this).closest('form');

						var formData = form.serializeArray();
						formData.push({ name: $(this).attr('name'), value: $(this).val() });

						var actionurl = form.attr('action');
						console.log(actionurl);

						// TODO: find better way to determine is its a save command
						var is_save_cmd = function(cmd) {
							var cmds = ['save', 'update'];
							return jQuery.inArray(cmd, cmds);
						};

						$.ajax({
							url: actionurl,
							type: 'post',
							dataType: 'json',
							data: formData,
							success: function(response, status, xhr) {
								//try {
									if(response) {

										// start other ajax request if saving failed for display data
										if(is_save_cmd(response.cmd) != -1 && response.success == false && jQuery.isArray(response.errors)) {
											console.log("error!");
											form.find('div.' + $.ilAsyncPropertyForm.global_config.alert_class).remove();
											for(var i = 0; i < response.errors.length; i++) {
												var message = $.ilAsyncPropertyForm.global_config.error_message_template.replace('[TXT_ALERT]', response.errors[i].message);

												// TODO: might need a more specific selector
												$('#' + response.errors[i].key).after(message);
											}
											$("body").trigger({type: "async_form-error", message: response.message});

										} else if(is_save_cmd(response.cmd) != -1 && response.success == true) {
											$("body").trigger({type: "async_form-success", message: response.message});
										} else if(response.cmd == "cancel") {
											$("body").trigger({type: "async_form-cancel"});
										}
									}
								/*} catch (error) {
									console.log("The AJAX-response for the async form " + form.attr('id') + " is not JSON. Please check if the return values are set correctly: " + error);
								}*/

							}
						});
					});
				});
			}
			setup_async_form();

			// TODO: Handle this more global and not modal specific
			$(element).on('shown.bs.modal', function () {
				setup_async_form();
			})

			return true;
		}
	});
}(jQuery));