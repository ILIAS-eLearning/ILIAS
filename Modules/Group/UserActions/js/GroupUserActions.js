il = il || {};
il.Group = il.Group || {};

(function($, il) {

	il.Group.UserActions = (function ($, il) {
		var public_interface;

		// public interface
		public_interface = {

			initCreationForm: function (event, url) {
				console.log("initCreationForm");
				event.preventDefault();
				event.stopPropagation();
				il.Util.sendAjaxGetRequestToUrl (url, {}, {}, function (o) {
					if (o.responseText !== undefined) {
						$('#il_grp_action_modal_content').html(o.responseText);
						il.Group.UserActions.setCreationSubmit();
					}
				});
			},

			setCreationSubmit: function () {
				$('#il_grp_action_modal_content form').on("submit", function(e) {
					var values;

					e.preventDefault();
					values = $('#il_grp_action_modal_content form').serializeArray();
					console.log("Form Submitted...");
					console.log(values);
					il.Util.sendAjaxPostRequestToUrl($(this).attr('action'), values, function (o) {
						console.log("post sucess on form submitted");
						$('#il_grp_action_modal_content').html(o);
						il.Group.UserActions.setCreationSubmit();
					});
				});

			},

			createGroup: function (e) {
				console.log("createGroup");
				e.preventDefault();
				values = $('#il_grp_action_modal_content form').serializeArray();
				il.Util.sendAjaxPostRequestToUrl($('#il_grp_action_modal_content form').attr('action'), values, function (o) {
					console.log("post sucess in createGroup");
					$('#il_grp_action_modal_content').html(o);
				});
			},

			closeModal: function () {
				$('#il_grp_action_modal_content').closest('.il-modal-roundtrip').find("button.close").click();
			}
		};

		return public_interface;
	})($, il);


	// on ready initialisation
	$(function () {

		function initEvents(id) {
			console.log(id);

			$(id).find("[data-grp-action-add-to='1']").each(function () {
				$(this).on("click", function(e) {
					var url;

					e.preventDefault();
					if (il.Awareness) {
						il.Awareness.close();
					}
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
			console.log("il.user.actions.updated catched, id: " + id);
			initEvents("#" + id);
		});
		initEvents("body");
	});


}($, il));