/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import QueryAction from "./query-action.js";
import CommandAction from "./command-action.js";
import FormCommandAction from "./form-command-action.js";

/**
 * COPage action factory
 */
export default class ClientActionFactory {

  /**
   */
  constructor() {
  }

  /**
   * @param {string} component
   * @param {string} type
   * @param {Object} params
   */
  query(component, type, params) {
    return new QueryAction(component, type, params);
  }

  /**
   * @param {string} component
   * @param {string} type
   * @param {Object} params
   */
  command(component, type, params, queueable = false) {
    return new CommandAction(component, type, params, queueable);
  }

  /**
   *
   * @param {string} component
   * @param {string} type
   * @param {formData} data
   * @return {FormCommandAction}
   */
  formCommand(component, type, data) {
    return new FormCommandAction(component, type, data);
  }
}