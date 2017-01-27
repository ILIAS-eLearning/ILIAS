var modal = (function ($) {

    var defaultOptions = {
        backdrop: true,
        keyboard: true,
        ajaxUrl: ''
    };

    var showModal = function (id, options) {
        console.log('showModal');
        options = $.extend(defaultOptions, options);
        console.log(options);
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
        console.log('hideModal');
        $(id).modal('close');
    };

    // Public interface
    return {
        showModal: showModal,
        closeModal: closeModal
    };

})($);

var il = il || {};
il.UI = il.UI || {};
il.UI.modal = modal;
