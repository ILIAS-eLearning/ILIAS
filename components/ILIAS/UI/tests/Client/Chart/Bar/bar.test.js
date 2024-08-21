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

import { describe, expect, it } from '@jest/globals';
import horizontal from '../../../../resources/js/Chart/Bar/src/bar.horizontal';
import vertical from '../../../../resources/js/Chart/Bar/src/bar.vertical';

describe('bar', () => {
  it('components are defined', () => {
    expect(horizontal).toBeDefined();
    expect(vertical).toBeDefined();
  });

  const hl = horizontal();
  const vl = vertical();

  it('public interface is defined on horizontal', () => {
    expect(hl.init).toBeInstanceOf(Function);
  });
  it('public interface is defined on vertical', () => {
    expect(vl.init).toBeInstanceOf(Function);
  });
});
