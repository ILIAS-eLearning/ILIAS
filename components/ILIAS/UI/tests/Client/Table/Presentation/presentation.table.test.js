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
 ******************************************************************** */

import { describe, expect, it } from '@jest/globals';
import PresentationTableFactory from '../../../../resources/js/Table/src/presentationtable.factory';
import PresentationTable from '../../../../resources/js/Table/src/presentationtable.class';
// import { JSDOM } from 'jsdom';
// import fs from 'fs';

describe.skip('Presentation Table', () => {
  beforeEach(() => {
    const domString = fs.readFileSync('./components/ILIAS/UI/tests/Client/Table/Presentation/PresentationTest.html').toString();
    const dom = new JSDOM(domString);
    /* eslint-env jquery */
    dom.window.document.getElementById = (id) => document.querySelector(`#${id}`);
    global.window = dom.window;
    global.document = dom.window.document;
  });

  it('classes exist', () => {
    /* eslint-disable no-unused-expressions */
    expect(PresentationTableFactory).toBeDefined();
    expect(PresentationTable).toBeDefined();
  });

  it('factory has public methods', () => {
    const f = new PresentationTableFactory();
    expect(f.init).toBeInstanceOf(Function);
    expect(f.get).toBeInstanceOf(Function);
  });

  it('factors a PresentationTable', () => {
    const f = new PresentationTableFactory();
    f.init('il_ui_test_table_id');
    const pt = f.get('il_ui_test_table_id');

    expect(pt instanceof PresentationTable);
    expect(pt.expandRow).toBeInstanceOf(Function);
    expect(pt.collapseRow).toBeInstanceOf(Function);
    expect(pt.toggleRow).toBeInstanceOf(Function);
    expect(pt.expandAll).toBeInstanceOf(Function);
  });
});
