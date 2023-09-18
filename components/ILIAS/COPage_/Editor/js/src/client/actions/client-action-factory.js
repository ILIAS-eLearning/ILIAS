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
 *
 *********************************************************************/

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