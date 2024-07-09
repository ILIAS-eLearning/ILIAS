var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

    UI.modal = (function ($) {

        var triggeredSignalsStorage = [];
        var defaultShowOptions = {
            backdrop: true,
            keyboard: true,
            ajaxRenderUrl: '',
            trigger: 'click'
        };

        var initializedModalboxes = {};


        var showModal = function (id, options, signalData) {
            if (triggeredSignalsStorage[signalData.id] === true) {
              return;
            }
            triggeredSignalsStorage[signalData.id] = true;
            options = $.extend(defaultShowOptions, options);
            if (options.ajaxRenderUrl) {
                var $container = $('#' + id);
                $container.load(options.ajaxRenderUrl, function() {
                    document.querySelector('#' + id + ' > dialog').showModal();
                    triggeredSignalsStorage[signalData.id] = false;
                });
            } else {
                //$modal.modal(options);
                document.getElementById(id).showModal();
                triggeredSignalsStorage[signalData.id] = false;
            }
            initializedModalboxes[signalData.id] = id;
        };

        var closeModal = function (id) {
            document.getElementById(id).close();
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
