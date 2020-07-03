/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import Action from "../../actions/action.js";

/**
 * Command action. Command actions invoke state changes like
 * create, update or delete actions.
 */
export default class CommandAction extends Action {

  /**
   * @type {Object}
   */
  data = {};

  /**
   */
  constructor(component, type, data = {}) {
    super(component, type);
    this.data = data;
  }

  /**
   * @returns {Object}
   */
  getData () {
    return this.data;
  }

}