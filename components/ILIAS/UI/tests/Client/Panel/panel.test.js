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

import { beforeEach, describe, it } from 'node:test';
import { strict } from 'node:assert/strict';
import { JSDOM } from 'jsdom';

import panel from '../../../resources/js/Panel/src/panel.main.js';

const PANEL_ID = 'panel_1';

/**
 * @param {string} panelId
 * @param {string} type
 * @returns {string}
 */
function buildPanelHtml(panelId = PANEL_ID, type = 'standard') {
  const bodyClass = type === 'listing' ? 'panel-listing-body' : 'panel-body';

  return `
    <div id="${panelId}" class="panel panel-primary panel-expandable">
      <div class="panel-heading">
        <div class="panel-toggler">
          <button aria-expanded="false" aria-controls="body_${panelId}" id="header_${panelId}">
            <span>
              <span data-collapse-glyph-visibility="0">collapse</span>
              <span data-expand-glyph-visibility="1">expand</span>
            </span>
          </button>
        </div>
        <div class="panel-viewcontrols" data-vc-expanded="0"></div>
      </div>
      <div class="${bodyClass}" id="body_${panelId}" data-body-expanded="0">content</div>
    </div>
  `;
}

/**
 * @param {string} html
 * @returns {import('jsdom').JSDOM}
 */
function initMockedDom(html = buildPanelHtml()) {
  const dom = new JSDOM(html, { url: 'https://localhost' });
  global.window = dom.window;
  global.document = dom.window.document;
  return dom;
}

class MockEventDispatcher {
  constructor() {
    /** @type {Array<{ element: HTMLElement, eventType: string, data: Object }>} */
    this.dispatches = [];
  }

  /**
   * @param {HTMLElement} element
   * @param {string} eventType
   * @param {Object} data
   * @returns {void}
   */
  dispatch(element, eventType, data) {
    this.dispatches.push({ element, eventType, data });
  }

  register() {}
}

