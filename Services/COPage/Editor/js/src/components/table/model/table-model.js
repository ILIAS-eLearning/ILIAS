/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Controller (handles editor initialisation process)
 */
export default class TableModel {

  //debug = true;

  //currentRow = null;
  //currentCol = null;

  constructor() {
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
}