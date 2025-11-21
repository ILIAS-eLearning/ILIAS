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
});
