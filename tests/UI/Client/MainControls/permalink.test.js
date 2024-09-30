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

import { describe, it, beforeEach, afterEach } from 'mocha';
import { expect } from 'chai';
import { copyText } from '../../../../src/UI/templates/js/MainControls/src/footer/permalink';

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
    expect(copyText('foo')).to.be.equal(response);
    expect(written).to.be.equal('foo');
  });

  it('Legacy Clipboard API', () => {
    const {callOnce, finish} = expectOneCall();
    const node = { remove: callOnce() };
    const range = {
      selectNodeContents: callOnce(n => expect(n).to.be.equal(node))
    };
    const selection = {
      addRange: callOnce(x => expect(x).to.be.equal(range)),
      removeAllRanges: callOnce(),
    };

    globalThis.window = {
      navigator: {},
      getSelection: callOnce(() => selection),
    };

    globalThis.document = {
      createRange: callOnce(() => range),

      createElement: callOnce(text => {
        expect(text).to.be.equal('span');
        return node;
      }),

      execCommand: callOnce(s => {
        expect(s).to.be.equal('copy');
        return true;
      }),

      body: {
        appendChild: callOnce(n => {
          expect(n).to.be.equal(node);
          expect(n.textContent).to.be.equal('foo');
        }),
      },
    };

    return copyText('foo').then(finish);
  });
});
