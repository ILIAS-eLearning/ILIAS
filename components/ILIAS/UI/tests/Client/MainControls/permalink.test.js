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

import { describe, it, beforeEach, afterEach } from 'node:test';
import { copyText, showTooltip } from '../../../resources/js/MainControls/src/footer/permalink.js';
import { strict } from 'node:assert/strict';

const expectOneCall = () => {
  const expected = [];
  const called = [];

  return {
    callOnce: (proc = () => {}) => {
      const f = (...args) => {
        if (called.includes(f)) {
          throw new Error('Called more than once.');
        }
        called.push(f);
        return proc(...args);
      };
      expected.push(f);

      return f;
    },
    finish: () => expected.forEach(proc => {
      if (!called.includes(proc)) {
        throw new Error('Never called.');
      }
    }),
  };
};

describe('Test permalink copy to clipboard', () => {
  const saved = {};
  beforeEach(() => {
    saved.window = globalThis.window;
    saved.document = globalThis.document;
  });
  afterEach(() => {
    globalThis.window = saved.window;
    globalThis.document = saved.document;
  });

  it('Clipboard API', () => {
    let written = null;
    const response = {};
    const writeText = s => {
      written = s;
      return response;
    };
    globalThis.window = { navigator: { clipboard: { writeText } } };
    strict.deepEqual(copyText('foo'), response)
    strict.equal(written, 'foo')
  });

  it('Legacy Clipboard API', () => {
    const {callOnce, finish} = expectOneCall();
    const node = { remove: callOnce() };
    const range = {
      selectNodeContents: callOnce(n => strict.deepEqual(n, node)),
    };
    const selection = {
      addRange: callOnce(x => strict.equal(x, range)),
      removeAllRanges: callOnce(),
    };

    globalThis.window = {
      navigator: {},
      getSelection: callOnce(() => selection),
    };

    globalThis.document = {
      createRange: callOnce(() => range),

      createElement: callOnce(text => {
        strict.equal(text,  'span');
        return node;
      }),

      execCommand: callOnce(s => {
        strict.equal(s,  'copy');
        return true;
      }),

      body: {
        appendChild: callOnce(n => {
          strict.deepEqual(n, node);
          strict.equal(n.textContent, 'foo');
        }),
      },
    };

    return copyText('foo').then(finish);
  });
});

describe('Test permanentlink show tooltip', () => {
  const saved = {};
  beforeEach(() => {
    saved.setTimeout = globalThis.setTimeout;
    saved.document = globalThis.document;
  });
  afterEach(() => {
    globalThis.setTimeout = saved.setTimeout;
    globalThis.document = saved.document;
  });

  const testTooltip = (mainRect, nodeRect, expectTransform = null) => () => {
    const {callOnce, finish} = expectOneCall();
    let callTimeout = null;
    globalThis.document = {
      getElementsByTagName: callOnce(tag => {
        strict.equal(tag, 'main');
        return [
          {getBoundingClientRect: callOnce(() => mainRect)}
        ];
      }),
    };

    globalThis.setTimeout = callOnce((proc, delay) => {
      callTimeout = proc;
      strict.equal(delay, 4321);
    });

    const isTooltipClass = name => strict.equal(name, 'c-tooltip--visible');
    const node = {
      parentNode: {
        classList: {
          add: callOnce(isTooltipClass),
          remove: callOnce(isTooltipClass),
        },
      },
      getBoundingClientRect: callOnce(() => nodeRect),
      style: {transform: null},
    };
    showTooltip(node, 4321);

    strict.notEqual(callTimeout, null);
    strict.deepEqual(node.style.transform, expectTransform);

    callTimeout();
    finish();
  };

  it('Show tooltip', testTooltip({left: 0, right: 10}, {left: 1, right: 9}));
  it('Show tooltip left aligned', testTooltip({left: 5, right: 10}, {left: 3, right: 9}, 'translateX(calc(2px - 50%))'));
  it('Show tooltip right aligned', testTooltip({left: 0, right: 7}, {left: 1, right: 9}, 'translateX(calc(-2px - 50%))'));
});
