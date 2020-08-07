/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import CommandAction from "./command-action.js";

/**
 * COPage command actions being sent to the server
 */
export default class COPageCommandActionFactory {

  COMPONENT = "copage";
  CREATE_LEGACY = "create.legacy";
  MULTI_LEGACY = "multi.legacy";

  /**
   */
  constructor() {
  }

  /**
   * @param {string} ctype
   * @param {string} pcid
   * @param {string} hier_id
   * @return {CommandAction}
   */
  createLegacy(ctype, pcid, hier_id) {
    return new CommandAction(this.COMPONENT, this.CREATE_LEGACY, {
      cmd: "insert",
      ctype: ctype,
      pcid: pcid,
      hier_id: hier_id
    });
  }

  /**
   * @param {string} type
   * @param {[]} ids
   * @return {CommandAction}
   */
  multiLegacy(type, ids) {
    return new CommandAction(this.COMPONENT, this.MULTI_LEGACY, {
      cmd: type,
      ids: ids
    });
  }
}