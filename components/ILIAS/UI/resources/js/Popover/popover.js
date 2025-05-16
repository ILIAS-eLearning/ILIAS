/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

var il = il || {};
il.UI = il.UI || {};

(function ($, UI) {
  UI.popover = (function ($) {
    /**
         * Tracks the current container to which the scroll listener is bound.
         * @type {JQuery|null}
         */
    let currentScrollableContainer = null;

    /**
         * Timeout ID used for debouncing the scroll event.
         * @type {number|null}
         */
    let repositionTimeout = null;

    /**
         * Flag indicating whether the scroll listener is currently bound.
         * @type {boolean}
         */
    let scrollPosRecalcisBound = false;

    /**
         * Determine the appropriate scroll container based on viewport width.
         * @return {JQuery}
         */
    function getScrollableContainer() {
      if (window.innerWidth < 768) {
        return $('body');
      }
      return $(document.querySelector('.il-layout-page-content'));
    }

    /**
         * Scroll event handler to update the position of open popovers.
         * Debounced to prevent excessive calls.
         * @return {void}
         */
    const updateAfterScroll = function () {
      clearTimeout(repositionTimeout);
      repositionTimeout = setTimeout(() => {
        $('[data-target*="webuiPopover"]').each(function () {
          const $triggerer = $(this);
          const pop = $triggerer.data('plugin_webuiPopover');
          if (pop && pop._opened) {
            pop.displayContent();
          }
        });
      }, 30);
    };

    /**
         * Sets up a scroll listener on the appropriate container based on screen size.
         * Ensures only one container is bound at a time.
         * @type {Function}
         */
    const scrollPosRecalcHandler = (function () {
      return function () {
        const newScrollableContainer = getScrollableContainer();

        // Only rebind if the container has changed
        if (scrollPosRecalcisBound && currentScrollableContainer) {
          currentScrollableContainer.off('scroll', updateAfterScroll);
          scrollPosRecalcisBound = false;
        }

        currentScrollableContainer = newScrollableContainer;

        if (!scrollPosRecalcisBound) {
          currentScrollableContainer.on('scroll', updateAfterScroll);
          scrollPosRecalcisBound = true;
        }
      };
    }());

    const defaultOptions = {
      title: '',
      container: getScrollableContainer(),
      url: '',
      trigger: 'click',
      placement: 'auto',
      multi: true,
    };

    const initializedPopovers = {};

    /**
         * Show a popover for a triggerer element (the element triggering the show signal) with the given options.
         * @param signalData Object containing all data from the signal
         * @param options Object with popover options
         */
    const showFromSignal = function (signalData, options) {
      const $triggerer = signalData.triggerer;
      if (!$triggerer.length) return;

      const triggererId = $triggerer.attr('id');
      if (signalData.event === 'mouseenter') {
        options.trigger = 'hover';
      }

      const initialized = show($triggerer, options);
      if (initialized === false) {
        initializedPopovers[signalData.id] = triggererId;
      }

      scrollPosRecalcHandler();
    };

    /**
         * Replace the content of the popover showed by the given showSignal with the data returned by the URL
         * set in the signal options.
         * @param showSignal ID of the show signal for the popover
         * @param signalData Object containing all data from the replace signal
         */
    const replaceContentFromSignal = function (showSignal, signalData) {
      const triggererId = (showSignal in initializedPopovers) ? initializedPopovers[showSignal] : 0;
      if (!triggererId) return;

      const { url } = signalData.options;
      const $triggerer = $(`#${triggererId}`);
      const id = $triggerer.attr('data-target');

      il.UI.core.replaceContent(id, url, 'content');
    };

    /**
         * Show a popover next to the given triggerer element with the provided options
         * @param $triggerer JQuery object acting as triggerer
         * @param options Object with popover options
         * @returns {boolean} True if the popover has already been initialized, false otherwise
         */
    var show = function ($triggerer, options) {
      if (WebuiPopovers.isCreated(`#${$triggerer.attr('id')}`)) {
        return true;
      }

      let container;
      if (container = $(`#${$triggerer.attr('id')}`).parents('.il-popover-container')[0]) {
        options = $.extend({}, { container }, options);
      }

      options = $.extend({}, {
        onShow($el) {
          $el.trigger('il.ui.popover.show');
        },
      }, options);

      options = $.extend({}, defaultOptions, options);

      $triggerer.webuiPopover(options).webuiPopover('show');

      return false;
    };

    return {
      showFromSignal,
      replaceContentFromSignal,
      show,
    };
  }($));
}($, il.UI));
