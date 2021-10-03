import Component from '../src/component.js';
import { expect } from 'chai';

// test 1
describe('Docu Test component example 1', function() {

  let c = new Component();

  it('calculate test example 1', function () {
    expect(c.calculate(1, 2)).to.equal(3);
  });
});

// test 2
describe('Docu Test component example 1', function() {

  // pass mock as dependency
  const mathMock = {
    sum: (a, b) => {
      return a + b;
    }
  };

  let c = new Component(mathMock);

  it('calculate test example 2', function () {
    expect(c.calculate(1, 2)).to.equal(3);
  });

});