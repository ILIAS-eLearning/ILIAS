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

import { afterEach, beforeEach, describe, it, mock } from 'node:test';
import { strict } from 'node:assert/strict';
import * as assert from 'node:assert';
import * as PROMPT from '../../../resources/js/Prompt/src/constants.js';
import Prompt from '../../../resources/js/Prompt/src/Prompt.js';

const BASE_URI = 'https://example.com/base/';

// fake DOM classes, the class under test only uses instanceof checks on these
class FakeForm {
  constructor({ method = 'get', action = 'https://example.com/submit', entries = [] } = {}) {
    this.method = method;
    this.action = action;
    this.entries = entries;
  }
}

class FakeAnchor {
  constructor({ href = 'https://example.com/link', target = '_self' } = {}) {
    this.href = href;
    this.target = target;
  }
}

class FakeFormData {
  constructor(form) {
    this.form = form;
    this.pairs = form.entries ?? [];
  }

  [Symbol.iterator]() {
    return this.pairs[Symbol.iterator]();
  }
}

function createDialog() {
  const listeners = {};
  const location = { baseURI: BASE_URI, replace: mock.fn() };
  const view = { HTMLFormElement: FakeForm, HTMLAnchorElement: FakeAnchor, location };
  return {
    ownerDocument: { defaultView: view },
    location,
    listeners,
    addEventListener: mock.fn((type, handler) => {
      listeners[type] = handler;
    }),
    showModal: mock.fn(),
    close: mock.fn(),
  };
}

function createEvent(target, submitterAttributes = null) {
  const event = {
    target,
    defaultPrevented: false,
    submitter: submitterAttributes === null
      ? undefined
      : { getAttribute: (name) => submitterAttributes[name] ?? null },
    preventDefault: mock.fn(() => {
      event.defaultPrevented = true;
    }),
  };
  return event;
}

const flush = () => new Promise((resolve) => { setImmediate(resolve); });

