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