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