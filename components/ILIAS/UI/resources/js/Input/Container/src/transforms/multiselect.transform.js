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

export default class MultiSelectTransforms {
  /**
   * @param {FormNode} node
   * @return {Array}
   */
  valueTransform(node) {
    const checked = node.getHtmlFields().filter((element) => element.checked);
    if (checked.length === 0) {
      return [];
    }
    const representation = [];
    checked.forEach(
      (field) => representation.push(field.parentNode.textContent),
    );
    return representation;
  }
}
