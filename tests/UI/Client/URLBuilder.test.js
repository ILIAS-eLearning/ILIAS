import { expect } from 'chai';
import { describe, it } from 'mocha';
import URLBuilder from '../../../src/UI/templates/js/Core/src/core.URLBuilder';
import URLBuilderToken from '../../../src/UI/templates/js/Core/src/core.URLBuilderToken';

describe('URLBuilder and URLBuilder are available', () => {
  it('URLBuilder', () => {
    expect(URLBuilder).to.not.be.undefined;
  });
  it('URLBuilderToken', () => {
    expect(URLBuilderToken).to.not.be.undefined;
  });
});

describe('URLBuilder Test', () => {
  it('constructor()', () => {
    const u = new URLBuilder('https://www.ilias.de/ilias.php?a=1#123');
    expect(u).to.be.an('object');
    expect(u).to.be.instanceOf(URLBuilder);
  });

  it('constructor() with token', () => {
    const token = new URLBuilderToken(['testing'], 'name');
    const u = new URLBuilder('https://www.ilias.de/ilias.php?testing_name=foo#123', token);
    expect(u).to.be.an('object');
    expect(u).to.be.instanceOf(URLBuilder);
  });

  it('getUrl()', () => {
    const u = new URLBuilder('https://www.ilias.de/ilias.php?a=1#123');
    expect(u.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1#123');
  });

  it('acquireParameter()', () => {
    const u = new URLBuilder('https://www.ilias.de/ilias.php?a=1#123');
    const result = u.acquireParameter(['testing'], 'name');
    const url = result[0];
    const token = result[1];
    expect(url).to.be.instanceOf(URLBuilder);
    expect(token).to.be.instanceOf(URLBuilderToken);
    expect(token.getName()).to.eql('testing_name');
    expect(url.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=#123');
    expect(token.token).to.not.be.empty;
  });

  it('acquireParameter() with long namespace', () => {
    const u = new URLBuilder('https://www.ilias.de/ilias.php?a=1#123');
    const result = u.acquireParameter(['testing', 'my', 'object'], 'name');
    const url = result[0];
    expect(url.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_my_object_name=#123');
  });

  it('acquireParameter() with value', () => {
    const u = new URLBuilder('https://www.ilias.de/ilias.php?a=1#123');
    const result = u.acquireParameter(['testing'], 'name', 'foo');
    const url = result[0];
    expect(url.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=foo#123');
  });

  it('acquireParameter() with same name', () => {
    const u = new URLBuilder('https://www.ilias.de/ilias.php?a=1#123');
    const result = u.acquireParameter(['testing'], 'name', 'foo');
    const url = result[0];
    expect(url.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=foo#123');

    const result2 = url.acquireParameter(['nottesting'], 'name', 'bar');
    const url2 = result2[0];
    expect(url2.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=foo&nottesting_name=bar#123');
  });

  it('acquireParameter() which is already claimed', () => {
    const token = new URLBuilderToken(['testing'], 'name');
    const u = new URLBuilder('https://www.ilias.de/ilias.php?testing_name=foo#123', token);

    expect(() => u.acquireParameter(['testing'], 'name')).to.throw(Error);
  });

  it('writeParameter()', () => {
    const u = new URLBuilder('https://www.ilias.de/ilias.php?a=1#123');
    const result = u.acquireParameter(['testing'], 'name', 'foo');
    let url = result[0];
    const token = result[1];
    expect(url.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=foo#123');

    url = url.writeParameter(token, 'bar');
    expect(url).to.be.instanceOf(URLBuilder);
    expect(url.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=bar#123');
  });

  it('deleteParameter()', () => {
    const u = new URLBuilder('https://www.ilias.de/ilias.php?a=1#123');
    const result = u.acquireParameter(['testing'], 'name', 'foo');
    let url = result[0];
    const token = result[1];
    expect(url.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1&testing_name=foo#123');

    url = url.deleteParameter(token);
    expect(url).to.be.instanceOf(URLBuilder);
    expect(url.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1#123');
  });

  it('URL too long', () => {
    const u = new URLBuilder('https://www.ilias.de/ilias.php?a=1#123');
    const longValue = 'x'.repeat(URLBuilder.URL_MAX_LENGTH);
    expect(() => u.acquireParameter(['foo'], 'bar', longValue)).to.throw(Error);
  });

  it('Remove/add/change fragment', () => {
    const u = new URLBuilder('https://www.ilias.de/ilias.php?a=1#123');
    u.fragment = '';
    expect(u.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1');
    u.fragment = '678';
    expect(u.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1#678');
    u.fragment = '123';
    expect(u.getUrl()).to.eql('https://www.ilias.de/ilias.php?a=1#123');
  });
});
