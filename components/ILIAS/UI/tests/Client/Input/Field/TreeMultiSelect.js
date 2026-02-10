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

import {
  beforeEach, describe, it, mock,
} from 'node:test';
import { strict } from 'node:assert/strict';
import TreeSelect from '../../../../resources/js/Input/Field/src/TreeSelect/TreeSelect.js';
import unselectChildNodes
  from '../../../../resources/js/Input/Field/src/TreeSelect/unselectChildNodes.js';
import updateMultiSelectButtonStates
  from '../../../../resources/js/Input/Field/src/TreeSelect/updateMultiSelectButtonStates.js';

describe('TreeMultiSelect', () => {
  let jQueryEventListenerMock;
  let templateRendererMock;
  let asyncRendererMock;
  let languageMock;
  let drilldownMock;
  let basicElementMock;
  let elementWithClassListMock;
  let basicLeafNodeMock;

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
    elementWithClassListMock = {
      toggle: () => {},
      remove: () => {},
      add: () => {},
      get classList() {
        return this;
      },
    };
    basicLeafNodeMock = {
      id: 'node-id-1',
      name: 'node name 1',
      element: {
        __proto__: elementWithClassListMock,
        // signals there is a child node
        querySelector: () => basicElementMock,
      },
      selectButton: {
        querySelector: () => elementWithClassListMock,
        addEventListener: () => {},
        setAttribute: () => {},
        disabled: false,
      },
      drilldownParentLevel: '0',
      drilldownButton: null,
      listElement: null,
      renderUrl: null,
    };
  });

  it('updates select button states and selection (cannot select child nodes)', () => {
    const parentBranchNode = {
      id: 'node-id-1',
      name: 'node name 1',
      element: {
        __proto__: elementWithClassListMock,
        // signals there is a child node
        querySelector: () => basicElementMock,
      },
      selectButton: {
        querySelector: () => elementWithClassListMock,
        addEventListener: () => {},
        setAttribute: () => {},
        disabled: false,
      },
      drilldownParentLevel: '0',
      drilldownButton: basicElementMock,
      listElement: {
        querySelectorAll: () => [
          childLeafNode.selectButton,
        ],
      },
      renderUrl: null,
    };
    const childLeafNode = {
      id: 'node-id-2',
      name: 'node name 2',
      element: {
        __proto__: elementWithClassListMock,
        // signals there is no child node
        querySelector: () => null,
      },
      selectButton: {
        querySelector: () => elementWithClassListMock,
        addEventListener: () => {},
        setAttribute: () => {},
        disabled: false,
      },
      drilldownParentLevel: '1',
      drilldownButton: null,
      listElement: null,
      renderUrl: null,
    };
    const nodeMapMock = new Map();
    nodeMapMock.set(parentBranchNode.id, parentBranchNode);
    nodeMapMock.set(childLeafNode.id, childLeafNode);

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
        unselectChildNodes(treeSelectComponent);
        updateMultiSelectButtonStates(treeSelectComponent);
      },
      null,
      null,
      null,
      null,
    );

    strict.equal(parentBranchNode.selectButton.disabled, false);
    strict.equal(childLeafNode.selectButton.disabled, false);
    // ensure child nodes are disabled
    component.selectNode(parentBranchNode.id);
    strict.equal(parentBranchNode.selectButton.disabled, false);
    strict.equal(childLeafNode.selectButton.disabled, true);
    // ensure parent nodes are not disabled
    component.unselectNode(parentBranchNode.id);
    component.selectNode(childLeafNode.id);
    strict.equal(parentBranchNode.selectButton.disabled, false);
    strict.equal(childLeafNode.selectButton.disabled, false);
    // ensure child nodes are unselected and disabled
    component.selectNode(parentBranchNode.id);
    strict.equal(component.getSelection().has(childLeafNode.id), false);
    strict.equal(parentBranchNode.selectButton.disabled, false);
    strict.equal(childLeafNode.selectButton.disabled, true);
  });

  it('updates select button states (can select child nodes)', () => {
    const parentBranchNode = {
      id: 'node-id-1',
      name: 'node name 1',
      element: {
        __proto__: elementWithClassListMock,
        // signals there is a child node
        querySelector: () => basicElementMock,
      },
      selectButton: {
        querySelector: () => elementWithClassListMock,
        addEventListener: () => {},
        setAttribute: () => {},
        disabled: false,
      },
      drilldownParentLevel: '0',
      drilldownButton: basicElementMock,
      listElement: {
        querySelectorAll: () => [
          childLeafNode.selectButton,
        ],
      },
      renderUrl: null,
    };
    const childLeafNode = {
      id: 'node-id-2',
      name: 'node name 2',
      element: {
        __proto__: elementWithClassListMock,
        // signals there is no child node
        querySelector: () => null,
      },
      selectButton: {
        querySelector: () => elementWithClassListMock,
        addEventListener: () => {},
        setAttribute: () => {},
        disabled: false,
      },
      drilldownParentLevel: '1',
      drilldownButton: null,
      listElement: null,
      renderUrl: null,
    };
    const nodeMapMock = new Map();
    nodeMapMock.set(parentBranchNode.id, parentBranchNode);
    nodeMapMock.set(childLeafNode.id, childLeafNode);

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
      null,
      null,
      null,
      null,
    );

    strict.equal(parentBranchNode.selectButton.disabled, false);
    strict.equal(childLeafNode.selectButton.disabled, false);
    // ensure child nodes are not disabled
    component.selectNode(parentBranchNode.id);
    strict.equal(parentBranchNode.selectButton.disabled, false);
    strict.equal(childLeafNode.selectButton.disabled, false);
    // ensure parent nodes are not disabled
    component.unselectNode(parentBranchNode.id);
    component.selectNode(childLeafNode.id);
    strict.equal(parentBranchNode.selectButton.disabled, false);
    strict.equal(childLeafNode.selectButton.disabled, false);
    // ensure nothing gets unselected or disabled
    component.selectNode(parentBranchNode.id);
    strict.equal(component.getSelection().has(childLeafNode.id), true);
    strict.equal(parentBranchNode.selectButton.disabled, false);
    strict.equal(childLeafNode.selectButton.disabled, false);
  });

  it('dropdown menu entries are removed and rendered', () => {
    // indicates if there is an entry for some node
    const nodeDropdownMenuEntries = [];
    const nodeDropdownMenuElementMock = {
      append: mock.fn((element) => nodeDropdownMenuEntries.push(element)),
      querySelectorAll: () => nodeDropdownMenuEntries,
      querySelector: () => nodeDropdownMenuEntries[0] ?? null,
    };
    const nodeDropdownMenuEntryAndTemplateMock = {
      __proto__: Object.assign(
        Object.create(basicElementMock),
        elementWithClassListMock,
      ),
      remove: mock.fn(() => nodeDropdownMenuEntries.pop()),
      setAttribute: mock.fn(() => {}),
      textContent: '',
      children: [this],
      addEventListener: () => {},
      querySelector() {
        return this;
      },
    };
    const nodeDropdownMenuEntryPlaceholderMock = {
      classList: {
        remove: mock.fn(() => {}),
        add: mock.fn(() => {}),
      },
    };
    templateRendererMock = {
      createContent: () => nodeDropdownMenuEntryAndTemplateMock,
    };

    const nodeMapMock = new Map();
    nodeMapMock.set(basicLeafNodeMock.id, basicLeafNodeMock);

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
      nodeDropdownMenuElementMock,
      null,
      nodeDropdownMenuEntryAndTemplateMock,
      nodeDropdownMenuEntryPlaceholderMock,
    );

    strict.equal(nodeDropdownMenuEntryPlaceholderMock.classList.remove.mock.callCount(), 0);
    strict.equal(nodeDropdownMenuEntryPlaceholderMock.classList.add.mock.callCount(), 0);
    strict.equal(nodeDropdownMenuEntryAndTemplateMock.remove.mock.callCount(), 0);
    strict.equal(nodeDropdownMenuElementMock.append.mock.callCount(), 0);
    // ensure dropdown entry is rendered
    component.selectNode(basicLeafNodeMock.id);
    strict.equal(nodeDropdownMenuEntryPlaceholderMock.classList.remove.mock.callCount(), 0);
    strict.equal(nodeDropdownMenuEntryPlaceholderMock.classList.add.mock.callCount(), 1);
    strict.equal(nodeDropdownMenuEntryAndTemplateMock.remove.mock.callCount(), 0);
    strict.equal(nodeDropdownMenuElementMock.append.mock.callCount(), 1);
    // ensure aria labels and node data is added
    strict.equal(nodeDropdownMenuEntryAndTemplateMock.textContent, basicLeafNodeMock.name);
    strict.equal(nodeDropdownMenuEntryAndTemplateMock.setAttribute.mock.callCount(), 3);
    strict.equal(nodeDropdownMenuEntryAndTemplateMock.setAttribute.mock.calls[2].arguments[0], 'aria-label');
    strict.equal(nodeDropdownMenuEntryAndTemplateMock.setAttribute.mock.calls[1].arguments[0], 'aria-label');
    strict.equal(
      nodeDropdownMenuEntryAndTemplateMock.setAttribute.mock.calls[0].arguments[1],
      basicLeafNodeMock.id,
    );
    // ensure consecutive calls are handled
    component.selectNode(basicLeafNodeMock.id);
    strict.equal(nodeDropdownMenuEntryPlaceholderMock.classList.remove.mock.callCount(), 0);
    strict.equal(nodeDropdownMenuEntryPlaceholderMock.classList.add.mock.callCount(), 2);
    strict.equal(nodeDropdownMenuEntryAndTemplateMock.remove.mock.callCount(), 0);
    strict.equal(nodeDropdownMenuElementMock.append.mock.callCount(), 1);
    // ensure dropdown entry is removed
    component.unselectNode(basicLeafNodeMock.id);
    strict.equal(nodeDropdownMenuEntryPlaceholderMock.classList.remove.mock.callCount(), 1);
    strict.equal(nodeDropdownMenuEntryPlaceholderMock.classList.add.mock.callCount(), 2);
    strict.equal(nodeDropdownMenuEntryAndTemplateMock.remove.mock.callCount(), 1);
    strict.equal(nodeDropdownMenuElementMock.append.mock.callCount(), 1);
  });

  it('updates the counter component state', () => {
    const nodeDropdownMenuElementMock = {
      querySelectorAll: () => [],
      querySelector: () => {},
    };
    const nodeDropdownCounterMock = {
      setStatusTo: mock.fn(() => {}),
    };

    const nodeMapMock = new Map();
    nodeMapMock.set(basicLeafNodeMock.id, basicLeafNodeMock);

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
      nodeDropdownMenuElementMock,
      nodeDropdownCounterMock,
      null,
      null,
    );

    strict.equal(nodeDropdownCounterMock.setStatusTo.mock.callCount(), 0);
    // ensure counter component is updated during select
    component.selectNode(basicLeafNodeMock.id);
    strict.equal(nodeDropdownCounterMock.setStatusTo.mock.callCount(), 1);
    strict.equal(nodeDropdownCounterMock.setStatusTo.mock.calls[0].arguments[0], 1);
    // ensure consecutive calls are handled
    component.selectNode(basicLeafNodeMock.id);
    strict.equal(nodeDropdownCounterMock.setStatusTo.mock.callCount(), 2);
    strict.equal(nodeDropdownCounterMock.setStatusTo.mock.calls[1].arguments[0], 1);
    // ensure counter component is updated during unselect
    component.unselectNode(basicLeafNodeMock.id);
    strict.equal(nodeDropdownCounterMock.setStatusTo.mock.callCount(), 3);
    strict.equal(nodeDropdownCounterMock.setStatusTo.mock.calls[2].arguments[0], 0);
  });
});
