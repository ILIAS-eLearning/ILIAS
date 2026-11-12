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

import { describe, it, beforeEach } from 'node:test';
import { strict as assert } from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const formJs = readFileSync(
  new URL('../../resources/Form.js', import.meta.url),
  'utf8',
);

/**
 * Like the browser FileList: indexed, has a length and is iterable, but has
 * no forEach(). See https://developer.mozilla.org/docs/Web/API/FileList
 */
class FakeFileList {
  constructor(files) {
    files.forEach((file, i) => {
      this[i] = file;
    });
    this.length = files.length;
  }

  item(i) {
    return this[i] ?? null;
  }

  * [Symbol.iterator]() {
    for (let i = 0; i < this.length; i += 1) {
      yield this[i];
    }
  }
}

class FakeDataTransfer {
  constructor() {
    const files = [];
    this.items = { add: (file) => files.push(file) };
    Object.defineProperty(this, 'files', { get: () => new FakeFileList(files) });
  }
}

/**
 * Just enough of jQuery for the file input handlers of il.Form.
 */
function createJQueryStub(state) {
  const chain = {
    next: () => chain,
    addClass: () => chain,
    removeClass: () => chain,
  };

  return function $(target) {
    if (target === state.document) {
      return {
        on: (event, selector, handler) => {
          state.handlers[event] = handler;
        },
      };
    }
    if (target === state.element) {
      return {
        get: () => state.element,
        attr: (name) => state.element.attributes[name],
        val: () => (state.element.files.length > 0
          ? `C:\\fakepath\\${state.element.files[0].name}`
          : ''),
        parents: () => chain,
        trigger: (event, args) => state.triggered.push({ event, args: Array.from(args) }),
      };
    }
    if (typeof target === 'function') {
      return undefined;
    }
    return { on: () => undefined };
  };
}

describe('il.Form file input', () => {
  let state;

  beforeEach(() => {
    state = {
      document: {},
      handlers: {},
      triggered: [],
      element: {
        attributes: {
          'data-maxsize': '1000',
          'data-maxsize-warning': 'File too big. ',
        },
        files: new FakeFileList([]),
      },
    };
    const context = {
      il: { Util: { addOnLoad: () => undefined } },
      $: createJQueryStub(state),
      document: state.document,
      DataTransfer: FakeDataTransfer,
      console,
    };
    runInNewContext(formJs, context);
    context.il.Form.registerFileUploadInputEventTrigger();
  });

  const selectFiles = (files) => {
    let stopped = false;
    state.element.files = new FakeFileList(files);
    state.handlers.change.call(
      state.element,
      { stopImmediatePropagation: () => { stopped = true; } },
    );
    return stopped;
  };

  it('reports the name of a selected file without a forEach on the FileList', () => {
    const stopped = selectFiles([{ name: 'module.zip', size: 500 }]);

    assert.equal(stopped, false);
    assert.equal(state.element.files.length, 1);
    assert.deepEqual(state.triggered, [{ event: 'fileselect', args: [1, 'module.zip'] }]);
  });

  it('drops a file exceeding the max size and reports the warning', () => {
    const stopped = selectFiles([{ name: 'big.zip', size: 2000 }]);

    assert.equal(stopped, true);
    assert.equal(state.element.files.length, 0);
    assert.deepEqual(state.triggered, [{ event: 'fileselect', args: [0, 'File too big. '] }]);
  });
});
