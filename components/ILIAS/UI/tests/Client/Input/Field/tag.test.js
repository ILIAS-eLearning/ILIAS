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
import Tagify from '../../../../../../../node_modules/@yaireo/tagify/dist/tagify.js';
import * as Tag from '../../../../resources/js/Input/Field/src/Tag/tag.js';
import URLBuilder from '../../../../resources/js/Core/src/core.URLBuilder.js';
import URLBuilderToken from '../../../../resources/js/Core/src//core.URLBuilderToken.js';

const input_id = 'my_element';

function buildInput() {
  if (typeof global.document === 'undefined') {
    global.window = (new JSDOM('<!doctype html><html><body></body></html>')).window;
    global.document = window.document;
    global.DOMParser = window.DOMParser;
    global.getComputedStyle = window.getComputedStyle;
    global.EventTarget = window.EventTarget;
    global.localStorage = {
      value: undefined,
      setItem(v) { this.value = v; },
      getItem() { this.value; },
    };
  }
  const input = global.document.createElement('div');
  input.setAttribute('id', input_id);
  document.body.appendChild(input);

  return input;
}

function buildConfig() {
  return {
    allowDuplicates: false,
    autocompleteTriggerTimeout: 200,
    debug: false,
    dropdownCloseOnSelect: false,
    dropdownMaxItems: 200,
    dropdownSuggestionsStartAfter: 1,
    highlight: true,
    id: null,
    maxChars: 2000,
    maxItems: 20,
    options: [],
    readonly: false,
    selectedOptions: null,
    suggestionLimit: 50,
    suggestionStarts: 1,
    tagClass: "input-tag",
    tagTextProp: "displayValue",
    userInput: false,
  };
}

function buildTag(value) {
  Tag.init(
    Tagify,
    buildInput(),
    buildConfig(),
    value,
    new URLBuilder(
      new URL("http://ilias.de/ilias.php?cmd=123&examples_term="),
      new Map([["t_t",new URLBuilderToken(["t"], "t", "bf83e70336d140b479705a74")]])
    ),
    new URLBuilderToken(["t"], "t", "bf83e70336d140b479705a74"),
  );
}

describe('Tag Input', () => {
  it('values are not changed', () => {
    new Map([
      ['1,2,3', [1,2,3]],
      ['%2B%2B1%23%2A,%5B-2%5D,%7B%3F3%7D', ['++1#*', '[-2]', '{?3}']],
      ['some%27thing+%22else%22,%26%2F%5C' ['some\'thing "else"', '&/\\']],
      ['f%C3%BCnf%2C+sechs,sieben%2C+acht', ['fünf, sechs', 'sieben, acht']],
    ]).forEach(
      (value, index) => {
        buildTag(index);
        console.log(value);
        console.log(Tag.getTagifyInstance(input_id).value);
        strict.equal(Tag.getTagifyInstance(input_id).value, value);
      }
    );
  });
});
