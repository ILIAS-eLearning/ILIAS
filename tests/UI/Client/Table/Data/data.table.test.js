import { expect } from 'chai';
import {JSDOM} from "jsdom";

import DataTableFactory from '../../../../../src/UI/templates/js/Table/src/datatable.factory';
import DataTable from '../../../../../src/UI/templates/js/Table/src/datatable.class';
import Params from '../../../../../src/UI/templates/js/Table/src/Params';

function initMockedDom() {
    let dom = new JSDOM(
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
</div>
        `,
        {
            url: 'https://localhost',
        }
    );

    global.window = dom.window;
    global.document = dom.window.document;
}

describe('Data Table', function() {

    beforeEach(initMockedDom);

    it('classes exist', function() {
        expect(DataTableFactory).to.not.be.undefined;
        expect(DataTable).to.not.be.undefined;
        expect(Params).to.not.be.undefined;
    });
    it('factory has public methods', function() {
        const f = new DataTableFactory();
        expect(f.init).to.be.an('function');
        expect(f.get).to.be.an('function');
    });
    it('factors a DataTable', function() {
        const f = new DataTableFactory({}, new Params());
        f.init('tid', 'typeURL', 'typeSignal', 'optOptions', 'optId');
        const dt = f.get('tid');
        expect(dt.registerAction).to.be.an('function');
        expect(dt.selectAll).to.be.an('function');
        expect(dt.doMultiAction).to.be.an('function');
        expect(dt.doActionForAll).to.be.an('function');
        expect(dt.doAction).to.be.an('function');
    });
});

describe('Params', function() {
    it('is defined', function() {
        expect(Params).to.not.be.undefined;
    });

    var p = new Params();

    it('amends parameters to a signal', function() {
        var sig = JSON.stringify({
            "id": "some id",
            "options": {
                "o1": "v1"
            }
        });
        sig = p.amendParameterToSignal(sig, 'par', 'val');
        expect(sig.options).to.eql({
            "o1": "v1",
            "par" : "val"
        });
    });

    it('amends parameters to an url', function() {
        var url = 'https://www.ilias.de/x.php?target=cat_123&node=1:2',
            id = 'row_ids',
            values = ['row-1', 'row-2'],
            expected = encodeURI(JSON.stringify(values));

        expect(
            p.amendParameterToUrl(url, id, values)
        ).to.eql(
            url + '&' + id + '=' + expected
        );
    });

    it('retrieves params from an url', function() {
        var url = 'https://www.ilias.de/x.php?target=cat_123&node=1:2';
        expect(p.getParametersFromUrl(url))
        .to.eql({
            "node": "1:2",
            "target": "cat_123"
        });
    });

});
