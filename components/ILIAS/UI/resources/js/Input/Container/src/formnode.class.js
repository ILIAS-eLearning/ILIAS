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
  #fields;

  /**
   * @param {string} name
   * @return {void}
   */
  constructor(name) {
    this.#name = name;
    this.#nodes = [];
    this.#fields = [];
  }

  /**
   * @param {FormNode} node
   * @return {void}
   */
  addNode(node) {
    this.#nodes[node.getName()] = node;
  }

  /**
   * @param {HTMLElement} field
   * @return {void}
   */
  addField(field) {
    this.#fields.push(field);
  }

  /**
   * @return {string}
   */
  getName() {
    return this.#name;
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
   * @return {HTMLElement[]}
   */
  getFields() {
    return this.#fields;
  }

  /**
   * @return {Array}
   */
  getValues() {
    const values = [];

    this.#fields.forEach(
      (field) => {
        if (field.type === 'checkbox' || field.type === 'radio') {
          if (field.checked) {
            values.push(field.value);
          }
        } else {
          values.push(field.value);
        }
      },
    );

    return values;
  }

  /**
   * @return {FormNode[]}
   */
  #filteredSubnodes() {
    let subnodes = this.getNodeNames();

    // optional groups:
    if (this.getFields().length > 0 && this.getValues().length === 0) {
      subnodes = []; // or, equally: return values;
    }
    // switchable groups
    if (this.getFields().length > 0
      && this.getFields().filter((f) => f.type === 'radio').length === this.getFields().length
    ) {
      subnodes = [];
      const index = this.getFields().findIndex((f) => f.value === this.getValues().shift());
      if (this.getNodeNames().length > index && index > -1) {
        subnodes = [this.getNodeNames()[index]];
      }
    }
    return subnodes;
  }

  /**
   * @param {Array} [initValues]
   * @return {Array<string, Array>}
   */
  getValuesRecursively(initValues) {
    const values = initValues || [];

    values[this.getName()] = this.getValues();

    const subnodes = this.#filteredSubnodes();
    subnodes.forEach(
      (n) => this.getNodeByName(n).getValuesRecursively(values[this.getName()]),
    );
    return values;
  }

  /**
   * @param {Array} [initValues]
   * @param {string} [initName]
   * @return {Array<string, Array>}
   */
  getValuesFlat(initValues, initName) {
    const values = initValues || [];
    const name = initName || [this.getName()];

    values[name.join('/')] = this.getValues();

    const subnodes = this.#filteredSubnodes();
    subnodes.forEach(
      (n) => this.getNodeByName(n).getValuesFlat(values, name.concat([n])),
    );
    return values;
  }
}
