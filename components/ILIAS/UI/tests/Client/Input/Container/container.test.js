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

import { assert, expect } from 'chai';
import { JSDOM } from 'jsdom';

import ContainerFactory from '../../../../src/templates/js/Input/Container/src/container.factory.js';
import Container from '../../../../src/templates/js/Input/Container/src/container.class.js';
import FormNode from '../../../../src/templates/js/Input/Container/src/formnode.class.js';

/**
 * get HTML document from file
 *
 * @return JSDOM
 */
function loadMockedDom(file) {
  const path = 'components/ILIAS/UI/tests/Client/Input/Container/';
  const doc = JSDOM.fromFile(path + file, { contentType: 'text/html', resources: 'usable' })
    .then((dom) => dom.window.document);
  return doc;
}

describe('Container components are there', () => {
  it('ContainerFactory', () => {
    expect(ContainerFactory).to.not.be.undefined;
  });
  it('Container', () => {
    expect(Container).to.not.be.undefined;
  });
  it('FormNode', () => {
    expect(FormNode).to.not.be.undefined;
  });
});

describe('Container', () => {
  before(async () => {
    global.doc = await loadMockedDom('containertest_simple.html');
    global.containerSimple = new Container(doc.querySelector('#test_container_id'));
  });

  it('is build and provides a FormNode', () => {
    expect(containerSimple).to.be.an.instanceOf(Container);
    expect(containerSimple.node()).to.be.an.instanceOf(FormNode);
  });

  it('provides a flat list of FormNodes and values', () => {
    const expected = {
      form: [],
      'form/input_0': ['value_1'],
      'form/input_1': ['value_2'],
      'form/input_2': ['value_3'],
    };
    expect(Object.keys(containerSimple.getValuesFlat())).to.eql(Object.keys(expected));
    expect(Object.values(containerSimple.getValuesFlat())).to.eql(Object.values(expected));
  });

  it('provides a tree of FormNodes and values', async () => {
    const values = containerSimple.getValues();
    expect(values.form.input_0).to.eql(['value_1']);
    expect(values.form.input_1).to.eql(['value_2']);
    expect(values.form.input_2).to.eql(['value_3']);
  });
  it('...also in complex forms', async () => {
    const doc = await loadMockedDom('containertest_switchablegroup.html');
    const containerSwitchableGroup = new Container(doc.querySelector('#test_container_id'));
    const values = containerSwitchableGroup.getValues();
    expect(values.form.input_0.input_1.input_2).to.eql(['value_1.1']);
    expect(values.form.input_0.input_1.input_3).to.eql(['value_1.2']);
    // expect(values.form.input_1).to.eql(['value_2']);
    // expect(values.form.input_2).to.eql(['value_3']);
  });

  it('filters switchable groups', async () => {
    const doc = await loadMockedDom('containertest_switchablegroup.html');
    const containerSwitchableGroup = new Container(doc.querySelector('#test_container_id'));
    const expected = {
      form: [],
      'form/input_0': ['1'],
      'form/input_0/input_1': [],
      'form/input_0/input_1/input_2': ['value_1.1'],
      'form/input_0/input_1/input_3': ['value_1.2'],
      // 'form/input_0/input_4' : [],
      // 'form/input_0/input_4/input_5' : ['value_2.1'],
    };
    expect(Object.keys(containerSwitchableGroup.getValuesFlat())).to.eql(Object.keys(expected));
    expect(Object.values(containerSwitchableGroup.getValuesFlat())).to.eql(Object.values(expected));

    doc.getElementsByName('form/input_0')[1].checked = 'checked';
    const containerSwitchableGroup2 = new Container(doc.querySelector('#test_container_id'));

    const expected2 = {
      form: [],
      'form/input_0': ['2'],
      // 'form/input_0/input_1': [],
      // 'form/input_0/input_1/input_2': ['value_1.1'],
      // 'form/input_0/input_1/input_3': ['value_1.2'],
      'form/input_0/input_4': [],
      'form/input_0/input_4/input_5': ['value_2.1'],
    };
    expect(Object.keys(containerSwitchableGroup2.getValuesFlat())).to.eql(Object.keys(expected2));
    expect(Object.values(containerSwitchableGroup2.getValuesFlat())).to.eql(Object.values(expected2));
  });
});
