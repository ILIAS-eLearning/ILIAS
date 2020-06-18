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