/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import Action from "../../actions/action.js";

/**
 * Query action. Query actions do not invoke state changes.
 */
export default class QueryAction extends Action {

  /**
   * @type {Object}
   */
  params = {};

  /**
   * @param {string} component
   * @param {string} type
   * @param {Object} params
   */
  constructor(component, type, params = {}) {
    super(component, type);
    this.params = params;
  }

  /**
   * @returns {Object}
   */
  getParams () {
    return this.params;
  }
}