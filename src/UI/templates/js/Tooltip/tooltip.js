/**
 * @author Niels Theen <ntheen@databay.de>
 * @author Coling Kiegel <kiegel@qualitus.de>
 * @author Michael Jansen <mjansen@databay.de>
 */

var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

    UI.tooltip = (function ($) {

        let defaultOptions = {
            placement: "top",
            interactive: true,
            arrow: true,
            content: ''
        };

        /**
         * Internal cache to store the initialized tooltips
         */
        let initializedTooltips = {};


        /**
         * Show a tooltip for a triggerer element (the element triggering the show signal) with the given options.
         *
         * @param signalData Object containing all data from the signal
         * @param options Object with tooltip options
         */
        let showFromSignal = function (signalData, options) {
            let $triggerer = signalData.triggerer;
           
            if (!$triggerer.length) {
                return;
            }

            show($triggerer, options);
        };


        /**
         * Show a tooltip next to the given triggerer element with the provided options
         *
         * @param $triggerer JQuery object acting as triggerer
         * @param options Object with tooltip options
         * @returns {boolean} True if the tooltip has already been initialized, false otherwise
         */
        let show = function($triggerer, options) {
            let triggererId = $triggerer.attr('id');

            options = $.extend({}, defaultOptions, options);

            if (!initializedTooltips.hasOwnProperty(triggererId)) {
                tippy('#' + triggererId, options);
                initializedTooltips[triggererId] = true;
            }
            
            return false;
        };

        /**
         * Public interface
         */
        return {
            showFromSignal: showFromSignal,
            show: show
        };

    })($);
})($, il.UI);