//'use strict';
import { expect } from 'chai';
//import { assert } from 'chai';

import mainbar  from '../src/mainbar.main.js';
import model  from '../src/mainbar.model.js';
import persistence  from '../src/mainbar.persistence.js';
import renderer  from '../src/mainbar.renderer.js';


describe('mainbar components are there', function() {
    it('mainbar', function() {
        expect(mainbar).to.not.be.undefined;
    });
    it('model', function() {
        expect(model).to.not.be.undefined;
    });
    it('persistence', function() {
        expect(persistence).to.not.be.undefined;
    });
    it('renderer', function() {
        expect(renderer).to.not.be.undefined;
    });
});


describe('model', function() {
    var m = model(),
        state,
        entry,
        entry_id = '0:1',
        sub_entry,
        sub_entry_id = '0:1:1.1',
        tool_entry,
        tool_entry_id = 't:0';

    it('initializes with (empty) state', function() {
        state = m.getState();
        expect(state).to.be.an('object');
        expect(state.entries).to.be.an('object');
        expect(state.tools).to.be.an('object');
        //....
    });

    it('factors and adds entries/tools', function() {
        m.actions.addEntry(entry_id);
        m.actions.addEntry(sub_entry_id);
        m.actions.addTool(tool_entry_id);
        state = m.getState();
        entry = state.entries[entry_id];
        sub_entry = state.entries[sub_entry_id];

        expect(entry).to.be.an('object');
        expect([
            entry.id,
            entry.engaged,
            entry.hidden
        ]).to.eql([
            entry_id,
            false,
            false
        ]);

        tool_entry = state.tools[tool_entry_id];
        expect(tool_entry).to.be.an('object');

    });

    it('entries have (top-)levels and model filters properly', function() {
        expect([
            entry.isTopLevel(),
            sub_entry.isTopLevel()
        ]).to.eql([
            true,
            false
        ]);

        expect(m.getTopLevelEntries()).to.eql([entry]);
    });

    it('actions engage and disengage entries', function() {
        m.actions.engageEntry(entry_id);
        state = m.getState();

        expect([
            state.entries[entry_id].engaged,
            state.entries[sub_entry_id].engaged,
            state.tools[tool_entry_id].engaged
        ]).to.eql([
            true,
            false,
            false
        ]);

        m.actions.disengageEntry(entry_id);
        state = m.getState();
        expect([
            state.entries[entry_id].engaged,
            state.entries[sub_entry_id].engaged,
            state.tools[tool_entry_id].engaged
        ]).to.eql([
            false,
            false,
            false
        ]);

        m.actions.engageEntry(sub_entry_id);
        state = m.getState();
        expect([
            state.entries[entry_id].engaged,
            state.entries[sub_entry_id].engaged,
            state.tools[tool_entry_id].engaged
        ]).to.eql([
            true,
            true,
            false
        ]);

        m.actions.engageTool(tool_entry_id);
        state = m.getState();
        expect([
            state.entries[entry_id].engaged,
            state.entries[sub_entry_id].engaged,
            state.tools[tool_entry_id].engaged
        ]).to.eql([
            false,
            true, //subentry, still engaged.
            true
        ]);


        m.actions.engageEntry(entry_id);
        state = m.getState();
        expect([
            state.entries[entry_id].engaged,
            state.entries[sub_entry_id].engaged,
            state.tools[tool_entry_id].engaged
        ]).to.eql([
            true,
            true, //subentry, still engaged.
            false
        ]);

    });
    
    it('calculates engaged path correctly', function() {
        m.actions.addEntry('xx:1');
        m.actions.addEntry('xx:1:1');
        state = m.getState();

        state.entries['xx:1'].engaged = true;
        state.entries['xx:1:1'].engaged = true;
        expect(m.isInView('xx:1')).to.be.true;
        expect(m.isInView('xx:1:1')).to.be.true;

        state.entries['xx:1'].engaged = false;
        state.entries['xx:1:1'].engaged = true;
        expect(m.isInView('xx:1')).to.be.false;
        expect(m.isInView('xx:1:1')).to.be.false;

        expect(m.isInView('apparently_nonsense')).to.be.true;
    });
});
