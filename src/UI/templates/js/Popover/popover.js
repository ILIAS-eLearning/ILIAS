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
            multi: false
        };


        var initializedPopovers = {};


        /**
         * Show a popover for a triggerer element with the given options.
         * The popover is displayed next to the triggerer, the exact position depends on the placement option.
         *
         * @param triggererId ID of the triggerer in the DOM
         * @param options Object with popover options
         * @param signalData Object with signal options
         */
        var show = function (triggererId, options, signalData) {
            var $triggerer = $('#' + triggererId);
            if (!$triggerer.length) {
                return;
            }
            if (!WebuiPopovers.isCreated('#' + triggererId)) {
                options = $.extend({}, defaultOptions, options);
                // Extend options with data from the signal
                if (signalData && signalData.event === 'mouseenter') {
                    options.trigger = 'hover';
                }
                $triggerer.webuiPopover(options).webuiPopover('show');
                // Map the signal to the received ID from the popover
                initializedPopovers[signalData.id] = triggererId;
            }
        };

        var replaceContent = function (showSignal, signalData) {
            console.log(signalData);
            // Find the ID of the triggerer where this popover belongs to
            var triggererId = (showSignal in initializedPopovers) ? initializedPopovers[showSignal] : 0;
            if (!triggererId) return;
            // Find the content of the popover
            var $triggerer = $('#' + triggererId);
            var $content = $('#' + $triggerer.attr('data-target')).find('.webui-popover-content');
            if (!$content.length) return;
            $content.html('<i class="icon-refresh"></i><p>&nbsp;</p>');
            $content.load(signalData.options.url, function() {
                console.log('loaded');
            });
        };

        return {
            show: show,
            replaceContent: replaceContent
        };

    })($);
})($, il.UI);