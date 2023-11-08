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
 */

import { assert } from 'chai';
import Component from '../src/Component';

describe('Docu Test Example', () => {
  it('calculate example 1', () => {
    const component = new Component();

    assert.equal(3, component.calculate(1, 2));
  });

  it('calculate example 2', () => {
    let isSumCalled = false;
    const component = new Component({
      sum() {
        isSumCalled = true;
      },
    });

    component.calculate(0, 0);
    assert.isTrue(isSumCalled);
  });
});
