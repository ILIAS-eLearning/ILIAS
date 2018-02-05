var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

    UI.modal = (function ($) {

        var defaultShowOptions = {
            backdrop: true,
            keyboard: true,
            ajaxRenderUrl: '',
            trigger: 'click'
        };

        var initializedModalboxes = {};


        var showModal = function (id, options, signalData) {
            options = $.extend(defaultShowOptions, options);
            if (options.ajaxRenderUrl) {
                var $container = $('#' + id);
                $container.load(options.ajaxRenderUrl, function() {
                    var $modal = $(this).find('.modal');
                    if ($modal.length) {
                        $modal.modal(options);
                    }
                });
            } else {
                var $modal = $('#' + id);
                $modal.modal(options);
            }
			initializedModalboxes[signalData.id] = id;
		};

        var closeModal = function (id) {
            $('#' + id).modal('close');
        };

        /**
         * Replace the content of the modalbox showed by the given showSignal with the data returned by the URL
         * set in the signal options.
         *
         * @param showSignal ID of the show signal for the modalbox
         * @param signalData Object containing all data from the replace signal
         */
        var replaceContentFromSignal = function (showSignal, signalData) {

            // Find the ID of the triggerer where this modalbox belongs to
            var triggererId = (showSignal in initializedModalboxes) ? initializedModalboxes[showSignal] : 0;
            if (!triggererId) return;

            // Find the content of the modalbox
            var $modal = $('#' + triggererId + " .modal");
            var url = signalData.options.url;

            // get new stuff via ajax
			$.ajax({
				url: url,
				dataType: 'html'
			}).done(function(html) {
				var $new_modal = $("<div>" + html + "<div>");

				// of the new modal, we want the inner html of the modal (without the new top modal node, since
                // we want to keep our id. Additionally we want the script tag with its content.
                // Since html() gives us the inner html of the script tag only, we clone, wrap and get the inner from the wrapper...
				$modal.html($new_modal.find(".modal").first().html() + $new_modal.find("script").first().clone().wrap('<p/>').parent().html());
			});
        };

        /**
         * Replace the content of the modalbox of the $triggerer JQuery object with the data returned by the
         * given url.
         *
         * @param $triggerer JQuery object where the modalbox belongs to
         * @param url The URL where the ajax GET request is sent to load the new content
         */
        var replaceContent = function($triggerer, url) {
            var $content = $('#' + $triggerer.attr('data-target')).find('.modal-content');
            if (!$content.length) return;
            $content.html('<i class="icon-refresh"></i><p>&nbsp;</p>');
            $content.load(url, function() {
                console.log('loaded');
            });
        };

        return {
            showModal: showModal,
            closeModal: closeModal,
            replaceContentFromSignal: replaceContentFromSignal,
            replaceContent: replaceContent
        };

    })($);

})($, il.UI);