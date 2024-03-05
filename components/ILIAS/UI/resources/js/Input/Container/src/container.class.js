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

import FormNode from './formnode.class';

/**
 * @type {string}
 */
const ROOT = 'root';

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
    this.#nodes = new FormNode(ROOT);
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
    return this.node(fieldName).getValuesRecursively();
  }

  /**
   * @param {string} [fieldName]
   * @return {Array}
   */
  getValuesFlat(fieldName) {
    return this.node(fieldName).getValuesFlat();
  }
}
