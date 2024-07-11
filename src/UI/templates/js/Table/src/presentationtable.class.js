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

export default class PresentationTable {
  /**
   * @type {HTMLDivElement}
   */
  #component;

  /**
   * @param {string} componentId
   * @throws {Error} if DOM element is missing
   */
  constructor(componentId) {
    this.#component = document.getElementById(componentId);
    if (this.#component === null) {
      throw new Error(`Could not find a PresentationTable for id '${componentId}'.`);
    }
  }

  /**
   * @param {string} rowId
   */
  expandRow(rowId) {
    const row = this.#component.querySelector(`#${rowId}`);
    row.classList.remove('collapsed');
    row.classList.add('expanded');
  }

  /**
   * @param {string} rowId
   */
  collapseRow(rowId) {
    const row = this.#component.querySelector(`#${rowId}`);
    row.classList.remove('expanded');
    row.classList.add('collapsed');
  }

  /**
   * @param {string} rowId
   */
  toggleRow(rowId) {
    const row = this.#component.querySelector(`#${rowId}`);
    if (row.classList.contains('expanded')) {
      this.collapseRow(rowId);
    } else {
      this.expandRow(rowId);
    }
  }

  /**
   * @param {array} signalData
   */
  expandAll(signalData) {
    const rows = this.#component.querySelectorAll('.il-table-presentation-row');
    if (signalData.options.expand) {
      rows.forEach((row) => this.expandRow(row.id));
    } else {
      rows.forEach((row) => this.collapseRow(row.id));
    }
  }
}
