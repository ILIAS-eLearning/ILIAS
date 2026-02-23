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
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

import { beforeEach, describe, it } from 'node:test';
import { strict } from 'node:assert/strict';
import TreeSelect from '../../../../resources/js/Input/Field/src/TreeSelect/TreeSelect.js';
import updateSingleSelectButtonStates
  from '../../../../resources/js/Input/Field/src/TreeSelect/updateSingleSelectButtonStates.js';
import engageParentDrilldownLevel
  from '../../../../resources/js/Input/Field/src/TreeSelect/engageParentDrilldownLevel.js';

describe('TreeSelect', () => {
  let jQueryEventListenerMock;
  let templateRendererMock;
  let asyncRendererMock;
  let languageMock;
  let drilldownMock;
  let basicElementMock;

  beforeEach(() => {
    jQueryEventListenerMock = {
      on: () => {},
    };
    templateRendererMock = {
      createContent: () => basicElementMock,
    };
    asyncRendererMock = {
      loadContent: () => basicElementMock,
    };
    languageMock = {
      txt: (s) => s,
    };
    drilldownMock = {
      getBackSignal: () => '',
      addEngageListener: () => {},
      getCurrentLevel: () => '0',
      getParentLevel: () => null,
    };
    basicElementMock = {
      remove: () => {},
      addEventListener: () => {},
      querySelectorAll: () => [],
      querySelector() {
        return this;
      },
    };
  });

  it('getSelection() returns a copy', () => {
    const component = new TreeSelect(
      new Map(),
      jQueryEventListenerMock,
      templateRendererMock,
      asyncRendererMock,
      languageMock,
      drilldownMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      () => {},
    );

    const actual = component.getSelection();
    actual.add('some-value');
    strict.notDeepEqual(actual, component.getSelection());
  });

  it('getNodes() returns a copy', () => {
    const emptyNodeMap = new Map();

    const component = new TreeSelect(
      emptyNodeMap,
      jQueryEventListenerMock,
      templateRendererMock,
      asyncRendererMock,
      languageMock,
      drilldownMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      () => {},
    );

    const actual = component.getNodes();
    actual.set('some-other-node-id', {});
    strict.notDeepEqual(actual, emptyNodeMap);
  });

  it('initial leaf nodes are hydrated', () => {
    const nodeMapMock = new Map();

    const selectButtonMock = {
      data: [],
      addEventListener(event, handler) {
        this.data.push({ event, handler });
      },
    };

    /** @type {TreeSelectNode} */
    const initialLeafNode = {
      id: 'node-id',
      name: 'node name',
      element: basicElementMock,
      selectButton: selectButtonMock,
      drilldownParentLevel: null,
      drilldownButton: null,
      listElement: null,
      renderUrl: null,
    };

    nodeMapMock.set(initialLeafNode.id, initialLeafNode);

    const component = new TreeSelect(
      nodeMapMock,
      jQueryEventListenerMock,
      templateRendererMock,
      asyncRendererMock,
      languageMock,
      drilldownMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      () => {},
    );

    strict.equal(selectButtonMock.data.length, 1);
    strict.equal(selectButtonMock.data[0].event, 'click');
    strict.equal((selectButtonMock.data[0].handler instanceof Function), true);
  });

  it('initial async and branch nodes are hydrated', () => {
    const nodeMapMock = new Map();

    const selectButtonMock = {
      data: [],
      addEventListener(event, handler) {
        this.data.push({ event, handler });
      },
    };

    /** @type {TreeSelectNode} */
    const initialBranchNode = {
      id: 'node-id',
      name: 'node name',
      element: basicElementMock,
      selectButton: selectButtonMock,
      drilldownParentLevel: '0',
      drilldownButton: basicElementMock,
      listElement: basicElementMock,
      renderUrl: null,
    };

    nodeMapMock.set(initialBranchNode.id, initialBranchNode);

    const component = new TreeSelect(
      nodeMapMock,
      jQueryEventListenerMock,
      templateRendererMock,
      asyncRendererMock,
      languageMock,
      drilldownMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      () => {},
    );

    strict.equal(selectButtonMock.data.length, 1);
    strict.equal(selectButtonMock.data[0].event, 'click');
    strict.equal((selectButtonMock.data[0].handler instanceof Function), true);
  });

  it('breadcrumbs are removed and rendered', () => {
    const newBreadcrumbElementMock = {
      id: 'some-html-id-to-verify',
      attributes: new Map(),
      textContent: '',
      get firstElementChild() {
        return this;
      },
      addEventListener: () => {},
      setAttribute(attribute, value) {
        this.attributes.set(attribute, value);
      },
      querySelector() {
        return this;
      },
    };
    const templateRendererMock = {
      createContent: () => newBreadcrumbElementMock,
    };
    const nodeElementMock = {
      isReturned: false,
      getAttribute: () => engagedBranchNode.id,
      closest() {
        if (this.isReturned) {
          return null;
        }
        this.isReturned = true;
        return this;
      },
    };
    /** @type {TreeSelectNode} */
    const engagedBranchNode = {
      id: 'engaged-node-id',
      name: 'engaged node name',
      element: nodeElementMock,
      selectButton: basicElementMock,
      drilldownParentLevel: '0',
      drilldownButton: basicElementMock,
      listElement: basicElementMock,
      renderUrl: null,
    };

    const drilldownMock = {
      engageListener: null,
      getBackSignal: () => '',
      addEngageListener(listener) {
        this.engageListener = listener;
      },
      getCurrentLevel: () => '0',
      getParentLevel: () => null,
    };
    const breadcrumbElementMock = {
      isRemoved: false,
      remove() {
        this.isRemoved = true;
      },
    };
    const breadcrumbsElementMock = {
      appended: [],
      querySelectorAll: () => [breadcrumbElementMock],
      append(element) {
        this.appended.push(element);
      },
    };
    const dialogElementMock = {
      querySelectorAll: () => [],
      querySelector() {
        return this;
      },
      closest() {
        return this;
      },
      getAttribute: () => engagedBranchNode.id,
    };

    const nodeMapMock = new Map();
    nodeMapMock.set(engagedBranchNode.id, engagedBranchNode);

    const component = new TreeSelect(
      nodeMapMock,
      jQueryEventListenerMock,
      templateRendererMock,
      asyncRendererMock,
      languageMock,
      drilldownMock,
      breadcrumbsElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      dialogElementMock,
      () => {},
    );

    // ensure drilldown engage listener is set
    strict.notEqual(drilldownMock.engageListener, null);
    drilldownMock.engageListener('value does not matter if we mocked everythig right =).');
    // ensure breadcrumbs are removed
    strict.equal(breadcrumbElementMock.isRemoved, true);
    // ensure breadcrumbs are rendered
    strict.deepEqual(breadcrumbsElementMock.appended, [newBreadcrumbElementMock]);
    strict.equal(newBreadcrumbElementMock.attributes.has('data-ddindex'), true);
    strict.equal(newBreadcrumbElementMock.attributes.get('data-ddindex'), engagedBranchNode.drilldownParentLevel);
    strict.equal(newBreadcrumbElementMock.textContent, engagedBranchNode.name);
  });

  it('can select and unselect nodes', () => {
    const elementWithClassListMock = {
      toggle: () => {},
      remove: () => {},
      add: () => {},
      get classList() {
        return this;
      },
    };
    const nodeElementMock = {
      get classList() {
        return elementWithClassListMock;
      },
    };
    const selectButtonMock = {
      querySelector: () => elementWithClassListMock,
      addEventListener: () => {},
      setAttribute: () => {},
    };
    /** @type {TreeSelectNode} */
    const initialLeafNode = {
      id: 'node-id',
      name: 'node name',
      element: nodeElementMock,
      selectButton: selectButtonMock,
      drilldownParentLevel: null,
      drilldownButton: null,
      listElement: null,
      renderUrl: null,
    };
    const nodeMapMock = new Map();
    nodeMapMock.set(initialLeafNode.id, initialLeafNode);

    const component = new TreeSelect(
      nodeMapMock,
      jQueryEventListenerMock,
      templateRendererMock,
      asyncRendererMock,
      languageMock,
      drilldownMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      (treeSelectComponent) => {
        updateSingleSelectButtonStates(treeSelectComponent);
        engageParentDrilldownLevel(treeSelectComponent);
      },
    );

    component.selectNode(initialLeafNode.id);
    strict.equal(component.getSelection().has(initialLeafNode.id), true);

    component.unselectNode(initialLeafNode.id);
    strict.equal(component.getSelection().has(initialLeafNode.id), false);
  });

  it('can update node select button states', () => {
    const elementWithClassListMock = {
      toggle: () => {},
      remove: () => {},
      add: () => {},
      get classList() {
        return this;
      },
    };
    /** @type {TreeSelectNode} */
    const leafNode1 = {
      id: 'node-id-1',
      name: 'node name',
      element: elementWithClassListMock,
      selectButton: {
        querySelector: () => elementWithClassListMock,
        addEventListener: () => {},
        setAttribute: () => {},
        disabled: false,
      },
      drilldownParentLevel: null,
      drilldownButton: null,
      listElement: null,
      renderUrl: null,
    };
    /** @type {TreeSelectNode} */
    const leafNode2 = {
      id: 'node-id-2',
      name: 'node name',
      element: elementWithClassListMock,
      selectButton: {
        querySelector: () => elementWithClassListMock,
        addEventListener: () => {},
        setAttribute: () => {},
        disabled: false,
      },
      drilldownParentLevel: null,
      drilldownButton: null,
      listElement: null,
      renderUrl: null,
    };
    const nodeMapMock = new Map();
    nodeMapMock.set(leafNode1.id, leafNode1);
    nodeMapMock.set(leafNode2.id, leafNode2);

    const component = new TreeSelect(
      nodeMapMock,
      jQueryEventListenerMock,
      templateRendererMock,
      asyncRendererMock,
      languageMock,
      drilldownMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      basicElementMock,
      (treeSelectComponent) => {
        updateSingleSelectButtonStates(treeSelectComponent);
        engageParentDrilldownLevel(treeSelectComponent);
      },
    );

    strict.equal(leafNode1.selectButton.disabled, false);
    strict.equal(leafNode2.selectButton.disabled, false);
    component.selectNode(leafNode1.id);
    strict.equal(leafNode1.selectButton.disabled, false);
    strict.equal(leafNode2.selectButton.disabled, true);
  });
});
