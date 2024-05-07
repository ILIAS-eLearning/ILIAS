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
import { JSDOM } from 'jsdom';

import DataTableFactory from '../../../../../src/UI/templates/js/Table/src/datatable.factory';
import DataTable from '../../../../../src/UI/templates/js/Table/src/datatable.class';

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
    /* eslint  no-unused-expressions:0 */
    expect(DataTableFactory).to.not.be.undefined;
    expect(DataTable).to.not.be.undefined;
  });
  it('factory has public methods', () => {
    const f = new DataTableFactory();
    expect(f.init).to.be.an('function');
    expect(f.get).to.be.an('function');
  });
  it('factors a DataTable', () => {
    const f = new DataTableFactory({});
    f.init('tid', 'actId', 'rowId');
    const dt = f.get('tid');
    expect(dt.registerAction).to.be.an('function');
    expect(dt.selectAll).to.be.an('function');
    expect(dt.doSingleAction).to.be.an('function');
    expect(dt.doMultiAction).to.be.an('function');
    expect(dt.doActionForAll).to.be.an('function');
    expect(dt.doAction).to.be.an('function');
  });
});
