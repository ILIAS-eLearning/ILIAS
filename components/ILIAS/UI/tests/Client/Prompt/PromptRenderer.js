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

import { describe, it, beforeEach } from 'node:test';
import assert from 'node:assert/strict';
import PromptRenderer from '../../../resources/js/Prompt/src/PromptRenderer.js';

// minimal fake element that tracks its children like the real DOM methods would
function createElement(name, textContent = '') {
  return {
    name,
    textContent,
    children: [],
    replaceChildren(...nodes) {
      this.children = nodes;
    },
    append(...nodes) {
      this.children.push(...nodes);
    },
  };
}

const createScript = (source) => ({ outerHTML: `<script>${source}</script>` });

describe('PromptRenderer', () => {
  let closeDialogElement;
  let titleElement;
  let contentElement;
  let buttonsElement;
  let scriptsElement;
  let renderer;

  beforeEach(() => {
    closeDialogElement = createElement('close');
    titleElement = createElement('title', 'Old title');
    contentElement = createElement('content');
    buttonsElement = createElement('buttons');
    scriptsElement = createElement('scripts');
    renderer = new PromptRenderer(
      closeDialogElement,
      titleElement,
      contentElement,
      buttonsElement,
      scriptsElement,
    );
  });

  describe('render() title', () => {
    it('updates the title if one is provided', () => {
      renderer.render('New title', [], []);
      assert.equal(titleElement.textContent, 'New title');
    });

    it('keeps the current title if null is provided', () => {
      renderer.render(null, [], []);
      assert.equal(titleElement.textContent, 'Old title');
    });

    it('keeps the current title if an empty string is provided', () => {
      renderer.render('', [], []);
      assert.equal(titleElement.textContent, 'Old title');
    });

    it('keeps the current title if a blank string is provided', () => {
      renderer.render('   \n\t', [], []);
      assert.equal(titleElement.textContent, 'Old title');
    });
  });

  describe('render() content', () => {
    it('sets the given contents', () => {
      const first = createElement('first');
      const second = createElement('second');

      renderer.render(null, [first, second], []);

      assert.deepEqual(contentElement.children, [first, second]);
    });

    it('replaces previously rendered contents', () => {
      const old = createElement('old');
      const next = createElement('next');

      renderer.render(null, [old], []);
      renderer.render(null, [next], []);

      assert.deepEqual(contentElement.children, [next]);
    });

    it('clears the contents if none are given', () => {
      renderer.render(null, [createElement('old')], []);
      renderer.render(null, [], []);

      assert.deepEqual(contentElement.children, []);
    });
  });

  describe('render() buttons', () => {
    it('renders the buttons followed by the close element', () => {
      const first = createElement('first');
      const second = createElement('second');

      renderer.render(null, [], [first, second]);

      assert.deepEqual(buttonsElement.children, [first, second, closeDialogElement]);
    });

    it('renders only the close element if there are no buttons', () => {
      renderer.render(null, [], []);

      assert.deepEqual(buttonsElement.children, [closeDialogElement]);
    });

    it('replaces previously rendered buttons and keeps the close element last', () => {
      const old = createElement('old');
      const next = createElement('next');

      renderer.render(null, [], [old]);
      renderer.render(null, [], [next]);

      assert.deepEqual(buttonsElement.children, [next, closeDialogElement]);
    });
  });

  describe('render() scripts', () => {
    it('does not touch the scripts element if no scripts are given', () => {
      renderer.render(null, [], []);

      assert.deepEqual(scriptsElement.children, []);
    });

    it('does not touch the scripts element for an empty scripts array', () => {
      renderer.render(null, [], [], []);

      assert.deepEqual(scriptsElement.children, []);
    });

    it('appends the given scripts', () => {
      const scripts = [createScript('a'), createScript('b')];

      renderer.render(null, [], [], scripts);

      assert.deepEqual(scriptsElement.children, scripts);
    });

    it('does not append the same scripts twice', () => {
      const scripts = [createScript('a'), createScript('b')];

      renderer.render(null, [], [], scripts);
      renderer.render(null, [], [], scripts);

      assert.deepEqual(scriptsElement.children, scripts);
    });

    it('identifies scripts by their markup, not by instance', () => {
      const first = [createScript('a')];
      const second = [createScript('a')];

      renderer.render(null, [], [], first);
      renderer.render(null, [], [], second);

      assert.deepEqual(scriptsElement.children, first);
    });

    it('appends a different set of scripts additionally', () => {
      const first = [createScript('a')];
      const second = [createScript('b')];

      renderer.render(null, [], [], first);
      renderer.render(null, [], [], second);

      assert.deepEqual(scriptsElement.children, [...first, ...second]);
    });

    it('still renders title, contents and buttons when scripts are skipped', () => {
      const scripts = [createScript('a')];
      const content = createElement('content-node');
      const button = createElement('button-node');

      renderer.render(null, [], [], scripts);
      renderer.render('Second', [content], [button], scripts);

      assert.deepEqual(scriptsElement.children, scripts);
      assert.equal(titleElement.textContent, 'Second');
      assert.deepEqual(contentElement.children, [content]);
      assert.deepEqual(buttonsElement.children, [button, closeDialogElement]);
    });

    it('keeps the script history per renderer instance', () => {
      const otherScriptsElement = createElement('other-scripts');
      const other = new PromptRenderer(
        createElement('close'),
        createElement('title'),
        createElement('content'),
        createElement('buttons'),
        otherScriptsElement,
      );
      const scripts = [createScript('a')];

      renderer.render(null, [], [], scripts);
      other.render(null, [], [], scripts);

      assert.deepEqual(scriptsElement.children, scripts);
      assert.deepEqual(otherScriptsElement.children, scripts);
    });
  });
});
