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

(function (window) {
  if (!window.il) {
    return;
  }

  window.il.LSO = window.il.LSO || {};

  /**
   * Rating support for the LSO kiosk player.
   *
   * In the kiosk layout, rating popover wiring (webuiPopover) is not reliably
   * executed after the rating snippet is replaced via AJAX. Therefore we bind
   * popover opening and overlay-star clicks via delegated handlers.
   */
  window.il.LSO.KioskRating = window.il.LSO.KioskRating || (function () {
    let initialized = false;

    function hasJQuery() {
      return typeof window.$ !== 'undefined';
    }

    /**
     * Reads the rating value from the star button. The core renders the value
     * into the link target, the list position is only used as a fallback.
     *
     * @param {object} $btn Star button of the rating overlay.
     * @return {number} Rating value between 1 and 5.
     */
    function ratingOf($btn) {
      const href = ($btn.attr('href') || '');
      const match = href.match(/[?&]rating=(\d+)/);
      if (match) {
        return parseInt(match[1], 10);
      }

      return ($btn.closest('li').index() || 0) + 1;
    }

    function bindOverlayStars() {
      window.$(document).on('click', '.ilRatingOverlay .il-rating-stars button', function (e) {
        const $btn = window.$(this);
        const popoverId = ($btn.closest('.webui-popover').attr('id') || '');
        if (!popoverId) {
          return;
        }

        const $trigger = window.$(`[data-target='${popoverId}']`).first();
        if (!$trigger.length) {
          return;
        }

        const $lg = $trigger.closest('div[id^=lg_div_]');
        if (!$lg.length) {
          return;
        }

        const refId = parseInt($lg.attr('data-lso-rating-refid') || '', 10);
        const hash = ($lg.attr('data-lso-rating-hash') || '');
        if (!refId || !hash) {
          return;
        }

        const rating = ratingOf($btn);

        e.preventDefault();
        e.stopPropagation();
        if (window.il && window.il.Object && typeof window.il.Object.saveRatingFromListGUI === 'function') {
          window.il.Object.saveRatingFromListGUI(refId, hash, rating);
        }
      });
    }

    function bindTriggerPopover() {
      window.$(document).on(
        'click',
        '.ilLSOKioskModeObjectHeader a.ilRating, .ilLSOKioskModeObjectHeader button.ilRating',
        function (e) {
          const $t = window.$(this);
          const $lg = $t.closest('div[id^=lg_div_]');
          if (!$lg.length) {
            return;
          }
          const $content = $lg.find('.il-standard-popover-content').first();
          if (!$content.length) {
            return;
          }

          e.preventDefault();
          e.stopPropagation();

          try {
            try {
              const dt = $t.attr('data-target');
              $t.webuiPopover('destroy');
              $t.removeData('plugin_webuiPopover');
              $t.removeAttr('data-target');
              if (dt) {
                const selector = dt.startsWith('#') ? dt : `#${dt}`;
                window.$(selector).remove();
              }
            } catch (removalError) {
              window.console.warn('LSO kiosk rating: could not reset popover.', removalError);
            }

            $t.webuiPopover({
              trigger: 'click',
              placement: 'auto',
              // Let the plugin compute width.
              width: 'auto',
              multi: true,
              container: window.$('body'),
              closeable: false,
              content() {
                return $content.html();
              },
            });
            $t.webuiPopover('show');
          } catch (popoverError) {
            window.console.warn('LSO kiosk rating: could not open popover.', popoverError);
          }
        },
      );
    }

    /**
     * Works around a core issue: the rating request of il.Object is built with
     * an unencoded ajax hash, so the kiosk player has to send the request on
     * its own.
     */
    function overrideSaveRatingFromListGUI() {
      if (!window.il || !window.il.Object || typeof window.il.Object.saveRatingFromListGUI !== 'function') {
        return;
      }

      const original = window.il.Object.saveRatingFromListGUI;

      window.il.Object.saveRatingFromListGUI = function (refId, hash, mark) {
        const urlRedraw = window.il.Object.url_redraw_li;

        window.$.ajax({
          url: `${window.il.Object.url_rating}&child_ref_id=${refId}&cadh=${encodeURIComponent(hash)}`,
          data: { rating: mark },
          type: 'POST',
          success() {
            if (window.WebuiPopovers && typeof window.WebuiPopovers.hideAll === 'function') {
              window.WebuiPopovers.hideAll();
            }

            window.$(`div[id^=lg_div_${refId}_pref_]`).each(function () {
              const id = window.$(this).attr('id');
              const parent = id.split('_').pop();

              if (urlRedraw) {
                window.$.ajax({
                  url: `${urlRedraw}&child_ref_id=${refId}&parent_ref_id=${parent}`,
                  type: 'GET',
                  success(html) {
                    window.$(`#${id}`).replaceWith(html);
                  },
                });
              } else if (typeof original === 'function') {
                original(refId, hash, mark);
              }
            });
          },
        });
      };
    }

    function init() {
      if (initialized) {
        return;
      }
      if (!hasJQuery()) {
        return;
      }
      if (typeof window.$.fn.webuiPopover !== 'function') {
        return;
      }
      initialized = true;

      bindOverlayStars();
      bindTriggerPopover();
      overrideSaveRatingFromListGUI();
    }

    return {
      init,
    };
  }());
}(window));
