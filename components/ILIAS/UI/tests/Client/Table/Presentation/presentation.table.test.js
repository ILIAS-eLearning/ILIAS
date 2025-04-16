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

import { beforeEach, describe, it } from 'node:test';
import { strict } from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import fs from 'node:fs';
import PresentationTableFactory from '../../../../resources/js/Table/src/presentationtable.factory.js';
import PresentationTable from '../../../../resources/js/Table/src/presentationtable.class.js';

describe('Presentation Table', () => {
  beforeEach(() => {
    const domString = fs.readFileSync('./components/ILIAS/UI/tests/Client/Table/Presentation/PresentationTest.html').toString();
    const dom = new JSDOM(domString);
    dom.window.document.getElementById = (id) => document.querySelector(`#${id}`);
    global.window = dom.window;
    global.document = dom.window.document;
  });

  it('classes exist', () => {
    strict.notEqual(PresentationTableFactory, undefined);
    strict.notEqual(PresentationTable, undefined);
  });

  it('factory has public methods', () => {
    const f = new PresentationTableFactory();
    strict.equal(f.init instanceof Function, true);
    strict.equal(f.get instanceof Function, true);
  });

  it('factors a PresentationTable', () => {
    const f = new PresentationTableFactory();
    f.init('il_ui_test_table_id');
    const pt = f.get('il_ui_test_table_id');

    strict.equal(pt instanceof PresentationTable, true);
    strict.equal(pt.expandRow instanceof Function, true);
    strict.equal(pt.collapseRow instanceof Function, true);
    strict.equal(pt.toggleRow instanceof Function, true);
    strict.equal(pt.expandAll instanceof Function, true);
  });
});
