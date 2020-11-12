/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import Action from "../../actions/action.js";

/**
 * Command action. Command actions invoke state changes like
 * create, update or delete actions.
 */
export default class FormCommandAction extends Action {

  /**
   * @param {string} component
   * @param {string} type
   * @param {formData} data
   */
  constructor(component, type, data = {}) {
    super(component, type, data);
  }
}