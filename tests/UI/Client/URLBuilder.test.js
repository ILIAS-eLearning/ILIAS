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
 *
 ********************************************************************
 */

import { expect } from 'chai';
import { describe, it } from 'mocha';
import URLBuilder from '../../../src/UI/templates/js/Core/src/core.URLBuilder';
import URLBuilderToken from '../../../src/UI/templates/js/Core/src/core.URLBuilderToken';

describe('URLBuilder and URLBuilderToken are available', () => {
  it('URLBuilder', () => {
    expect(URLBuilder).to.not.be.undefined;
  });
  it('URLBuilderToken', () => {
    expect(URLBuilderToken).to.not.be.undefined;
  });
});

describe('URLBuilder Test', () => {
  it('constructor()', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    expect(u).to.be.an('object');
    expect(u).to.be.instanceOf(URLBuilder);
  });

  it('constructor() with token', () => {
    const token = new URLBuilderToken(['testing'], 'name');
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?testing_name=foo#123'),
      new Map([
        [token.getName(), token],
      ]),
    );
    expect(u).to.be.an('object');
    expect(u).to.be.instanceOf(URLBuilder);
  });

  it('getUrl()', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    expect(u.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1#123');
  });

  it('acquireParameter()', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result = u.acquireParameter(['testing'], 'name');
    const { url } = result;
    const { token } = result;
    expect(url).to.be.instanceOf(URLBuilder);
    expect(token).to.be.instanceOf(URLBuilderToken);
    expect(token.getName()).to.eql('testing_name');
    expect(url.getUrl()).to.be.instanceOf(URL);
    expect(url.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=#123');
    expect(token.getToken()).to.not.be.empty;
  });

  it('acquireParameter() with long namespace', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result = u.acquireParameter(['testing', 'my', 'object'], 'name');
    const { url } = result;
    expect(url.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_my_object_name=#123');
  });

  it('acquireParameter() with value', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result = u.acquireParameter(['testing'], 'name', 'foo');
    const { url } = result;
    expect(url.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=foo#123');
  });

  it('acquireParameter() with same name', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result = u.acquireParameter(['testing'], 'name', 'foo');
    const { url } = result;
    expect(url.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=foo#123');

    const result2 = url.acquireParameter(['nottesting'], 'name', 'bar');
    const { url: url2 } = result2;
    expect(url2.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=foo&nottesting_name=bar#123');
  });

  it('acquireParameter() which is already acquired', () => {
    const token = new URLBuilderToken(['testing'], 'name');
    const u = new URLBuilder(
      new URL('https://www.ilias.de/ilias.php?testing_name=foo#123'),
      new Map([
        [token.getName(), token],
      ]),
    );

    expect(() => u.acquireParameter(['testing'], 'name')).to.throw(Error);
  });

  it('writeParameter()', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result = u.acquireParameter(['testing'], 'name', 'foo');
    let { url } = result;
    const { token } = result;
    expect(url.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=foo#123');

    url = url.writeParameter(token, 'bar');
    expect(url).to.be.instanceOf(URLBuilder);
    expect(url.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=bar#123');
  });

  it('deleteParameter()', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result = u.acquireParameter(['testing'], 'name', 'foo');
    let { url } = result;
    const { token } = result;
    expect(url.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=foo#123');

    url = url.deleteParameter(token);
    expect(url).to.be.instanceOf(URLBuilder);
    expect(url.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1#123');
  });

  it('URL too long', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const longValue = 'x'.repeat(10000);
    u.acquireParameter(['foo'], 'bar', longValue);
    expect(() => u.getUrl()).to.throw(Error);
  });

  it('Remove/add/change fragment', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    u.setFragment('');
    expect(u.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1');
    u.setFragment('678');
    expect(u.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1#678');
    u.setFragment('123');
    expect(u.getUrl().toString()).to.eql('https://www.ilias.de/ilias.php?a=1#123');
  });
});
