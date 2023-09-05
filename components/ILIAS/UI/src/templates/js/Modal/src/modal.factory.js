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

import Modal from './modal.class';

export default class ModalFactory {
  /**
   * @type {DOMParser}
   */
  #DOMParser;

  /**
   * @type {Array<string, Modal>}
   */
  #instances = [];

  /**
   * @param {DOMParser} DOMParser
   */
  constructor(DOMParser) {
    this.#DOMParser = DOMParser;
  }

  /**
   * @param {string} id
   * @return {void}
   * @throws {Error} if the modal was already initialized.
   */
  init(id) {
    if (this.#instances[id] !== undefined) {
      throw new Error(`Modal with id '${id}' has already been initialized.`);
    }

    this.#instances[id] = new Modal(this.#DOMParser, id);
  }

  /**
   * @param {string} id
   * @return {Modal|null}
   */
  get(id) {
    return this.#instances[id] ?? null;
  }
}
