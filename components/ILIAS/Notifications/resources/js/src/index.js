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

import BrowserNotification from './BrowserNotification.js';

const PERMISSION_DEFAULT = 'default';
const PERMISSION_GRANTED = 'granted';
const PERMISSION_DENIED = 'denied';

const BrowserNotifications = {
  /**
   * @returns {boolean}
   */
  isSupported() {
    return window.location.protocol === 'https:'
      && 'Notification' in window
      && typeof window.Notification.requestPermission === 'function';
  },

  /**
   * @returns {boolean}
   */
  isBlocked() {
    return window.Notification
      && window.Notification.permission
      && window.Notification.permission === PERMISSION_DENIED;
  },

  /**
   * @returns {boolean}
   */
  isGranted() {
    return !this.needsPermission();
  },

  /**
   * @returns {boolean}
   */
  needsPermission() {
    return window.Notification
      && window.Notification.permission
      && window.Notification.permission === PERMISSION_GRANTED;
  },

  /**
   * @param {string} title - The title of the notification.
   * @param {Object} [options={}] - Notification options and event callbacks.
   * @param {Function} [options.onShow] - Called on 'show'.
   * @param {Function} [options.onClose] - Called on 'close'.
   * @param {Function} [options.onClick] - Called on 'click'.
   * @param {Function} [options.onError] - Called on 'error'.
   * @param {boolean} [options.closeOnClick=false] - Whether to close on click.
   * @param {number|null} [options.timeout=null] - Auto-close timeout in seconds.
   * @returns {BrowserNotification}
   */
  notification(title, options = {}) {
    return new BrowserNotification(title, options);
  },

  /**
   * @returns {Promise<string>} The resulting permission value.
   */
  requestPermission() {
    return new Promise((resolve, reject) => {
      window.setTimeout(() => {
        const handle = (permission) => {
          switch (permission) {
            case PERMISSION_GRANTED:
              resolve(permission);
              break;

            case PERMISSION_DEFAULT:
            case PERMISSION_DENIED:
            default:
              reject(permission);
              break;
          }
        };

        const result = window.Notification.requestPermission(handle);
        // This stunt is necessary because of old Safari browsers
        if (result?.then) {
          result.then(handle).catch(() => reject());
        }
      }, 0);
    });
  },
};

export default BrowserNotifications;
