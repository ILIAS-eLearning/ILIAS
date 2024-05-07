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

/**
 * Controller (handles editor initialisation process)
 */
export default class TableModel {

  //debug = true;

  //currentRow = null;
  //currentCol = null;

  constructor(pageModel) {
    console.log("TABLE MODEL CONSTRUCTUR");
    console.log(pageModel);
    this.pageModel = pageModel;
    this.STATE_DATA = "data";          // data editing
    this.STATE_TABLE = "table";        // table properties editing
    this.STATE_CELLS = "cells";        // cells properties editing
    this.STATE_MERGE = "merge";        // merge/split cells
    this.states = [
      this.STATE_DATA,
      this.STATE_TABLE,
      this.STATE_CELLS,
      this.STATE_MERGE
    ];

    this.state = this.STATE_TABLE;
    this.selected = {
      top: -1,
      left: -1,
      bottom: -1,
      right: -1
    },
    this.debug = true;
    this.currentRow = null;
    this.currentCol = null;
  }

  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }

  /**
   * @param {string} state
   */
  setState(state) {
    if (this.states.includes(state)) {
      this.log("table-model.setState " + state);
      this.state = state;
    }
  }

  /**
   * @return {string}
   */
  getState() {
    return this.state;
  }


  /**
   *
   * @param {number} row
   * @param {number} col
   */
  setCurrentCell(row, col) {
    this.currentRow = row;
    this.currentCol = col;
  }

  /**
   * @return {number}
   */
  getCurrentRow() {
    return this.currentRow;
  }

  /**
   * @return {number}
   */
  getCurrentColumn() {
    return this.currentCol;
  }

  /**
   * @param {number} row
   * @param {number} col
   */
  toggleCell(row, col, expand) {
    this.updateSelection({
      top: parseInt(row),
      left: parseInt(col),
      bottom: parseInt(row),
      right: parseInt(col)
    }, expand);
  }

  /**
   * @param {number} row
   */
  toggleRow(row, expand) {
    this.updateSelection({
      top: row,
      left: 0,
      bottom: row,
      right: this.getNrOfCols() - 1
    }, expand);
  }

  /**
   * @param {number} col
   */
  toggleCol(col, expand) {
    this.updateSelection({
      top: 0,
      left: col,
      bottom: this.getNrOfRows() - 1,
      right: col
    }, expand);
  }

  /**
   * @param {number} col
   */
  toggleTable(expand) {
    this.updateSelection({
      top: 0,
      left: 0,
      bottom: this.getNrOfRows() - 1,
      right: this.getNrOfCols() - 1
    }, expand);
  }

  updateSelection(selection, expand) {
    // if area is identical with current area > remove selection
    if (this.hasSelected() && this.selected.top === selection.top
      && this.selected.left === selection.left
      && this.selected.bottom === selection.bottom
      && this.selected.right === selection.right) {
      this.selectNone();
      return;
    }
    if (!expand || !this.hasSelected()) {
      // just set selection
      this.selected = selection;
    } else {
      // get maximum range
      this.selected = {
        top: Math.min(this.selected.top, selection.top),
        left: Math.min(this.selected.left, selection.left),
        bottom: Math.max(this.selected.bottom, selection.bottom),
        right: Math.max(this.selected.right, selection.right)
      };
    }
  }

  selectNone() {
    this.selected = {
      top: -1,
      left: -1,
      bottom: -1,
      right: -1
    }
  }

  /**
   * Do we have selected cells?
   * @return {boolean}
   */
  hasSelected() {
    return (this.selected.top  > -1 &&
      this.selected.bottom  > -1 &&
      this.selected.left  > -1 &&
      this.selected.right  > -1
    );
  }

  /**
   * Get all selected cells
   * @return {Object}
   */
  getSelected() {
    return this.selected;
  }

  getNrOfRows() {
    const pcModel = this.pageModel.getPCModel(this.pageModel.getCurrentPCId());
    return pcModel.content.length;
  }

  getNrOfCols() {
    const pcModel = this.pageModel.getPCModel(this.pageModel.getCurrentPCId());
    return pcModel.content[1].length;
  }

}