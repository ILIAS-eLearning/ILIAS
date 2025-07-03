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

import { describe, it } from 'node:test';
import { strict } from 'node:assert/strict';
import URLBuilder from '../../resources/js/Core/src/core.URLBuilder.js';
import URLBuilderToken from '../../resources/js/Core/src/core.URLBuilderToken.js';

describe('URLBuilder and URLBuilderToken are available', () => {
  it('URLBuilder', () => {
    strict.equal(URLBuilder instanceof Function, true);
  });
  it('URLBuilderToken', () => {
    strict.equal(URLBuilderToken instanceof Function, true);
  });
});

describe('URLBuilder Test', () => {
  it('constructor()', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    strict.equal(u instanceof URLBuilder, true);
  });

  it('constructor() with token', () => {
    const token = new URLBuilderToken(['testing'], 'name');
    const u = new URLBuilder(
      new URL('https://www.ilias.de/ilias.php?testing_name=foo#123'),
      new Map([
        [token.getName(), token],
      ]),
    );
    strict.equal(u instanceof URLBuilder, true);
  });

  it('getUrl()', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    strict.equal(u.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1#123');
  });

  it('acquireParameter()', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result = u.acquireParameter(['testing'], 'name');
    const [url, token] = result;
    strict.equal(url instanceof URLBuilder, true);
    strict.equal(token instanceof URLBuilderToken, true);
    strict.equal(token.getName(), 'testing_name');
    strict.equal(url.getUrl() instanceof URL, true);
    strict.equal(url.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1&testing_name=#123');
    strict.equal(typeof token.getToken() === 'string', true);
    strict.notEqual(token.getToken(), '');
  });

  it('acquireParameter() with long namespace', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result = u.acquireParameter(['testing', 'my', 'object'], 'name');
    const [url] = result;
    strict.equal(url.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1&testing_my_object_name=#123');
  });

  it('acquireParameter() with value', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result = u.acquireParameter(['testing'], 'name', 'foo');
    const [url] = result;
    strict.equal(url.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1&testing_name=foo#123');
  });

  it('acquireParameter() with same name', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result = u.acquireParameter(['testing'], 'name', 'foo');
    const [url] = result;
    strict.equal(url.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1&testing_name=foo#123');

    const result2 = url.acquireParameter(['nottesting'], 'name', 'bar');
    const [url2] = result2;
    strict.equal(url2.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1&testing_name=foo&nottesting_name=bar#123');
  });

  it('acquireParameter() which is already acquired', () => {
    const token = new URLBuilderToken(['testing'], 'name');
    const u = new URLBuilder(
      new URL('https://www.ilias.de/ilias.php?testing_name=foo#123'),
      new Map([
        [token.getName(), token],
      ]),
    );
    strict.throws(() => u.acquireParameter(['testing'], 'name'), Error);
  });

  it('writeParameter()', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result = u.acquireParameter(['testing'], 'name', 'foo');
    let url = result.shift();
    const token = result.shift();
    strict.equal(url.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1&testing_name=foo#123');

    url = url.writeParameter(token, 'bar');
    strict.equal(url instanceof URLBuilder, true);
    strict.equal(url.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1&testing_name=bar#123');

    const u1 = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result1 = u1.acquireParameter(['testing'], 'arr');
    url = result1.shift();
    const token1 = result1.shift();
    url = url.writeParameter(token1, ['foo', 'bar']);
    strict.equal(
      url.getUrl().toString(),
      'https://www.ilias.de/ilias.php?a=1'
       + `&${encodeURIComponent('testing_arr')}%5B%5D=foo`
       + `&${encodeURIComponent('testing_arr')}%5B%5D=bar`
       + '#123',
    );
  });

  it('deleteParameter()', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const result = u.acquireParameter(['testing'], 'name', 'foo');
    let url = result.shift();
    const token = result.shift();
    strict.equal(url.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1&testing_name=foo#123');

    url = url.deleteParameter(token);
    strict.equal(url instanceof URLBuilder, true);
    strict.equal(url.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1#123');
  });

  it('URL too long', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    const longValue = 'x'.repeat(10000);
    u.acquireParameter(['foo'], 'bar', longValue);
    strict.throws(() => u.getUrl(), Error);
  });

  it('Remove/add/change fragment', () => {
    const u = new URLBuilder(new URL('https://www.ilias.de/ilias.php?a=1#123'));
    u.setFragment('');
    strict.equal(u.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1');
    u.setFragment('678');
    strict.equal(u.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1#678');
    u.setFragment('123');
    strict.equal(u.getUrl().toString(), 'https://www.ilias.de/ilias.php?a=1#123');
  });
});
