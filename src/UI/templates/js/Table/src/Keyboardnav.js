/**
 * @type {number}
 */
const KEY_LEFT = 37;

/**
 * @type {number}
 */
const KEY_UP = 38;

/**
 * @type {number}
 */
const KEY_RIGHT = 39;

/**
 * @type {number}
 */
const KEY_DOWN = 40;

class Keyboardnav {
  /**
   * @param {KeyboardEvent} event
   */
  navigateCellsWithArrowKeys(event) {
    if (!(event.which === KEY_LEFT
            || event.which === KEY_UP
            || event.which === KEY_RIGHT
            || event.which === KEY_DOWN
    )) {
      return;
    }

    const cell = event.target.closest('td, th');
    const row = cell.closest('tr');
    const table = row.closest('table');
    let { cellIndex } = cell;
    let { rowIndex } = row;

    switch (event.which) {
      case KEY_LEFT:
        cellIndex -= 1;
        break;
      case KEY_RIGHT:
        cellIndex += 1;
        break;
      case KEY_UP:
        rowIndex -= 1;
        break;
      case KEY_DOWN:
        rowIndex += 1;
        break;
      default:
        break;
    }

    if (rowIndex < 0 || cellIndex < 0
            || rowIndex >= table.rows.length
            || cellIndex >= row.cells.length
    ) {
      return;
    }
    this.focusCell(table, cell, rowIndex, cellIndex);
  }

  /**
   * @param {HTMLTableElement} table
   * @param {HTMLTableCellElement} cell
   * @param {number} rowIndex
   * @param {number} cellIndex
   */
  focusCell(table, cell, rowIndex, cellIndex) {
    const nextCell = table.rows[rowIndex].cells[cellIndex];
    nextCell.focus();
    cell.setAttribute('tabindex', -1);
    nextCell.setAttribute('tabindex', 0);
  }

  /**
   * @param {string} targetId
   */
  init(targetId) {
    document.getElementById(targetId)?.addEventListener('keydown', (event) => this.navigateCellsWithArrowKeys(event, this));
  }
}

export default Keyboardnav;
