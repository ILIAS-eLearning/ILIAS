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
 ******************************************************************** */

import EditorAction from '../../../actions/editor-action.js';
import ACTIONS from './paragraph-action-types.js';

/**
 * COPage action factory
 *
 */
export default class ParagraphEditorActionFactory {
  // COMPONENT = "Paragraph";

  /**
   * @type {EditorActionFactory}
   */
  // editorActionFactory;

  /**
   *
   * @param {EditorActionFactory} editorActionFactory
   */
  constructor(editorActionFactory) {
    this.COMPONENT = 'Paragraph';
    this.editorActionFactory = editorActionFactory;
  }

  /**
   * @returns {EditorAction}
   */
  selectionFormat(format) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SELECTION_FORMAT, {
      format,
    });
  }

  /**
   * @returns {EditorAction}
   */
  selectionRemoveFormat() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SELECTION_REMOVE_FORMAT);
  }

  /**
   * @returns {EditorAction}
   */
  selectionKeyword() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SELECTION_KEYWORD);
  }

  /**
   * @returns {EditorAction}
   */
  selectionTex() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SELECTION_TEX);
  }

  /**
   * @returns {EditorAction}
   */
  selectionFn() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SELECTION_FN);
  }

  /**
   * @returns {EditorAction}
   */
  selectionAnchor() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SELECTION_ANCHOR);
  }

  /**
   * @returns {EditorAction}
   */
  listBullet() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LIST_BULLET);
  }

  /**
   * @returns {EditorAction}
   */
  listBulletStyle(format) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LIST_BULLET_STYLE, {
      format,
    });
  }

  /**
   * @returns {EditorAction}
   */
  listNumber() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LIST_NUMBER);
  }

  /**
   * @returns {EditorAction}
   */
  listNumberStyle(format) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LIST_NUMBER_STYLE, {
      format,
    });
  }

  /**
   * @returns {EditorAction}
   */
  listItemStyle(format) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LIST_ITEM_STYLE, {
      format,
    });
  }

  /**
   * @returns {EditorAction}
   */
  listOutdent() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LIST_OUTDENT);
  }

  /**
   * @returns {EditorAction}
   */
  listIndent() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LIST_INDENT);
  }

  /**
   * @returns {EditorAction}
   */
  linkWikiSelection(url) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LINK_WIKI_SELECTION, {
      url,
    });
  }

  /**
   * @returns {EditorAction}
   */
  linkWiki() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LINK_WIKI);
  }

  /**
   * @returns {EditorAction}
   */
  linkInternal() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LINK_INTERNAL);
  }

  /**
   * @returns {EditorAction}
   */
  linkExternal() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LINK_EXTERNAL);
  }

  /**
   * @returns {EditorAction}
   */
  linkUser() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LINK_USER);
  }

  /**
   * @returns {EditorAction}
   */
  saveReturn(text, characteristic) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SAVE_RETURN, {
      text,
      characteristic,
    });
  }

  /**
   * @returns {EditorAction}
   */
  paragraphClass(characteristic) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.PARAGRAPH_CLASS, {
      characteristic,
    });
  }

  /**
   * @returns {EditorAction}
   */
  autoSave(text, characteristic) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.AUTO_SAVE, {
      text,
      characteristic,
    });
  }

  /**
   * @returns {EditorAction}
   */
  autoInsertPostProcessing() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.AUTO_INSERT_POST, {
    });
  }

  /**
   * @returns {EditorAction}
   */
  splitPostProcessing() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SPLIT_POST, {
    });
  }

  splitParagraph(pcid, text, characteristic, contents) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SPLIT_PARAGRAPH, {
      pcid,
      text,
      characteristic,
      contents,
    });
  }

  /**
   * @returns {EditorAction}
   */
  sectionClass(parText, parCharacteristic, oldSectionCharacteristic, newSectionCharacteristic) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SECTION_CLASS, {
      parText,
      parCharacteristic,
      oldSectionCharacteristic,
      newSectionCharacteristic,
    });
  }

  mergePrevious(pcid, newPreviousContent, previousPcid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.MERGE_PREVIOUS, {
      pcid,
      previousPcid,
      newPreviousContent,
    });
  }
}
