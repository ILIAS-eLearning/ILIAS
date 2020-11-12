/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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