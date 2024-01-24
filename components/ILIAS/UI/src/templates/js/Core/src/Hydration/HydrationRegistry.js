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

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class HydrationRegistry {
  /** @type {Map<string, {function(HTMLElement)}>} */
  #functions = new Map();

  /** @type {Map<string, number>} */
  #order = new Map();

  /**
   * @param {string} id
   * @param {function(HTMLElement)} fn
   * @throws {Error} if the id already exists
   */
  addFunction(id, fn) {
    if (this.#functions.has(id)) {
      throw new Error(`Function with id "${id}" already exists.`);
    }

    // keeps track of the initialisation order, starting from 0.
    this.#order.set(id, this.#functions.size);
    this.#functions.set(id, fn);
  }

  /**
   * @param {string} id
   * @returns {function(HTMLElement)|null}
   */
  getFunction(id) {
    if (this.#functions.has(id)) {
      return this.#functions.get(id);
    }

    return null;
  }

  /**
   * Returns the order in which the hydrators have been provided for initialisation.
   *
   * @returns {Map<string, number>}
   */
  getOrder() {
    return this.#order;
  }
}
