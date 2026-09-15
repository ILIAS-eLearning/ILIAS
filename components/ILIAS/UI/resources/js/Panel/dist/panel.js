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
(function (il, $, document) {
  'use strict';

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

  /**
   * @param {Document} document
   * @param {JQueryEventDispatcher} eventDispatcher
   * @returns {{ initExpandable: function(string, string, string, string, Object|false, Object|false): void }}
   */
  const panel = function (document, eventDispatcher) {
    /**
     * @param {string} panelId
     * @returns {HTMLElement|null}
     */
    const getPanel = function (panelId) {
      return document.getElementById(panelId);
    };

    /**
     * @param {string} action
     * @returns {void}
     */
    const performAsync = function (action) {
      fetch(action, {
        method: 'GET',
      });
    };

    /**
     * @param {HTMLButtonElement} button
     * @param {{ signal_id: string, event: string, options: Object }} signal
     * @returns {void}
     */
    const performSignal = function (button, signal) {
      eventDispatcher.dispatch(button, signal.signal_id, {
        id: signal.signal_id,
        event: signal.event,
        triggerer: button,
        options: signal.options,
      });
    };

    /**
     * @param {HTMLElement} panelElement
     * @param {string} type
     * @returns {HTMLElement|null}
     */
    const getBodyElement = function (panelElement, type) {
      if (type === 'standard') {
        return panelElement.querySelector('.panel-body');
      }
      if (type === 'listing') {
        return panelElement.querySelector('.panel-listing-body');
      }
      return null;
    };

    /**
     * @param {string} panelId
     * @param {string} type
     * @returns {void}
     */
    const showAndHideElementsForCollapse = function (panelId, type) {
      const panelElement = getPanel(panelId);
      if (!panelElement) {
        return;
      }

      const collapseGlyph = panelElement.querySelector('[data-collapse-glyph-visibility]');
      if (collapseGlyph) {
        collapseGlyph.dataset.collapseGlyphVisibility = '0';
      }

      const expandGlyph = panelElement.querySelector('[data-expand-glyph-visibility]');
      if (expandGlyph) {
        expandGlyph.dataset.expandGlyphVisibility = '1';
      }

      const viewControls = panelElement.querySelector('.panel-viewcontrols');
      if (viewControls) {
        viewControls.dataset.vcExpanded = '0';
      }

      const body = getBodyElement(panelElement, type);
      if (body) {
        body.dataset.bodyExpanded = '0';
      }
    };

    /**
     * @param {string} panelId
     * @param {string} type
     * @returns {void}
     */
    const showAndHideElementsForExpand = function (panelId, type) {
      const panelElement = getPanel(panelId);
      if (!panelElement) {
        return;
      }

      const expandGlyph = panelElement.querySelector('[data-expand-glyph-visibility]');
      if (expandGlyph) {
        expandGlyph.dataset.expandGlyphVisibility = '0';
      }

      const collapseGlyph = panelElement.querySelector('[data-collapse-glyph-visibility]');
      if (collapseGlyph) {
        collapseGlyph.dataset.collapseGlyphVisibility = '1';
      }

      const viewControls = panelElement.querySelector('.panel-viewcontrols');
      if (viewControls) {
        viewControls.dataset.vcExpanded = '1';
      }

      const body = getBodyElement(panelElement, type);
      if (body) {
        body.dataset.bodyExpanded = '1';
      }
    };

    /**
     * @param {string} id
     * @param {string} type
     * @param {string} collapseUri
     * @param {string} expandUri
     * @param {Object|false} collapseSignal
     * @param {Object|false} expandSignal
     * @returns {void}
     */
    const initExpandable = function (
      id,
      type,
      collapseUri,
      expandUri,
      collapseSignal,
      expandSignal,
    ) {
      const panelElement = getPanel(id);
      if (!panelElement) {
        return;
      }

      const button = panelElement.querySelector('.panel-toggler')?.querySelector('button');
      if (!button) {
        return;
      }

      button.addEventListener('click', () => {
        if (button.getAttribute('aria-expanded') === 'false') {
          button.setAttribute('aria-expanded', 'true');
          showAndHideElementsForExpand(id, type);
          if (expandUri) {
            performAsync(expandUri);
          } else if (expandSignal) {
            performSignal(button, expandSignal);
          }
        } else {
          button.setAttribute('aria-expanded', 'false');
          showAndHideElementsForCollapse(id, type);
          if (collapseUri) {
            performAsync(collapseUri);
          } else if (collapseSignal) {
            performSignal(button, collapseSignal);
          }
        }
      });
    };

    return {
      initExpandable,
    };
  };

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

  class JQueryEventDispatcher {
    /**
     * @type {jQuery}
     */
    #jquery;

    /**
     * @param {jQuery} jquery
     */
    constructor(jquery) {
      this.#jquery = jquery;
    }

    /**
     * @param {HTMLElement} element
     * @param {string} eventType
     * @param {array} data
     */
    dispatch(element, eventType, data) {
      this.#jquery(element).trigger(eventType, data);
    }

    /**
     * @param {HTMLElement} element
     * @param {string} eventType
     * @param {function} handler
     */
    register(element, eventType, handler) {
      this.#jquery(element).on(eventType, handler);
    }
  }

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


  const eventDispatcher = new JQueryEventDispatcher($);

  il.UI = il.UI || {};
  il.UI.panel = panel(document, eventDispatcher);

})(il, $, document);
