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

import FormNode from './formnode.class.js';

/**
 * @type {string}
 */
const SEARCH = '[name]';

export default class Container {
  /**
   * @type {HTMLElement}
   */
  #component;

  /**
   * @type {FormNode}
   */
  #nodes;

  /**
   * @param {HTMLElement} component
   * @return {void}
   */
  constructor(component) {
    this.#component = component;
    this.#buildTree();
  }

  /**
   * @return {void}
   */
  #buildTree() {
    const fields = this.#component.querySelectorAll(SEARCH);
    fields.forEach((field) => {
      this.#register(this.#nodes, field.name.split('/'), field);
    });
  }

  /*
   * @param {FormNode} pointer
   * @param {string[]} nameparts
   * @param {HTMLElement} component
   * @return {FormNode}
   */
  #register(pointer, nameparts, component) {
    let current = pointer;
    const part = nameparts.shift();

    if (!current) {
      this.#nodes = new FormNode(part);
      current = this.#nodes;
    }

    if (!current.getNodeNames().includes(part)) {
      current.addNode(new FormNode(part));
    }
    current = current.getNodeByName(part);

    if (nameparts.length > 0) {
      this.#register(current, nameparts, component);
    } else {
      current.addField(component);
    }
  }

  /**
   * @param {string} [fieldName]
   * @return {FormNode}
   */
  node(fieldName) {
    let node = this.#nodes;
    if (fieldName === '' || fieldName === undefined) {
      return node.getNodeByName(node.getNodeNames().shift());
    }
    fieldName.split('/').forEach((n) => { node = node.getNodeByName(n); });
    return node;
  }

  /**
   * @param {string} [fieldName]
   * @return {Array}
   */
  getValues(fieldName) {
    const node = this.node(fieldName);
    return this.#getValuesRecursively(node, null);
  }

  /**
   * @param {string} [fieldName]
   * @return {Array}
   */
  getValuesFlat(fieldName) {
    const node = this.node(fieldName);
    return this.#getValuesFlat(node, null, null);
  }

  /**
   * @param {FormNode} node
   * @param {Array|null} [initValues]
   * @return {Array<string, Array>}
   */
  #getValuesRecursively(node, initValues) {
    const values = initValues || [];
    values[node.getName()] = node.getValues();

    const subnodes = this.#groupFilteredSubnodes(node);
    subnodes.forEach(
      (n) => this.#getValuesRecursively(node.getNodeByName(n), values[node.getName()]),
    );
    return values;
  }

  /**
   * @param {FormNode} [node]
   * @param {Array|null} [initValues]
   * @param {string|null} [initName]
   * @return {Array<string, Array>}
   */
  #getValuesFlat(node, initValues, initName) {
    const values = initValues || [];
    const name = initName || [node.getName()];

    values[name.join('/')] = node.getValues();
    const subnodes = this.#groupFilteredSubnodes(node);
    subnodes.forEach(
      (n) => this.#getValuesFlat(node.getNodeByName(n), values, name.concat([n])),
    );
    return values;
  }

  /**
   * @return {FormNode[]}
   */
  #groupFilteredSubnodes(node) {
    let subnodes = node.getNodeNames();

    // optional groups:
    if (node.getFields().length > 0 && node.getValues().length === 0) {
      subnodes = []; // or, equally: return values;
    }
    // switchable groups
    if (node.getFields().length > 0
      && node.getFields().filter((f) => f.type === 'radio').length === node.getFields().length
    ) {
      subnodes = [];
      const index = node.getFields().findIndex((f) => f.value === node.getValues().shift());
      if (node.getNodeNames().length > index && index > -1) {
        subnodes = [node.getNodeNames()[index]];
      }
    }
    return subnodes;
  }
}
