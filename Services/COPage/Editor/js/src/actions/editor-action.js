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

import Action from "./action.js";

/**
 * Editor actions can perform model changes and invoke query/command actions
 */
export default class EditorAction extends Action {

  /**
   * @param {string} component
   * @param {string} type
   * @param {Object} params
   */
  constructor(component, type, params = {}) {
    super(component, type, params);
  }

}