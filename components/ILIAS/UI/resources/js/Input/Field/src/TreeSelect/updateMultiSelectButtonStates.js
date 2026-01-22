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

import * as CONSTANTS from './constants.js';

/**
 * Updates the TreeSelectNode.selectButton state in the following manner:
 * - if one or more nodes are selected, disable their descendant buttons and enable all others
 * - if no node is selected, enable all buttons
 *
 * @param {TreeSelect} treeSelectComponent
 */
export default function updateMultiSelectButtonStates(treeSelectComponent) {
  const nodeMap = treeSelectComponent.getNodes();
  const nodeSelectionSet = treeSelectComponent.getSelection();
  nodeMap.forEach((node) => {
    node.selectButton.disabled = false;
    node.selectButton.querySelector(CONSTANTS.GLYPH).classList.remove(CONSTANTS.DISABLED_CLASS);
  });
  nodeSelectionSet.forEach((nodeId) => {
    const node = nodeMap.get(nodeId);
    // ignore nodes which have not been loaded (yet) and leaf nodes
    if (node === null || node.listElement === null) {
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
