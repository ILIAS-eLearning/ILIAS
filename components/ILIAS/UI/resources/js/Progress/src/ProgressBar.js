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

import ProgressUpdateEvent from './ProgressUpdateEvent.js';

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class ProgressBar {
  /** @var {HTMLProgressElement} */
  #progressElement;

  /** @var {HTMLDivElement} */
  #messageElement;

  /** @var {Set<function(ProgressUpdateEvent)>} */
  #updateListeners = new Set();

  /**
   * @param {HTMLProgressElement} progressElement
   * @param {HTMLDivElement} messageElement
   */
  constructor(progressElement, messageElement) {
    this.#progressElement = progressElement;
    this.#messageElement = messageElement;
  }

  /**
   * @param {function(ProgressUpdateEvent)} listener
   */
  removeUpdateListener(listener) {
    if (this.#updateListeners.has(listener)) {
      this.#updateListeners.delete(listener);
    }
  }

  /**
   * @param {function(ProgressUpdateEvent)} listener
   */
  addUpdateListener(listener) {
    if (!this.#updateListeners.has(listener)) {
      this.#updateListeners.add(listener);
    }
  }

  /**
   * @param {string|null} message
   */
  indeterminate(message = null) {
    if (this.#progressElement.hasAttribute('value')) {
      this.#progressElement.removeAttribute('value');
    }

    if (message !== null) {
      this.#showMessage(message);
    } else {
      this.#hideMessage();
    }
    this.#broadcastUpdate('indeterminate');
  }

  /**
   * @param {number} visibleProgress
   * @param {string|null} message
   */
  determinate(visibleProgress, message = null) {
    if (!Number.isInteger(visibleProgress)
      || visibleProgress < 0
      || visibleProgress >= this.#progressElement.max
    ) {
      throw new Error(`Progress value must be a whole number between 0 and ${this.#progressElement.max}.`);
    }

    this.#progressElement.value = visibleProgress;

    if (message !== null) {
      this.#showMessage(message);
    } else {
      this.#hideMessage();
    }
    this.#broadcastUpdate('determinate');
  }

  /**
   * @param {message} message
   */
  success(message) {
    if (this.#progressElement.value !== this.#progressElement.max) {
      this.#finish(message, 'success');
    }
    this.#broadcastUpdate('success');
  }

  /**
   * @param {message} message
   */
  failure(message) {
    if (this.#progressElement.value !== this.#progressElement.max) {
      this.#finish(message, 'failure');
    }
    this.#broadcastUpdate('failure');
  }

  reset() {
    this.#progressElement.labels.forEach((label) => {
      label.querySelectorAll('span[data-status]').forEach((span) => {
        span.classList.remove('visible');
        span.classList.add('hidden');
      });
    });

    this.#progressElement.classList.remove('c-progress-bar--success');
    this.#progressElement.classList.remove('c-progress-bar--failure');
    this.#progressElement.value = 0;
    this.#hideMessage();
  }

  /**
   * @param {string} message
   * @param {string} modifier (success|failure)
   */
  #finish(message, modifier) {
    this.#progressElement.labels.forEach((label) => {
      label.parentElement.querySelectorAll('span[data-status]').forEach((span) => {
        if (span.getAttribute('data-status') === modifier) {
          span.classList.remove('hidden');
          span.classList.add('visible');
        } else {
          span.classList.remove('visible');
          span.classList.add('hidden');
        }
      });
    });

    this.#progressElement.value = this.#progressElement.max;
    this.#progressElement.classList.add(`c-progress-bar--${modifier}`);

    this.#showMessage(message);
  }

  /**
   * @param {string} state
   */
  #broadcastUpdate(state) {
    const event = new ProgressUpdateEvent(
      state,
      this.#progressElement.value,
      this.#messageElement.textContent,
    );

    this.#updateListeners.forEach((listener) => listener(event));
  }

  /**
   * @param {string} message
   */
  #showMessage(message) {
    this.#messageElement.classList.remove('invisible');
    this.#messageElement.classList.add('visible');
    this.#messageElement.textContent = message;
  }

  #hideMessage() {
    this.#messageElement.classList.remove('visible');
    this.#messageElement.classList.add('invisible');
    this.#messageElement.textContent = '';
  }
}
