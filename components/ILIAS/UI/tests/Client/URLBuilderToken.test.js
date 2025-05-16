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
import URLBuilderToken from '../../resources/js/Core/src/core.URLBuilderToken.js';

describe('URLBuilderToken is available', () => {
  it('URLBuilderToken', () => {
    strict.notEqual(URLBuilderToken, undefined);
  });
});

describe('URLBuilderToken Test', () => {
  it('constructor()', () => {
    const token = new URLBuilderToken(['testing'], 'name');
    strict.equal(token instanceof URLBuilderToken, true);
  });

  it('getName()', () => {
    const token = new URLBuilderToken(['testing'], 'name');
    strict.equal(token.getName(), 'testing_name');
  });

  it('getToken()', () => {
    const token = new URLBuilderToken(['testing'], 'name');
    strict.equal(typeof token.getToken() === 'string', true);
    strict.notEqual(token.getToken(), '');
    strict.equal(token.getToken().length >= 1, true)
  });
});
