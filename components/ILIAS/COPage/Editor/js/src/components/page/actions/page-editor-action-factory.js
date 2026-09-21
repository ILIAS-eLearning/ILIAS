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
import ACTIONS from './page-action-types.js';

/**
 * COPage action factory
 *
 */
export default class PageEditorActionFactory {
  // COMPONENT = "Page";

  /**
   * @type {EditorActionFactory}
   */
  // editorActionFactory;

  /**
   *
   * @param {EditorActionFactory} editorActionFactory
   */
  constructor(editorActionFactory) {
    this.COMPONENT = 'Page';
    this.editorActionFactory = editorActionFactory;
  }

  /**
   * @returns {EditorAction}
   */
  dndDrag() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.DND_DRAG);
  }

  /**
   * @returns {EditorAction}
   */
  dndStopped() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.DND_STOPPED);
  }

  /**
   * @returns {EditorAction}
   */
  dndDrop(target, source) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.DND_DROP, {
      target,
      source,
    });
  }

  /**
   * @returns {EditorAction}
   */
  componentInsert(cname, pcid, hierid, pluginName, fromPlaceholder) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COMPONENT_INSERT, {
      cname,
      pcid,
      hierid,
      pluginName,
      fromPlaceholder,
    });
  }

  /**
   * @returns {EditorAction}
   */
  componentEdit(cname, pcid, hierid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COMPONENT_EDIT, {
      cname,
      pcid,
      hierid,
    });
  }

  /**
   * @returns {EditorAction}
   */
  componentForm(cname, pcid, hierid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COMPONENT_FORM, {
      cname,
      pcid,
      hierid,
    });
  }

  /**
   * @returns {EditorAction}
   */
  componentSwitch(cname, state, oldPcid, oldPara, newPcid, newHierid, switchToEnd = false) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COMPONENT_SWITCH, {
      cname,
      oldComponentState: state,
      oldPcid,
      oldParameters: oldPara,
      newPcid,
      newHierid,
      switchToEnd,
    });
  }

  /**
   * @returns {EditorAction}
   */
  componentCancel() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COMPONENT_CANCEL, {});
  }

  /**
   * @returns {EditorAction}
   */
  componentBack() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COMPONENT_BACK, {});
  }

  /**
   * @returns {EditorAction}
   */
  componentSave(afterPcid, pcid, component, data) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COMPONENT_SAVE, {
      afterPcid,
      pcid,
      component,
      data,
    });
  }

  /**
   * @returns {EditorAction}
   */
  componentAfterSave(afterPcid, pcid, component, data) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COMPONENT_AFTER_SAVE, {
      afterPcid,
      pcid,
      component,
      data,
    });
  }

  /**
   * @returns {EditorAction}
   */
  componentFormLoaded(component) {
    return this.editorActionFactory.action(component, ACTIONS.COMPONENT_FORM_LOADED, {
      component,
    });
  }

  /**
   * @returns {EditorAction}
   */
  componentUpdate(pcid, component, data) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COMPONENT_UPDATE, {
      pcid,
      component,
      data,
    });
  }

  /**
   * @returns {EditorAction}
   */
  componentUpdateBack(pcid, component, data) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COMPONENT_UPDATE_BACK, {
      pcid,
      component,
      data,
    });
  }

  /**
   * @returns {EditorAction}
   */
  componentSettings(cname, pcid, hierid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COMPONENT_SETTINGS, {
      cname,
      pcid,
      hierid,
    });
  }

  /**
   * @returns {EditorAction}
   */
  multiToggle(ctype, pcid, hierid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.MULTI_TOGGLE, {
      ctype,
      pcid,
      hierid,
    });
  }

  /**
   * @returns {EditorAction}
   */
  multiAction(type) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.MULTI_ACTION, {
      type,
    });
  }

  /**
   * @returns {EditorAction}
   */
  multiPaste(pcid, hierid, mode) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.MULTI_PASTE, {
      pcid,
      hierid,
      mode,
    });
  }

  /**
   * @returns {EditorAction}
   */
  formatSave(pcids, parFormat, secFormat, medFormat, tableFormat) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.FORMAT_SAVE, {
      pcids,
      parFormat,
      secFormat,
      medFormat,
      tableFormat,
    });
  }

  /**
   * @returns {EditorAction}
   */
  formatCancel() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.FORMAT_CANCEL, {});
  }

  /**
   * @returns {EditorAction}
   */
  multiDelete(pcids) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.MULTI_DELETE, {
      pcids,
    });
  }

  /**
   * @returns {EditorAction}
   */
  multiActivate(pcids) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.MULTI_ACTIVATE, {
      pcids,
    });
  }

  /**
   * @returns {EditorAction}
   */
  formatParagraph(format) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.FORMAT_PARAGRAPH, {
      format,
    });
  }

  /**
   * @returns {EditorAction}
   */
  formatSection(format) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.FORMAT_SECTION, {
      format,
    });
  }

  /**
   * @returns {EditorAction}
   */
  formatMedia(format) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.FORMAT_MEDIA, {
      format,
    });
  }

  /**
   * @returns {EditorAction}
   */
  formatTable(format) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.FORMAT_TABLE, {
      format,
    });
  }

  /**
   * @returns {EditorAction}
   */
  switchSingle(pcids) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SWITCH_SINGLE);
  }

  /**
   * @returns {EditorAction}
   */
  switchMulti(pcids) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SWITCH_MULTI);
  }

  /**
   * @returns {EditorAction}
   */
  enablePageEditing() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.PAGE_EDITING);
  }

  /**
   * @returns {EditorAction}
   */
  editListItem(listCmd, pcid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.LIST_EDIT, {
      listCmd,
      pcid,
    });
  }
}
