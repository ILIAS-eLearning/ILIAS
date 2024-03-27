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

import PresentationTable from './presentationtable.class';

export default class PresentationTableFactory {
  /**
   * @type {Array<string, PresentationTable>}
   */
  #instances = [];

  /**
   * @param {string} tableId
   */
  init(tableId) {
    if (this.#instances[tableId] !== undefined) {
      throw new Error(`PresentationTable with input-id '${tableId}' has already been initialized.`);
    }
    this.#instances[tableId] = new PresentationTable(tableId);
  }

  /**
   * @param {string} tableId
   * @return {PresentationTable|null}
   */
  get(tableId) {
    return this.#instances[tableId] ?? null;
  }
}