describe('Panel', () => {
  /** @type {ReturnType<typeof panel>} */
  let panelApi;

  /** @type {MockEventDispatcher} */
  let eventDispatcher;

  beforeEach(() => {
    initMockedDom();
    eventDispatcher = new MockEventDispatcher();
    panelApi = panel(document, eventDispatcher);
  });

  it('expands panel on click when collapsed', () => {
    panelApi.initExpandable(PANEL_ID, 'standard', '', '', false, false);

    const panelElement = document.getElementById(PANEL_ID);
    const button = document.getElementById(`header_${PANEL_ID}`);
    const collapseGlyph = panelElement.querySelector('[data-collapse-glyph-visibility]');
    const expandGlyph = panelElement.querySelector('[data-expand-glyph-visibility]');
    const viewControls = panelElement.querySelector('.panel-viewcontrols');
    const body = panelElement.querySelector('.panel-body');

    button.click();

    strict.equal(button.getAttribute('aria-expanded'), 'true');
    strict.equal(collapseGlyph.dataset.collapseGlyphVisibility, '1');
    strict.equal(expandGlyph.dataset.expandGlyphVisibility, '0');
    strict.equal(viewControls.dataset.vcExpanded, '1');
    strict.equal(body.dataset.bodyExpanded, '1');
  });

  it('collapses panel on second click', () => {
    panelApi.initExpandable(PANEL_ID, 'standard', '', '', false, false);

    const panelElement = document.getElementById(PANEL_ID);
    const button = document.getElementById(`header_${PANEL_ID}`);
    const collapseGlyph = panelElement.querySelector('[data-collapse-glyph-visibility]');
    const expandGlyph = panelElement.querySelector('[data-expand-glyph-visibility]');
    const viewControls = panelElement.querySelector('.panel-viewcontrols');
    const body = panelElement.querySelector('.panel-body');

    button.click();
    button.click();

    strict.equal(button.getAttribute('aria-expanded'), 'false');
    strict.equal(collapseGlyph.dataset.collapseGlyphVisibility, '0');
    strict.equal(expandGlyph.dataset.expandGlyphVisibility, '1');
    strict.equal(viewControls.dataset.vcExpanded, '0');
    strict.equal(body.dataset.bodyExpanded, '0');
  });

  it('updates listing panel body on expand', () => {
    initMockedDom(buildPanelHtml(PANEL_ID, 'listing'));
    panelApi = panel(document, eventDispatcher);
    panelApi.initExpandable(PANEL_ID, 'listing', '', '', false, false);

    const panelElement = document.getElementById(PANEL_ID);
    const button = document.getElementById(`header_${PANEL_ID}`);
    const body = panelElement.querySelector('.panel-listing-body');

    button.click();

    strict.equal(body.dataset.bodyExpanded, '1');
  });

  it('calls fetch when expand uri is provided', () => {
    /** @type {{ url: string, options: Object }|null} */
    let fetchCall = null;
    global.fetch = (url, options) => {
      fetchCall = { url, options };
      return Promise.resolve();
    };

    panelApi.initExpandable(
      PANEL_ID,
      'standard',
      '',
      'https://example.com/expand',
      false,
      false,
    );

    document.getElementById(`header_${PANEL_ID}`).click();

    strict.notEqual(fetchCall, null);
    strict.equal(fetchCall.url, 'https://example.com/expand');
    strict.deepEqual(fetchCall.options, { method: 'GET' });
  });

  it('calls fetch when collapse uri is provided', () => {
    /** @type {{ url: string, options: Object }|null} */
    let fetchCall = null;
    global.fetch = (url, options) => {
      fetchCall = { url, options };
      return Promise.resolve();
    };

    panelApi.initExpandable(
      PANEL_ID,
      'standard',
      'https://example.com/collapse',
      'https://example.com/expand',
      false,
      false,
    );

    const button = document.getElementById(`header_${PANEL_ID}`);
    button.click();
    button.click();

    strict.notEqual(fetchCall, null);
    strict.equal(fetchCall.url, 'https://example.com/collapse');
    strict.deepEqual(fetchCall.options, { method: 'GET' });
  });

  it('dispatches expand signal when expand signal is provided', () => {
    const expandSignal = {
      signal_id: 'expand_signal',
      event: 'click',
      options: { expanded: true },
    };

    panelApi.initExpandable(PANEL_ID, 'standard', '', '', false, expandSignal);

    const button = document.getElementById(`header_${PANEL_ID}`);
    button.click();

    strict.equal(eventDispatcher.dispatches.length, 1);
    strict.equal(eventDispatcher.dispatches[0].element, button);
    strict.equal(eventDispatcher.dispatches[0].eventType, 'expand_signal');
    strict.deepEqual(eventDispatcher.dispatches[0].data, {
      id: 'expand_signal',
      event: 'click',
      triggerer: button,
      options: { expanded: true },
    });
  });

  it('dispatches collapse signal when collapse signal is provided', () => {
    const collapseSignal = {
      signal_id: 'collapse_signal',
      event: 'click',
      options: { expanded: false },
    };

    panelApi.initExpandable(PANEL_ID, 'standard', '', '', collapseSignal, false);

    const button = document.getElementById(`header_${PANEL_ID}`);
    button.click();
    button.click();

    strict.equal(eventDispatcher.dispatches.length, 1);
    strict.equal(eventDispatcher.dispatches[0].element, button);
    strict.equal(eventDispatcher.dispatches[0].eventType, 'collapse_signal');
    strict.deepEqual(eventDispatcher.dispatches[0].data, {
      id: 'collapse_signal',
      event: 'click',
      triggerer: button,
      options: { expanded: false },
    });
  });

  it('does nothing when panel element is missing', () => {
    panelApi.initExpandable('missing_panel', 'standard', '', '', false, false);
    strict.doesNotThrow(() => {
      document.getElementById(`header_${PANEL_ID}`)?.click();
    });
  });

  it('does nothing when toggler button is missing', () => {
    initMockedDom(`
      <div id="${PANEL_ID}" class="panel panel-primary panel-expandable">
        <div class="panel-body" data-body-expanded="0">content</div>
      </div>
    `);
    panelApi = panel(document, eventDispatcher);

    strict.doesNotThrow(() => {
      panelApi.initExpandable(PANEL_ID, 'standard', '', '', false, false);
    });
  });
});