describe('Prompt', () => {
  let dialog;
  let renderer;
  let loader;
  let errorElements;
  let prompt;
  let originalFormData;

  const showState = {
    instruction: PROMPT.PROMPT_STATE_INSTRUCTION_SHOW,
    title: 'Title',
    contents: ['content'],
    buttons: ['button'],
    scripts: ['script'],
  };

  beforeEach(() => {
    originalFormData = globalThis.FormData;
    globalThis.FormData = FakeFormData;

    dialog = createDialog();
    renderer = { render: mock.fn() };
    loader = { load: mock.fn(async () => showState) };
    errorElements = [{ id: 'error' }];
    prompt = new Prompt(renderer, loader, errorElements, dialog);
  });

  afterEach(() => {
    globalThis.FormData = originalFormData;
    mock.restoreAll();
  });

  describe('constructor', () => {
    it('registers submit and click listeners on the dialog', () => {
      assert.equal(dialog.addEventListener.mock.callCount(), 2);
      assert.equal(typeof dialog.listeners.submit, 'function');
      assert.equal(typeof dialog.listeners.click, 'function');
    });
  });

  describe('show() / close()', () => {
    it('opens the dialog modally', () => {
      prompt.show();
      assert.equal(dialog.showModal.mock.callCount(), 1);
    });

    it('closes the dialog', () => {
      prompt.close();
      assert.equal(dialog.close.mock.callCount(), 1);
    });
  });

  describe('loadState()', () => {
    it('resolves a relative url string against the base uri and passes the request', async () => {
      const request = { method: 'POST' };
      await prompt.loadState('foo/bar?x=1', request);

      assert.equal(loader.load.mock.callCount(), 1);
      const [url, passedRequest] = loader.load.mock.calls[0].arguments;
      assert.ok(url instanceof URL);
      assert.equal(url.href, 'https://example.com/base/foo/bar?x=1');
      assert.equal(passedRequest, request);
    });

    it('accepts URL objects', async () => {
      await prompt.loadState(new URL('https://other.example.org/x'));
      const [url, request] = loader.load.mock.calls[0].arguments;
      assert.equal(url.href, 'https://other.example.org/x');
      assert.deepEqual(request, {});
    });

    it('renders and shows the prompt on a "show" instruction', async () => {
      await prompt.loadState('x');

      assert.equal(renderer.render.mock.callCount(), 1);
      assert.deepEqual(renderer.render.mock.calls[0].arguments, [
        'Title', ['content'], ['button'], ['script'],
      ]);
      assert.equal(dialog.showModal.mock.callCount(), 1);
    });

    it('replaces the location on a "redirect" instruction', async () => {
      loader.load = mock.fn(async () => ({
        instruction: PROMPT.PROMPT_STATE_INSTRUCTION_REDIRECT,
        redirect: '/target?a=b',
      }));

      await prompt.loadState('x');

      assert.equal(dialog.location.replace.mock.callCount(), 1);
      const arg = dialog.location.replace.mock.calls[0].arguments[0];
      assert.ok(arg instanceof URL);
      assert.equal(arg.href, 'https://example.com/target?a=b');
      assert.equal(renderer.render.mock.callCount(), 0);
    });

    it('closes the dialog on a "close" instruction', async () => {
      loader.load = mock.fn(async () => ({ instruction: PROMPT.PROMPT_STATE_INSTRUCTION_CLOSE }));

      await prompt.loadState('x');

      assert.equal(dialog.close.mock.callCount(), 1);
      assert.equal(dialog.showModal.mock.callCount(), 0);
      assert.equal(renderer.render.mock.callCount(), 0);
    });

    it('rejects and renders the error elements on an unknown instruction', async () => {
      loader.load = mock.fn(async () => ({ instruction: 'bogus' }));

      await assert.rejects(
        prompt.loadState('x'),
        { message: "Could not process state instruction: 'bogus'" },
      );

      assert.equal(renderer.render.mock.callCount(), 1);
      assert.deepEqual(renderer.render.mock.calls[0].arguments, [null, errorElements, []]);
      assert.equal(dialog.showModal.mock.callCount(), 1);
    });

    it('rethrows loader errors after rendering the error elements', async () => {
      const error = new Error('network down');
      loader.load = mock.fn(async () => { throw error; });

      await assert.rejects(prompt.loadState('x'), (thrown) => thrown === error);

      assert.deepEqual(renderer.render.mock.calls[0].arguments, [null, errorElements, []]);
      assert.equal(dialog.showModal.mock.callCount(), 1);
    });

    it('ignores calls while a state is still loading', async () => {
      let resolveLoad;
      loader.load = mock.fn(() => new Promise((resolve) => { resolveLoad = resolve; }));

      const first = prompt.loadState('first');
      await prompt.loadState('second');

      assert.equal(loader.load.mock.callCount(), 1);

      resolveLoad(showState);
      await first;
      assert.equal(renderer.render.mock.callCount(), 1);
    });

    it('allows loading again after a successful load', async () => {
      await prompt.loadState('one');
      await prompt.loadState('two');
      assert.equal(loader.load.mock.callCount(), 2);
    });

    it('allows loading again after a failed load', async () => {
      loader.load = mock.fn(async () => { throw new Error('fail'); });
      await assert.rejects(prompt.loadState('one'));

      loader.load = mock.fn(async () => showState);
      await prompt.loadState('two');

      assert.equal(loader.load.mock.callCount(), 1);
      assert.equal(dialog.showModal.mock.callCount(), 2);
    });
  });

  describe('form submit interception', () => {
    const submit = (event) => dialog.listeners.submit(event);

    it('submits POST forms via loadState with method and form data', async () => {
      const form = new FakeForm({ method: 'post', action: 'https://example.com/save' });
      const event = createEvent(form);

      submit(event);
      await flush();

      assert.equal(event.preventDefault.mock.callCount(), 1);
      assert.equal(loader.load.mock.callCount(), 1);
      const [url, request] = loader.load.mock.calls[0].arguments;
      assert.equal(url.href, 'https://example.com/save');
      assert.equal(request.method, 'POST');
      assert.ok(request.body instanceof FakeFormData);
      assert.equal(request.body.form, form);
    });

    it('converts GET form data into query parameters', async () => {
      const form = new FakeForm({
        method: 'get',
        action: 'https://example.com/search?old=1',
        entries: [['q', 'a b'], ['page', '2']],
      });
      const event = createEvent(form);

      submit(event);
      await flush();

      assert.equal(event.preventDefault.mock.callCount(), 1);
      const [url, request] = loader.load.mock.calls[0].arguments;
      assert.equal(url.href, 'https://example.com/search?q=a+b&page=2');
      assert.deepEqual(request, {});
    });

    it('prefers the submitter formmethod and formaction', async () => {
      const form = new FakeForm({ method: 'get', action: 'https://example.com/default' });
      const event = createEvent(form, { formmethod: 'post', formaction: 'other' });

      submit(event);
      await flush();

      const [url, request] = loader.load.mock.calls[0].arguments;
      assert.equal(url.href, 'https://example.com/base/other');
      assert.equal(request.method, 'POST');
    });

    it('falls back to form attributes when the submitter has none', async () => {
      const form = new FakeForm({ method: 'post', action: 'https://example.com/fallback' });
      const event = createEvent(form, {});

      submit(event);
      await flush();

      const [url, request] = loader.load.mock.calls[0].arguments;
      assert.equal(url.href, 'https://example.com/fallback');
      assert.equal(request.method, 'POST');
    });

    it('ignores forms with method "dialog"', async () => {
      const event = createEvent(new FakeForm({ method: 'dialog' }));

      submit(event);
      await flush();

      assert.equal(event.preventDefault.mock.callCount(), 0);
      assert.equal(loader.load.mock.callCount(), 0);
    });

    it('ignores submitters that override the method with "dialog"', async () => {
      const event = createEvent(new FakeForm({ method: 'post' }), { formmethod: 'dialog' });

      submit(event);
      await flush();

      assert.equal(event.preventDefault.mock.callCount(), 0);
      assert.equal(loader.load.mock.callCount(), 0);
    });

    it('ignores events that are already default-prevented', async () => {
      const event = createEvent(new FakeForm({ method: 'post' }));
      event.defaultPrevented = true;

      submit(event);
      await flush();

      assert.equal(event.preventDefault.mock.callCount(), 0);
      assert.equal(loader.load.mock.callCount(), 0);
    });

    it('ignores events whose target is not a form', async () => {
      const event = createEvent({});

      submit(event);
      await flush();

      assert.equal(event.preventDefault.mock.callCount(), 0);
      assert.equal(loader.load.mock.callCount(), 0);
    });
  });

  describe('anchor click interception', () => {
    const click = (event) => dialog.listeners.click(event);

    it('loads the state of anchors targeting _self', async () => {
      const event = createEvent(new FakeAnchor({ href: 'https://example.com/next?x=1' }));

      click(event);
      await flush();

      assert.equal(event.preventDefault.mock.callCount(), 1);
      assert.equal(loader.load.mock.callCount(), 1);
      const [url, request] = loader.load.mock.calls[0].arguments;
      assert.equal(url.href, 'https://example.com/next?x=1');
      assert.deepEqual(request, {});
    });

    it('ignores anchors with another target', async () => {
      const event = createEvent(new FakeAnchor({ target: '_blank' }));

      click(event);
      await flush();

      assert.equal(event.preventDefault.mock.callCount(), 0);
      assert.equal(loader.load.mock.callCount(), 0);
    });

    it('ignores events that are already default-prevented', async () => {
      const event = createEvent(new FakeAnchor());
      event.defaultPrevented = true;

      click(event);
      await flush();

      assert.equal(event.preventDefault.mock.callCount(), 0);
      assert.equal(loader.load.mock.callCount(), 0);
    });

    it('ignores events whose target is not an anchor', async () => {
      const event = createEvent({});

      click(event);
      await flush();

      assert.equal(event.preventDefault.mock.callCount(), 0);
      assert.equal(loader.load.mock.callCount(), 0);
    });
  });
});
