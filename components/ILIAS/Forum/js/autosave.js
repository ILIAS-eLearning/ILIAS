
(function (root, scope, factory) {
	scope.ForumDraftsAutosave = factory(root, root.jQuery);
}(window, il, function(root, $) {

	"use strict";
	
	var pub = {}, pro = {}, draft_as_handle = null, autosave_active = true ;

	pub.disableAutosave = function()
	{
		autosave_active = false;
	};

	pub.enableAutosave = function()
	{
		autosave_active = true;
	};

	pub.init = function(options) {
		var settings = $.extend({
			interval: 1000 * 10,
			url: "",
			loading_img_src: "",
			draft_id: 0,
			selectors: {
				form: ""
			}
		}, options), draft_id = settings.draft_id;

		var $form = $(settings.selectors.form);

		var saveDraftCallback = function saveDraftCallback() {
			if (typeof tinyMCE !== "undefined") {
				if (tinyMCE) tinyMCE.triggerSave();
			}

			if (autosave_active && $('#subject').val() != '' && $('#message').val() != '') 	{
				var data = $form.serialize();

				$form.find(".ilFrmLoadingImg").remove();
				$form.find("input[type=submit]").attr("disabled", "disabled");
				$form.find(".ilFormCmds").each(function () {
					$('<img class="ilFrmLoadingImg" src="' + settings.loading_img_src + '" />')
						.css("paddingRight", "10px")
						.insertBefore($(this).find("input[type=submit]:first"));
				});
				$('#ilsaving').removeClass("ilNoDisplay");

				il.ForumDraftsAutosave.disableAutosave();
				$.ajax({
					type:     "POST",
					url:      settings.url,
					data:     data,
					dataType: "json",
					success:  function (response) {
						$form.find("input[type=submit]").attr("disabled", false);
						$form.find(".ilFrmLoadingImg").remove();
						$('#ilsaving').addClass("ilNoDisplay");

						if (typeof response.draft_id !== "undefined" && response.draft_id > 0) {
							$draft_id.val(response.draft_id);
						}

						il.ForumDraftsAutosave.enableAutosave();
					}
				});
			}
		};

		if ($("#ilsaving").size() === 0) {
			$('<div id="ilsaving" class="ilHighlighted ilNoDisplay">' + il.Language.txt("saving") + '</div>').appendTo($("body"));
		}
		$("#ilsaving").css("zIndex", 10000);
		var $draft_id = $form.find("#draft_id");
		if ($draft_id.size() === 0) {
			$draft_id = $('<input type="hidden" name="draft_id" id="draft_id" value="" />');
			$form.append($draft_id);
		}

		$(function() {
			draft_as_handle = root.setInterval(saveDraftCallback, settings.interval);

			$form.on("submit", function() {
				root.clearInterval(draft_as_handle);
			});
		});
	};

	return pub;
}));

$( document ).ready(function() {
	if($('.found_threat_history_to_restore').length > 0)
	{
		var $modal = $("#frm_autosave_restore");
		il.ForumDraftsAutosave.disableAutosave();
		$modal.modal("show");
		$modal.on('hidden.bs.modal', function () {
			il.ForumDraftsAutosave.enableAutosave();
		})

	}
});
