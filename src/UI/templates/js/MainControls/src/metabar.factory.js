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

import Metabar from './metabar.class';

export default class MetabarFactory {
  /**
    * @type {jQuery}
    */
  #jquery;

  /**
   * @type {Array<string, Metabar>}
   */
  #instances = [];

  /**
   * @type {function}
   */
  #pageIsSmallScreen;

  /**
   * @type {counterFactory}
   */
  #counterFactory;

  /**
   * @type {function}
   */
  #disengageMainbar;

  /**
   * @type {function}
   */
  #disengageSlate;

  /**
   * @param {jQuery} jquery
   * @param {function} pageIsSmallScreen
   * @param {counterFactory} counterFactory
   * @param {function} disengageMainbar
   * @param {function} disengageSlate
   */
  constructor(
    jquery,
    pageIsSmallScreen,
    counterFactory,
    disengageMainbar,
    disengageSlate,
  ) {
    this.#jquery = jquery;
    this.#pageIsSmallScreen = pageIsSmallScreen;
    this.#counterFactory = counterFactory;
    this.#disengageMainbar = disengageMainbar;
    this.#disengageSlate = disengageSlate;
  }

  /**
   * @param {string} componentId
   * @return {void}
   * @throws {Error} if the bar was already initialized.
   */
  init(componentId) {
    if (this.#instances[componentId] !== undefined) {
      throw new Error(`Metabar with id '${componentId}' has already been initialized.`);
    }

    this.#instances[componentId] = new Metabar(
      this.#jquery,
      componentId,
      this.#pageIsSmallScreen,
      this.#counterFactory,
      this.#disengageMainbar,
      this.#disengageSlate,
    );
  }

  /**
   * @param {string} tableId
   * @return {Metabar|null}
   */
  get(componentId) {
    return this.#instances[componentId] ?? null;
  }

  /**
   * @return {void}
   */
  disengageAll() {
    Object.values(this.#instances).forEach(
      (slate) => slate.disengageAll(),
    );
  }
}
