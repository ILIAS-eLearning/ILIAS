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
    const isMonthSupported = () => {
      const input = document.createElement('input');
      input.setAttribute('type', 'month');

      // Valid browsers will reject this value in a month input, Firefox does currently accept it.
      const notADateValue = 'not-a-month';
      input.value = notADateValue;
      const rejectsInvalid = input.value !== notADateValue;

      // Safari does reject the input but also has a bad month picker on desktop
      const userAgent = navigator.userAgent.toLowerCase();
      const isIphone = userAgent.includes('iphone');
      const isIpad = userAgent.includes('ipad');
      const isSafari = userAgent.includes('safari') && !userAgent.includes('chrome'); // chrome's user agent also includes the word 'safari'

      const isLikelyBadWebKit = isSafari && !isIphone && !isIpad;

      return rejectsInvalid && !isLikelyBadWebKit;
    };

    const initMonth = function (id) {
      const container = document.querySelector(`#${id}`);
      const btn = document.querySelector(`#${id} > input`);
      const triggerEvent = (id, value) => {
        $(`#${id}`).trigger('il.ui.button.month.changed', [id, value]);
      };
      btn.addEventListener('change', (e) => {
        const value = e.target.value.split('-').reverse().join('-');
        const id = e.target.closest('.il-btn-month').getAttribute('id');
        triggerEvent(id, value);
      });

      //
      // Fallback for browsers that do not support a month picker e.g. Firefox, Safari on desktop
      // Be aware that this disregards the initially rendered input element, but fires the same event.
      //
      if (!isMonthSupported()) {
        btn.style.display = 'none';
        const valueOnLoad = btn.value.split('-');

        // month selector dropdown
        const monthDropdown = document.createElement('select');
        function populateMonthDropdown(selectElement, locale = navigator.language) {
          // DateTimeFormat generates month names in the client's language
          const formatter = new Intl.DateTimeFormat(locale, { month: 'long' });

          for (let i = 0; i < 12; i++) {
            const monthValue = String(i + 1).padStart(2, '0');
            const date = new Date(2020, i, 1);
            const monthLabel = formatter.format(date);

            const option = document.createElement('option');
            option.value = monthValue;
            option.textContent = monthLabel;

            selectElement.appendChild(option);
          }
        }
        populateMonthDropdown(monthDropdown);
        monthDropdown.value = valueOnLoad[1];
        container.appendChild(monthDropdown);

        // year input
        const yearNumber = document.createElement('input');
        // relying on inputmode rather than type=number because html5 validation only works on type=text
        yearNumber.setAttribute('type', 'text');
        yearNumber.setAttribute('inputmode', 'numeric');
        yearNumber.setAttribute('minlength', '2');
        yearNumber.setAttribute('maxlength', '4');
        yearNumber.setAttribute(
          'pattern',
          '([0-9]{2})|([0-9]{4})',
        );
        yearNumber.style.width = '6ch';
        yearNumber.value = valueOnLoad[0];
        container.appendChild(yearNumber);

        // handle input
        const passOnValues = () => {
          if (yearNumber.value.length === 2) { // converting two digit years like 19 to 2019
            yearNumber.value = `20${yearNumber.value}`;
          }
          if (yearNumber.checkValidity()) { // prevents the event to trigger on 1 or 3 digit inputs
            const monthYear = `${monthDropdown.value}-${yearNumber.value}`;
            triggerEvent(id, monthYear);
          }
        };

        yearNumber.addEventListener('change', passOnValues);
        monthDropdown.addEventListener('change', passOnValues);
      }
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
