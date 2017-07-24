(function($) {
	$(function () {

		var $modal = il.Modal.dialogue({
			show: false,
			header: "header",
			body: "message",
			buttons: [{
				type:      "button",
				label:     "button label",
				className: "btn btn-default",
				callback:  function (e) {
					$modal.hide();
				}
			}]
		});

		function initEvents(id) {
			console.log(id);

			$("#" + id).find("[data-grp-action-add-to='1']").each(function () {
				$(this).on("click", function(e) {
					e.preventDefault();

					il.Util.sendAjaxGetRequestToUrl($(this).data("url"), [], [], function (r) {
						console.log(r);
						console.log(r.responseText);
						$("body").append(r.responseText);
						//$(document).append( "<p>Test</p>" );
						//$(document).append($(r.responseText));
					});

					// get url, add it to document, call the modal
					console.log($(this).data("url"));

					//$modal.show();

				});
			});
		}

		$(document).on('il.user.actions.updated', function(ev, id) {
			initEvents(id);
		});
	});
}($));