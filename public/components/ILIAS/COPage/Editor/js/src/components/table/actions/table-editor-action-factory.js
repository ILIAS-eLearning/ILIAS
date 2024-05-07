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
import ACTIONS from "./table-action-types.js";

/**
 * COPage action factory
 *
 */
export default class TableEditorActionFactory {

  //COMPONENT = "Table";

  /**
   * @type {EditorActionFactory}
   */
  //editorActionFactory;

  /**
   *
   * @param {EditorActionFactory} editorActionFactory
   */
  constructor(editorActionFactory) {
    this.COMPONENT = "Table";
    this.editorActionFactory = editorActionFactory;
  }

  /**
   * @returns {EditorAction}
   */
  editCell(tablePcid, tableHierid, row, column) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.EDIT_CELL, {
      tablePcid: tablePcid,
      tableHierid: tableHierid,
      row: row,
      column: column
    });
  }

  saveReturn(content) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SAVE_RETURN, {
      content: content
    });
  }

  /**
   * @returns {EditorAction}
   */
  colBefore(nr, cellPcid, tablePcid, cnt) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COL_BEFORE, {
      nr: nr,
      cellPcid: cellPcid,
      tablePcid: tablePcid,
      cnt: cnt
    });
  }

  /**
   * @returns {EditorAction}
   */
  colAfter(nr, cellPcid, tablePcid, cnt) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COL_AFTER, {
      nr: nr,
      cellPcid: cellPcid,
      tablePcid: tablePcid,
      cnt: cnt
    });
  }

  /**
   * @returns {EditorAction}
   */
  colLeft(nr, cellPcid, tablePcid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COL_LEFT, {
      nr: nr,
      cellPcid: cellPcid,
      tablePcid: tablePcid
    });
  }

  /**
   * @returns {EditorAction}
   */
  colRight(nr, cellPcid, tablePcid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COL_RIGHT, {
      nr: nr,
      cellPcid: cellPcid,
      tablePcid: tablePcid
    });
  }

  /**
   * @returns {EditorAction}
   */
  colDelete(nr, cellPcid, tablePcid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.COL_DELETE, {
      nr: nr,
      cellPcid: cellPcid,
      tablePcid: tablePcid
    });
  }

  /**
   * @returns {EditorAction}
   */
  rowBefore(nr, cellPcid, tablePcid, cnt) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.ROW_BEFORE, {
      nr: nr,
      cellPcid: cellPcid,
      tablePcid: tablePcid,
      cnt: cnt
    });
  }

  /**
   * @returns {EditorAction}
   */
  rowAfter(nr, cellPcid, tablePcid, cnt) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.ROW_AFTER, {
      nr: nr,
      cellPcid: cellPcid,
      tablePcid: tablePcid,
      cnt: cnt
    });
  }

  /**
   * @returns {EditorAction}
   */
  rowUp(nr, cellPcid, tablePcid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.ROW_UP, {
      nr: nr,
      cellPcid: cellPcid,
      tablePcid: tablePcid
    });
  }

  /**
   * @returns {EditorAction}
   */
  rowDown(nr, cellPcid, tablePcid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.ROW_DOWN, {
      nr: nr,
      cellPcid: cellPcid,
      tablePcid: tablePcid
    });
  }

  /**
   * @returns {EditorAction}
   */
  rowDelete(nr, cellPcid, tablePcid) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.ROW_DELETE, {
      nr: nr,
      cellPcid: cellPcid,
      tablePcid: tablePcid
    });
  }

  /**
   * @returns {EditorAction}
   */
  autoSave() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.AUTO_SAVE);
  }

  /**
   * @returns {EditorAction}
   */
  switchEditTable() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SWITCH_EDIT_TABLE);
  }

  /**
   * @returns {EditorAction}
   */
  switchFormatCells() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SWITCH_FORMAT_CELLS);
  }

  /**
   * @returns {EditorAction}
   */
  switchMergeCells() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.SWITCH_MERGE_CELLS);
  }

  /**
   * @returns {EditorAction}
   */
  toggleRow(nr,expand) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.TOGGLE_ROW,{
      nr: nr,
      expand: expand
    });
  }

  /**
   * @returns {EditorAction}
   */
  toggleCol(nr,expand) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.TOGGLE_COL,{
      nr: nr,
      expand: expand
    });
  }

  /**
   * @returns {EditorAction}
   */
  toggleTable(expand) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.TOGGLE_TABLE, {
        expand: expand
    });
  }

  /**
   * @returns {EditorAction}
   */
  toggleCell(col,row,expand) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.TOGGLE_CELL,{
      col: col,
      row: row,
      expand: expand
    });
  }

  /**
   * @returns {EditorAction}
   */
  propertiesSet(pcid, selected, data) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.PROPERTIES_SET,{
      pcid:pcid,
      selected: selected,
      data:data
    });
  }

  /**
   * @returns {EditorAction}
   */
  toggleMerge(pcid, selected) {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.TOGGLE_MERGE,{
      pcid:pcid,
      selected: selected
    });
  }

  /**
   * @returns {EditorAction}
   */
  cancelCellEdit() {
    return this.editorActionFactory.action(this.COMPONENT, ACTIONS.CANCEL_CELL_EDIT);
  }

}