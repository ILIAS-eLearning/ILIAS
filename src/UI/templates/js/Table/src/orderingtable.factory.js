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

import OrderingTable from './orderingtable.class';

export default class OrderingTableFactory {
  /**
   * @type {Array<string, OrderingTable>}
   */
  #instances = [];

  /**
   * @param {string} tableId
   * @return {void}
   * @throws {Error} if the table was already initialized.
   */
  init(tableId) {
    if (this.#instances[tableId] !== undefined) {
      throw new Error(`OrderingTable with id '${tableId}' has already been initialized.`);
    }
    this.#instances[tableId] = new OrderingTable(tableId);
  }

  /**
   * @param {string} tableId
   * @return {OrderingTable|null}
   */
  get(tableId) {
    return this.#instances[tableId] ?? null;
  }
}
