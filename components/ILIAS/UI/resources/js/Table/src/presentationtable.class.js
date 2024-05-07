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
    this.#component.querySelector(`#${rowId} .il-table-presentation-row-controls-expander`).style.display = 'none';
    this.#component.querySelector(`#${rowId} .il-table-presentation-row-controls-collapser`).style.display = 'block';
    this.#component.querySelector(`#${rowId} .il-table-presentation-row-expanded`).style.display = 'block';
    this.#component.querySelector(`#${rowId} .il-table-presentation-row-header-fields`).style.display = 'none';
    this.#component.classList.remove('collapsed');
    this.#component.classList.add('expanded');
  }

  /**
   * @param {string} rowId
   */
  collapseRow(rowId) {
    this.#component.querySelector(`#${rowId} .il-table-presentation-row-controls-expander`).style.display = 'block';
    this.#component.querySelector(`#${rowId} .il-table-presentation-row-controls-collapser`).style.display = 'none';
    this.#component.querySelector(`#${rowId} .il-table-presentation-row-expanded`).style.display = 'none';
    this.#component.querySelector(`#${rowId} .il-table-presentation-row-header-fields`).style.display = 'block';
    this.#component.classList.remove('expanded');
    this.#component.classList.add('collapsed');
  }

  /**
   * @param {string} rowId
   */
  toggleRow(rowId) {
    const elements = [
      this.#component.querySelector(`#${rowId} .il-table-presentation-row-controls-expander`),
      this.#component.querySelector(`#${rowId} .il-table-presentation-row-controls-collapser`),
      this.#component.querySelector(`#${rowId} .il-table-presentation-row-expanded`),
      this.#component.querySelector(`#${rowId} .il-table-presentation-row-header-fields`),
    ];
    let i = 0;
    for (i; i < elements.length; i += 1) {
      const el = elements[i];
      const mode = (el.style.display === 'none') ? 'block' : 'none';
      el.style.display = mode;
    }

    if (this.#component.classList.contains('expanded')) {
      this.#component.classList.remove('expanded');
      this.#component.classList.add('collapsed');
    } else {
      this.#component.classList.remove('collapsed');
      this.#component.classList.add('expanded');
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
