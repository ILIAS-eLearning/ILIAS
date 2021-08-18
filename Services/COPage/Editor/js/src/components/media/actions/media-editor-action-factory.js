/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import EditorAction from "../../../actions/editor-action.js";
import ACTIONS from "./media-action-types.js";

/**
 * COPage action factory
 *
 */
export default class ParagraphEditorActionFactory {

  //COMPONENT = "Media";

  /**
   * @type {EditorActionFactory}
   */
  //editorActionFactory;

  /**
   *
   * @param {EditorActionFactory} editorActionFactory
   */
  constructor(editorActionFactory) {
    this.COMPONENT = "Media";
    this.editorActionFactory = editorActionFactory;
  }

  /**
   * @returns {EditorAction}
   */
  selectPool(url, pcid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SELECT_POOL, {
      url: url,
      pcid: pcid
    });
  }

  /**
   * @returns {EditorAction}
   */
  openClipboard(url, pcid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.OPEN_CLIPBOARD, {
      url: url,
      pcid: pcid
    });
  }
}