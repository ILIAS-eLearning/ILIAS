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

il = il || {};
il.UI = il.UI || {};
il.UI.button = il.UI.button || {};
(function ($, il) {
  il.UI.button = (function ($) {
    /* month button */
    const initMonth = function (id) {
      const btn = document.querySelector(`#${id} > input`);
      btn.addEventListener('change', (e) => {
        const value = e.srcElement.value.split('-').reverse().join('-');
        const id = e.srcElement.closest('.il-btn-month').getAttribute('id');
        $(`#${id}`).trigger('il.ui.button.month.changed', [id, value]);
      });
    };

    /* toggle button */
    const handleToggleClick = function (event, id, on_url, off_url, signals) {
      const b = $(`#${id}`);
      const pressed = b.attr('aria-pressed');
      for (let i = 0; i < signals.length; i++) {
        const s = signals[i];
        if (s.event === 'click'
					|| (pressed === 'true' && s.event === 'toggle_on')
					|| (pressed !== 'true' && s.event === 'toggle_off')
        ) {
          $(b).trigger(s.signal_id, {
            id: s.signal_id,
            event: s.event,
            triggerer: b,
            options: s.options,
          });
        }
      }

      if (pressed === 'true' && on_url !== '') {
        window.location = on_url;
      }

      if (pressed !== 'true' && off_url !== '') {
        window.location = off_url;
      }

      return false;
    };

    const activateLoadingAnimation = function (id) {
      const $button = $(`#${id}`);
      $button.addClass('il-btn-with-loading-animation');
      $button.addClass('disabled');
      return $button;
    };

    const deactivateLoadingAnimation = function (id) {
      const $button = $(`#${id}`);
      $button.removeClass('il-btn-with-loading-animation');
      $button.removeClass('disabled');
      return $button;
    };

    return {
      initMonth,
      handleToggleClick,
      activateLoadingAnimation,
      deactivateLoadingAnimation,
    };
  }($));
}($, il));

// toggle init
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.il-toggle-button:not(.unavailable)').forEach((button) => {
    const refreshLabels = (b, toggle = false) => {
      let on = b.classList.contains('on');
      if (toggle) {
        on = !on;
      }
      if (b.querySelectorAll('.il-toggle-label-off, .il-toggle-label-on').length > 0) {
        b.querySelectorAll('.il-toggle-label-off, .il-toggle-label-on').forEach((l) => {
          l.style.display = 'none';
        });
        if (on) {
          b.setAttribute('aria-pressed', true);
          b.classList.add('on');
          b.classList.remove('off');
          b.querySelector('.il-toggle-label-on').style.display = '';
        } else {
          b.setAttribute('aria-pressed', false);
          b.classList.add('off');
          b.classList.remove('on');
          b.querySelector('.il-toggle-label-off').style.display = '';
        }
      } else if (on) {
        b.setAttribute('aria-pressed', true);
        b.classList.add('on');
        b.classList.remove('off');
      } else {
        b.setAttribute('aria-pressed', false);
        b.classList.add('off');
        b.classList.remove('on');
      }
    };
    refreshLabels(button);

    button.addEventListener('click', (e) => {
      const b = e.currentTarget;
      refreshLabels(b, true);
    });
  });
});
