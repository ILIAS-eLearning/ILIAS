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
  #label;

  /**
   * @type {string}
   */
  #type;

  /**
   * @type {string}
   */
  #id;

  /**
   * @type {FormNode[]}
   */
  #children;

  /**
   * @type {HTMLElement[]}
   */
  #htmlFields;

  #transforms;

  /**
   * @param {string} name
   * @param {string} type
   * @return {void}
   */
  constructor(label, type, id) {
    this.#label = label;
    this.#type = type;
    this.#id = id;

    this.#children = [];
    this.#htmlFields = [];
  }

  /**
   * @return {string}
   */
  getId() {
    return this.#id;
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
   * @param {FormNode} node
   * @return {void}
   */
  addChildNode(node) {
    this.#children.push(node);
  }

  /**
   * @return {FormNode[]}
   */
  getAllChildren() {
    return this.#children;
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
   * @param {Class} transforms
   */
  setTransforms(transforms) {
    this.#transforms = transforms;
  }

  /**
   * @return {FormNode[]}
   */
  getChildren() {
    if (this.#transforms && this.#transforms.childrenTransform) {
      return this.#transforms.childrenTransform(this);
    }
    return this.getAllChildren();
  }

  /**
  * @return {Array}
  */
  getValuesRepresentation() {
    if (this.#transforms && this.#transforms.valueTransform) {
      return this.#transforms.valueTransform(this);
    }
    return this.getValues();
  }
}
