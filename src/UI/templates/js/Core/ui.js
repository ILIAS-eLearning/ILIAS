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

					// if marker does not come with the new content, we put the new content into the existing marker
					// (this includes all script tags already)
					$("#" + id + " [data-replace-marker='" + marker + "']").html(html);

				} else {

					// if marker is in new content, we replace the complete old node with the marker
					// with the new marked node
					$("#" + id + " [data-replace-marker='" + marker + "']").first()
						.replaceWith($marked_new_content);

					// append included script (which will not be part of the marked node
					$("#" + id + " [data-replace-marker='" + marker + "']").first()
						.after($new_content.find("[data-replace-marker='script']"));
				}
			});
		};

		return {
			replaceContent: replaceContent
		};

	})($);

})($, il.UI);