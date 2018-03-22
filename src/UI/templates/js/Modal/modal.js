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
         * @param id component ID
         * @param signalData Object containing all data from the replace signal
         */
        var replaceFromSignal = function (id, signalData) {
            var url = signalData.options.url;

            il.UI.core.replaceContent(id, url, "component");
        };

        return {
            showModal: showModal,
            closeModal: closeModal,
            replaceFromSignal: replaceFromSignal
        };

    })($);

})($, il.UI);