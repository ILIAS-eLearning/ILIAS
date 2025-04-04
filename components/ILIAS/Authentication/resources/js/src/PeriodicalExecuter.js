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
 ******************************************************************** */

/**
 * @callback NoArgsCallback
 * @returns {void}
 */

export default class PeriodicalExecuter {
  /**
   * @type {Window}
   */
  #windowObj;

  /**
   * @type {NoArgsCallback}
   */
  #callback;

  /**
   * @type {number}
   */
  #frequency;

  /**
   * @type {number | null}
   */
  #timer = null;

  /**
   * @type {boolean}
   */
  #currentlyExecuting;

  /**
   * @param {Window} windowObj - The global window object.
   * @param {NoArgsCallback} callback - The function to execute periodically.
   * @param {number} frequency - The execution interval in milliseconds.
   */
  constructor(windowObj, callback, frequency) {
    this.#windowObj = windowObj;
    this.#callback = callback;
    this.#frequency = frequency;
    this.#currentlyExecuting = false;
    this.#registerCallback();
  }

  #registerCallback() {
    this.#timer = this.#windowObj.setInterval(() => this.#onTimerEvent(), this.#frequency);
  }

  stop() {
    if (!this.#timer) return;
    this.#windowObj.clearInterval(this.#timer);
    this.#timer = null;
  }

  #onTimerEvent() {
    if (!this.#currentlyExecuting) {
      try {
        this.#currentlyExecuting = true;
        this.#callback();
      } finally {
        this.#currentlyExecuting = false;
      }
    }
  }
}
