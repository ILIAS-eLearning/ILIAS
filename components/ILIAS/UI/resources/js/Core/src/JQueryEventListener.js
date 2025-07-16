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
export default class JQueryEventListener {
  /** @type {jQuery} */
  #jquery;

  /**
   * @param {jQuery} jquery
   */
  constructor(jquery) {
    this.#jquery = jquery;
  }

  /**
   * @param {HTMLElement} element
   * @param {string} eventType
   * @param {Function} callback
   */
  on(element, eventType, callback) {
    this.#jquery(element).on(eventType, callback);
  }

  /**
   * @param {HTMLElement} element
   * @param {string} eventType
   * @param {Function} callback
   */
  off(element, eventType, callback) {
    this.#jquery(element).off(eventType, callback);
  }
}
