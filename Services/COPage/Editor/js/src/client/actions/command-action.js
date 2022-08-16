/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import Action from "../../actions/action.js";

/**
 * Command action. Command actions invoke state changes like
 * create, update or delete actions.
 */
export default class CommandAction extends Action {

  /**
   * @param {string} component
   * @param {string} type
   * @param {Object} params
   */
  constructor(component, type, params = {}, queueable = false) {
    super(component, type, params, queueable);
  }
}