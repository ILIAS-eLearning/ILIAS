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
import { strict } from 'node:assert/strict';
import { init } from '../../../../resources/js/Input/Field/src/Tag/tag.js';

describe('Tag Input Field', () => {
  const pseudoConfig = {
    options: [],
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
  it('should call the provided async endpoint.', () => {
    const inputMock = {
      id: 'test-id',
    };
    const tagifyWindowMock = {
      setTimeout: mock.fn(() => {}),
      clearTimeout: () => {
      },
    };
    const tagifyInstanceMock = {
      DOM: { scope: { ownerDocument: { defaultView: tagifyWindowMock } } },
      on: mock.fn(() => {
      }),
      loading: () => {
      },
      addTags: () => {
      },
    };
    const tagifyMock = class {
      constructor() {
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

    init(tagifyMock, inputMock, pseudoConfig, [], urlBuilderMock, urlBuilderTokenMock);

    strict.equal(tagifyInstanceMock.on.mock.callCount(), 1);
    strict.equal(tagifyInstanceMock.on.mock.calls[0].arguments[0], 'input');
    const inputEventHandler = tagifyInstanceMock.on.mock.calls[0].arguments[1];
    strict.notEqual(inputEventHandler, undefined);
    strict.notEqual(inputEventHandler, null);

    const eventMock = {
      detail: { value: { length: pseudoConfig.suggestionStarts + 1 } },
    };
    mock.method(global, 'AbortController', { signal: '' });
    const fetchMock = mock.fn(() => new Promise(() => {}));
    mock.method(global, 'fetch', fetchMock);

    inputEventHandler(eventMock);

    strict.equal(tagifyWindowMock.setTimeout.mock.callCount(), 1);
    strict.equal(
      tagifyWindowMock.setTimeout.mock.calls[0].arguments[1],
      pseudoConfig.autocompleteTriggerTimeout,
    );
    const timoutHandler = tagifyWindowMock.setTimeout.mock.calls[0].arguments[0];
    strict.notEqual(timoutHandler, undefined);
    strict.notEqual(timoutHandler, null);

    timoutHandler();
    strict.equal(fetchMock.mock.callCount(), 1);

    mock.reset();
  });
});
