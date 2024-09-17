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

import { expect } from 'chai';
import { JSDOM } from 'jsdom';

import ContainerFactory from '../../../../resources/js/Input/Container/src/container.factory.js';
import Container from '../../../../resources/js/Input/Container/src/container.class.js';
import FormNode from '../../../../resources/js/Input/Container/src/formnode.class.js';
import SwitchableGroupTransforms from '../../../../resources/js/Input/Container/src/transforms/switchablegroup.transform.js';

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

describe('Input\\Container components are there', () => {
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

/*
*/
describe('Input\\Container', () => {
  before(async () => {
    const transforms = {};
    global.doc = await loadMockedDom('containertest_simple.html');
    global.containerSimple = new Container(transforms, global.doc.querySelector('#test_container_id'));
  });

  it('is built and provides a FormNode', () => {
    expect(global.containerSimple).to.be.an.instanceOf(Container);
    expect(global.containerSimple.getNodes()).to.be.an.instanceOf(FormNode);
  });

  it('provides a list of FormNodes and values', () => {
    const expected = [
      {
        label: 'test_container_id',
        value: [],
        indent: 0,
        type: 'form',
      },
      {
        label: 'Item 1',
        value: ['value 0'],
        indent: 1,
        type: 'text-field-input',
      },
      {
        label: 'Item 2',
        value: ['value 1'],
        indent: 1,
        type: 'text-field-input',
      },
      {
        label: 'Item 3',
        value: [''],
        indent: 1,
        type: 'text-field-input',
      },
    ];

    expect(global.containerSimple.getValuesRepresentation()).to.eql(expected);
  });

  it('finds FormNodes by name; FormNodes have name, label and value', async () => {
    const doc = await loadMockedDom('containertest_switchablegroup.html');
    const transforms = {};
    const containerSwitchableGroup = new Container(transforms, doc.querySelector('#test_container_id'));
    const expected = [
      ['form/input_0', 'Pick One', []],
      ['form/input_0/input_1', 'Switchable Group number one (with numeric key)', ['1']],
      ['form/input_0/input_1/input_2', 'Item 1.1', ['']],
      ['form/input_0/input_1/input_3', 'Item 1.2', ['val 1.2']],
      ['form/input_0/input_1/input_4', 'Item 1.3', ['2024-09-26']],
      ['form/input_0/input_5', 'Switchable Group number two', []],
      ['form/input_0/input_5/input_6', 'Item 2', ['this should not appear']],
    ];

    expected.forEach(
      (n) => {
        const [name, label, values] = n;
        const node = containerSwitchableGroup.getNodeByName(name);
        expect(node.getFullName()).to.eql(name);
        expect(node.getLabel()).to.eql(label);
        expect(node.getValues()).to.eql(values);
      },
    );
  });

  it('filters switchable groups', async () => {
    const doc = await loadMockedDom('containertest_switchablegroup.html');
    const transforms = {};
    transforms['switchable-group-field-input'] = new SwitchableGroupTransforms();

    const containerSwitchableGroup = new Container(transforms, doc.querySelector('#test_container_id'));

    let expected = [
      {
        label: 'test_container_id', value: [], indent: 0, type: 'form',
      },
      {
        label: 'Pick One',
        value: ['Switchable Group number one (with numeric key)'],
        indent: 1,
        type: 'switchable-group-field-input',
      },
      {
        label: 'Switchable Group number one (with numeric key)',
        value: ['1'],
        indent: 2,
        type: 'group-field-input',
      },
      {
        label: 'Item 1.1',
        value: [''],
        indent: 3,
        type: 'text-field-input',
      },
      {
        label: 'Item 1.2',
        value: ['val 1.2'],
        indent: 3,
        type: 'text-field-input',
      },
      {
        label: 'Item 1.3',
        value: ['2024-09-26'],
        indent: 3,
        type: 'date-time-field-input',
      },
    ];
    expect(containerSwitchableGroup.getValuesRepresentation()).to.eql(expected);

    doc.getElementsByName('form/input_0')[1].checked = 'checked';

    expected = [
      {
        label: 'test_container_id', value: [], indent: 0, type: 'form',
      },
      {
        label: 'Pick One',
        value: ['Switchable Group number two'],
        indent: 1,
        type: 'switchable-group-field-input',
      },
      {
        label: 'Switchable Group number two',
        value: ['g2'],
        indent: 2,
        type: 'group-field-input',
      },
      {
        label: 'Item 2',
        value: ['this should not appear'],
        indent: 3,
        type: 'text-field-input',
      },
    ];
    expect(containerSwitchableGroup.getValuesRepresentation()).to.eql(expected);
  });
});
