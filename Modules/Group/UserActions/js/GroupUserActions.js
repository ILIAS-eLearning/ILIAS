il = il || {};
il.Group = il.Group || {};

(function($, il) {

	il.Group.UserActions = (function ($, il) {
		var public_interface;

		// public interface
		public_interface = {

			initCreationForm: function (event, url) {
				event.preventDefault();
				event.stopPropagation();
				il.repository.core.fetchHtml(url).then((html) => {
					const modalContent = document.getElementById('il_grp_action_modal_content');
					il.repository.core.setInnerHTML(modalContent, html);
					il.Group.UserActions.setCreationSubmit();
				});
			},

			setCreationSubmit: function () {
				$('#il_grp_action_modal_content form').on("submit", function(e) {
					e.preventDefault();
					const form = document.querySelector("#il_grp_action_modal_content form");
					const formData = new FormData(form);
					let data = {};
					formData.forEach((value	, key) => (data[key] = value));
					il.repository.core.fetchHtml(form.action, data, true). then(function (o) {
						const contentEl = document.getElementById("il_grp_action_modal_content");
						il.repository.core.setInnerHTML(contentEl, o);
						il.Group.UserActions.setCreationSubmit();
					});
				});

			},

			createGroup: function (e) {
				e.preventDefault();
				const form = document.querySelector("#il_grp_action_modal_content form");
				const formData = new FormData(form);
				let data = {};
				formData.forEach((value, key) => (data[key] = value));
				il.repository.core.fetchHtml(form.action, data, true). then(function (o) {
					const contentEl = document.getElementById("il_grp_action_modal_content");
					il.repository.core.setInnerHTML(contentEl, o);
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
					} else {
						url = url + "&modal_exists=0";
					}
					il.repository.core.fetchHtml(url).then((html) => {
						const modalContent = document.getElementById('il_grp_action_modal_content');
						if (modalContent) {
							il.repository.core.setInnerHTML(modalContent, html);
							$(modal_content).closest('.il-modal-roundtrip').modal().show();
						} else {
							$("body").append(html);
						}
					});
				});
			});
		}

		$(document).on('il.user.actions.updated', function(ev, id) {
			initEvents("#" + id);
		});
		initEvents("body");
	});


}($, il));