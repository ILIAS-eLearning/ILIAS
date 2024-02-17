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

export default class DrilldownPersistence {
  /**
     * @type {string}
     */
  #key = 'level_id';

  /**
     * @type {object}
     */
  #cookieStorage;

  /**
     * @param {string} persistenceId
     */
  constructor(cookieStorage) {
    this.#cookieStorage = cookieStorage;
  }

  #storage() {
    return this.#cookieStorage;
  }

  /**
     * @returns {string}
     */
  read() {
    return this.#cookieStorage.items[this.#key] ?? 0;
  }

  /**
     *
     * @param {string} level_id
     * @returns void
     */
  store(levelId) {
    this.#cookieStorage.add(this.#key, levelId);
    this.#cookieStorage.store();
  }
}
