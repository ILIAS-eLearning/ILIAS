/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import EditorAction from "./editor-action.js";

/**
 * COPage action factory
 *
 */
export default class EditorActionFactory {

  COMPONENT = "copage";
  DND_DRAG = "dnd.drag";      // start dragging
  DND_DROP = "dnd.drop";      // dropping
  CREATE_ADD = "create.add";  // hit add link in add dropdown


  /**
   */
  constructor() {
  }

  /**
   * @returns {EditorAction}
   */
  dndDrag() {
    return new EditorAction(this.COMPONENT, this.DND_DRAG);
  }

  /**
   * @returns {EditorAction}
   */
  dndDrop() {
    return new EditorAction(this.COMPONENT, this.DND_DROP);
  }

  /**
   * @returns {EditorAction}
   */
  createAdd(ctype, pcid, hierid) {
    return new EditorAction(this.COMPONENT, this.CREATE_ADD, {
      ctype: ctype,
      pcid: pcid,
      hierid: hierid
    });
  }

}