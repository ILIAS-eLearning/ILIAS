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

describe('mainbar keyboard activation', () => {
  it('opens on Space and Enter and focuses the first slate item', async () => {
    const { JSDOM } = await import('jsdom');
    const { default: jquery } = await import('jquery');
    const dom = new JSDOM('<button id="entry">Repository</button><div id="slate"><a href="#home">Repository - Home</a></div><button id="more">More</button><div id="more-slate"></div>');
    const $ = jquery(dom.window);
    $.expr.pseudos.visible = () => true; // jsdom has no layout.
    const names = ['document', 'HTMLInputElement', 'HTMLSelectElement', 'HTMLTextAreaElement', 'HTMLButtonElement', 'il', 'btns', 'idx'];
    const saved = names.map((name) => globalThis[name]);
    try {
      names.slice(0, 5).forEach((name) => { globalThis[name] = dom.window[name]; });
      globalThis.btns = undefined;
      globalThis.idx = undefined;
      const m = model();
      m.actions.addEntry('entry');
      m.actions.addEntry('more');
      const r = renderer($);
      globalThis.il = { UI: { page: { isSmallScreen: () => false }, maincontrols: { mainbar: { model: m } } } };
      for (const [id, slate] of [['entry', 'slate'], ['more', 'more-slate']]) {
        r.addEntry(id, 'triggerer', id);
        r.addEntry(id, 'slate', slate);
      }
      $('#entry').on('click', () => {
        if (m.getState().entries.entry.engaged) {
          m.actions.disengageEntry('entry');
        } else {
          m.actions.engageEntry('entry');
        }
        r.render(m.getState());
      });
      r.render(m.getState());
      for (const key of [' ', 'Enter']) {
        $('#entry')[0].focus();
        $('#entry').trigger($.Event('keydown', { key }));
        strict.equal(dom.window.document.activeElement.textContent, 'Repository - Home');
        $('a').trigger($.Event('keydown', { key: 'Tab', shiftKey: true }));
        strict.equal(dom.window.document.activeElement.id, 'entry');
        $('#entry').trigger($.Event('keydown', { key }));
        strict.equal(m.getState().entries.entry.engaged, false);
        strict.equal(dom.window.document.activeElement.id, 'entry');
      }
    } finally {
      names.forEach((name, index) => { globalThis[name] = saved[index]; });
      dom.window.close();
    }
  });
});
