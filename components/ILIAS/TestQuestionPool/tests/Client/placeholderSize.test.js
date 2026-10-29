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

import { describe, it } from 'node:test';
import { strict } from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import matchingHandler from '../../resources/js/src/matching/matchingHandler.js';
import orderingVerticalHandler from '../../resources/js/src/orderingvertical/orderingverticalHandler.js';
import orderingHorizontalHandler from '../../resources/js/src/orderinghorizontal/orderinghorizontalHandler.js';

/**
 * jsdom does no layout. Every element reports a size of 0, which is what the
 * browser reports while the question is hidden, e.g. inside a collapsed
 * accordion. The size of the placeholders must come from the CSS only.
 */
function createParent(html) {
  const { window } = new JSDOM(`<div id="parent">${html}</div>`);
  return window.document.getElementById('parent');
}

/**
 * Initializes the handler with a stub for makeDraggable and returns the
 * onStartPrepareHandler, which makeDraggable calls on drag start.
 */
function initHandler(handler, parentElement, ...args) {
  let onStartPrepareHandler;
  handler(
    parentElement,
    (dragType, parent, draggableClass, placeholderClass, onChange, onStartPrepare) => {
      onStartPrepareHandler = onStartPrepare;
    },
    ...args,
  );
  return onStartPrepareHandler;
}

function assertNoInlineSize(parentElement) {
  strict.equal(parentElement.getAttribute('style'), null);
  parentElement.querySelectorAll('.c-test__dropzone').forEach((elem) => {
    strict.equal(elem.getAttribute('style'), null);
  });
}

describe('drop zones get no inline size', () => {
  it('in vertical ordering questions', () => {
    const parentElement = createParent(`
      <div class="c-test__dropzone"></div>
      <div class="dd-item" draggable="true">Answer 1</div>
      <div class="c-test__dropzone"></div>
      <div class="dd-item" draggable="true">Answer 2</div>
      <div class="c-test__dropzone"></div>`);
    const onStartPrepare = initHandler(orderingVerticalHandler, parentElement);
    assertNoInlineSize(parentElement);

    onStartPrepare(parentElement.querySelector('.dd-item'));
    assertNoInlineSize(parentElement);
  });

  it('in horizontal ordering questions', () => {
    const parentElement = createParent(`
      <div class="c-test__dropzone"></div>
      <div class="answers" draggable="true"><div><span data-rawvalue="a">a</span></div></div>
      <div class="c-test__dropzone"></div>
      <div class="answers" draggable="true"><div><span data-rawvalue="b">b</span></div></div>
      <div class="c-test__dropzone"></div>`);
    const onStartPrepare = initHandler(orderingHorizontalHandler, parentElement);
    assertNoInlineSize(parentElement);

    onStartPrepare(parentElement.querySelector('.answers'));
    assertNoInlineSize(parentElement);
  });

  it('in matching questions', () => {
    const parentElement = createParent(`
      <div class="source_area">
        <div class="c-test__dropzone"></div>
        <div class="c-test__term" data-id="1" draggable="true">Term 1</div>
      </div>
      <div class="c-test__definition">
        <input type="hidden" value="[]">
        <div class="ilMatchingQuestionTerm"></div>
      </div>`);
    const onStartPrepare = initHandler(matchingHandler, parentElement, '1:1');
    assertNoInlineSize(parentElement);

    onStartPrepare(parentElement.querySelector('.c-test__term'));
    assertNoInlineSize(parentElement);
    strict.equal(parentElement.querySelectorAll('.ilMatchingQuestionTerm > .c-test__dropzone').length, 1);
  });
});
