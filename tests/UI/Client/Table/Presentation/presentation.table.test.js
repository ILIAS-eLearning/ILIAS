import { expect } from 'chai';
import { JSDOM } from 'jsdom';
import fs from 'fs';
import PresentationTableFactory from '../../../../../src/UI/templates/js/Table/src/presentationtable.factory';
import PresentationTable from '../../../../../src/UI/templates/js/Table/src/presentationtable.class';

describe('Presentation Table', () => {
  beforeEach(() => {
    const domString = fs.readFileSync('./tests/UI/Client/Table/Presentation/PresentationTest.html').toString();
    const dom = new JSDOM(domString);
    /* eslint-env jquery */
    dom.window.document.getElementById = (id) => $(`#${id}`)[0];
    global.window = dom.window;
    global.document = dom.window.document;
  });

  it('classes exist', () => {
    /* eslint-disable no-unused-expressions */
    expect(PresentationTableFactory).to.not.be.undefined;
    expect(PresentationTable).to.not.be.undefined;
  });

  it('factory has public methods', () => {
    const f = new PresentationTableFactory();
    expect(f.init).to.be.an('function');
    expect(f.get).to.be.an('function');
  });

  it('factors a PresentationTable', () => {
    const f = new PresentationTableFactory();
    f.init('il_ui_test_table_id');
    const pt = f.get('il_ui_test_table_id');

    expect(pt instanceof PresentationTable);
    expect(pt.expandRow).to.be.an('function');
    expect(pt.collapseRow).to.be.an('function');
    expect(pt.toggleRow).to.be.an('function');
    expect(pt.expandAll).to.be.an('function');
  });
});
