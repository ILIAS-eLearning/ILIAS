var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

	UI.core = (function ($) {

		/**
		 * Replace a component or parts of a component using ajax call
		 *
		 * @param id component id
		 * @param url replacement url
		 * @param marker replacement marker ("component", "content", "header", ...)
		 */
		var replaceContent = function (id, url, marker) {

			// get new stuff via ajax
			$.ajax({
				url: url,
				dataType: 'html'
			}).done(function(html) {
				var $new_content = $("<div>" + html + "</div>");
				var $marked_new_content = $new_content.find("[data-replace-marker='" + marker + "']").first();
				if ($marked_new_content.length == 0) {
					$("#" + id + " [data-replace-marker='" + marker + "']").html(html);
				} else {
					$("#" + id + " [data-replace-marker='" + marker + "']").first()
						.replaceWith($marked_new_content);
				}
				$("#" + id + " [data-replace-marker='" + marker + "']").first()
					.after($new_content.find("[data-replace-marker='script']"));
			});
		};

		return {
			replaceContent: replaceContent
		};

	})($);

})($, il.UI);