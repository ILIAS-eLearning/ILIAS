il = il || {};
il.Group = il.Group || {};

(function($, il) {

	il.Group.UserActions = (function ($, il) {
		var public_interface;

		// public interface
		public_interface = {
			selectTargetObject: function($type, $child) {
				console.log($type);
				console.log($child);
			}
		};

		return public_interface;
	})($, il);


	// on ready initialisation
	$(function () {

		function initEvents(id) {
			console.log(id);

			$("#" + id).find("[data-grp-action-add-to='1']").each(function () {
				$(this).on("click", function(e) {
					var url;

					e.preventDefault();
					url = $(this).data("url");

					if ($('#il_grp_action_modal_content').length) {
						url = url + "&modal_exists=1";
					}

					il.Util.sendAjaxGetRequestToUrl(url, [], [], function (r) {
						var modal_content = $('#il_grp_action_modal_content');
						console.log(r.responseText);
						if (modal_content.length) {
							modal_content.html(r.responseText);
							modal_content.closest('.il-modal-roundtrip').modal().show();
						} else {
							$("body").append(r.responseText);
						}
					});
				});
			});
		}

		$(document).on('il.user.actions.updated', function(ev, id) {
			initEvents(id);
		});
	});


}($, il));