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

import DataTable from './datatable.class';

export default class DataTableFactory {
  /**
    * @type {jQuery}
    */
  #jquery;

  /**
   * @type {Array<string, DataTable>}
   */
  #instances = [];

  /**
   * @param {jQuery} jquery
   */
  constructor(jquery) {
    this.#jquery = jquery;
  }

  /**
   * @param {string} tableId
   * @param {string} optActionId
   * @param {string} optRowId
   * @return {void}
   * @throws {Error} if the table was already initialized.
   */
  init(tableId, optActionId, optRowId) {
    if (this.#instances[tableId] !== undefined) {
      throw new Error(`DataTable with id '${tableId}' has already been initialized.`);
    }

    this.#instances[tableId] = new DataTable(
      this.#jquery,
      optActionId,
      optRowId,
      tableId,
    );
  }

  /**
   * @param {string} tableId
   * @return {DataTable|null}
   */
  get(tableId) {
    return this.#instances[tableId] ?? null;
  }
}
