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
 * @param {DocumentFragment} state
 * @returns {string|null}
 */
function extractMessageFromState(state) {
  const messageElement = state.querySelector('section[data-section="message"]');
  if (messageElement === null || !messageElement.innerText) {
    return null;
  }
  return messageElement.innerText;
}

/**
 * @param {DocumentFragment} state
 * @returns {number|null} ({0..100}|null)
 */
function extractProgressFromState(state) {
  const progressElement = state.querySelector('section[data-section="progress"] > progress');
  if (!(progressElement instanceof state.ownerDocument.defaultView.HTMLProgressElement)) {
    return null;
  }
  return parseInt(progressElement.value, 10);
}

/**
 * @param {DocumentFragment} state
 * @returns {string|null} (indeterminate|determinate|success|failure|null)
 */
function extractStatusFromState(state) {
  const statusElement = state.querySelector('section[data-section="status"]');
  if (statusElement === null || !statusElement.hasAttribute('data-status')) {
    return null;
  }
  return statusElement.getAttribute('data-status');
}

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class ProgressBarAsyncDecorator {
  /** @var {AsyncRenderer} */
  #asyncRenderer;

  /** @var {ProgressBar} */
  #progressBar;

  /** @var {number} */
  #refreshRateInMs;

  /** @var {string} */
  #asyncUrl;

  /** @var {number|null} */
  #refreshInterval = null;

  /** @var {boolean} */
  #isFetching = false;

  /**
   * @param {AsyncRenderer} asyncRenderer
   * @param {ProgressBar} progressBar
   * @param {number} refreshRateInMs
   * @param {string} asyncUrl
   */
  constructor(asyncRenderer, progressBar, refreshRateInMs, asyncUrl) {
    this.#asyncRenderer = asyncRenderer;
    this.#progressBar = progressBar;
    this.#refreshRateInMs = refreshRateInMs;
    this.#asyncUrl = asyncUrl;
  }

  /**
   * @param {DocumentFragment} state
   */
  #handleState(state) {
    const status = extractStatusFromState(state);
    const progress = extractProgressFromState(state);
    const message = extractMessageFromState(state);

    if (status === 'determinate') {
      this.determinate(progress ?? -1, message);
    } else if (status === 'indeterminate') {
      this.indeterminate(message);
    } else if (status === 'success') {
      this.success(message);
    } else {
      this.failure(message);
    }
  }

  #start() {
    if (this.#refreshInterval !== null) {
      return;
    }
    this.#refreshInterval = setInterval(
      () => this.#update(),
      this.#refreshRateInMs,
    );
  }

  #stop() {
    if (this.#refreshInterval !== null) {
      clearInterval(this.#refreshInterval);
      this.#refreshInterval = null;
    }
  }

  /**
   * @returns {Promise<void>}
   */
  async #update() {
    if (this.#isFetching) {
      return;
    }
    try {
      this.#isFetching = true;
      const state = await this.#asyncRenderer.loadContent(this.#asyncUrl);
      this.#handleState(state);
    } catch (error) {
      this.failure(error.message);
    } finally {
      this.#isFetching = false;
    }
  }

  /**
   * @param {string|null} message
   * @see {ProgressBar.indeterminate}
   */
  indeterminate(message = null) {
    this.#progressBar.indeterminate(message);
    this.#start();
  }

  /**
   * @param {number} progress
   * @param {string|null} message
   * @see {ProgressBar.determinate}
   */
  determinate(progress, message = null) {
    this.#progressBar.determinate(progress, message);
  }

  /**
   * @param {string} message
   * @see {ProgressBar.success}
   */
  success(message) {
    this.#progressBar.success(message);
    this.#stop();
  }

  /**
   * @param {string} message
   * @see {ProgressBar.failure}
   */
  failure(message) {
    this.#progressBar.failure(message);
    this.#stop();
  }

  /**
   * @see {ProgressBar.reset}
   */
  reset() {
    this.#progressBar.reset();
    this.#stop();
  }
}
