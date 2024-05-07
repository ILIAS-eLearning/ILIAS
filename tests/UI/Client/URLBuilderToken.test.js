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
import URLBuilderToken from '../../../src/UI/templates/js/Core/src/core.URLBuilderToken';

const URLBuilderTokenLength = 24;

describe('URLBuilderToken is available', () => {
  it('URLBuilderToken', () => {
    expect(URLBuilderToken).to.not.be.undefined;
  });
});

describe('URLBuilderToken Test', () => {
  it('constructor()', () => {
    const token = new URLBuilderToken(['testing'], 'name');
    expect(token).to.be.an('object');
    expect(token).to.be.instanceOf(URLBuilderToken);
  });

  it('getName()', () => {
    const token = new URLBuilderToken(['testing'], 'name');
    expect(token.getName()).to.eql('testing_name');
  });

  it('getToken()', () => {
    const token = new URLBuilderToken(['testing'], 'name');
    expect(token.getToken()).to.not.be.empty;
    expect(token.getToken()).to.be.a('string');
    expect(token.getToken()).to.have.lengthOf(URLBuilderTokenLength);
  });
});
