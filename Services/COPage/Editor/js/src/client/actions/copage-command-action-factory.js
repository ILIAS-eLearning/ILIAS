/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import CommandAction from "./command-action.js";

/**
 * COPage action factory
 *
 */
export default class COPageCommandActionFactory {

  COMPONENT = "copage";
  CREATE_LEGACY = "create.legacy";

  /**
   */
  constructor() {
  }

  createLegacy(ctype, pcid, hier_id) {
    return new CommandAction(this.COMPONENT, this.CREATE_LEGACY, {
      cmd: "insert",
      ctype: ctype,
      pcid: pcid,
      hier_id: hier_id
    });
  }
}