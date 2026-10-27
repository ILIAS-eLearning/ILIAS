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

import { describe, it, beforeEach, mock } from 'node:test';
import assert from 'node:assert/strict';
import * as PROMPT from '../../../resources/js/Prompt/src/constants.js';
import PromptState from '../../../resources/js/Prompt/src/PromptState.js';
import PromptStateLoader from '../../../resources/js/Prompt/src/PromptStateLoader.js';

/**
 * Creates the smallest possible fake DocumentFragment that createPromptState()
 * can work with: it only knows the instruction attribute, everything else is
 * "not found". The functionality of createPromptState() itself is not tested
 * here, the loader just needs valid (or invalid) input for it.
 *
 * @param {string|null} instruction null creates an invalid fragment
 */
function createFragment(instruction = 'show') {
  const instructionSelector = `[${PROMPT.PROMPT_STATE_INSTRUCTION_ATTRIBUTE}]`;
  return {
    querySelector: (selector) => (instruction !== null && selector === instructionSelector
      ? { getAttribute: (name) => (name === PROMPT.PROMPT_STATE_INSTRUCTION_ATTRIBUTE ? instruction : null) }
      : null),
    querySelectorAll: () => [],
  };
}

const url = (href) => new URL(href);

describe('PromptStateLoader', () => {
  let asyncRenderer;
  let loader;

  beforeEach(() => {
    asyncRenderer = { loadContent: mock.fn(async () => createFragment()) };
    loader = new PromptStateLoader(asyncRenderer);
  });

  describe('load()', () => {
    it('loads the content via the async renderer and returns it as prompt state', async () => {
      const endpoint = url('https://example.com/prompt?cmd=show');
      const request = { method: 'GET' };

      const state = await loader.load(endpoint, request);

      assert.equal(asyncRenderer.loadContent.mock.callCount(), 1);
      const [passedEndpoint, passedRequest] = asyncRenderer.loadContent.mock.calls[0].arguments;
      assert.equal(passedEndpoint, endpoint);
      assert.equal(passedRequest, request);
      assert.ok(state instanceof PromptState);
    });

    it('defaults the request to an empty object', async () => {
      await loader.load(url('https://example.com/a'));

      assert.deepEqual(asyncRenderer.loadContent.mock.calls[0].arguments[1], {});
    });

    it('returns the cached state for a repeated request without body', async () => {
      const first = await loader.load(url('https://example.com/a'));
      const second = await loader.load(url('https://example.com/a'));

      assert.equal(asyncRenderer.loadContent.mock.callCount(), 1);
      assert.equal(second, first);
    });

    it('uses the url href as cache key, not the URL instance', async () => {
      const first = await loader.load(url('https://example.com/a?x=1'));
      const second = await loader.load(url('https://example.com/a?x=1'));

      assert.equal(asyncRenderer.loadContent.mock.callCount(), 1);
      assert.equal(second, first);
    });

    it('caches different urls separately', async () => {
      await loader.load(url('https://example.com/a'));
      await loader.load(url('https://example.com/b'));
      await loader.load(url('https://example.com/a?x=1'));

      assert.equal(asyncRenderer.loadContent.mock.callCount(), 3);
    });

    it('bypasses the cache for requests with a body', async () => {
      const endpoint = url('https://example.com/a');
      await loader.load(endpoint);
      await loader.load(endpoint, { method: 'POST', body: new URLSearchParams({ a: '1' }) });

      assert.equal(asyncRenderer.loadContent.mock.callCount(), 2);
    });

    it('does not use the cache for body requests even when nothing is cached yet', async () => {
      const endpoint = url('https://example.com/a');
      const body = new URLSearchParams({ a: '1' });

      await loader.load(endpoint, { method: 'POST', body });
      await loader.load(endpoint, { method: 'POST', body });

      assert.equal(asyncRenderer.loadContent.mock.callCount(), 2);
    });

    it('replaces the cached state with the result of a body request', async () => {
      const endpoint = url('https://example.com/a');
      const cached = await loader.load(endpoint);
      const posted = await loader.load(endpoint, { method: 'POST', body: new URLSearchParams() });
      const afterwards = await loader.load(endpoint);

      assert.notEqual(posted, cached);
      assert.equal(afterwards, posted);
      assert.equal(asyncRenderer.loadContent.mock.callCount(), 2);
    });

    it('treats requests without body as cacheable regardless of other options', async () => {
      const endpoint = url('https://example.com/a');
      const first = await loader.load(endpoint, { method: 'GET' });
      const second = await loader.load(endpoint, { method: 'GET', headers: { 'X-Test': '1' } });

      assert.equal(asyncRenderer.loadContent.mock.callCount(), 1);
      assert.equal(second, first);
    });

    it('propagates errors from the async renderer', async () => {
      const error = new Error('network down');
      asyncRenderer.loadContent = mock.fn(async () => {
        throw error;
      });

      await assert.rejects(
        loader.load(url('https://example.com/a')),
        (thrown) => thrown === error,
      );
    });

    it('does not cache failed loads', async () => {
      const endpoint = url('https://example.com/a');
      asyncRenderer.loadContent = mock.fn(async () => {
        throw new Error('fail');
      });
      await assert.rejects(loader.load(endpoint));

      asyncRenderer.loadContent = mock.fn(async () => createFragment());
      const state = await loader.load(endpoint);

      assert.equal(asyncRenderer.loadContent.mock.callCount(), 1);
      assert.ok(state instanceof PromptState);
    });

    it('rejects if the loaded content cannot be converted to a prompt state', async () => {
      asyncRenderer.loadContent = mock.fn(async () => createFragment(null));

      await assert.rejects(
        loader.load(url('https://example.com/a')),
        { message: 'State must provide instruction' },
      );
    });

    it('does not cache content that could not be converted to a prompt state', async () => {
      const endpoint = url('https://example.com/a');
      asyncRenderer.loadContent = mock.fn(async () => createFragment(null));
      await assert.rejects(loader.load(endpoint));

      asyncRenderer.loadContent = mock.fn(async () => createFragment());
      const state = await loader.load(endpoint);

      assert.equal(asyncRenderer.loadContent.mock.callCount(), 1);
      assert.ok(state instanceof PromptState);
    });

    it('keeps separate caches per loader instance', async () => {
      const otherRenderer = { loadContent: mock.fn(async () => createFragment()) };
      const otherLoader = new PromptStateLoader(otherRenderer);
      const endpoint = url('https://example.com/a');

      await loader.load(endpoint);
      await otherLoader.load(endpoint);

      assert.equal(asyncRenderer.loadContent.mock.callCount(), 1);
      assert.equal(otherRenderer.loadContent.mock.callCount(), 1);
    });
  });
});
