/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "./table-action-types.js";

/**
 * COPage command actions being sent to the server
 */
export default class TableCommandActionFactory {

  /**
   * @type {ClientActionFactory}
   */
  //clientActionFactory;

  //COMPONENT = "Table";

  /**
   * @param {ClientActionFactory} clientActionFactory
   */
  constructor(clientActionFactory) {
    this.COMPONENT = "Table";
    this.clientActionFactory = clientActionFactory;
  }

  /**
   * @param pcid
   * @param content
   * @param redirect
   * @return {CommandAction}
   */
  updateData(pcid, content, redirect) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.UPDATE_DATA, {
      pcid: pcid,
      content: content,
      redirect: redirect
    });
  }

  /**
   * @param tablePcid
   * @param content
   * @param modification
   * @param nr
   * @param cellPcid
   * @return {CommandAction}
   */
  modifyTable(tablePcid, content, modification, nr, cellPcid) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.MODIFY_TABLE, {
      tablePcid: tablePcid,
      content: content,
      modification: modification,
      nr: nr,
      cellPcid: cellPcid
    });
  }

}