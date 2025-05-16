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
 * @typedef {Object} SessionState
 * @property {'disabled'|'enabled'} activation
 * @property {'locked'|'unlocked'} status
 * @property {string} hash
 */

/**
 * @typedef {Object} PartialSessionState
 * @property {'disabled'|'enabled'} [activation]
 * @property {'locked'|'unlocked'} [status]
 * @property {string} [hash]
 */

export default class State {
  /**
   * @type {Window}
   */
  #windowObj;

  /**
   * @type {SessionState}
   */
  #state;

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
  #key;

  /**
   * @type {SessionState
   */
  #initialState;

  /**
   *
   * @param {Window} windowObj - The global window object.
   * @param {Storage} storage - The storrage mechanism (e.g., localStorage).
   * @param {Logger} logger - The logger.
   * @param {SessionState} initialState - The initial state to be stored.
   * @param {string} key - The key under which the state is stored.
   */
  constructor(windowObj, storage, logger, initialState, key) {
    this.#windowObj = windowObj;
    this.#logger = logger;
    this.#storage = storage;
    this.#initialState = initialState;
    this.#key = key;

    const saved = this.#storage.getItem(this.#key);
    this.#state = saved ? { ...initialState, ...saved } : initialState;

    storage.notifyOnUpdate(
      this.#key,
      (updates) => {
        if (updates !== null) {
          this.#state = { ...this.#state, ...updates };
          this.#logger.info(`State updated: ${JSON.stringify(this.#state)}`);
        }
      },
    );
  }

  /**
   * Returns the current state.
   * @returns {SessionState}
   */
  get() {
    return { ...this.#state };
  }

  /**
   *
   * @param {PartialSessionState} updates
   */
  update(updates) {
    this.#state = { ...this.#state, ...updates };
    this.#storage.setItem(this.#key, this.#state);
    this.#logger.info(`State updated (incl. storage): ${JSON.stringify(this.#state)}`);
  }
}
