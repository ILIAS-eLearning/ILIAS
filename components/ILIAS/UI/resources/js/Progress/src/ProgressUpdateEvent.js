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
export default class ProgressUpdateEvent {
  /** @var {string} */
  #state;

  /**
   * @param {string} state (indeterminate|determinate|success|failure)
   * @param {number} value
   * @param {string|null} message
   */
  constructor(state, value, message) {
    this.#state = state;
    this.value = value;
    this.message = message;
  }

  /** @return {boolean} */
  isIndeterminate() {
    return this.#state === 'indeterminate';
  }

  /** @return {boolean} */
  isDeterminate() {
    return this.#state === 'determinate';
  }

  /** @return {boolean} */
  isSuccess() {
    return this.#state === 'success';
  }

  /** @return {boolean} */
  isFailure() {
    return this.#state === 'failure';
  }
}
