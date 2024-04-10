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

export default class FormNode {
  /**
   * @type {string}
   */
  #name;

  /**
   * @type {Array<string, FormNode>}
   */
  #nodes;

  /**
   * @type {HTMLElement[]}
   */
  #htmlFields;

  /**
   * @param {string} name
   * @return {void}
   */
  constructor(name) {
    this.#name = name;
    this.#nodes = [];
    this.#htmlFields = [];
  }

  /**
   * @return {string}
   */
  getName() {
    return this.#name;
  }

  /**
   * @param {FormNode} node
   * @return {void}
   */
  addNode(node) {
    this.#nodes[node.getName()] = node;
  }

  /**
   * @return {Array<string, FormNode>}
   */
  getNodes() {
    return this.#nodes;
  }

  /**
   * @return {string[]}
   */
  getNodeNames() {
    return Object.keys(this.#nodes);
  }

  /**
   * @return {FormNode}
   */
  getNodeByName(name) {
    return this.#nodes[name];
  }

  /**
   * @param {HTMLElement} htmlField
   * @return {void}
   */
  addHtmlField(htmlField) {
    this.#htmlFields.push(htmlField);
  }

  /**
   * @return {HTMLElement[]}
   */
  getHtmlFields() {
    return this.#htmlFields;
  }

  /**
   * @return {Array}
   */
  getValues() {
    const values = [];

    this.#htmlFields.forEach(
      (htmlField) => {
        if (htmlField.type === 'checkbox' || htmlField.type === 'radio') {
          if (htmlField.checked) {
            values.push(htmlField.value);
          }
        } else {
          values.push(htmlField.value);
        }
      },
    );

    return values;
  }
}
