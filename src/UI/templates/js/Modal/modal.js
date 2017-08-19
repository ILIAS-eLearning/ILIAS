var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

    UI.modal = (function ($) {

        var defaultShowOptions = {
            backdrop: true,
            keyboard: true,
            ajaxRenderUrl: ''
        };

        var showModal = function (id, options) {
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
        };

        var closeModal = function (id) {
            $('#' + id).modal('close');
        };

        return {
            showModal: showModal,
            closeModal: closeModal
        };

    })($);

})($, il.UI);