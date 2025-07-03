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

export default class Presentation {
  /**
   * @type {string}
   */
  #type;

  /**
   * @type {string}
   */
  #name;

  /**
   * @type {string}
   */
  #label;

  /**
   * @type {Presentation[]}
   */
  #children;

  /**
   * @type {HTMLElement[]}
   */
  #htmlFields;

  #hooks;

  /**
   * @param {string} type
   * @param {string} name
   * @param {string} label
   * @return {void}
   */
  constructor(type, name, label) {
    this.#type = type;
    this.#name = name;
    this.#label = label;

    this.#children = [];
    this.#htmlFields = [];
  }

  /**
   * @return {string}
   */
  getName() {
    return this.#name.split('/').pop();
  }

  /**
   * @return {string}
   */
  getFullName() {
    return this.#name;
  }

  /**
   * @return {string}
   */
  getLabel() {
    return this.#label;
  }

  /**
   * @return {string}
   */
  getType() {
    return this.#type;
  }

  /**
   * @param {Presentation} node
   * @return {void}
   */
  addChildNode(node) {
    this.#children.push(node);
  }

  /**
   * @return {Presentation[]}
   */
  getAllChildren() {
    return this.#children;
  }

  /**
   * @param {string} name
   * @return {Presentation|null}
   */
  getChildByName(name) {
    const filtered = Array.from(this.#children)
      .filter(
        (child) => child.getName() === name,
      );
    if (filtered.length === 0) {
      return null;
    }
    return filtered.shift();
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

  /**
   * @param {Object} hooks
   */
  setHooks(hooks) {
    this.#hooks = hooks;
  }

  /**
   * @return {Presentation[]}
   */
  getChildren() {
    if (this.#hooks && this.#hooks.childrenTransform) {
      return this.#hooks.childrenTransform(this);
    }
    return this.getAllChildren();
  }

  /**
  * @return {Array}
  */
  getValuesRepresentation() {
    if (this.#hooks && this.#hooks.valueTransform) {
      return this.#hooks.valueTransform(this);
    }
    return this.getValues();
  }
}
