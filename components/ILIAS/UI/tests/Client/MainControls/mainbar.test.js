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

import { describe, expect, it } from '@jest/globals';
import mainbar from '../../../resources/js/MainControls/src/mainbar.main';
import model from '../../../resources/js/MainControls/src/mainbar.model';
import persistence from '../../../resources/js/MainControls/src/mainbar.persistence';
import renderer from '../../../resources/js/MainControls/src/mainbar.renderer';

describe('mainbar components are there', () => {
  it('mainbar', () => {
    expect(mainbar).toBeDefined();
  });
  it('model', () => {
    expect(model).toBeDefined();
  });
  it('persistence', () => {
    expect(persistence).toBeDefined();
  });
  it('renderer', () => {
    expect(renderer).toBeDefined();
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
    expect(state).toBeInstanceOf(Object);
    expect(state.entries).toBeInstanceOf(Object);
    expect(state.tools).toBeInstanceOf(Object);
    // ....
  });

  it('factors and adds entries/tools', () => {
    m.actions.addEntry(entry_id);
    m.actions.addEntry(sub_entry_id);
    m.actions.addTool(tool_entry_id);
    state = m.getState();
    entry = state.entries[entry_id];
    sub_entry = state.entries[sub_entry_id];

    expect(entry).toBeInstanceOf(Object);
    expect([
      entry.id,
      entry.engaged,
      entry.hidden,
    ]).toEqual([
      entry_id,
      false,
      false,
    ]);

    tool_entry = state.tools[tool_entry_id];
    expect(tool_entry).toBeInstanceOf(Object);
  });

  it('entries have (top-)levels and model filters properly', () => {
    expect([
      entry.isTopLevel(),
      sub_entry.isTopLevel(),
    ]).toEqual([
      true,
      false,
    ]);

    expect(m.getTopLevelEntries()).toEqual([entry]);
  });

  it('actions engage and disengage entries', () => {
    m.actions.engageEntry(entry_id);
    state = m.getState();

    expect([
      state.entries[entry_id].engaged,
      state.entries[sub_entry_id].engaged,
      state.tools[tool_entry_id].engaged,
    ]).toEqual([
      true,
      false,
      false,
    ]);

    m.actions.disengageEntry(entry_id);
    state = m.getState();
    expect([
      state.entries[entry_id].engaged,
      state.entries[sub_entry_id].engaged,
      state.tools[tool_entry_id].engaged,
    ]).toEqual([
      false,
      false,
      false,
    ]);

    m.actions.engageEntry(sub_entry_id);
    state = m.getState();
    expect([
      state.entries[entry_id].engaged,
      state.entries[sub_entry_id].engaged,
      state.tools[tool_entry_id].engaged,
    ]).toEqual([
      true,
      true,
      false,
    ]);

    m.actions.engageTool(tool_entry_id);
    state = m.getState();
    expect([
      state.entries[entry_id].engaged,
      state.entries[sub_entry_id].engaged,
      state.tools[tool_entry_id].engaged,
    ]).toEqual([
      false,
      true, // subentry, still engaged.
      true,
    ]);

    m.actions.engageEntry(entry_id);
    state = m.getState();
    expect([
      state.entries[entry_id].engaged,
      state.entries[sub_entry_id].engaged,
      state.tools[tool_entry_id].engaged,
    ]).toEqual([
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
    expect(m.isInView('xx:1')).toBe(true);
    expect(m.isInView('xx:1:1')).toBe(true);

    state.entries['xx:1'].engaged = false;
    state.entries['xx:1:1'].engaged = true;
    expect(m.isInView('xx:1')).toBe(false);
    expect(m.isInView('xx:1:1')).toBe(false);

    expect(m.isInView('apparently_nonsense')).toBe(true);
  });
});
