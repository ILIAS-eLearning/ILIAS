import { expect } from 'chai';
import data from '../src/table.data.js';
import params from '../src/params.js';

describe('table.data', function() {
    it('is defined', function() {
        expect(data).to.not.be.undefined;
    });
    it('has public action-methods', function() {
        var d = new data();
        expect(d.registerAction).to.be.an('function');
        expect(d.doAction).to.be.an('function');
    });


});

describe('params', function() {
    it('is defined', function() {
        expect(params).to.not.be.undefined;
    });

    var p = new params();

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
