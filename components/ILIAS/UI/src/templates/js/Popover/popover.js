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

        /**
         * Internal cache to store the initialized popovers
         */
        var initializedPopovers = {};


        /**
         * Show a popover for a triggerer element (the element triggering the show signal) with the given options.
         *
         * @param signalData Object containing all data from the signal
         * @param options Object with popover options
         */
        var showFromSignal = function (signalData, options) {
            var $triggerer = signalData.triggerer;
            if (!$triggerer.length) {
                return;
            }
            var triggererId = $triggerer.attr('id');
            if (signalData.event === 'mouseenter') {
                options.trigger = 'hover';
            }
            var initialized = show($triggerer, options);
            if (initialized === false) {
                initializedPopovers[signalData.id] = triggererId;
            }
        };


        /**
         * Replace the content of the popover showed by the given showSignal with the data returned by the URL
         * set in the signal options.
         *
         * @param showSignal ID of the show signal for the popover
         * @param signalData Object containing all data from the replace signal
         */
        var replaceContentFromSignal = function (showSignal, signalData) {
            // Find the ID of the triggerer where this popover belongs to
            var triggererId = (showSignal in initializedPopovers) ? initializedPopovers[showSignal] : 0;
            if (!triggererId) return;

			var url = signalData.options.url;
			var $triggerer = $('#' + triggererId);
			var id = $triggerer.attr('data-target');

			il.UI.core.replaceContent(id, url, "content");
        };

        /**
         * Show a popover next to the given triggerer element with the provided options
         *
         * @param $triggerer JQuery object acting as triggerer
         * @param options Object with popover options
         * @returns {boolean} True if the popover has already been initialized, false otherwise
         */
        var show = function($triggerer, options) {

            if (WebuiPopovers.isCreated('#' + $triggerer.attr('id'))) {
                return true;
            }
            // webui is moving the content at the end of the document which makes it impossible to use
            // popovers in forms without specifying a container here. We search for upper il-popover-container.
            // If given we use this element as a container for the popover
            var container;
            if (container = $('#' + $triggerer.attr('id')).parents(".il-popover-container")[0]) {
				options = $.extend({}, {container: container}, options);
            }
			options = $.extend({}, {onShow: function($el) {
					$el.trigger("il.ui.popover.show");}}, options);

            options = $.extend({}, defaultOptions, options);
            // Extend options with data from the signal
            $triggerer.webuiPopover(options).webuiPopover('show');


            return false;
        };

        /**
         * Public interface
         */
        return {
            showFromSignal: showFromSignal,
            replaceContentFromSignal: replaceContentFromSignal,
            show: show
        };

    })($);
})($, il.UI);