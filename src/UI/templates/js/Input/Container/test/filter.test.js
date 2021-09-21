import { expect } from 'chai';

import filter  from '../src/filter.main.js';


describe('filter components are there', function() {
  it('filter', function() {
    expect(filter).to.not.be.undefined;
  });
});