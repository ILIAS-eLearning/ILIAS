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
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

import { describe, it, mock } from 'node:test';
import * as assert from 'node:assert';
import init from '../../../../resources/js/Input/Field/src/Tag/tag.js';

describe('Tag Input Field', () => {
  const pseudoConfig = {
    options: ['123', 'Yay'],
    userInput: false,
    allowDuplicates: false,
    maxItems: 5,
    dropdownSuggestionsStartAfter: 1,
    suggestionStarts: 1,
    autocompleteTriggerTimeout: 100,
    dropdownMaxItems: 5,
    dropdownCloseOnSelect: true,
    highlight: true,
  };
  const labelMock = {
    id: '',
    addEventListener: mock.fn(() => {
    }),
  };
  const formContextMock = {
    querySelector: () => labelMock,
  };
  const inputMock = {
    id: 'test-id',
    readOnly: false,
    disabled: false,
    closest: () => formContextMock,
  };
  const tagifyWindowMock = {
    setTimeout: mock.fn(() => {}),
    clearTimeout: () => {
    },
  };
  const editableInputOwnerDocumentMock = {
    getElementById: () => null,
  };
  const editableInputMock = {
    ownerDocument: editableInputOwnerDocumentMock,
    setAttribute: mock.fn(() => {
    }),
    focus: mock.fn(() => {
    }),
  };
  const tagifyInstanceMock = {
    DOM: {
      scope: {
        ownerDocument: { defaultView: tagifyWindowMock },
        querySelector: () => editableInputMock,
      },
      input: editableInputMock,
    },
    on: mock.fn(() => {
    }),
    loading: () => {
    },
    addTags: () => {
    },
    settings: null,
  };
  const tagifyMock = class {
    constructor(inputMock, settings) {
      tagifyInstanceMock.settings  = settings;
      // eslint-disable-next-line no-constructor-return
      return tagifyInstanceMock;
    }
  };
  const urlBuilderMock = {
    writeParameter: () => {
    },
    getUrl() {
      return {
        toString() {
          return '';
        },
      };
    },
  };
  const urlBuilderTokenMock = {};

  it('should call the provided async endpoint.', () => {
    init(tagifyMock, inputMock, pseudoConfig, [], urlBuilderMock, urlBuilderTokenMock);

    assert.strict.equal(tagifyInstanceMock.on.mock.callCount(), 1);
    assert.strict.equal(tagifyInstanceMock.on.mock.calls[0].arguments[0], 'input');
    const inputEventHandler = tagifyInstanceMock.on.mock.calls[0].arguments[1];
    assert.strict.notEqual(inputEventHandler, undefined);
    assert.strict.notEqual(inputEventHandler, null);

    const eventMock = {
      detail: { value: { length: pseudoConfig.suggestionStarts + 1 } },
    };
    mock.method(global, 'AbortController', { signal: '' });
    const fetchMock = mock.fn(() => new Promise(() => {}));
    mock.method(global, 'fetch', fetchMock);

    inputEventHandler(eventMock);

    assert.strict.equal(tagifyWindowMock.setTimeout.mock.callCount(), 1);
    assert.strict.equal(
      tagifyWindowMock.setTimeout.mock.calls[0].arguments[1],
      pseudoConfig.autocompleteTriggerTimeout,
    );
    const timoutHandler = tagifyWindowMock.setTimeout.mock.calls[0].arguments[0];
    assert.strict.notEqual(timoutHandler, undefined);
    assert.strict.notEqual(timoutHandler, null);

    timoutHandler();
    assert.strict.equal(fetchMock.mock.callCount(), 1);

    mock.reset();
  });

  it('should build correct settings.', () => {
    init(tagifyMock, inputMock, pseudoConfig, [], urlBuilderMock, urlBuilderTokenMock);

    assert.strict.equal(tagifyInstanceMock.settings.whitelist, pseudoConfig.options);
    assert.strict.equal(tagifyInstanceMock.settings.enforceWhitelist, !pseudoConfig.userInput);
    assert.strict.equal(tagifyInstanceMock.settings.duplicates, pseudoConfig.allowDuplicates);
    assert.strict.equal(tagifyInstanceMock.settings.maxTags, pseudoConfig.maxItems);
    assert.strict.equal(tagifyInstanceMock.settings.dropdown.enabled, pseudoConfig.dropdownSuggestionsStartAfter);
    assert.strict.equal(tagifyInstanceMock.settings.dropdown.maxItems, pseudoConfig.dropdownMaxItems);
    assert.strict.equal(tagifyInstanceMock.settings.dropdown.closeOnSelect, pseudoConfig.dropdownCloseOnSelect);
    assert.strict.equal(tagifyInstanceMock.settings.dropdown.highlightFirst, pseudoConfig.highlight);

    mock.reset();
  });

  it('should apply label relation to editable input.', () => {
    mock.reset();
    labelMock.id = '';
    init(tagifyMock, inputMock, pseudoConfig, [], urlBuilderMock, urlBuilderTokenMock);

    assert.ok(editableInputMock.setAttribute.mock.callCount() > 0);
    const latestCall = editableInputMock.setAttribute.mock.calls[
      editableInputMock.setAttribute.mock.calls.length - 1
    ];
    assert.deepEqual(
      latestCall.arguments,
      ['aria-labelledby', 'test-id-label'],
    );

    mock.reset();
  });

  it('should urlencode user input.', () => {
    const userInput = [
      {value: '++1#*'},
      {value: '[-2]'},
      {value: '{?3}'},
      {value: 'some\'thing "else"'},
      {value: '&/\\'},
      {value: 'fünf, sechs'},
      {value: 'sieben, acht'},
      {value: '<sieben, acht>'},
      {value: '<test>'},
    ];
    init(tagifyMock, inputMock, pseudoConfig, userInput, urlBuilderMock, urlBuilderTokenMock);

    userInput.forEach((v) => {
      const display = v.value;
      tagifyInstanceMock.settings.transformTag(v);
      assert.deepEqual(v, {value: encodeURIComponent(display), display: display.replace(/</g, '&lt;').replace(/>/g, '&gt;')});
    });

    mock.reset();
  });

  it('should wrapp in div with attributes.', () => {
    const tagData = {value: 'test', display: 'test', tagifySuggestionIdx: 'test'};
    init(tagifyMock, inputMock, pseudoConfig, tagData, urlBuilderMock, urlBuilderTokenMock);

    const input = {className: 'test'};

    const config  = [
      {classNames: {namespace: 'all_false'}, readonly: false, disabled: false, required: false, mode: ''},
      {classNames: {namespace: 'all_true', strictMode: 'strict_mode'}, readonly: true, disabled: true, required: true, mode: 'strict'},
    ];

    const result = [
      `<div class="all_false  test" tabIndex="-1">\u200B</div>`.replace(/ /g, ''),
      `<div class="all_true strict_mode test" readonly disabled required tabIndex="-1">\u200B</div>`.replace(/ /g, ''),
    ];

    config.forEach(
      (v, i) => {
        const test_obj = new class {
          settings = {templates: {input: {call: () => ''}}};

          test(wrapper_function, v, result) {
            const wf = wrapper_function.bind(this);
            console.log(this.settings.templates.input.call);
            assert.strict.equal(
              wf(input, v).replace(/[ \n]/g, ''),
              result,
            );
          }
        };

        test_obj.test(tagifyInstanceMock.settings.templates.wrapper, v, result[i]);
      }
    );

    mock.reset();
  });

  it('should build correct tags as divs.', () => {
    const tagData = {value: 'test', display: 'test', tagifySuggestionIdx: 'test'};
    init(tagifyMock, inputMock, pseudoConfig, tagData, urlBuilderMock, urlBuilderTokenMock);

    assert.strict.equal(
      tagifyInstanceMock.settings.templates.tag(tagData),
      `<div contenteditable='false'
          spellcheck="false" class='tagify__tag'
          value="${tagData.value}"
          tabindex="0">
          <span title='remove tag' class='tagify__tag__removeBtn'></span>
          <div>
              <span class='tagify__tag-text'>${tagData.display}</span>
          </div>
        </div>`
    );

    mock.reset();
  });

  it('should build correct dropdowns as divs.', () => {
    const tagData = {value: 'test', display: 'test', tagifySuggestionIdx: 'test'};
    init(tagifyMock, inputMock, pseudoConfig, tagData, urlBuilderMock, urlBuilderTokenMock);

    assert.strict.equal(
      tagifyInstanceMock.settings.templates.dropdownItem(tagData),
      `<div class='tagify__dropdown__item' tagifySuggestionIdx="${tagData.tagifySuggestionIdx}" value="${tagData.value}">
          <span>${tagData.display}</span>
          </div>`
    );

    mock.reset();
  });
});
