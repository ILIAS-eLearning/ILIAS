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
export default class GlobalProgressBarSignalDispatcher {
  /** @var {Map<string, ProgressBar>} */
  #progressBars = new Map();

  /**
   * @param {ProgressBar} progressBar
   * @param {string} updateSignal
   */
  register(progressBar, updateSignal) {
    if (!this.#progressBars.has(updateSignal)) {
      this.#progressBars.set(updateSignal, progressBar);
    }
  }

  /**
   * @param {string} updateSignal
   * @param {string|null} message
   */
  indeterminate(updateSignal, message = null) {
    this.#getProgressBarOrAbort(updateSignal).indeterminate(message);
  }

  /**
   * @param {string} updateSignal
   * @param {number} visibleProgress (between 0 and 99)
   * @param {string|null} message
   */
  determinate(updateSignal, visibleProgress, message = null) {
    this.#getProgressBarOrAbort(updateSignal).determinate(visibleProgress, message);
  }

  /**
   * @param {string} updateSignal
   * @param {string|null} message
   */
  success(updateSignal, message) {
    this.#getProgressBarOrAbort(updateSignal).success(message);
  }

  /**
   * @param {string} updateSignal
   * @param {string|null} message
   */
  failure(updateSignal, message) {
    this.#getProgressBarOrAbort(updateSignal).failure(message);
  }

  /**
   * @param {stringl} updateSignal
   * @return {ProgressBar}
   */
  #getProgressBarOrAbort(updateSignal) {
    if (!this.#progressBars.has(updateSignal)) {
      throw new Error(`Could not find progress bar component for signal '${updateSignal}'`);
    }
    return this.#progressBars.get(updateSignal);
  }
}
