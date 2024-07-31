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

const panel = function () {
  const performAsync = function (action) {
    if (action !== null) {
      fetch(action, {
        method: 'GET',
      });
    }
  };

  const performSignal = function (button, signal) {
    if (signal !== null) {
      // eslint-disable-next-line no-undef
      $(button).trigger(signal.signal_id, {
        id: signal.signal_id,
        event: signal.event,
        triggerer: button,
        options: signal.options,
      });
    }
  };

  const showAndHideElementsForCollapse = function (id, type) {
    const p = document.getElementById(id);
    p.querySelector('[data-collapse-glyph-visibility]').dataset.collapseGlyphVisibility = '0';
    p.querySelector('[data-expand-glyph-visibility]').dataset.expandGlyphVisibility = '1';
    p.querySelector('.panel-viewcontrols').dataset.vcExpanded = '0';
    if (type === 'standard') {
      p.querySelector('.panel-body').dataset.bodyExpanded = '0';
    } else if (type === 'listing') {
      p.querySelector('.panel-listing-body').dataset.bodyExpanded = '0';
    }
  };

  const showAndHideElementsForExpand = function (id, type) {
    const p = document.getElementById(id);
    p.querySelector('[data-expand-glyph-visibility]').dataset.expandGlyphVisibility = '0';
    p.querySelector('[data-collapse-glyph-visibility]').dataset.collapseGlyphVisibility = '1';
    p.querySelector('.panel-viewcontrols').dataset.vcExpanded = '1';
    if (type === 'standard') {
      p.querySelector('.panel-body').dataset.bodyExpanded = '1';
    } else if (type === 'listing') {
      p.querySelector('.panel-listing-body').dataset.bodyExpanded = '1';
    }
  };

  const initExpandable = function (
    id,
    type,
    collapseUri,
    expandUri,
    collapseSignal,
    expandSignal,
  ) {
    const button = document.getElementById(id).querySelector('.panel-toggler').querySelector('button');
    button.addEventListener('click', () => {
      if (button.getAttribute('aria-expanded') === 'false') {
        button.setAttribute('aria-expanded', true);
        showAndHideElementsForExpand(id, type);
        if (expandUri) {
          performAsync(expandUri);
        } else if (expandSignal) {
          performSignal(button, expandSignal);
        }
      } else {
        button.setAttribute('aria-expanded', false);
        showAndHideElementsForCollapse(id, type);
        if (collapseUri) {
          performAsync(collapseUri);
        } else if (collapseSignal) {
          performSignal(button, collapseSignal);
        }
      }
    });
  };

  /**
     * Public interface
     */
  return {
    initExpandable,
  };
};

export default panel;
