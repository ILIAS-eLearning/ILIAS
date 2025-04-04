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
 * @typedef {Object} LogLevel
 * @property {number} value
 * @property {string} name
 */

/**
 * @callback GenericCallback
 * @param {*} value
 * @returns {void}
 */

/**
 * @typedef {Object.<string, GenericCallback[]>} Subscribers
 */

export default class Storage {
  /**
   * @type {Window}
   */
  #windowObj;

  /**
   * @type {Storage}
   */
  #storage;

  /**
   * @type {Logger}
   */
  #logger;

  /**
   * @type {string}
   */
  #namespace;

  /**
   * @type {string}
   */
  #prefix;

  /**
   * @type {Subscribers}
   */
  #subscribers = {};

  /**
   * @param {Window} windowObj - The global window object.
   * @param {Storage} storage - The storage mechanism (e.g., localStorage).
   * @param {Logger} logger - The logger.
   * @param {string} namespace - The namespace for keys.
   * @param {string} prefix - The prefix for keys.
   */
  constructor(windowObj, storage, logger, namespace, prefix) {
    this.#windowObj = windowObj;
    this.#storage = storage;
    this.#logger = logger;
    this.#namespace = namespace;
    this.#prefix = prefix;

    this.#windowObj.addEventListener('storage', (event) => {
      if (event.key && event.key.startsWith(`${this.#namespace}_${this.#prefix}_`)) {
        const key = event.key.replace(`${this.#namespace}_${this.#prefix}_`, '');

        if (Object.prototype.hasOwnProperty.call(this.#subscribers, key)) {
          const item = JSON.parse(event.newValue);
          const value = item ? item.value : null;

          this.#logger.info(`Storage event: Item ${event.key} changed to: ${event.newValue}`);

          this.#subscribers[key].forEach((callback) => {
            callback(value);
          });
        } else {
          this.#logger.info(`Storage event: Could not find subscriber for item: ${key}`);
        }
      }
    });

    this.gc();
  }

  /**
   * @param {string} key - The key to listen for a change.
   * @param {GenericCallback} callback - A callback function to be executed when the value changes.
   */
  notifyOnUpdate(key, callback) {
    if (!Object.prototype.hasOwnProperty.call(this.#subscribers, key)) {
      this.#subscribers[key] = [];
    }

    this.#subscribers[key].push(callback);
  }

  /**
   *
   * @param {string} key - The keyword to store the value under.
   * @param {object} value - The value to store.
   */

  setItem(key, value) {
    const item = {
      lastChange: (new Date()).getTime(),
      value,
    };

    this.#storage.setItem(`${this.#namespace}_${this.#prefix}_${key}`, JSON.stringify(item));
  }

  /**
   * Retrieves the stored value.
   * @param {string} key - The keyword to store the value under.
   * @param {object} defaultValue - A default valur.
   * @returns {object} The parsed state object.
   */
  getItem(key, defaultValue = {}) {
    const item = this.#storage.getItem(`${this.#namespace}_${this.#prefix}_${key}`);
    if (item === null) {
      return defaultValue;
    }

    const parseItem = JSON.parse(item);

    return parseItem.value || defaultValue;
  }

  gc() {
    this.#windowObj.setInterval(() => {
      for (const [key, value] of Object.entries(this.#storage)) {
        if (key.indexOf(this.#namespace) !== -1
          && Object.prototype.hasOwnProperty.call(this.#storage, key)
        ) {
          let item = value;
          if (typeof item === 'string') {
            item = JSON.parse(item);
          }

          if (item.lastChange < (new Date()).getTime() - (24 * 60 * 60 * 1000)) {
            this.#storage.removeItem(key);
            this.#logger.debug(`Garbage collected: ${key}`);
          }
        }
      }
    }, (60 * 1000));
  }
}
