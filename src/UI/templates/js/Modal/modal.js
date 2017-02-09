var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

    UI.modal = (function ($) {

        var defaultShowOptions = {
            backdrop: true,
            keyboard: true,
            ajaxUrl: ''
        };

        var showModal = function (id, options) {
            options = $.extend(defaultShowOptions, options);
            var $modal = $(id);
            if (options.ajaxUrl) {
                $modal.load(options.ajaxUrl + ' .modal-dialog', function() {
                    $modal.modal(options);
                });
            } else {
                $modal.modal(options);
            }
        };

        var closeModal = function (id) {
            $(id).modal('close');
        };

        return {
            showModal: showModal,
            closeModal: closeModal
        };

    })($);

})($, il.UI);