/*
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
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

        const rating = ($btn.closest('li').index() || 0) + 1;

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
                window.$(dt).remove();
              }
            } catch (e2) {
              // ignore
            }

            $t.webuiPopover({
              trigger: 'click',
              placement: 'auto',
              multi: true,
              container: window.$('body'),
              closeable: false,
              content() {
                return $content.html();
              },
            });
            $t.webuiPopover('show');
          } catch (ex) {
            // ignore
          }
        },
      );
    }

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
      initialized = true;

      if (!hasJQuery()) {
        return;
      }
      if (typeof window.$.fn.webuiPopover !== 'function') {
        return;
      }

      bindOverlayStars();
      bindTriggerPopover();
      overrideSaveRatingFromListGUI();
    }

    return {
      init,
    };
  }());
}(window));
