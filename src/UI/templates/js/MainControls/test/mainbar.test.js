//'use strict';
import { expect } from 'chai';
import { assert } from 'chai';

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
        entry_id = '0:e1',
        sub_entry,
        sub_entry_id = '0:e1:e1.1';

    it('initializes with (empty) state', function() {
        state = m.getState();
        expect(state).to.be.an('object');
        expect(state.entries).to.be.an('object');
        expect(state.tools).to.be.an('object');
        //....
    });

    it('factors and adds entries', function() {
        m.actions.addEntry(entry_id);
        m.actions.addEntry(sub_entry_id);
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
    });

    it('entries have (top-)levels', function() {
        expect([
            entry.isTopLevel(),
            sub_entry.isTopLevel()
        ]).to.eql([
            true,
            false
        ]);

        expect(m.getTopLevelEntries()).to.eql([entry]);
    });

});