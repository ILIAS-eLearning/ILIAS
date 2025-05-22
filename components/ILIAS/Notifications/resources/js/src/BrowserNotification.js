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

export default class BrowserNotification {
  /**
   * @param {string} title - Title of the notification.
   * @param {Object} [options={}] - Additional options and event handlers.
   * @param {Function} [options.onShow] - Called on 'show' event.
   * @param {Function} [options.onClose] - Called on 'close' event.
   * @param {Function} [options.onClick] - Called on 'click' event.
   * @param {Function} [options.onError] - Called on 'error' event.
   * @param {boolean} [options.closeOnClick=false] - Close on click?
   * @param {number|null} [options.timeout=null] - Auto-close after N seconds.
   */
  constructor(title, options = {}) {
    if (typeof title !== 'string') {
      throw new Error('First argument (title) must be a string.');
    }
    if (typeof options !== 'object') {
      throw new Error('Second argument (options) must be an object.');
    }

    const {
      onShow = null,
      onClose = null,
      onClick = null,
      onError = null,
      closeOnClick = false,
      timeout = null,
      ...rest
    } = options;

    this.title = title;
    this.options = rest;
    this.closeOnClick = closeOnClick;
    this.timeout = timeout;
    this.onShow = typeof onShow === 'function' ? onShow : null;
    this.onClose = typeof onClose === 'function' ? onClose : null;
    this.onClick = typeof onClick === 'function' ? onClick : null;
    this.onError = typeof onError === 'function' ? onError : null;
  }

  show() {
    this.n = new window.Notification(this.title, this.options);
    ['show', 'error', 'close', 'click'].forEach((type) => this.n.addEventListener(type, this));
  }

  destroy() {
    if (!this.n) return;
    ['show', 'error', 'close', 'click'].forEach((type) => this.n.removeEventListener(type, this));
  }

  close() {
    this.n?.close();
  }

  /**
   * @param {Event} e
   */
  handleEvent(e) {
    switch (e.type) {
      case 'show': this.#onShow(e); break;
      case 'close': this.#onClose(e); break;
      case 'click': this.#onClick(e); break;
      case 'error': this.#onError(e); break;
      default: throw new Error(`Unknown event type: ${e.type}`);
    }
  }

  /**
   * @param {Event} e
   */
  #onShow(e) {
    this.onShow?.(e);
    if (!this.options.requireInteraction && typeof this.timeout === 'number' && !Number.isNaN(this.timeout)) {
      window.setTimeout(() => this.n?.close(), this.timeout * 1000);
    }
  }

  /**
   * @param {Event} e
   */
  #onClose(e) {
    this.onClose?.(e);
    this.destroy();
  }

  /**
   * @param {Event}
   */
  #onClick(e) {
    this.onClick?.(e);
    if (this.closeOnClick) {
      this.close();
    }
  }

  /**
   * @param {Event}
   */
  #onError(e) {
    this.onError?.(e);
    this.destroy();
  }
}
