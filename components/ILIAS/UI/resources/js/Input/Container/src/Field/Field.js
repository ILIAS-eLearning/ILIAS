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

/**
 * A Field is used in Input Containers for various calculations, where a
 * rudamentary representation of an Input Field is sufficient. This is NOT
 * a base class for Input Field implementations and is deliberately kept
 * inside this namespace/directory.
 */
export default class Field {
  /** @type {Array<HTMLInputElement|HTMLSelectElement|HTMLTextAreaElement>} */
  #inputs;

  /** @type {Field[]} */
  #children;

  /**
   * @param {string} type (canonical name)
   * @param {string} name
   * @param {Array<HTMLInputElement|HTMLSelectElement|HTMLTextAreaElement>} inputs
   * @param {string|null} label
   * @param {HTMLElement} element
   * @param {Field[]} [children=Field[]]
   */
  constructor(type, name, label, inputs, element, children = []) {
    this.type = type;
    this.name = name;
    this.label = label;
    this.#inputs = inputs;
    this.element = element;
    this.#children = children;
  }

  /**
   * Reduces the Field by applying the given function to the current instance
   * and its children. The scheme starts at the leaves and moves up the composite
   * tree recusrively.
   *
   * @param {function(Field, Array): *} fn
   * @returns {*}
   */
  reduceWith(fn) {
    const results = [];
    const children = this.getChildren();
    for (let childIndex = 0; childIndex < children.length; childIndex += 1) {
      results.push(children[childIndex].reduceWith(fn));
    }
    return fn(this, results);
  }

  /**
   * @returns {Array<HTMLInputElement|HTMLSelectElement|HTMLTextAreaElement>}
   */
  getInputs() {
    return Array.from(this.#inputs);
  }

  /**
   * @returns {Field[]}
   */
  getChildren() {
    return Array.from(this.#children);
  }
}
