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

import TreeSelect from './TreeSelect.js';
import * as CONSTANTS from './constants.js';

/**
 * @param {TreeSelectNode} child
 * @param {TreeSelectNode} parent
 * @returns {boolean}
 */
function isNodeChildOf(child, parent) {
  return parent.element.querySelector(`[${CONSTANTS.NODE_ID}="${child.id}"]`) !== null;
}

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class TreeMultiSelect extends TreeSelect {
  /** @type {boolean} */
  #canSelectChildNodes;

  /**
   * @param {Map<string, TreeSelectNode>} nodeMap (node-id => node)
   * @param {JQueryEventListener} jqueryEventListener
   * @param {TemplateRenderer} templateRenderer
   * @param {AsyncRenderer} asyncRenderer
   * @param {{txt: function(string): string}} language
   * @param {Drilldown} drilldownComponent
   * @param {HTMLElement} breadcrumbsElement
   * @param {HTMLTemplateElement} breadcrumbTemplate
   * @param {HTMLUListElement} nodeSelectionElement
   * @param {HTMLTemplateElement} nodeSelectionTemplate
   * @param {HTMLButtonElement} dialogSelectButton
   * @param {HTMLButtonElement} dialogOpenButton
   * @param {HTMLDialogElement} dialogElement
   * @param {boolean} canSelectChildNodes
   */
  constructor(
    nodeMap,
    jqueryEventListener,
    templateRenderer,
    asyncRenderer,
    language,
    drilldownComponent,
    breadcrumbsElement,
    breadcrumbTemplate,
    nodeSelectionElement,
    nodeSelectionTemplate,
    dialogSelectButton,
    dialogOpenButton,
    dialogElement,
    canSelectChildNodes,
  ) {
    super(
      nodeMap,
      jqueryEventListener,
      templateRenderer,
      asyncRenderer,
      language,
      drilldownComponent,
      breadcrumbsElement,
      breadcrumbTemplate,
      nodeSelectionElement,
      nodeSelectionTemplate,
      dialogSelectButton,
      dialogOpenButton,
      dialogElement,
    );

    this.#canSelectChildNodes = canSelectChildNodes;
  }

  /**
   * @inheritDoc
   */
  selectNode(nodeId) {
    if (!this.#canSelectChildNodes) {
      // provide selection with new node-id already
      const newSelection = Array.from(this.getSelection().add(nodeId));
      this.#unselectChildNodes(newSelection, this.getNodes());
    }
    super.selectNode(nodeId);
  }

  /**
   * Updates the TreeSelectNode.selectButton state in the following manner:
   * - if one or more node is selected, disable their descendant buttons and enable all others
   * - if no node is selected, enable all buttons
   */
  updateNodeSelectButtonStates() {
    if (this.#canSelectChildNodes) {
      return;
    }
    const nodeMap = this.getNodes();
    nodeMap.forEach((node) => {
      node.selectButton.disabled = false;
      node.selectButton.querySelector(CONSTANTS.GLYPH).classList.remove(CONSTANTS.DISABLED_CLASS);
    });
    this.getSelection().forEach((nodeId) => {
      const node = nodeMap.get(nodeId);
      // ignore nodes which have not been loaded (yet) and leaf nodes
      if (null === node || node.listElement === null) {
        return;
      }
      // disable all descending select buttons
      node.listElement
        .querySelectorAll(CONSTANTS.NODE_SELECT_BUTTON)
        .forEach((button) => {
          button.disabled = true;
          button.querySelector(CONSTANTS.GLYPH).classList.add(CONSTANTS.DISABLED_CLASS);
        });
    });
  }

  /**
   * Calls unselectNode() for each node-id which is selected in a nested manner (parent-child).
   *
   * @param {string[]} selection (node-ids)
   * @param {Map<string, TreeSelectNode>} nodeMap (node-id => node)
   */
  #unselectChildNodes(selection, nodeMap) {
    for (let childIndex = 0; childIndex < selection.length; childIndex += 1) {
      for (let parentIndex = 0; parentIndex < selection.length; parentIndex += 1) {
        const parentNodeId = selection[parentIndex];
        const childNodeId = selection[childIndex];
        // skip same index or if one of both node-ids does not (yet) exist.
        if (childIndex === parentIndex || !nodeMap.has(childNodeId) || !nodeMap.has(parentNodeId)) {
          continue;
        }
        if (isNodeChildOf(nodeMap.get(childNodeId), nodeMap.get(parentNodeId))) {
          this.unselectNode(childNodeId);
        }
      }
    }
  }
}
