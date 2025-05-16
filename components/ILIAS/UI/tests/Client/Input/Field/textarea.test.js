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
import TextareaFactory from '../../../../resources/js/Input/Field/src/Textarea/textarea.factory.js';
import Textarea from '../../../../resources/js/Input/Field/src/Textarea/textarea.class.js';

/**
 * Input-ID that should be used to initialize instances, it will be used when
 * setting up the mocked DOM.
 *
 * @type {string}
 */
const test_input_id = 'test_input_id';

/**
 * Initializes the global window and document variable that holds a mocked
 * DOM containing the textarea html.
 *
 * @param {number} max_limit=null
 * @return {void}
 */
function initMockedDom() {
  const dom = new JSDOM(
    `
                <textarea id="${test_input_id}" class="c-field-textarea" name="test_input_1"></textarea>
                <div class="c-input__help-byline">
                    Characters remaining: <span data-action="remainder">0</span>
                </div>
        `,
    {
      url: 'https://localhost',
    },
  );

  global.window = dom.window;
  global.document = dom.window.document;

  // otherwise instanceof HTMLSpanElement checks fail, due to
  // not being in an actual "window".
  global.HTMLSpanElement = dom.window.HTMLSpanElement;
}

describe('Textarea input', () => {
  beforeEach(initMockedDom);

  it('can return lines relative to the current selection.', () => {
    const input = new Textarea(test_input_id);

    const line_1 = 'this is line 1';
    const line_2 = 'this is line 2';
    const line_3 = 'this is line 3';

    input.textarea.value = `${line_1}\n${line_2}\n${line_3}`;

    // selection or cursor is at the begining of line_2.
    input.textarea.selectionStart = input.textarea.selectionEnd = line_1.length + 1;

    strict.deepEqual(input.getLinesBeforeSelection(), [line_1]);
    strict.deepEqual(input.getLinesOfSelection(), [line_2]);
    strict.deepEqual(input.getLinesAfterSelection(), [line_3]);
  });

  it('can return lines relative to the current multiline selection.', () => {
    const input = new Textarea(test_input_id);

    const line_1 = 'this is line 1';
    const line_2 = 'this is line 2';
    const line_3 = 'this is line 3';
    const line_4 = 'this is line 4';

    input.textarea.value = `${line_1}\n${line_2}\n${line_3}\n${line_4}`;

    // selection starts at the begining of line_2 and ends before the newline on line_3.
    input.textarea.selectionStart = line_1.length + 1;
    input.textarea.selectionEnd = input.textarea.selectionStart + line_2.length + 1;

    strict.deepEqual(input.getLinesBeforeSelection(), [line_1]);
    strict.deepEqual(input.getLinesOfSelection(), [line_2, line_3]);
    strict.deepEqual(input.getLinesAfterSelection(), [line_4]);
  });

  it('can update the textarea content and selection.', () => {
    const input = new Textarea(test_input_id);

    const content = '0123456789';
    const position = 5;

    input.textarea.value = '';
    input.textarea.selectionStart = input.textarea.selectionEnd = 0;

    input.updateTextareaContent(content, position, position);

    strict.equal(input.textarea.value, content);
    strict.equal(input.textarea.selectionStart, position);
    strict.equal(input.textarea.selectionEnd, position);
  });

  it('can update the remainder if the content is updated programaticaly.', () => {
    const content = '12345';
    const max_limit = 10;
    const remainder = '5';

    // serverside rendering automatically adds this attribute,
    // in this unit test however, we append it manually.
    document.getElementById(test_input_id)?.setAttribute('maxLength', max_limit);

    const input = new Textarea(test_input_id);

    input.updateTextareaContent(content);

    strict.notEqual(input.remainder, null);
    strict.equal(input.remainder.innerHTML, remainder);
  });

  it('can update the remainder according to the current value.', () => {
    const content = '123456789';
    const max_limit = 10;
    const remainder = '1';

    // serverside rendering automatically adds this attribute,
    // in this unit test however, we append it manually.
    document.getElementById(test_input_id)?.setAttribute('maxLength', max_limit);

    const input = new Textarea(test_input_id);

    strict.equal(input.remainder.innerHTML, '0');

    input.textarea.value = content;
    input.updateRemainderCountHook({});

    strict.equal(input.remainder.innerHTML, remainder);
  });
});

describe('Textarea factory', () => {
  beforeEach(initMockedDom);

  it('can initialize textarea instances.', () => {
    const factory = new TextareaFactory();

    factory.init(test_input_id, null, null);

    strict.equal(factory.instances[test_input_id] instanceof Textarea, true);
  });

  it('can only instantiate the same ID once.', () => {
    const factory = new TextareaFactory();

    factory.init(test_input_id, null, null);

    strict.throws(() => {
      factory.init(test_input_id, null, null);
    }, Error);
  });

  it('can return an already created instance.', () => {
    const factory = new TextareaFactory();

    factory.init(test_input_id, null, null);

    const instance = factory.get(test_input_id);

    strict.equal(instance instanceof Textarea, true);
  });
});
