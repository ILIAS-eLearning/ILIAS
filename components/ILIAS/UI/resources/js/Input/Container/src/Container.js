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

import addFormContainerFieldReducers from './Field/DisplayValue/addFormContainerFieldReducers.js';

/**
 * @param {Field[]} fields
 * @returns {Field[]}
 */
function flattenFields(fields) {
  return fields.reduce((flat, field) => {
    flat.push(field);
    const fieldChildren = field.getChildren();
    if (fieldChildren.length > 0) {
      flat.push(...flattenFields(fieldChildren));
    }
    return flat;
  }, []);
}

export default class Container {
  /** @type {Map<string, (function(Field, Array): *)>} */
  #fieldReducers = new Map();

  /** @type {Field[]} */
  #fields;

  /**
   * @param {Field[]} fields
   */
  constructor(fields) {
    this.#fields = fields;
  }

  /**
   * @param {Field} field
   * @param {Array} childResults
   * @returns {*|null}
   */
  #reduceField(field, childResults) {
    const reducer = this.getFieldReducer(field.type);
    if (reducer !== null) {
      return reducer(field, childResults);
    }
    return null;
  }

  /**
   * @returns {FieldDisplayValue[]}
   */
  getFieldDisplayValues() {
    addFormContainerFieldReducers(this);
    const result = this.reduceFields();
    this.removeAllFieldReducers();
    return result;
  }

  /**
   * Reduces the Field's of this Container by applying the registered reducers.
   * Reducer functions may return null, which will be filtered from the result
   * array returned by this method.
   *
   * @returns {Array}
   */
  reduceFields() {
    const reducer = (field, results) => this.#reduceField(field, results);
    return this.getFields()
      .map((field) => field.reduceWith(reducer))
      .filter((result) => result !== null);
  }

  /**
   * @param {string} type
   * @param {function(Field, Array): *} reducer
   */
  addFieldReducer(type, reducer) {
    if (this.#fieldReducers.has(type)) {
      throw new Error(`Cannot register more than one field reducer for type '${type}'.`);
    }
    this.#fieldReducers.set(type, reducer);
  }

  /**
   * @param {string} type
   * @returns {(function(Field, Array): *)|null}
   */
  getFieldReducer(type) {
    if (this.#fieldReducers.has(type)) {
      return this.#fieldReducers.get(type);
    }
    return null;
  }

  /**
   * @param {string} type
   */
  removeFieldReducer(type) {
    if (this.#fieldReducers.has(type)) {
      this.#fieldReducers.delete(type);
    }
  }

  removeAllFieldReducers() {
    this.#fieldReducers.clear();
  }

  /**
   * @param {string} name
   * @returns {Field}
   */
  getFieldByName(name) {
    return this.getFieldsFlat().find((field) => field.name === name);
  }

  /**
   * @param {Field|nul} [field=null]
   * @returns {Field[]}
   */
  getFieldsFlat(field = null) {
    if (field === null) {
      return flattenFields(this.getFields());
    }
    return flattenFields([field]);
  }

  /**
   * @returns {Field[]}
   */
  getFields() {
    return Array.from(this.#fields);
  }
}
