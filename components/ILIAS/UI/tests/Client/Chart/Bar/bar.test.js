import { expect } from 'chai';

import horizontal from '../../../../resources/js/Chart/Bar/src/bar.horizontal';
import vertical from '../../../../resources/js/Chart/Bar/src/bar.vertical';

describe('bar', () => {
  it('components are defined', () => {
    expect(horizontal).to.not.be.undefined;
    expect(vertical).to.not.be.undefined;
  });

  const hl = horizontal();
  const vl = vertical();

  it('public interface is defined on horizontal', () => {
    expect(hl.init).to.be.a('function');
  });
  it('public interface is defined on vertical', () => {
    expect(vl.init).to.be.a('function');
  });
});
