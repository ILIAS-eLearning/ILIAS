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

import ACTIONS from "./page-action-types.js";

/**
 * COPage action factory
 */
export default class PageQueryActionFactory {

  //COMPONENT = "Page";

  /**
   * @type {ClientActionFactory}
   */
  //clientActionFactory;

  /**
   * @param {ClientActionFactory} clientActionFactory
   */
  constructor(clientActionFactory) {
    this.COMPONENT = "Page";
    this.clientActionFactory = clientActionFactory;
  }

  uiAll() {
    return this.clientActionFactory.query(this.COMPONENT, ACTIONS.UI_ALL);
  }

  loadEditingForm(cname, pcid, hierid) {
    return this.clientActionFactory.query(this.COMPONENT, ACTIONS.EDIT_FORM, {
      cname: cname,
      pcid: pcid,
      hierid: hierid
    });
  }
}