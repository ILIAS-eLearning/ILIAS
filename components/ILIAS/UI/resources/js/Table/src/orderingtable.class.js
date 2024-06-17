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
 */

export default class OrderingTable {
  /**
   * @type {HTMLDivElement}
   */
  #component;

  /**
   * @type {HTMLTableElement}
   */
  #table;

  /**
   * @type {array<HTMLTableRowElement>}
   */
  #rows;

  /**
   * @type {HTMLTableRowElement}
   */
  #tmpDragRow;

  /**
   * @param {string} tableId
   * @throws {Error} if DOM element is missing
   */
  constructor(componentId) {
    this.#component = document.getElementById(componentId);
    if (this.#component === null) {
      throw new Error(`Could not find a OrderingTable for id '${componentId}'.`);
    }
    this.#table = this.#component.getElementsByTagName('table').item(0);
    if (this.#table === null) {
      throw new Error('There is no <table> in the component\'s HTML.');
    }

    this.#indexRows();
    this.#rows.forEach((row) => this.#addDraglisteners(row));
  }

  #indexRows() {
    this.#rows = Array.from(this.#table.rows);
    this.#rows.shift();// exclude header
    this.#rows.pop();// exclude footer
  }

  #addDraglisteners(row) {
    row.setAttribute('draggable', true);
    row.addEventListener('dragstart', (event) => this.dragstart(event));
    row.addEventListener('dragover', (event) => this.dragover(event));
    row.addEventListener('dragend', () => { this.#indexRows(); this.#renumberAfterDrag(); });
  }

  dragstart(event) {
    this.#tmpDragRow = event.target;
  }

  #isDraggedElementValidRow() {
    return this.#rows.includes(this.#tmpDragRow);
  }

  dragover(event) {
    if (!this.#isDraggedElementValidRow()) {
      return;
    }

    const e = event;
    e.preventDefault();
    const target = e.target.closest('tr');

    if (this.#rows.indexOf(target) > this.#rows.indexOf(this.#tmpDragRow)) {
      target.after(this.#tmpDragRow);
    } else {
      target.before(this.#tmpDragRow);
    }
  }

  #renumberAfterDrag() {
    let pos = 10;
    this.#table.querySelectorAll('input[type="number"]').forEach(
      (input) => {
        const field = input;
        field.value = pos;
        pos += 10;
      },
    );
  }
}
