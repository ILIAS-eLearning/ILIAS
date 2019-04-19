/**
 * @author Niels Theen <ntheen@databay.de>
 * @author Colin Kiegel <kiegel@qualitus.de>
 * @author Michael Jansen <mjansen@databay.de>
 */

var il = il || {};
il.UI = il.UI || {};

(function ($, UI) {

	UI.tooltip = (function ($) {

		const defaultOptions = {
			placement: "top",
			interactive: true,
			arrow: true,
			trigger: 'click',
			contentId: '',
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
		const showFromSignal = function (signalData, options) {
			let $triggerer = signalData.triggerer;

			if (!$triggerer.length) {
				return;
			}

			options.trigger = signalData.event;
			options.content = $("#" + options.contentId).get(0).innerHTML;

			options = $.extend({}, defaultOptions, options);

			delete options.contentId;

			show($triggerer, options);
		};


		/**
		 * Show a tooltip next to the given triggerer element with the provided options
		 *
		 * @param $triggerer JQuery object acting as triggerer
		 * @param options Object with tooltip options
		 * @returns {boolean} True if the tooltip has already been initialized, false otherwise
		 */
		const show = function ($triggerer, options) {
			let triggererId = $triggerer.attr("id");

			if (!initializedTooltips.hasOwnProperty(triggererId)) {
				let t = tippy('#' + triggererId, options);
				t[0].show();

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