var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

    UI.popover = (function ($) {

        var defaultOptions = {
            // Title of the popover
            title: '',
            // JQuery selector of the element containing the popover content
            url: '',
            // How the popover is being triggered: click|hover
            trigger: 'click',
            // Where the popover is placed: auto|horizontal|vertical
            placement: 'auto',
            // Allow multiple popovers being opened at the same time
            multi: true
        };

        /**
         * Show a popover for a triggerer element with the given options.
         * The popover is displayed next to the triggerer, the exact position depends on the placement option.
         *
         * @param triggerer_id ID of the triggerer in the DOM
         * @param options Object with options
         */
        var show = function (triggerer_id, options) {
            var $triggerer = $('#' + triggerer_id);
            if (!$triggerer.length) {
                return;
            }
            if (!WebuiPopovers.isCreated('#' + triggerer_id)) {
                options = $.extend({}, defaultOptions, options);
                $triggerer.webuiPopover(options).webuiPopover('show');
            }
        };

        return {
            show: show
        };

    })($);
})($, il.UI);