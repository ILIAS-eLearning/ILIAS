import { expect } from 'chai';

import horizontal from "../../../../../src/UI/templates/js/Chart/Bar/src/bar.horizontal";
import vertical from "../../../../../src/UI/templates/js/Chart/Bar/src/bar.vertical";


describe('bar', function() {
  it('components are defined', function() {
    expect(horizontal).to.not.be.undefined;
    expect(vertical).to.not.be.undefined;
  });

  var hl = horizontal();
  var vl = vertical();

  it('public interface is defined on horizontal', function() {
    expect(hl.init).to.be.a('function');
  });
  it('public interface is defined on vertical', function() {
    expect(vl.init).to.be.a('function');
  });

});