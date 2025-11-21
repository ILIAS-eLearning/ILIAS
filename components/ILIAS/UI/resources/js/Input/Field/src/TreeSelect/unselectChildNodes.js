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
 * @param {TreeSelectNode} child
 * @param {TreeSelectNode} parent
 * @returns {boolean}
 */
function isNodeChildOf(child, parent) {
  return parent.element.querySelector(`[${CONSTANTS.NODE_ID}="${child.id}"]`) !== null;
}

/**
 * Calls unselectNode() for each node-id which is selected in a nested manner (parent-child).
 *
 * @param {TreeSelect} treeSelectComponent
 */
export default function unselectChildNodes(treeSelectComponent) {
  const selection = Array.from(treeSelectComponent.getSelection());
  const nodeMap = treeSelectComponent.getNodes();
  for (let childIndex = 0; childIndex < selection.length; childIndex += 1) {
    for (let parentIndex = 0; parentIndex < selection.length; parentIndex += 1) {
      const parentNodeId = selection[parentIndex];
      const childNodeId = selection[childIndex];
      // ignore same index and if one of both node-ids does not (yet) exist.
      if (childIndex !== parentIndex
        && nodeMap.has(childNodeId)
        && nodeMap.has(parentNodeId)
        && isNodeChildOf(nodeMap.get(childNodeId), nodeMap.get(parentNodeId))
      ) {
        treeSelectComponent.unselectNode(childNodeId);
      }
    }
  }
}
