import Component from '../src/component.js';
import { expect } from 'chai';

// test 1
describe('component', function() {

  let c = new Component();

  it('calculate without mock', function () {
    expect(c.calculate(1, 2)).to.equal(3);
  });
});

// test 2
describe('component', function() {

  // pass mock as dependency
  const mathMock = {
    sum: (a, b) => {
      return a + b;
    }
  };

  let c = new Component(mathMock);

  it('calculate with mock', function () {
    expect(c.calculate(1, 2)).to.equal(3);
  });

});