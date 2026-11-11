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

import { readFileSync } from 'node:fs';
import { describe, it } from 'node:test';
import { strict } from 'node:assert/strict';
import mainbar from '../../../resources/js/MainControls/src/mainbar.main.js';
import model from '../../../resources/js/MainControls/src/mainbar.model.js';
import persistence from '../../../resources/js/MainControls/src/mainbar.persistence.js';
import renderer from '../../../resources/js/MainControls/src/mainbar.renderer.js';

describe('mainbar components are there', () => {
  it('mainbar', () => {
    strict.notEqual(mainbar, undefined);
  });
  it('model', () => {
    strict.notEqual(model, undefined);
  });
  it('persistence', () => {
    strict.notEqual(persistence, undefined);
  });
  it('renderer', () => {
    strict.notEqual(renderer, undefined);
  });
});

describe('mainbar model', () => {
  const m = model();
  let state;
  let entry;
  const entry_id = '0:1';
  let sub_entry;
  const sub_entry_id = '0:1:1.1';
  let tool_entry;
  const tool_entry_id = 't:0';

  it('initializes with (empty) state', () => {
    state = m.getState();
    strict.equal(state instanceof Object, true);
    strict.equal(state.entries instanceof Object, true);
    strict.equal(state.tools instanceof Object, true);
    // ....
  });

  it('factors and adds entries/tools', () => {
    m.actions.addEntry(entry_id);
    m.actions.addEntry(sub_entry_id);
    m.actions.addTool(tool_entry_id);
    state = m.getState();
    entry = state.entries[entry_id];
    sub_entry = state.entries[sub_entry_id];

    strict.equal(entry instanceof Object, true);
    strict.deepEqual([
      entry.id,
      entry.engaged,
      entry.hidden,
    ], [
      entry_id,
      false,
      false,
    ]);

    tool_entry = state.tools[tool_entry_id];
    strict.equal(tool_entry instanceof Object, true);
  });

  it('entries have (top-)levels and model filters properly', () => {
    strict.deepEqual([
      entry.isTopLevel(),
      sub_entry.isTopLevel(),
    ], [
      true,
      false,
    ]);

    strict.deepEqual(m.getTopLevelEntries(), [entry]);
  });

  it('actions engage and disengage entries', () => {
    m.actions.engageEntry(entry_id);
    state = m.getState();

    strict.deepEqual([
      state.entries[entry_id].engaged,
      state.entries[sub_entry_id].engaged,
      state.tools[tool_entry_id].engaged,
    ], [
      true,
      false,
      false,
    ]);

    m.actions.disengageEntry(entry_id);
    state = m.getState();
    strict.deepEqual([
      state.entries[entry_id].engaged,
      state.entries[sub_entry_id].engaged,
      state.tools[tool_entry_id].engaged,
    ], [
      false,
      false,
      false,
    ]);

    m.actions.engageEntry(sub_entry_id);
    state = m.getState();
    strict.deepEqual([
      state.entries[entry_id].engaged,
      state.entries[sub_entry_id].engaged,
      state.tools[tool_entry_id].engaged,
    ], [
      true,
      true,
      false,
    ]);

    m.actions.engageTool(tool_entry_id);
    state = m.getState();
    strict.deepEqual([
      state.entries[entry_id].engaged,
      state.entries[sub_entry_id].engaged,
      state.tools[tool_entry_id].engaged,
    ], [
      false,
      true, // subentry, still engaged.
      true,
    ]);

    m.actions.engageEntry(entry_id);
    state = m.getState();
    strict.deepEqual([
      state.entries[entry_id].engaged,
      state.entries[sub_entry_id].engaged,
      state.tools[tool_entry_id].engaged,
    ], [
      true,
      true, // subentry, still engaged.
      false,
    ]);
  });

  it('calculates engaged path correctly', () => {
    m.actions.addEntry('xx:1');
    m.actions.addEntry('xx:1:1');
    state = m.getState();

    state.entries['xx:1'].engaged = true;
    state.entries['xx:1:1'].engaged = true;
    strict.equal(m.isInView('xx:1'), true);
    strict.equal(m.isInView('xx:1:1'), true);

    state.entries['xx:1'].engaged = false;
    state.entries['xx:1:1'].engaged = true;
    strict.equal(m.isInView('xx:1'), false);
    strict.equal(m.isInView('xx:1:1'), false);

    strict.equal(m.isInView('apparently_nonsense'), true);
  });
});

describe('mainbar keyboard focus', () => {
  it('moves focus after native keyboard activation and leaves navigation available', async () => {
    const { JSDOM } = await import('jsdom');
    const { default: jquery } = await import('jquery');
    const { window } = new JSDOM(`
      <ul class="il-mainbar-entries">
        <li><a id="link" href="#dashboard">Dashboard</a></li>
        <li><button id="entry">Repository</button></li>
        <li><button id="more">More</button></li>
      </ul>
      <div id="slate">
        <button tabindex="-1">Skip</button>
        <button disabled>Unavailable</button>
        <a id="home" href="#home">Repository - Home</a>
      </div>
      <div id="more-slate"></div>
    `, { runScripts: 'outside-only' });
    try {
      window.$ = jquery(window);
      window.il = { UI: { page: { isSmallScreen: () => false } } };
      const bundle = new URL('../../../resources/js/MainControls/dist/mainbar.js', import.meta.url);
      window.eval(readFileSync(bundle, 'utf8'));

      const mb = window.il.UI.maincontrols.mainbar;
      mb.persistence.store = () => {};
      for (const [id, triggerer, slate] of [['0:0', 'link'], ['0:1', 'entry', 'slate'], ['0:2', 'more', 'more-slate']]) {
        mb.addPartIdAndEntry(id, 'triggerer', triggerer, false);
        if (slate) mb.addPartIdAndEntry(id, 'slate', slate, false);
      }

      const { $ } = window;
      const { document } = window;
      const entry = document.getElementById('entry');
      const slate = document.getElementById('slate');
      mb.addTriggerSignal('open-slate');
      $(entry).on('click', (event) => {
        $(entry).trigger('open-slate', {
          originalEvent: event.originalEvent,
          options: { action: 'trigger', entry_id: '0:1' },
        });
      });
      mb.renderer.render(mb.model.getState());

      const press = (target, key, shiftKey = false) => target.dispatchEvent(
        new window.KeyboardEvent('keydown', {
          key, shiftKey, bubbles: true, cancelable: true,
        }),
      );
      const activate = (detail) => entry.dispatchEvent(
        new window.MouseEvent('click', { detail, bubbles: true }),
      );

      strict.equal(press(document.getElementById('link'), 'Enter'), true);
      entry.focus();
      activate(0);
      strict.equal(document.activeElement.id, 'home');
      strict.equal(press(document.activeElement, 'Tab', true), false);
      strict.equal(document.activeElement, entry);

      activate(0); // Close the slate.
      activate(1); // Open it with a pointer.
      strict.equal(document.activeElement, entry);
      strict.equal(press(entry, 'Tab'), false);
      strict.equal(document.activeElement.id, 'home');
      entry.focus();
      slate.replaceChildren();
      strict.equal(press(entry, 'Tab'), true);
      strict.equal(document.activeElement, entry);
    } finally {
      window.close();
    }
  });
});
