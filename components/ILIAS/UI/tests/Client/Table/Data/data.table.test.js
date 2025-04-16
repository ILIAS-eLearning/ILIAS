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

import { beforeEach, describe, it } from 'node:test';
import { strict } from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import DataTableFactory from '../../../../resources/js/Table/src/datatable.factory.js';
import DataTable from '../../../../resources/js/Table/src/datatable.class.js';

function initMockedDom() {
  const dom = new JSDOM(
    ` 
<div class="c-table-data" id="tid">
    <h3 class="ilHeader" id="il_ui_fw_646dc93cd340d5_15676162_label">a data table</h3>
    <div class="viewcontrols"></div>
    <table class="c-table-data__table" role="grid" aria-labelledby="il_ui_fw_646dc93cd340d5_15676162_label" aria-colcount="9">
        <thead>
            <tr class="c-table-data__header c-table-data__row" role="rowgroup">
                <th class="c-table-data__header c-table-data__cell c-table-data__cell--number" role="columnheader" tabindex="-1" aria-colindex="0">
                    <div class="c-table-data__header__resize-wrapper">
                        User ID
                    </div>
                </th>
            </tr>
        </thead>

        <tbody class="c-table-data__body" role="rowgroup">
            <tr class="c-table-data__row odd" role="row">
                <td class="c-table-data__cell c-table-data__cell--number " role="gridcell" aria-colindex="0" tabindex="-1">
                    867
                </td>
            </tr>
            <tr class="c-table-data__row even" role="row">
                <td class="c-table-data__cell c-table-data__cell--number " role="gridcell" aria-colindex="0" tabindex="-1">
                    8923
                </td>
            </tr>
        </tbody>
    </table>

    <div class="c-table-data__async_modal_container"></div>

    <div class="c-table-data__async_message modal" role="dialog" id="{ID}_msgmodal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="c-table-data__async_messageresponse modal-body"></div>
            </div>
        </div>
    </div>
</div>
        `,
    {
      url: 'https://localhost',
    },
  );

  global.window = dom.window;
  global.document = dom.window.document;
}

describe('Data Table', () => {
  beforeEach(initMockedDom);

  it('classes exist', () => {
    strict.notEqual(DataTableFactory, undefined);
    strict.notEqual(DataTable, undefined);
  });
  it('factory has public methods', () => {
    const f = new DataTableFactory();
    strict.equal(f.init instanceof Function, true);
    strict.equal(f.get instanceof Function, true);
  });
  it('factors a DataTable', () => {
    const f = new DataTableFactory({});
    f.init('tid', 'actId', 'rowId');
    const dt = f.get('tid');
    strict.equal(dt.registerAction instanceof Function, true);
    strict.equal(dt.selectAll instanceof Function, true);
    strict.equal(dt.doSingleAction instanceof Function, true);
    strict.equal(dt.doMultiAction instanceof Function, true);
    strict.equal(dt.doActionForAll instanceof Function, true);
    strict.equal(dt.doAction instanceof Function, true);
  });
});
